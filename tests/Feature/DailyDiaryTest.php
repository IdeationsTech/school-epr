<?php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DailyDiaryTest extends TestCase
{
    use RefreshDatabase;
    protected function beforeRefreshingDatabase(): void { if (!str_ends_with((string) config('database.connections.mysql.database'), '_test')) throw new \RuntimeException('Dedicated test DB required.'); }
    protected function setUp(): void { parent::setUp(); \Carbon\Carbon::setTestNow('2026-10-17 09:00:00'); }
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }
    private function fixture(string $code = 'D', string $role = 'owner'): array
    {
        if (!DB::table('roles')->exists()) $this->seed(DatabaseSeeder::class);
        $sid = DB::table('schools')->insertGetId(['name' => 'Diary '.$code, 'code' => $code, 'status' => 'active', 'timezone' => 'Asia/Karachi', 'currency' => 'PKR']);
        $cid = DB::table('campuses')->insertGetId(['school_id' => $sid, 'name' => 'Main', 'code' => 'MAIN', 'status' => 'active']);
        $user = User::factory()->create(); $mid = DB::table('school_memberships')->insertGetId(['school_id' => $sid, 'user_id' => $user->id, 'status' => 'active']);
        $rid = DB::table('roles')->where('name', $role)->value('id'); DB::table('membership_roles')->insert(['school_id' => $sid, 'membership_id' => $mid, 'role_id' => $rid]);
        DB::table('membership_campuses')->insert(['school_id' => $sid, 'membership_id' => $mid, 'campus_id' => $cid]);
        $session = DB::table('academic_sessions')->insertGetId(['school_id' => $sid, 'name' => '2026-27', 'starts_on' => '2026-04-01', 'ends_on' => '2027-03-31', 'status' => 'active']);
        $class = DB::table('classes')->insertGetId(['school_id' => $sid, 'name' => 'Class 1', 'position' => 1]);
        $section = DB::table('sections')->insertGetId(['school_id' => $sid, 'campus_id' => $cid, 'academic_session_id' => $session, 'class_id' => $class, 'name' => 'A']);
        $math = DB::table('subjects')->insertGetId(['school_id' => $sid, 'name' => 'Mathematics', 'code' => 'MATH']);
        $english = DB::table('subjects')->insertGetId(['school_id' => $sid, 'name' => 'English', 'code' => 'ENG']);
        foreach ([$math, $english] as $subject) DB::table('section_subjects')->insert(['school_id' => $sid, 'section_id' => $section, 'subject_id' => $subject, 'required_for_diary' => true]);
        $staff = DB::table('staff_profiles')->insertGetId(['school_id' => $sid, 'membership_id' => $mid, 'employee_no' => 'T1', 'status' => 'active']);
        DB::table('teacher_assignments')->insert(['school_id' => $sid, 'staff_profile_id' => $staff, 'section_id' => $section, 'subject_id' => $math, 'is_class_teacher' => true]);
        return compact('sid', 'cid', 'user', 'mid', 'rid', 'session', 'class', 'section', 'math', 'english', 'staff');
    }
    private function login(array $f): void { $this->actingAs($f['user'])->withSession(['school_id' => $f['sid']]); }
    private function day(array $f, string $suffix = '') { return $this->getJson('/app-api/diary/day?section_id='.$f['section'].'&date=2026-10-17'.$suffix); }
    private function payload(array $f, string $action = 'save', ?array $items = null): array
    {
        $row = DB::table('diaries')->where('school_id', $f['sid'])->where('section_id', $f['section'])->orderByDesc('revision')->first();
        return ['section_id' => $f['section'], 'date' => '2026-10-17', 'diary_id' => $row?->id ?? 0, 'lock_version' => $row?->lock_version ?? 0, 'action' => $action, 'items' => $items ?? [['subject_id' => $f['math'], 'classwork' => 'Fractions', 'homework' => 'Exercise 3', 'no_homework' => false], ['subject_id' => $f['english'], 'classwork' => 'Reading', 'homework' => null, 'no_homework' => true]]];
    }
    private function publish(array $f): int
    {
        foreach (['save', 'submit', 'publish'] as $action) $this->postJson('/app-api/diary/day', $this->payload($f, $action))->assertOk();
        return (int) DB::table('diaries')->where('school_id', $f['sid'])->orderByDesc('revision')->value('id');
    }
    public function test_complete_diary_review_publication_archives_names_text_and_subject_content(): void
    {
        $f = $this->fixture(); $this->login($f); $this->day($f)->assertOk()->assertJsonPath('diary', null)->assertJsonCount(2, 'completeness.missing');
        $id = $this->publish($f); $snapshot = DB::table('diaries')->where('id', $id)->value('snapshot');
        DB::table('schools')->where('id', $f['sid'])->update(['name' => 'Renamed School']); DB::table('subjects')->where('id', $f['math'])->update(['name' => 'Renamed Subject']);
        $this->day($f)->assertJsonPath('diary.status', 'published')->assertJsonPath('diary.snapshot.school_name', 'Diary D')->assertJsonPath('diary.snapshot.items.1.subject_name', 'Mathematics')->assertJsonPath('can_write', false)->assertJsonPath('can_share', true);
        $this->assertSame($snapshot, DB::table('diaries')->where('id', $id)->value('snapshot')); $this->assertDatabaseCount('diary_shares', 0);
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertUnprocessable();
        $this->getJson('/app-api/diary/history?section_id='.$f['section'].'&date=2026-10-17')->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('data.0.action', 'diary.publish');
        $this->artisan('database:verify')->assertSuccessful();
    }
    public function test_missing_homework_and_incomplete_optional_entries_cannot_be_reviewed_or_published(): void
    {
        $f = $this->fixture(); $this->login($f);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'classwork' => 'Chapter 1', 'no_homework' => false]]))->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertUnprocessable();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'publish'))->assertUnprocessable();
        $d = $this->payload($f); $d['items'][0]['no_homework'] = true; $this->postJson('/app-api/diary/day', $d)->assertUnprocessable();
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk();
        DB::table('section_subjects')->where('subject_id', $f['math'])->update(['required_for_diary' => false]);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'classwork' => 'Incomplete optional', 'no_homework' => false]]))->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertUnprocessable();
    }
    public function test_teacher_can_patch_only_assigned_subject_and_class_teacher_can_submit_but_not_publish(): void
    {
        $f = $this->fixture('T', 'teacher'); $this->login($f);
        $this->getJson('/app-api/diary/setup')->assertJsonCount(1, 'sections');
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertForbidden(); $this->assertDatabaseCount('diaries', 0);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'homework' => 'Teacher work', 'no_homework' => false]]))->assertOk();
        $id = DB::table('diaries')->value('id'); DB::table('diary_items')->insert(['school_id' => $f['sid'], 'diary_id' => $id, 'subject_id' => $f['english'], 'author_membership_id' => $f['mid'], 'no_homework' => true, 'status' => 'ready']);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'publish'))->assertForbidden();
        $d = $this->payload($f, 'return'); $d['reason'] = 'Teacher tries to unlock'; $this->postJson('/app-api/diary/day', $d)->assertForbidden();
        DB::table('staff_profiles')->where('id', $f['staff'])->update(['status' => 'inactive']); $this->day($f)->assertNotFound();
    }
    public function test_review_return_requires_feedback_and_edits_are_locked_until_returned(): void
    {
        $f = $this->fixture(); $this->login($f); $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertOk(); $this->postJson('/app-api/diary/day', $this->payload($f))->assertUnprocessable();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'return'))->assertUnprocessable();
        $d = $this->payload($f, 'return'); $d['reason'] = 'Add the exercise page number'; $this->postJson('/app-api/diary/day', $d)->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk(); $this->day($f)->assertJsonPath('diary.status', 'draft');
    }
    public function test_stale_tabs_cannot_replace_subject_work_or_review_a_newer_change(): void
    {
        $f = $this->fixture(); $this->login($f); $first = $this->payload($f); $this->postJson('/app-api/diary/day', $first)->assertOk();
        $this->postJson('/app-api/diary/day', $first)->assertUnprocessable(); $old = $this->payload($f, 'submit');
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'homework' => 'Newer homework', 'no_homework' => false]]))->assertOk();
        $this->postJson('/app-api/diary/day', $old)->assertUnprocessable();
        $this->assertDatabaseHas('diary_items', ['subject_id' => $f['math'], 'homework' => 'Newer homework']); $this->assertDatabaseCount('diary_items', 2); $this->assertDatabaseCount('diaries', 1);
    }
    public function test_correction_creates_new_revision_keeps_old_snapshot_and_does_not_inherit_sharing(): void
    {
        $f = $this->fixture(); $this->login($f); $id = $this->publish($f); $snapshot = DB::table('diaries')->where('id', $id)->value('snapshot');
        $share = ['diary_id' => $id, 'confirmed' => true, 'note' => 'Parents group A']; $this->postJson('/app-api/diary/share', $share)->assertOk();
        $this->postJson('/app-api/diary/share', $share)->assertOk(); $this->assertDatabaseCount('diary_shares', 1);
        $d = $this->payload($f, 'revise'); $this->postJson('/app-api/diary/day', $d)->assertUnprocessable(); $d['reason'] = 'Correct the exercise number'; $this->postJson('/app-api/diary/day', $d)->assertOk();
        $this->day($f)->assertJsonPath('diary.revision', 2)->assertJsonCount(0, 'shares')->assertJsonPath('can_share', false);
        $this->postJson('/app-api/diary/share', $share)->assertUnprocessable();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'homework' => 'Exercise 4', 'no_homework' => false]]))->assertOk();
        foreach (['submit', 'publish'] as $a) $this->postJson('/app-api/diary/day', $this->payload($f, $a))->assertOk();
        $this->day($f, '&revision=1')->assertJsonPath('latest', false)->assertJsonPath('diary.snapshot.items.1.homework', 'Exercise 3')->assertJsonCount(1, 'shares')->assertJsonPath('can_share', false);
        $this->day($f)->assertJsonPath('diary.snapshot.items.1.homework', 'Exercise 4')->assertJsonCount(0, 'shares'); $this->assertSame($snapshot, DB::table('diaries')->where('id', $id)->value('snapshot'));
    }
    public function test_school_campus_and_read_only_permissions_are_enforced(): void
    {
        $a = $this->fixture('A', 'teacher'); $b = $this->fixture('B'); $this->login($a);
        $this->day($b)->assertNotFound(); $this->postJson('/app-api/diary/day', $this->payload($b))->assertNotFound();
        $this->getJson('/app-api/diary/history?section_id='.$b['section'].'&date=2026-10-17')->assertNotFound();
        DB::table('school_role_permissions')->insert(['school_id' => $a['sid'], 'role_id' => $a['rid'], 'permission_id' => DB::table('permissions')->where('name', 'diary.write')->value('id'), 'enabled' => false]);
        $this->getJson('/app-api/diary/setup')->assertForbidden();
        DB::table('school_role_permissions')->insert(['school_id' => $a['sid'], 'role_id' => $a['rid'], 'permission_id' => DB::table('permissions')->where('name', 'diary.view')->value('id'), 'enabled' => true]);
        $this->day($a)->assertOk()->assertJsonPath('can_write', false); $this->postJson('/app-api/diary/day', $this->payload($a))->assertForbidden();
        DB::table('membership_campuses')->where('membership_id', $a['mid'])->delete(); $this->day($a)->assertNotFound();
    }
    public function test_manual_sharing_requires_confirmation_permission_and_published_latest_revision(): void
    {
        $f = $this->fixture('S', 'principal'); $this->login($f); $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk(); $id = DB::table('diaries')->value('id');
        $this->postJson('/app-api/diary/share', ['diary_id' => $id, 'confirmed' => true])->assertUnprocessable();
        foreach (['submit', 'publish'] as $a) $this->postJson('/app-api/diary/day', $this->payload($f, $a))->assertOk();
        $this->postJson('/app-api/diary/share', ['diary_id' => $id, 'confirmed' => false])->assertUnprocessable();
        DB::table('school_role_permissions')->insert(['school_id' => $f['sid'], 'role_id' => $f['rid'], 'permission_id' => DB::table('permissions')->where('name', 'reports.confirm_share')->value('id'), 'enabled' => false]);
        $this->day($f)->assertJsonPath('can_share', false); $this->postJson('/app-api/diary/share', ['diary_id' => $id, 'confirmed' => true])->assertForbidden(); $this->assertDatabaseCount('diary_shares', 0);
    }
    public function test_dates_archived_setup_legacy_publications_and_removed_offerings_are_safe(): void
    {
        $f = $this->fixture(); $this->login($f); $d = $this->payload($f); $d['date'] = '2026-10-18'; $this->postJson('/app-api/diary/day', $d)->assertUnprocessable(); $d['date'] = '2026-03-31'; $this->postJson('/app-api/diary/day', $d)->assertUnprocessable();
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk(); DB::table('teacher_assignments')->where('school_id', $f['sid'])->delete(); DB::table('section_subjects')->where('subject_id', $f['math'])->delete();
        $this->day($f)->assertJsonCount(1, 'completeness.orphan_items'); $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertUnprocessable();
        DB::table('academic_sessions')->where('id', $f['session'])->update(['status' => 'closed']); $this->day($f)->assertJsonPath('active_setup', false); $this->postJson('/app-api/diary/day', $this->payload($f))->assertUnprocessable();
        DB::table('academic_sessions')->where('id', $f['session'])->update(['status' => 'active']); DB::table('diaries')->update(['status' => 'published', 'snapshot' => null]);
        $this->day($f)->assertJsonPath('can_share', false); $d = $this->payload($f, 'revise'); $d['reason'] = 'Legacy diary review'; $this->postJson('/app-api/diary/day', $d)->assertUnprocessable();
    }
    public function test_guests_provider_accounts_and_license_modules_cannot_bypass_diary_scope(): void
    {
        $this->getJson('/app-api/diary/setup')->assertUnauthorized(); $f = $this->fixture(); $this->login($f);
        $id = $this->publish($f); $license = \App\Services\LicenseService::ensure($f['sid']);
        DB::table('subscriptions')->insert(['school_id' => $f['sid'], 'plan_id' => DB::table('plans')->value('id'), 'license_id' => $license->id, 'kind' => 'paid', 'student_limit' => 1000, 'campus_limit' => 2, 'modules' => json_encode(['daily_diary']), 'package_name' => 'Diary only', 'starts_on' => '2026-01-01', 'ends_on' => '2027-12-31', 'price' => '100', 'status' => 'active']);
        $this->day($f)->assertOk()->assertJsonPath('can_share', false); $this->postJson('/app-api/diary/share', ['diary_id' => $id, 'confirmed' => true])->assertForbidden();
        DB::table('subscriptions')->update(['modules' => json_encode(['fees'])]); $this->getJson('/app-api/diary/setup')->assertForbidden();
        $provider = User::factory()->create(); DB::table('platform_admins')->insert(['user_id' => $provider->id, 'active' => true]);
        $this->actingAs($provider)->withSession(['school_id' => $f['sid']])->getJson('/app-api/diary/setup')->assertForbidden();
    }
    public function test_subject_patches_preserve_other_entries_and_zero_text_is_not_treated_as_empty(): void
    {
        $f = $this->fixture(); $this->login($f);
        $this->postJson('/app-api/diary/day', $this->payload($f))->assertOk();
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'classwork' => '0', 'homework' => '0', 'no_homework' => false]]))->assertOk();
        $this->assertDatabaseHas('diary_items', ['subject_id' => $f['math'], 'classwork' => '0', 'homework' => '0', 'status' => 'ready']);
        $this->assertDatabaseHas('diary_items', ['subject_id' => $f['english'], 'no_homework' => true]);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'save', [['subject_id' => $f['math'], 'classwork' => '', 'homework' => '', 'no_homework' => false]]))->assertOk();
        $this->assertDatabaseMissing('diary_items', ['subject_id' => $f['math']]); $this->assertDatabaseCount('diary_items', 1);
        $this->postJson('/app-api/diary/day', $this->payload($f, 'submit'))->assertUnprocessable();
    }

}
