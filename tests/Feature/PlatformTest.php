<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class PlatformTest extends TestCase
{
    use RefreshDatabase;
    protected function beforeRefreshingDatabase():void
    {
        if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated _test database required.');
    }
    private function admin():User
    {
        $this->seed(DatabaseSeeder::class);$u=User::factory()->create(['password'=>'AdminPassword123!']);
        DB::table('platform_admins')->insert(['user_id'=>$u->id,'active'=>true]);$this->actingAs($u);return $u;
    }
    private function plan():int
    {
        return DB::table('plans')->insertGetId(['code'=>'BASIC','name'=>'Small School','student_limit'=>1000,'campus_limit'=>2,'monthly_price'=>'5000.00','features'=>json_encode(['active'=>true,'annual_price'=>'50000.00'])]);
    }
    private function payload(int $plan):array
    {
        return ['name'=>'Test School','code'=>'TEST','phone'=>'+923001234567','address'=>'12 School Road Lahore','owner_name'=>'School Owner','owner_email'=>'owner@example.test',
            'password'=>'OwnerPassword123!','password_confirmation'=>'OwnerPassword123!','plan_id'=>$plan,'starts_on'=>now('Asia/Karachi')->toDateString(),'ends_on'=>now('Asia/Karachi')->addMonth()->toDateString(),'price'=>'5000.00'];
    }
    private function onboard():array
    {
        $this->admin();$p=$this->plan();$d=$this->payload($p);$id=$this->postJson('/app-api/platform/schools',$d)->assertCreated()->json('id');return [$id,$p,$d];
    }
    public function test_platform_requires_explicit_database_grant():void
    {
        $this->getJson('/app-api/platform/dashboard')->assertUnauthorized();
        $u=User::factory()->create();$this->actingAs($u)->getJson('/app-api/platform/schools')->assertForbidden();
        $this->postJson('/app-api/platform/plans',[])->assertForbidden();
    }
    public function test_admin_without_school_membership_can_login_and_revocation_is_immediate():void
    {
        $u=$this->admin();$this->postJson('/app-api/logout');
        $this->postJson('/app-api/login',['email'=>$u->email,'password'=>'AdminPassword123!'])->assertOk()->assertJsonPath('is_platform_admin',true);
        $this->getJson('/app-api/platform/dashboard')->assertOk();
        DB::table('platform_admins')->where('user_id',$u->id)->update(['active'=>false]);
        $this->getJson('/app-api/platform/dashboard')->assertForbidden();
    }
    public function test_onboarding_is_atomic_and_owner_password_is_hashed():void
    {
        [$sid,$pid,$d]=$this->onboard();
        $this->assertDatabaseHas('campuses',['school_id'=>$sid,'phone'=>'+923001234567']);
        $this->assertDatabaseHas('subscriptions',['school_id'=>$sid,'plan_id'=>$pid,'price'=>'5000.00']);
        $this->assertDatabaseHas('subscription_invoices',['school_id'=>$sid,'amount'=>'5000.00','status'=>'unpaid']);
        $this->assertTrue(Hash::check($d['password'],DB::table('users')->where('email',$d['owner_email'])->value('password')));
        $this->getJson('/app-api/platform/schools/'.$sid)->assertOk()->assertJsonMissingPath('owners.0.password');
        $d['code']='ANOTHER';$this->postJson('/app-api/platform/schools',$d)->assertUnprocessable();$this->assertDatabaseCount('schools',1);
        $this->postJson('/app-api/logout');$this->postJson('/app-api/login',['email'=>$d['owner_email'],'password'=>$d['password']])->assertOk()->assertJsonPath('is_platform_admin',false);
        $this->getJson('/app-api/bootstrap')->assertOk()->assertJsonPath('school.id',$sid);
        $this->getJson('/app-api/platform/schools')->assertForbidden();
    }
    public function test_partial_payments_prevent_duplicates_overpayment_and_cross_school_invoice_ids():void
    {
        [$sid]=$this->onboard();$iid=DB::table('subscription_invoices')->value('id');$url="/app-api/platform/schools/$sid/invoices/$iid/payments";
        $p=['amount'=>'2000.00','paid_on'=>now('Asia/Karachi')->toDateString(),'method'=>'bank_transfer','reference'=>'BANK-001'];
        $this->postJson($url,$p)->assertOk();$this->assertDatabaseHas('subscription_invoices',['id'=>$iid,'status'=>'partial']);
        $this->postJson($url,$p)->assertUnprocessable();
        $p['reference']='BANK-002';$p['amount']='3000.01';$this->postJson($url,$p)->assertUnprocessable();
        $other=DB::table('schools')->insertGetId(['name'=>'Other','code'=>'OTHER','status'=>'active','timezone'=>'Asia/Karachi','currency'=>'PKR']);
        $this->postJson("/app-api/platform/schools/$other/invoices/$iid/payments",$p)->assertNotFound();
        $p['amount']='3000.00';$this->postJson($url,$p)->assertOk();$this->assertDatabaseHas('subscription_invoices',['id'=>$iid,'status'=>'paid']);
        $this->assertDatabaseCount('subscription_payments',2);
    }
    public function test_subscription_renewal_rejects_overlap_and_keeps_invoice_history():void
    {
        [$sid,$plan,$d]=$this->onboard();$url="/app-api/platform/schools/$sid/subscriptions";
        $this->postJson($url,['plan_id'=>$plan,'starts_on'=>$d['starts_on'],'ends_on'=>$d['ends_on'],'price'=>'5000'])->assertUnprocessable();
        $start=\Carbon\Carbon::parse($d['ends_on'])->addDay()->toDateString();$end=\Carbon\Carbon::parse($start)->addMonth()->toDateString();
        $this->postJson($url,['plan_id'=>$plan,'starts_on'=>$start,'ends_on'=>$end,'price'=>'4500'])->assertOk();
        $this->assertDatabaseCount('subscriptions',2);$this->assertDatabaseCount('subscription_invoices',2);
    }
    public function test_suspension_and_expiry_block_an_existing_owner_session():void
    {
        [$sid,,$d]=$this->onboard();$admin=auth()->user();
        $this->putJson("/app-api/platform/schools/$sid/status",['status'=>'inactive','reason'=>'Unpaid service invoice'])->assertOk();
        $owner=User::where('email',$d['owner_email'])->first();$this->actingAs($owner)->withSession(['school_id'=>$sid])->getJson('/app-api/bootstrap')->assertForbidden();
        $this->actingAs($admin)->putJson("/app-api/platform/schools/$sid/status",['status'=>'active','reason'=>'Service restored'])->assertOk();
        DB::table('subscriptions')->where('school_id',$sid)->update(['starts_on'=>'2020-01-01','ends_on'=>'2020-01-31']);
        $this->actingAs($owner)->withSession(['school_id'=>$sid])->getJson('/app-api/bootstrap')->assertForbidden();
        $this->postJson('/app-api/logout');$this->postJson('/app-api/login',['email'=>$d['owner_email'],'password'=>$d['password']])->assertForbidden();
    }
    public function test_campus_limit_is_enforced_by_school_api():void
    {
        [$sid,$plan,$d]=$this->onboard();DB::table('plans')->where('id',$plan)->update(['campus_limit'=>1]);
        $owner=User::where('email',$d['owner_email'])->first();$this->actingAs($owner)->withSession(['school_id'=>$sid]);
        $this->postJson('/app-api/campuses',['name'=>'Branch','code'=>'BRANCH','phone'=>'+923001234567','address'=>'12 Branch Road Lahore','status'=>'active'])->assertUnprocessable();
    }
    public function test_plan_edits_do_not_change_historical_subscription_price():void
    {
        [$sid,$plan]=$this->onboard();$this->putJson('/app-api/platform/plans/'.$plan,['name'=>'New rate','code'=>'BASIC','student_limit'=>2000,'campus_limit'=>3,'monthly_price'=>'6000','annual_price'=>'60000','active'=>true])->assertOk();
        $this->assertDatabaseHas('subscriptions',['school_id'=>$sid,'price'=>'5000.00']);
        $this->putJson('/app-api/platform/plans/'.$plan,['name'=>'Invalid','code'=>'BASIC','student_limit'=>0,'campus_limit'=>0,'monthly_price'=>'-1','annual_price'=>'-1','active'=>true])->assertUnprocessable();
    }
    public function test_platform_admin_command_grants_existing_user_without_password_reset():void
    {
        $u=User::factory()->create();$hash=$u->password;
        $this->artisan('platform:admin')->expectsQuestion('Existing account email for platform administrator',$u->email)->assertSuccessful();
        $this->assertDatabaseHas('platform_admins',['user_id'=>$u->id,'active'=>true]);$this->assertSame($hash,$u->fresh()->password);
    }
}
