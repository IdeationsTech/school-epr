<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\LicenseService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
class LicenseLifecycleTest extends TestCase
{
 use RefreshDatabase;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated _test database required.');}
 private function setupSchool(array $extra=[]):array {
  $this->seed(DatabaseSeeder::class);$admin=User::factory()->create();DB::table('platform_admins')->insert(['user_id'=>$admin->id,'active'=>true]);$this->actingAs($admin);
  $plan=DB::table('plans')->where('code','STANDARD-1000')->first();
  $d=['name'=>'Lifecycle School','code'=>'LIFE','phone'=>'+923001234567','address'=>'12 School Road Lahore','owner_name'=>'Owner','owner_email'=>'owner@lifecycle.test','password'=>'LifecyclePassword123!','password_confirmation'=>'LifecyclePassword123!','plan_id'=>$plan->id,'starts_on'=>now('Asia/Karachi')->toDateString(),'ends_on'=>now('Asia/Karachi')->addMonth()->toDateString(),'price'=>'5000.00']+$extra;
  $d=array_replace($d,$extra);$sid=$this->postJson('/app-api/platform/schools',$d)->assertCreated()->json('id');return [$sid,$plan,$admin,User::where('email',$d['owner_email'])->first()];
 }
 public function test_license_number_is_stable_and_plan_changes_do_not_mutate_existing_limits():void {
  [$sid,$p]=$this->setupSchool();$license=DB::table('school_licenses')->where('school_id',$sid)->first();$this->assertFalse((bool)$license->legacy_access);
  DB::table('plans')->where('id',$p->id)->update(['campus_limit'=>1,'student_limit'=>500,'name'=>'Changed template','monthly_price'=>'9000']);
  $s=LicenseService::summary($sid);$this->assertEquals(2,$s['period']->campus_limit);$this->assertEquals(1000,$s['period']->student_limit);$this->assertSame('Standard',$s['period']->package_name);
  $this->getJson('/app-api/platform/licenses')->assertOk()->assertJsonPath('data.0.license.number',$license->number);
  $this->assertSame($license->number,DB::table('school_licenses')->where('school_id',$sid)->value('number'));
 }
 public function test_grace_access_expiry_and_owner_account_screen_work_without_operational_access():void {
  [$sid,,$admin,$owner]=$this->setupSchool(['grace_days'=>3]);$day=now('Asia/Karachi')->toDateString();
  DB::table('subscriptions')->where('school_id',$sid)->update(['starts_on'=>now('Asia/Karachi')->subMonth()->toDateString(),'ends_on'=>now('Asia/Karachi')->subDay()->toDateString()]);
  $this->assertTrue(LicenseService::allowed($sid));$this->assertSame('grace',LicenseService::summary($sid)['state']);
  $this->getJson('/app-api/platform/licenses?state=grace')->assertOk()->assertJsonCount(1,'data');
  $this->getJson('/app-api/platform/licenses?state=valid')->assertOk()->assertJsonCount(0,'data');
  $this->actingAs($owner)->withSession(['school_id'=>$sid])->getJson('/app-api/bootstrap')->assertOk();
  $this->actingAs($admin)->putJson("/app-api/platform/schools/$sid/license",['status'=>'suspended','reason'=>'Service temporarily paused'])->assertOk();
  $this->actingAs($owner)->withSession(['school_id'=>$sid])->getJson('/app-api/bootstrap')->assertForbidden();
  $this->getJson('/app-api/account')->assertOk()->assertJsonPath('entitlement.license.status','suspended');
  $invoice=DB::table('subscription_invoices')->where('school_id',$sid)->value('id');
  $this->getJson('/app-api/account/invoices/'.$invoice.'/document')->assertOk();
  $this->postJson('/app-api/account/tickets',['subject'=>'Please renew','description'=>'Please help renew our service.'])->assertCreated();
 }
 public function test_reversal_and_invoice_adjustment_preserve_history_and_net_balances():void {
  [$sid]=$this->setupSchool();$i=DB::table('subscription_invoices')->where('school_id',$sid)->first();
  $this->postJson("/app-api/platform/schools/$sid/invoices/$i->id/payments",['amount'=>'2000','paid_on'=>now('Asia/Karachi')->toDateString(),'method'=>'cash','reference'=>'CASH-1'])->assertOk();$pid=DB::table('subscription_payments')->where('school_id',$sid)->value('id');
  $this->postJson("/app-api/platform/schools/$sid/invoices/$i->id/adjust",['amount'=>'1500','reason'=>'Wrong original charge'])->assertUnprocessable();
  $this->postJson("/app-api/platform/schools/$sid/payments/$pid/reverse",['reason'=>'Incorrect cash receipt'])->assertOk();
  $this->postJson("/app-api/platform/schools/$sid/payments/$pid/reverse",['reason'=>'Duplicate reversal attempt'])->assertUnprocessable();
  $this->postJson("/app-api/platform/schools/$sid/invoices/$i->id/adjust",['amount'=>'1500','reason'=>'Correct service charge'])->assertOk();
  $this->assertDatabaseCount('subscription_payments',1);$this->assertDatabaseCount('subscription_payment_reversals',1);$this->assertDatabaseCount('subscription_invoice_adjustments',1);
  $this->assertEquals('0',LicenseService::paid($sid,$i->id));$this->getJson('/app-api/platform/dashboard')->assertJsonPath('received',0);
  $this->getJson("/app-api/platform/schools/$sid/invoices/$i->id/document?payment_id=$pid")->assertOk()->assertJsonPath('receipt.reversed',true);
 }
 public function test_package_change_creates_new_snapshot_and_keeps_original_invoice():void {
  [$sid,$plan]=$this->setupSchool();$old=DB::table('subscriptions')->where('school_id',$sid)->first();$number=DB::table('school_licenses')->where('school_id',$sid)->value('number');
  $this->postJson("/app-api/platform/schools/$sid/subscriptions/$old->id/change",['plan_id'=>$plan->id,'starts_on'=>$old->starts_on,'ends_on'=>$old->ends_on,'price'=>'1000','student_limit'=>1500,'campus_limit'=>3,'reason'=>'Agreed additional capacity'])->assertOk();
  $this->assertDatabaseHas('subscriptions',['id'=>$old->id,'status'=>'replaced']);$this->assertDatabaseCount('subscription_invoices',2);
  $this->assertEquals(3,LicenseService::period($sid)->campus_limit);$this->assertSame($number,DB::table('school_licenses')->where('school_id',$sid)->value('number'));
 }
 public function test_support_assignment_reply_audit_and_cross_school_isolation():void {
  [$sid,,$admin,$owner]=$this->setupSchool();
  $this->actingAs($owner)->withSession(['school_id'=>$sid])->postJson('/app-api/account/tickets',['subject'=>'Need help','description'=>'Please check the license.'])->assertCreated();$ticket=DB::table('support_tickets')->first();
  $this->actingAs($admin)->putJson('/app-api/platform/support/'.$ticket->id,['school_id'=>$sid,'subject'=>$ticket->subject,'description'=>$ticket->description,'status'=>'resolved','priority'=>'high','assigned_user_id'=>$admin->id,'reply'=>'We have checked your license.'])->assertOk();
  $this->getJson('/app-api/platform/support/'.$ticket->id)->assertOk()->assertJsonPath('replies.0.body','We have checked your license.');$this->getJson('/app-api/platform/audit?search=support')->assertOk()->assertJsonCount(1,'data');
  $stranger=User::factory()->create();$other=DB::table('schools')->insertGetId(['code'=>'OTHER','name'=>'Other','timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);$mid=DB::table('school_memberships')->insertGetId(['school_id'=>$other,'user_id'=>$stranger->id,'status'=>'active']);DB::table('membership_roles')->insert(['school_id'=>$other,'membership_id'=>$mid,'role_id'=>DB::table('roles')->where('name','owner')->value('id')]);
  $this->actingAs($stranger)->withSession(['school_id'=>$other])->getJson('/app-api/account/tickets/'.$ticket->id)->assertNotFound();
  $this->getJson('/app-api/platform/licenses')->assertForbidden();$this->getJson('/app-api/platform/billing')->assertForbidden();$this->getJson('/app-api/platform/audit')->assertForbidden();
 }
 public function test_identity_format_uniqueness_and_nullable_legacy_records():void {
  [$sid]=$this->setupSchool();$v=Validator::make(['cnic'=>'35202-1234567-1'],['cnic'=>[new \App\Rules\PakistanIdentity()]]);$this->assertTrue($v->passes());$this->assertSame('3520212345671',\App\Rules\PakistanIdentity::normalize('35202-1234567-1'));
  $this->assertTrue(Validator::make(['cnic'=>'123'],['cnic'=>[new \App\Rules\PakistanIdentity()]])->fails());
  DB::table('guardians')->insert(['school_id'=>$sid,'name'=>'Guardian','phone'=>'+923001234567','cnic'=>'3520212345671']);
  $this->expectException(\Illuminate\Database\QueryException::class);DB::table('guardians')->insert(['school_id'=>$sid,'name'=>'Duplicate','phone'=>'+923001234568','cnic'=>'3520212345671']);
 }
 public function test_database_verify_command_is_read_only_and_passes():void {$this->artisan('database:verify')->assertSuccessful();}
}
