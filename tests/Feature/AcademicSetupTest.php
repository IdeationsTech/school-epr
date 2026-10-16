<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class AcademicSetupTest extends TestCase
{
 use RefreshDatabase;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 private function fixture(string $code='A',string $role='owner'):array {
  if(!DB::table('roles')->exists())$this->seed(DatabaseSeeder::class);
  $sid=DB::table('schools')->insertGetId(['name'=>'Academic '.$code,'code'=>$code,'status'=>'active','timezone'=>'Asia/Karachi','currency'=>'PKR']);$cid=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main','code'=>'MAIN','status'=>'active']);$user=User::factory()->create();$mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$user->id,'status'=>'active']);$rid=DB::table('roles')->where('name',$role)->value('id');DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$mid,'role_id'=>$rid]);DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid]);return compact('sid','cid','user','mid','rid');
 }
 private function login(array $f):void{$this->actingAs($f['user'])->withSession(['school_id'=>$f['sid']]);}
 private function create(string $resource,array $d):int{return $this->postJson('/app-api/academics/'.$resource,$d)->assertOk()->json('id');}
 private function graph(array $f):array {
  $session=$this->create('sessions',['name'=>'2026-27','starts_on'=>'2026-04-01','ends_on'=>'2027-03-31','status'=>'active']);$class=$this->create('classes',['name'=>'Class 1','position'=>1]);$section=$this->create('sections',['campus_id'=>$f['cid'],'academic_session_id'=>$session,'class_id'=>$class,'name'=>'A','capacity'=>20]);$subject=$this->create('subjects',['name'=>'English','code'=>'ENG']);return compact('session','class','section','subject');
 }
 private function teacher(array $f,string $code):int {
  $u=User::factory()->create();$mid=DB::table('school_memberships')->insertGetId(['school_id'=>$f['sid'],'user_id'=>$u->id,'status'=>'active']);DB::table('membership_roles')->insert(['school_id'=>$f['sid'],'membership_id'=>$mid,'role_id'=>DB::table('roles')->where('name','teacher')->value('id')]);DB::table('membership_campuses')->insert(['school_id'=>$f['sid'],'membership_id'=>$mid,'campus_id'=>$f['cid']]);return $this->create('teachers',['membership_id'=>$mid,'employee_no'=>$code,'status'=>'active']);
 }
 public function test_sessions_terms_current_selection_and_linked_history_are_validated():void {
  $f=$this->fixture();$this->login($f);$g=$this->graph($f);
  $this->postJson('/app-api/academics/sessions',['name'=>'Overlap','starts_on'=>'2027-03-31','ends_on'=>'2027-12-31','status'=>'active'])->assertUnprocessable();
  $this->postJson('/app-api/academics/terms',['academic_session_id'=>$g['session'],'name'=>'Outside','starts_on'=>'2026-03-01','ends_on'=>'2026-05-01','position'=>1])->assertUnprocessable();
  $this->create('terms',['academic_session_id'=>$g['session'],'name'=>'First term','starts_on'=>'2026-04-01','ends_on'=>'2026-08-31','position'=>1]);
  $this->postJson('/app-api/academics/terms',['academic_session_id'=>$g['session'],'name'=>'Overlap term','starts_on'=>'2026-08-31','ends_on'=>'2026-09-30','position'=>2])->assertUnprocessable();
  $this->putJson('/app-api/academics/current',['academic_session_id'=>$g['session']])->assertOk();$this->getJson('/app-api/academics')->assertJsonPath('current_session_id',$g['session']);
  $this->putJson('/app-api/academics/sessions/'.$g['session'],['name'=>'2026-27','starts_on'=>'2026-04-01','ends_on'=>'2027-03-31','status'=>'closed'])->assertUnprocessable();
  $this->deleteJson('/app-api/academics/sessions/'.$g['session'])->assertUnprocessable();
  $this->putJson('/app-api/academics/sessions/'.$g['session'],['name'=>'2026-27','starts_on'=>'2026-03-01','ends_on'=>'2027-03-31','status'=>'active'])->assertUnprocessable();
  $this->assertDatabaseHas('audit_logs',['school_id'=>$f['sid'],'actor_membership_id'=>$f['mid'],'action'=>'academics.current_session']);
 }
 public function test_teacher_assignments_require_offered_subject_active_teacher_and_single_class_teacher():void {
  $f=$this->fixture();$this->login($f);$g=$this->graph($f);$t=$this->teacher($f,'T001');$other=$this->teacher($f,'T002');
  $payload=['staff_profile_id'=>$t,'section_id'=>$g['section'],'subject_id'=>$g['subject'],'is_class_teacher'=>true];
  $this->postJson('/app-api/academics/assignments',$payload)->assertUnprocessable();
  $offering=$this->create('offerings',['section_id'=>$g['section'],'subject_id'=>$g['subject'],'required_for_diary'=>true]);$assignment=$this->create('assignments',$payload);
  $this->postJson('/app-api/academics/assignments',array_replace($payload,['staff_profile_id'=>$other,'subject_id'=>null]))->assertUnprocessable();
  $this->postJson('/app-api/academics/assignments',array_replace($payload,['is_class_teacher'=>false]))->assertUnprocessable();
  $this->putJson('/app-api/members/'.DB::table('staff_profiles')->where('id',$t)->value('membership_id'),['status'=>'inactive','role_ids'=>[DB::table('roles')->where('name','teacher')->value('id')],'campus_ids'=>[$f['cid']]])->assertUnprocessable();
  $this->deleteJson('/app-api/academics/offerings/'.$offering)->assertUnprocessable();$this->deleteJson('/app-api/academics/classes/'.$g['class'])->assertUnprocessable();
  $this->putJson('/app-api/academics/teachers/'.$t,['membership_id'=>DB::table('staff_profiles')->where('id',$t)->value('membership_id'),'employee_no'=>'T001','status'=>'inactive'])->assertUnprocessable();
  $this->deleteJson('/app-api/academics/assignments/'.$assignment)->assertOk();$this->deleteJson('/app-api/academics/offerings/'.$offering)->assertOk();$this->deleteJson('/app-api/academics/subjects/'.$g['subject'])->assertOk();
 }
 public function test_cross_school_ids_and_unassigned_campuses_are_rejected_and_hidden():void {
  $a=$this->fixture('A');$b=$this->fixture('B');$this->login($b);$g=$this->graph($b);$this->login($a);
  $this->getJson('/app-api/academics')->assertJsonCount(0,'sessions')->assertJsonCount(0,'sections');
  $this->putJson('/app-api/academics/current',['academic_session_id'=>$g['session']])->assertNotFound();
  $this->putJson('/app-api/academics/subjects/'.$g['subject'],['name'=>'Changed','code'=>'ENG'])->assertNotFound();
  $this->postJson('/app-api/academics/sections',['campus_id'=>$a['cid'],'academic_session_id'=>$g['session'],'class_id'=>$g['class'],'name'=>'A','capacity'=>20])->assertNotFound();
  $g=$this->graph($a);$principal=$this->fixture('C','principal');
  // A scoped principal in school A, assigned only to its main campus.
  DB::table('membership_roles')->where('school_id',$a['sid'])->where('membership_id',$a['mid'])->update(['role_id'=>DB::table('roles')->where('name','principal')->value('id')]);
  $hidden=DB::table('campuses')->insertGetId(['school_id'=>$a['sid'],'name'=>'Other','code'=>'OTHER','status'=>'active']);
  $this->postJson('/app-api/academics/sections',['campus_id'=>$hidden,'academic_session_id'=>$g['session'],'class_id'=>$g['class'],'name'=>'B'])->assertForbidden();
  $section=DB::table('sections')->insertGetId(['school_id'=>$a['sid'],'campus_id'=>$hidden,'academic_session_id'=>$g['session'],'class_id'=>$g['class'],'name'=>'B']);
  $this->getJson('/app-api/academics')->assertJsonCount(1,'sections');$this->deleteJson('/app-api/academics/sections/'.$section)->assertForbidden();
 }
 public function test_teacher_is_read_only_and_school_permission_overrides_remove_manager_write_access():void {
  $teacher=$this->fixture('TEACH','teacher');$this->login($teacher);$this->getJson('/app-api/academics')->assertOk()->assertJsonPath('can_manage',false);$this->postJson('/app-api/academics/classes',['name'=>'Class 1','position'=>1])->assertForbidden();
  $p=$this->fixture('PRIN','principal');$this->login($p);$this->create('classes',['name'=>'Class 1','position'=>1]);
  DB::table('school_role_permissions')->insert(['school_id'=>$p['sid'],'role_id'=>$p['rid'],'permission_id'=>DB::table('permissions')->where('name','academics.manage')->value('id'),'enabled'=>false]);
  $this->postJson('/app-api/academics/classes',['name'=>'Class 2','position'=>2])->assertForbidden();$this->getJson('/app-api/academics')->assertOk()->assertJsonPath('can_manage',false);
 }
 public function test_teacher_must_have_target_campus_and_teacher_role_and_linked_section_cannot_move():void {
  $f=$this->fixture();$this->login($f);$g=$this->graph($f);$t=$this->teacher($f,'T001');$off=$this->create('offerings',['section_id'=>$g['section'],'subject_id'=>$g['subject'],'required_for_diary'=>true]);
  $other=DB::table('campuses')->insertGetId(['school_id'=>$f['sid'],'name'=>'Branch','code'=>'BRANCH','status'=>'active']);$section=$this->create('sections',['campus_id'=>$other,'academic_session_id'=>$g['session'],'class_id'=>$g['class'],'name'=>'B']);
  $this->postJson('/app-api/academics/assignments',['section_id'=>$section,'staff_profile_id'=>$t,'subject_id'=>null,'is_class_teacher'=>true])->assertUnprocessable();
  $this->postJson('/app-api/academics/teachers',['membership_id'=>$f['mid'],'employee_no'=>'OWNER','status'=>'active'])->assertUnprocessable();
  $this->putJson('/app-api/academics/sections/'.$g['section'],['campus_id'=>$other,'academic_session_id'=>$g['session'],'class_id'=>$g['class'],'name'=>'A','capacity'=>20])->assertUnprocessable();
  $unused=$this->create('subjects',['name'=>'Unused','code'=>'UNUSED']);$this->deleteJson('/app-api/academics/subjects/'.$unused)->assertOk();
  $this->assertDatabaseHas('audit_logs',['school_id'=>$f['sid'],'action'=>'academics.subjects.deleted','target_id'=>$unused]);$this->artisan('database:verify')->assertSuccessful();
 }
 public function test_guest_and_provider_without_school_membership_cannot_access_academics():void {
  $this->getJson('/app-api/academics')->assertUnauthorized();$this->seed(DatabaseSeeder::class);$admin=User::factory()->create();DB::table('platform_admins')->insert(['user_id'=>$admin->id,'active'=>true]);$this->actingAs($admin);$this->getJson('/app-api/academics')->assertForbidden();
 }
}
