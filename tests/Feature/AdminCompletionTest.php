<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\{LicenseService,SchoolOnboarding};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class AdminCompletionTest extends TestCase
{
 use RefreshDatabase;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 private function admin():User{$this->seed(DatabaseSeeder::class);$u=User::factory()->create();DB::table('platform_admins')->insert(['user_id'=>$u->id,'active'=>true]);$this->actingAs($u);return $u;}
 private function school(string $code,array $extra=[]):int {
  $d=['name'=>$code.' Academy','code'=>$code,'phone'=>'+923001234567','address'=>'12 Academy Road Lahore','owner_name'=>'School Owner','owner_email'=>strtolower($code).'@completion.test','password'=>'CompletionPassword123!','password_confirmation'=>'CompletionPassword123!','plan_id'=>DB::table('plans')->where('code','STANDARD-1000')->value('id'),'starts_on'=>'2026-10-01','ends_on'=>'2026-10-31','price'=>'5000'];
  return $this->postJson('/app-api/platform/schools',array_replace($d,$extra))->assertCreated()->json('id');
 }
 public function test_dashboard_and_license_filters_agree_at_date_boundaries_and_exclude_future_or_suspended_access():void {
  $this->travelTo(\Carbon\Carbon::parse('2026-10-16 00:30:00','Asia/Karachi'));$this->admin();
  $valid=$this->school('VALID',['ends_on'=>'2026-10-16']);$future=$this->school('FUTURE',['starts_on'=>'2026-10-17','ends_on'=>'2026-10-25']);
  $grace=$this->school('GRACE',['ends_on'=>'2026-10-15','grace_days'=>2]);$expired=$this->school('EXPIRED',['ends_on'=>'2026-10-13','grace_days'=>2]);$suspended=$this->school('SUSPEND');
  $this->putJson("/app-api/platform/schools/$suspended/license",['status'=>'suspended','reason'=>'Pause service access'])->assertOk();
  $d=$this->getJson('/app-api/platform/dashboard')->assertOk()->json();
  foreach(['valid'=>1,'expiring'=>1,'grace'=>1,'expired'=>1,'suspended'=>1] as $key=>$n){$this->assertEquals($n,$d[$key]);$this->getJson('/app-api/platform/licenses?state='.$key)->assertOk()->assertJsonPath('total',$n);}
  $this->getJson('/app-api/platform/licenses?state=valid')->assertJsonPath('data.0.school_id',$valid);
  $this->getJson('/app-api/platform/licenses?state=upcoming')->assertJsonPath('data.0.school_id',$future);
  $this->travelBack();
 }
 public function test_onboarding_cannot_be_falsely_confirmed_and_readiness_regresses_when_required_setup_is_removed():void {
  $this->travelTo(\Carbon\Carbon::parse('2026-10-16'));$admin=$this->admin();$sid=$this->school('SETUP');
  $url="/app-api/platform/schools/$sid/onboarding";
  $this->getJson($url)->assertOk()->assertJsonPath('status','in_progress')->assertJsonPath('completed',5);
  $this->putJson($url,['handover_confirmed'=>true,'notes'=>'Owner login verified'])->assertOk()->assertJsonPath('onboarding.status','ready');
  $this->assertDatabaseHas('platform_audit_logs',['school_id'=>$sid,'action'=>'school.onboarding_updated','actor_user_id'=>$admin->id]);
  $this->assertDatabaseMissing('school_memberships',['school_id'=>$sid,'user_id'=>$admin->id]);
  DB::table('campuses')->where('school_id',$sid)->update(['status'=>'inactive']);
  $this->getJson($url)->assertJsonPath('status','in_progress')->assertJsonPath('prerequisites_ready',false);
  $this->putJson($url,['handover_confirmed'=>true,'notes'=>'Cannot bypass campus'])->assertUnprocessable();
  $this->putJson($url,['handover_confirmed'=>false,'notes'=>'Campus setup pending'])->assertOk();
  $this->assertFalse(SchoolOnboarding::summary($sid)['handover_confirmed']);$this->travelBack();
 }
 public function test_license_timeline_and_school_audit_are_isolated_and_keep_issue_suspend_restore_actors():void {
  $this->travelTo(\Carbon\Carbon::parse('2026-10-16'));$admin=$this->admin();$a=$this->school('FIRST');$b=$this->school('SECOND');
  foreach(['suspended','active'] as $status)$this->putJson("/app-api/platform/schools/$a/license",['status'=>$status,'reason'=>'Audited '.$status.' action'])->assertOk();
  $data=$this->getJson("/app-api/platform/schools/$a/license-history")->assertOk()->json('events.data');
  $this->assertContains('license.issued',array_column($data,'action'));$this->assertCount(2,array_filter($data,fn($e)=>$e['action']==='license.status_changed'));
  foreach($data as $e)$this->assertSame($admin->name,$e['actor_name']);
  $events=$this->getJson("/app-api/platform/audit?school_id=$a")->assertOk()->json('data');foreach($events as $e){$this->assertEquals($a,$e['school_id']);$this->assertSame('FIRST Academy',$e['school_name']);}
  $this->getJson("/app-api/platform/schools/$b/license-history")->assertJsonMissing(['action'=>'license.status_changed']);$this->travelBack();
 }
 public function test_subscriptions_filter_current_scheduled_trial_cancelled_and_retain_agreed_snapshots():void {
  $this->travelTo(\Carbon\Carbon::parse('2026-10-16'));$this->admin();$sid=$this->school('PERIODS');
  $plan=DB::table('plans')->where('code','STANDARD-1000')->value('id');
  $this->postJson("/app-api/platform/schools/$sid/subscriptions",['plan_id'=>$plan,'starts_on'=>'2026-11-01','ends_on'=>'2026-11-30','kind'=>'trial','price'=>'0'])->assertOk();
  DB::table('plans')->where('id',$plan)->update(['name'=>'Template changed','student_limit'=>900]);
  $this->getJson('/app-api/platform/subscriptions?state=current&kind=paid&search=PERIODS')->assertOk()->assertJsonPath('total',1)->assertJsonPath('data.0.package_name','Standard')->assertJsonPath('data.0.student_limit',1000);
  $up=$this->getJson("/app-api/platform/subscriptions?state=upcoming&kind=trial&school_id=$sid")->assertOk()->assertJsonPath('total',1)->json('data.0.id');
  $this->postJson("/app-api/platform/schools/$sid/subscriptions/$up/cancel",['reason'=>'Trial no longer required'])->assertOk();
  $this->getJson('/app-api/platform/subscriptions?state=cancelled&search=PERIODS')->assertJsonPath('total',1);
  $this->getJson('/app-api/platform/subscriptions?state=upcoming&search=PERIODS')->assertJsonPath('total',0);$this->travelBack();
 }
 public function test_audit_date_filter_uses_pakistan_midnight_and_supports_to_only():void {
  $admin=$this->admin();$sid=$this->school('DATES');
  foreach(['2026-10-15 18:59:59','2026-10-15 19:00:00','2026-10-16 18:59:59','2026-10-16 19:00:00'] as $at)DB::table('platform_audit_logs')->insert(['school_id'=>$sid,'actor_user_id'=>$admin->id,'action'=>'date.boundary','target_type'=>'school','target_id'=>$sid,'created_at'=>$at,'updated_at'=>$at]);
  $this->getJson("/app-api/platform/audit?school_id=$sid&search=date.boundary&from=2026-10-16&to=2026-10-16&actor={$admin->id}")->assertOk()->assertJsonPath('total',2);
  $this->getJson('/app-api/platform/audit?search=date.boundary&to=2026-10-16')->assertOk()->assertJsonPath('total',3);
  $this->getJson('/app-api/platform/audit?from=2026-10-17&to=2026-10-16')->assertUnprocessable();
 }
 public function test_school_owner_and_unauthenticated_users_cannot_use_provider_completion_endpoints():void {
  $this->travelTo(\Carbon\Carbon::parse('2026-10-16'));$this->admin();$sid=$this->school('AUTH');$owner=User::where('email','auth@completion.test')->first();$this->actingAs($owner);
  foreach(['/subscriptions','/audit',"/schools/$sid/license-history","/schools/$sid/onboarding"] as $path)$this->getJson('/app-api/platform'.$path)->assertForbidden();
  $this->putJson("/app-api/platform/schools/$sid/onboarding",['handover_confirmed'=>true])->assertForbidden();
  $this->postJson('/app-api/logout')->assertOk();$this->getJson('/app-api/platform/subscriptions')->assertUnauthorized();$this->travelBack();
 }
}
