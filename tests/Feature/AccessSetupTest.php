<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (!str_ends_with((string)config('database.connections.mysql.database'),'_test')) {
            throw new \RuntimeException('Use a dedicated _test database.');
        }
    }

    private function fixture(string $code='A', string $role='owner'): array
    {
        if (!DB::table('roles')->exists()) { $this->seed(DatabaseSeeder::class); }
        $sid=DB::table('schools')->insertGetId(['code'=>$code,'name'=>'School '.$code,'timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);
        $cid=DB::table('campuses')->insertGetId(['school_id'=>$sid,'code'=>'MAIN','name'=>'Main','status'=>'active']);
        $user=User::factory()->create(['email'=>strtolower($code).'@example.test','password'=>'LongPassword123!']);
        $mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$user->id,'status'=>'active']);
        $rid=DB::table('roles')->where('name',$role)->value('id');
        DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$mid,'role_id'=>$rid]);
        DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid]);
        return compact('sid','cid','user','mid','rid');
    }

    private function signIn(array $f): static
    {
        return $this->actingAs($f['user'])->withSession(['school_id'=>$f['sid']]);
    }

    public function test_guest_cannot_read_school_or_users(): void
    {
        $this->getJson('/app-api/bootstrap')->assertUnauthorized();
        $this->getJson('/app-api/members')->assertUnauthorized();
        $this->getJson('/app-api/session')->assertOk()->assertJsonPath('user',null);
    }

    public function test_login_and_logout_with_real_password(): void
    {
        $f=$this->fixture();
        $this->postJson('/app-api/login',['email'=>'a@example.test','password'=>'LongPassword123!'])->assertOk()
            ->assertJsonPath('school_id',$f['sid'])->assertJsonMissingPath('user.password');
        $this->assertAuthenticatedAs($f['user']);
        $this->getJson('/app-api/bootstrap')->assertOk()->assertJsonPath('school.id',$f['sid']);
        $this->postJson('/app-api/logout')->assertOk();
        $this->assertGuest();
        $this->getJson('/app-api/bootstrap')->assertUnauthorized();
    }

    public function test_wrong_password_and_login_rate_limit(): void
    {
        $this->fixture();
        for($i=0;$i<5;$i++) { $this->postJson('/app-api/login',['email'=>'a@example.test','password'=>'wrong'])->assertUnprocessable(); }
        $this->postJson('/app-api/login',['email'=>'a@example.test','password'=>'wrong'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_inactive_school_cannot_login(): void
    {
        $f=$this->fixture();DB::table('schools')->where('id',$f['sid'])->update(['status'=>'inactive']);
        $this->postJson('/app-api/login',['email'=>'a@example.test','password'=>'LongPassword123!'])->assertForbidden();
        $this->assertGuest();
    }

    public function test_teacher_cannot_manage_school_users_or_roles(): void
    {
        $f=$this->fixture('A','teacher');$this->signIn($f);
        $this->getJson('/app-api/bootstrap')->assertOk()->assertJsonPath('is_owner',false);
        $this->putJson('/app-api/school',['name'=>'Hacked'])->assertForbidden();
        $this->getJson('/app-api/members')->assertForbidden();
        $this->getJson('/app-api/roles')->assertForbidden();
        $this->postJson('/app-api/campuses',['name'=>'Unauthorized'])->assertForbidden();
    }

    public function test_cannot_switch_to_another_schools_membership(): void
    {
        $a=$this->fixture('A');$b=$this->fixture('B');$this->signIn($a);
        $this->postJson('/app-api/switch-school',['school_id'=>$b['sid']])->assertForbidden();
        $this->getJson('/app-api/bootstrap')->assertJsonPath('school.id',$a['sid']);
    }

    public function test_authorized_multi_school_switch_clears_old_school_context(): void
    {
        $a=$this->fixture('A');$b=$this->fixture('B');
        DB::table('school_memberships')->insert(['school_id'=>$b['sid'],'user_id'=>$a['user']->id,'status'=>'active']);
        $this->signIn($a)->postJson('/app-api/switch-school',['school_id'=>$b['sid']])->assertOk();
        $this->getJson('/app-api/bootstrap')->assertOk()->assertJsonPath('school.id',$b['sid']);
    }

    public function test_owner_can_save_school_and_campus_with_audit_record(): void
    {
        $f=$this->fixture();$this->signIn($f);
        $this->putJson('/app-api/school',['name'=>'Updated school','timezone'=>'Asia/Karachi','currency'=>'PKR','phone'=>'03001234567','email'=>'office@example.test'])
            ->assertOk()->assertJsonPath('school.name','Updated school');
        $this->postJson('/app-api/campuses',['name'=>'Second Campus','code'=>'SECOND','status'=>'active','address'=>'Test address'])->assertOk();
        $this->assertDatabaseHas('campuses',['school_id'=>$f['sid'],'code'=>'SECOND']);
        $this->assertDatabaseHas('audit_logs',['school_id'=>$f['sid'],'action'=>'campus.created']);
    }

    public function test_campus_ids_are_scoped_and_last_active_campus_is_protected(): void
    {
        $a=$this->fixture('A');$b=$this->fixture('B');$this->signIn($a);
        $payload=['name'=>'Campus','code'=>'MAIN','status'=>'inactive'];
        $this->putJson('/app-api/campuses/'.$b['cid'],$payload)->assertNotFound();
        $this->putJson('/app-api/campuses/'.$a['cid'],$payload)->assertUnprocessable();
    }

    public function test_owner_can_create_a_teacher_with_hashed_password_and_campus_scope(): void
    {
        $f=$this->fixture();$this->signIn($f);
        $rid=DB::table('roles')->where('name','teacher')->value('id');
        $this->postJson('/app-api/members',['name'=>'Teacher','email'=>'teacher@example.test','password'=>'TeacherPassword123!',
            'password_confirmation'=>'TeacherPassword123!','role_ids'=>[$rid],'campus_ids'=>[$f['cid']],'status'=>'active'])->assertOk();
        $user=DB::table('users')->where('email','teacher@example.test')->first();
        $this->assertTrue(Hash::check('TeacherPassword123!',$user->password));
        $this->assertDatabaseHas('school_memberships',['school_id'=>$f['sid'],'user_id'=>$user->id,'status'=>'active']);
    }

    public function test_cross_school_campus_assignment_is_rejected(): void
    {
        $a=$this->fixture('A');$b=$this->fixture('B');$this->signIn($a);
        $this->putJson('/app-api/members/'.$a['mid'],['role_ids'=>[$a['rid']],'campus_ids'=>[$b['cid']],'status'=>'active'])->assertUnprocessable();
        $this->putJson('/app-api/members/'.$b['mid'],['role_ids'=>[$a['rid']],'campus_ids'=>[$a['cid']],'status'=>'active'])->assertNotFound();
    }

    public function test_last_owner_cannot_be_demoted_or_deactivated(): void
    {
        $a=$this->fixture();$this->signIn($a);$teacher=DB::table('roles')->where('name','teacher')->value('id');
        $this->putJson('/app-api/members/'.$a['mid'],['role_ids'=>[$teacher],'campus_ids'=>[$a['cid']],'status'=>'active'])->assertUnprocessable();
        $this->putJson('/app-api/members/'.$a['mid'],['role_ids'=>[$a['rid']],'campus_ids'=>[$a['cid']],'status'=>'inactive'])->assertUnprocessable();
    }

    public function test_permission_customization_is_per_school_and_cannot_grant_owner_admin(): void
    {
        $a=$this->fixture('A');$b=$this->fixture('B');$this->signIn($a);
        $rid=DB::table('roles')->where('name','teacher')->value('id');
        $permission=DB::table('permissions')->where('name','diary.write')->value('id');
        $this->putJson('/app-api/roles/'.$rid,['permission_ids'=>[$permission]])->assertOk();
        $this->assertDatabaseHas('school_role_permissions',['school_id'=>$a['sid'],'role_id'=>$rid,'permission_id'=>$permission,'enabled'=>1]);
        $this->assertDatabaseMissing('school_role_permissions',['school_id'=>$b['sid']]);
        $admin=DB::table('permissions')->where('name','members.manage')->value('id');
        $this->putJson('/app-api/roles/'.$rid,['permission_ids'=>[$admin]])->assertUnprocessable();
        $this->putJson('/app-api/roles/'.$a['rid'],['permission_ids'=>[]])->assertForbidden();
    }

    public function test_revoked_membership_stops_existing_session(): void
    {
        $f=$this->fixture();$this->signIn($f);
        DB::table('school_memberships')->where('id',$f['mid'])->update(['status'=>'inactive']);
        $this->getJson('/app-api/bootstrap')->assertForbidden();
    }

    public function test_setup_command_creates_first_owner_without_fixed_password(): void
    {
        $this->artisan('school:setup')->expectsQuestion('School name','My School')
            ->expectsQuestion('Unique school code (letters, numbers, dash)','MY-SCHOOL')
            ->expectsQuestion('Owner name','Owner')->expectsQuestion('Owner email','owner@example.test')
            ->expectsQuestion('Owner password (at least 12 characters)','ChosenPassword123!')
            ->expectsQuestion('Confirm owner password','ChosenPassword123!')->assertSuccessful();
        $this->assertDatabaseHas('schools',['code'=>'MY-SCHOOL']);
        $this->postJson('/app-api/login',['email'=>'owner@example.test','password'=>'ChosenPassword123!'])->assertOk();
    }
}
