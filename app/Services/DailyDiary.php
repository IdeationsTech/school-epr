<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DailyDiary
{
    public function __construct(public SchoolAccess $access) {}

    public function fail(string $message): never
    {
        throw ValidationException::withMessages(['diary' => $message]);
    }

    private function can(Request $r, string $permission): bool
    {
        $key = 'diary.permission.'.$permission;
        if (!$r->attributes->has($key)) $r->attributes->set($key, $this->access->can($r, $permission));
        return $r->attributes->get($key);
    }

    public function today(): string { return now('Asia/Karachi')->toDateString(); }

    public function context(Request $r): array
    {
        if ($r->attributes->has('diary.context')) return $r->attributes->get('diary.context');
        abort_unless($this->can($r, 'diary.write') || $this->can($r, 'diary.publish') || $this->can($r, 'diary.view'), 403);
        $m = $this->access->membership($r);
        $period = LicenseService::period($m->school_id);
        if ($period) abort_unless(in_array('daily_diary', json_decode($period->modules, true) ?? [], true), 403, 'Daily diary is not included in this school license.');
        $campuses = in_array('owner', $this->access->roles($m), true)
            ? DB::table('campuses')->where('school_id', $m->school_id)->pluck('id')->all()
            : array_map(fn ($c) => $c->id, $this->access->campuses($m));
        $context = [(int) $m->school_id, $m, $campuses];
        $r->attributes->set('diary.context', $context);
        return $context;
    }

    public function assignments(object $m)
    {
        return DB::table('teacher_assignments as a')->join('staff_profiles as t', 't.id', '=', 'a.staff_profile_id')
            ->where('a.school_id', $m->school_id)->where('t.membership_id', $m->id)->where('t.status', 'active');
    }

    public function sections(Request $r)
    {
        [$sid, $m, $campuses] = $this->context($r);
        $q = DB::table('sections as s')->join('classes as c', 'c.id', '=', 's.class_id')
            ->join('campuses as p', 'p.id', '=', 's.campus_id')->join('academic_sessions as y', 'y.id', '=', 's.academic_session_id')
            ->where('s.school_id', $sid)->whereIn('s.campus_id', $campuses);
        if (!$this->can($r, 'diary.publish')) $q->whereIn('s.id', $this->assignments($m)->select('a.section_id'));
        return $q->select('s.id', 's.name', 's.class_id', 's.campus_id', 'c.name as class_name', 'p.name as campus_name', 'p.status as campus_status', 'y.name as session_name', 'y.starts_on as session_start', 'y.ends_on as session_end', 'y.status as session_status');
    }

    public function section(Request $r, int $id, string $date): array
    {
        [$sid, $m] = $this->context($r);
        $section = $this->sections($r)->where('s.id', $id)->first() ?? abort(404);
        if ($date > $this->today() || $date < $section->session_start || $date > $section->session_end) $this->fail('Choose a Pakistan date inside this academic session and through today.');
        return [$sid, $m, $section];
    }

    public function subjects(Request $r, object $section, object $m)
    {
        $publisher = $this->can($r, 'diary.publish');
        $assigned = $this->assignments($m)->where('a.section_id', $section->id)->pluck('a.subject_id')->all();
        return DB::table('section_subjects as o')->join('subjects as s', 's.id', '=', 'o.subject_id')
            ->where('o.school_id', $m->school_id)->where('o.section_id', $section->id)->orderBy('s.name')
            ->get(['s.id', 's.name', 's.code', 'o.required_for_diary'])->map(function ($s) use ($publisher, $assigned, $r) {
                $s->can_write = $this->can($r, 'diary.write') && ($publisher || in_array($s->id, $assigned));
                return $s;
            });
    }

    public function items(int $sid, int $diaryId)
    {
        return DB::table('diary_items as i')->join('subjects as s', 's.id', '=', 'i.subject_id')
            ->join('school_memberships as m', 'm.id', '=', 'i.author_membership_id')->join('users as u', 'u.id', '=', 'm.user_id')
            ->where('i.school_id', $sid)->where('i.diary_id', $diaryId)->orderBy('s.name')
            ->get(['i.subject_id', 's.name as subject_name', 's.code as subject_code', 'i.classwork', 'i.homework', 'i.no_homework', 'i.status', 'i.author_membership_id', 'u.name as author_name']);
    }

    public function completeness($subjects, $items): array
    {
        $map = $items->keyBy('subject_id');
        $ready = fn ($i) => $i && ((bool) $i->no_homework || trim($i->homework ?? '') !== '');
        return [
            'missing' => $subjects->filter(fn ($s) => $s->required_for_diary && !$ready($map->get($s->id)))->pluck('name')->values()->all(),
            'incomplete' => $items->filter(fn ($i) => !$ready($i))->pluck('subject_name')->values()->all(),
            'orphan_items' => $items->whereNotIn('subject_id', $subjects->pluck('id'))->pluck('subject_name')->values()->all(),
            'ready' => $items->filter($ready)->count(),
        ];
    }

    public function shareAllowed(Request $r): bool
    {
        $m = $this->access->membership($r); $period = LicenseService::period($m->school_id);
        return $this->can($r, 'reports.confirm_share') && (!$period || in_array('manual_whatsapp_sharing', json_decode($period->modules, true) ?? [], true));
    }

    public function day(Request $r, int $sectionId, string $date, ?int $revision = null): array
    {
        [$sid, $m, $section] = $this->section($r, $sectionId, $date);
        $revisions = DB::table('diaries')->where('school_id', $sid)->where('section_id', $sectionId)->where('diary_date', $date)->orderByDesc('revision')->get(['id', 'revision', 'status', 'published_at', 'lock_version']);
        $selected = $revision ? $revisions->firstWhere('revision', $revision) : $revisions->first();
        if ($revision && !$selected) abort(404);
        $diary = $selected ? DB::table('diaries')->where('school_id', $sid)->where('id', $selected->id)->first() : null;
        if ($diary) $diary->snapshot = $diary->snapshot ? json_decode($diary->snapshot, true) : null;
        $subjects = $this->subjects($r, $section, $m);
        $items = $diary ? $this->items($sid, $diary->id) : collect();
        $active = $section->session_status === 'active' && $section->campus_status === 'active';
        $latest = !$diary || $diary->id === $revisions->first()?->id;
        $classTeacher = $this->assignments($m)->where('a.section_id', $sectionId)->where('a.is_class_teacher', true)->exists();
        $publish = $this->can($r, 'diary.publish');
        $shares = $diary ? DB::table('diary_shares as s')->join('school_memberships as m', 'm.id', '=', 's.confirmed_by')->join('users as u', 'u.id', '=', 'm.user_id')->where('s.school_id', $sid)->where('s.diary_id', $diary->id)->orderByDesc('s.id')->get(['s.id', 's.confirmed_at', 's.note', 'u.name as confirmed_by_name']) : collect();
        return ['section' => $section, 'date' => $date, 'diary' => $diary, 'revisions' => $revisions, 'subjects' => $subjects, 'items' => $items,
            'completeness' => $this->completeness($subjects, $items), 'latest' => $latest, 'active_setup' => $active,
            'can_write' => $active && $latest && (!$diary || $diary->status === 'draft') && $subjects->contains('can_write', true),
            'can_submit' => $active && $latest && ($publish || ($classTeacher && $this->can($r, 'diary.write'))),
            'can_publish' => $active && $latest && $publish, 'can_share' => $latest && $diary?->status === 'published' && (bool) $diary?->snapshot && $this->shareAllowed($r),
            'shares' => $shares, 'text' => $diary?->snapshot ? $this->text($diary->snapshot) : null];
    }

    public function text(array $s): string
    {
        $lines = [$s['school_name'], 'DAILY DIARY · '.$s['date'].' · Revision '.$s['revision'], $s['section_label'], ''];
        foreach ($s['items'] as $i) {
            $lines[] = $i['subject_name'];
            if ($i['classwork'] !== null && $i['classwork'] !== '') $lines[] = 'Classwork: '.$i['classwork'];
            $lines[] = 'Homework: '.($i['no_homework'] ? 'No homework' : $i['homework']); $lines[] = '';
        }
        return implode("\n", $lines);
    }

    public function mutate(Request $r, array $d): array
    {
        return DB::transaction(function () use ($r, $d) {
            [$sid] = $this->context($r);
            DB::table('schools')->where('id', $sid)->lockForUpdate()->first();
            [$sid, $m, $section] = $this->section($r, (int) $d['section_id'], $d['date']);
            if ($section->session_status !== 'active' || $section->campus_status !== 'active') $this->fail('This campus/session is archived. Its diary history is read-only.');
            $diary = DB::table('diaries')->where('school_id', $sid)->where('section_id', $section->id)->where('diary_date', $d['date'])->orderByDesc('revision')->lockForUpdate()->first();
            if ((int) ($diary->id ?? 0) !== (int) $d['diary_id'] || (int) ($diary->lock_version ?? 0) !== (int) $d['lock_version']) $this->fail('A newer diary change exists. Reload before saving; the newer work was retained.');
            $action = $d['action']; $publisher = $this->can($r, 'diary.publish');
            $reason = trim($d['reason'] ?? '');
            if (in_array($action, ['publish', 'return', 'revise'], true)) abort_unless($publisher, 403);
            if (in_array($action, ['return', 'revise'], true) && mb_strlen($reason) < 5) $this->fail('Enter a review/correction reason of at least five characters.');
            $before = $diary ? $this->items($sid, $diary->id)->all() : [];
            if ($action === 'revise') {
                if (!$diary || $diary->status !== 'published' || !$diary->snapshot) $this->fail('Only an archived published diary can start a correction revision.');
                $snapshot = json_decode($diary->snapshot, true);
                // Copy the actual published snapshot, not mutable reference names/content.
                $new = DB::table('diaries')->insertGetId(['school_id' => $sid, 'section_id' => $section->id, 'diary_date' => $d['date'], 'revision' => $diary->revision + 1, 'status' => 'draft', 'supersedes_id' => $diary->id, 'lock_version' => 1, 'created_at' => now(), 'updated_at' => now()]);
                foreach ($snapshot['items'] as $i) DB::table('diary_items')->insert(['school_id' => $sid, 'diary_id' => $new, 'subject_id' => $i['subject_id'], 'author_membership_id' => $i['author_membership_id'], 'classwork' => $i['classwork'], 'homework' => $i['homework'], 'no_homework' => $i['no_homework'], 'status' => 'ready', 'created_at' => now(), 'updated_at' => now()]);
                $this->access->audit($r, 'diary.revision_created', 'diaries', $new, ['supersedes_id' => $diary->id, 'revision' => $diary->revision + 1, 'reason' => $reason]);
                return ['id' => $new, 'message' => 'Correction revision created. Review and publish it before sharing again.'];
            }
            $subjects = $this->subjects($r, $section, $m);
            if ($action === 'save') {
                abort_unless($this->can($r, 'diary.write'), 403);
                if ($diary && $diary->status !== 'draft') $this->fail('This diary is locked for review or already published. Request changes or create a correction revision.');
                foreach ($d['items'] ?? [] as $i) {
                    $subject = $subjects->firstWhere('id', (int) $i['subject_id']);
                    abort_unless($subject && $subject->can_write, 403, 'You may write only your assigned diary subjects.');
                    if ($i['no_homework'] && trim($i['homework'] ?? '') !== '') $this->fail('Choose no homework or enter homework, not both.');
                }
                if (!$diary) {
                    $id = DB::table('diaries')->insertGetId(['school_id' => $sid, 'section_id' => $section->id, 'diary_date' => $d['date'], 'revision' => 1, 'status' => 'draft', 'lock_version' => 0, 'created_at' => now(), 'updated_at' => now()]);
                    $diary = DB::table('diaries')->where('id', $id)->first();
                }
                foreach ($d['items'] ?? [] as $i) {
                    $cw = trim($i['classwork'] ?? ''); $hw = trim($i['homework'] ?? '');
                    $key = ['school_id' => $sid, 'diary_id' => $diary->id, 'subject_id' => $i['subject_id']];
                    if ($cw === '' && $hw === '' && !$i['no_homework']) DB::table('diary_items')->where($key)->delete();
                    else DB::table('diary_items')->updateOrInsert($key, ['author_membership_id' => $m->id, 'classwork' => $cw === '' ? null : $cw, 'homework' => $hw === '' ? null : $hw, 'no_homework' => $i['no_homework'], 'status' => ($hw !== '' || $i['no_homework']) ? 'ready' : 'draft', 'updated_at' => now()]);
                }
                $message = 'Diary draft saved.';
            } else {
                if (!$diary) $this->fail('Save a diary draft first.');
                if ($action === 'submit') {
                    $classTeacher = $this->assignments($m)->where('a.section_id', $section->id)->where('a.is_class_teacher', true)->exists();
                    abort_unless($publisher || ($classTeacher && $this->can($r, 'diary.write')), 403);
                    if ($diary->status !== 'draft') $this->fail('Only a draft diary can be submitted for review.');
                } elseif ($diary->status !== 'in_review') $this->fail('Submit this diary for coordinator review first.');
                if (in_array($action, ['submit', 'publish'], true)) {
                    $items = $this->items($sid, $diary->id); $complete = $this->completeness($subjects, $items);
                    if (!$complete['ready'] || $complete['missing'] || $complete['incomplete'] || $complete['orphan_items']) $this->fail('Complete all required subjects; explicitly choose no homework where applicable. Resolve removed subject entries before review.');
                }
                $values = ['status' => match ($action) { 'submit' => 'in_review', 'return' => 'draft', 'publish' => 'published' }];
                if ($action === 'publish') {
                    $school = DB::table('schools')->where('id', $sid)->first();
                    $snapshot = ['school_name' => $school->name, 'school_code' => $school->code, 'date' => $d['date'], 'revision' => $diary->revision, 'section_label' => $section->class_name.' · '.$section->name.' · '.$section->campus_name.' · '.$section->session_name, 'items' => $items->map(fn ($i) => (array) $i)->all()];
                    $values += ['snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE), 'published_by' => $m->id, 'published_at' => now()];
                }
                DB::table('diaries')->where('school_id', $sid)->where('id', $diary->id)->update($values);
                $message = match ($action) { 'submit' => 'Diary submitted for review.', 'return' => 'Diary returned for changes.', 'publish' => 'Diary published. Preview it before sharing.' };
            }
            DB::table('diaries')->where('school_id', $sid)->where('id', $diary->id)->update(['lock_version' => $diary->lock_version + 1, 'updated_at' => now()]);
            $this->access->audit($r, 'diary.'.$action, 'diaries', $diary->id, ['revision' => $diary->revision, 'reason' => $reason ?: null, 'before_status' => $diary->status, 'before' => $before, 'after' => $this->items($sid, $diary->id)->all()]);
            return ['id' => $diary->id, 'message' => $message];
        });
    }

    public function share(Request $r, array $d): array
    {
        return DB::transaction(function () use ($r, $d) {
            [$sid, $m] = $this->context($r);
            DB::table('schools')->where('id', $sid)->lockForUpdate()->first();
            $diary = DB::table('diaries')->where('school_id', $sid)->where('id', $d['diary_id'])->first() ?? abort(404);
            $this->section($r, $diary->section_id, $diary->diary_date);
            abort_unless($this->shareAllowed($r), 403);
            if ($diary->status !== 'published' || !$diary->snapshot || DB::table('diaries')->where('school_id', $sid)->where('section_id', $diary->section_id)->where('diary_date', $diary->diary_date)->where('revision', '>', $diary->revision)->exists()) $this->fail('Share only the latest published diary. An older revision cannot be confirmed as current.');
            $note = trim($d['note'] ?? '') ?: null;
            $existing = DB::table('diary_shares')->where('school_id', $sid)->where('diary_id', $diary->id)->where('confirmed_by', $m->id)->where('note', $note)->first();
            if (!$existing) {
                $id = DB::table('diary_shares')->insertGetId(['school_id' => $sid, 'diary_id' => $diary->id, 'confirmed_by' => $m->id, 'confirmed_at' => now(), 'channel' => 'whatsapp_group', 'note' => $note, 'created_at' => now(), 'updated_at' => now()]);
                $this->access->audit($r, 'diary.share_confirmed', 'diaries', $diary->id, ['share_id' => $id, 'revision' => $diary->revision, 'note' => $note, 'meaning' => 'Staff-confirmed manual sharing; not delivery/read confirmation.']);
            }
            return ['message' => 'WhatsApp group sharing confirmed by staff.'];
        });
    }
}
