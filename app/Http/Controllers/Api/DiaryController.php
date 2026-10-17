<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DailyDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiaryController extends Controller
{
    public function __construct(private DailyDiary $diary) {}
    public function setup(Request $r) { $this->diary->context($r); return response()->json(['today' => $this->diary->today(), 'sections' => $this->diary->sections($r)->orderByDesc('y.starts_on')->orderBy('c.position')->orderBy('s.name')->get()]); }
    public function day(Request $r) {
        $d = $r->validate(['section_id' => 'required|integer', 'date' => 'required|date_format:Y-m-d', 'revision' => 'nullable|integer|min:1']);
        return DB::transaction(fn () => response()->json($this->diary->day($r, $d['section_id'], $d['date'], $d['revision'] ?? null)));
    }
    public function save(Request $r) {
        $this->diary->context($r);
        $d = $r->validate(['section_id' => 'required|integer', 'date' => 'required|date_format:Y-m-d', 'diary_id' => 'required|integer|min:0', 'lock_version' => 'required|integer|min:0', 'action' => ['required', Rule::in(['save', 'submit', 'return', 'publish', 'revise'])], 'reason' => 'nullable|string|max:1000', 'items' => 'sometimes|array|max:100', 'items.*.subject_id' => 'required|integer|distinct', 'items.*.classwork' => 'nullable|string|max:4000', 'items.*.homework' => 'nullable|string|max:4000', 'items.*.no_homework' => 'required|boolean']);
        return response()->json($this->diary->mutate($r, $d));
    }
    public function share(Request $r) {
        $this->diary->context($r);
        $d = $r->validate(['diary_id' => 'required|integer', 'confirmed' => 'required|accepted', 'note' => 'nullable|string|max:300']);
        return response()->json($this->diary->share($r, $d));
    }
    public function overview(Request $r) {
        $d = $r->validate(['date' => 'required|date_format:Y-m-d|before_or_equal:'.$this->diary->today()]);
        return DB::transaction(function () use ($r, $d) {
            $sections = $this->diary->sections($r)->where('y.starts_on', '<=', $d['date'])->where('y.ends_on', '>=', $d['date'])->orderBy('p.name')->orderBy('c.position')->get();
            $rows = [];
            foreach ($sections as $section) {
                $day = $this->diary->day($r, $section->id, $d['date']);
                $rows[] = ['section' => $section, 'status' => $day['diary']?->status ?? 'not_started', 'revision' => $day['diary']?->revision ?? null, 'missing' => $day['completeness']['missing'], 'ready' => $day['completeness']['ready'], 'shares' => $day['shares']->count()];
            }
            return response()->json(['date' => $d['date'], 'rows' => $rows]);
        });
    }
    public function history(Request $r) {
        $d = $r->validate(['section_id' => 'required|integer', 'date' => 'required|date_format:Y-m-d', 'page' => 'nullable|integer|min:1']);
        [$sid] = $this->diary->section($r, $d['section_id'], $d['date']);
        $ids = DB::table('diaries')->where('school_id', $sid)->where('section_id', $d['section_id'])->where('diary_date', $d['date'])->pluck('id');
        $rows = DB::table('audit_logs as a')->leftJoin('school_memberships as m', 'm.id', '=', 'a.actor_membership_id')->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where('a.school_id', $sid)->where('a.target_type', 'diaries')->whereIn('a.target_id', $ids)->where('a.action', 'like', 'diary.%')->orderByDesc('a.id')->paginate(10, ['a.id', 'a.action', 'a.changes', 'a.occurred_at', 'u.name as actor_name']);
        foreach ($rows as $row) $row->changes = json_decode($row->changes ?? '{}', true);
        return response()->json($rows);
    }
}
