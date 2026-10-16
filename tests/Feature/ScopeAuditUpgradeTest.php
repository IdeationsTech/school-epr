<?php
namespace Tests\Feature;
use App\Support\SchemaContractInspector;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class ScopeAuditUpgradeTest extends TestCase
{
 use DatabaseMigrations;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 public function test_nullable_old_snapshots_are_completed_without_replacing_agreed_limits():void {
  $m=require database_path('migrations/2026_10_16_000004_harden_agreed_scope_contract.php');$m->down();
  $plan=DB::table('plans')->insertGetId(['name'=>'Old package','code'=>'OLD','student_limit'=>1000,'campus_limit'=>2,'monthly_price'=>'5000','annual_price'=>'50000','available'=>true,'modules'=>json_encode(['fees'])]);
  $school=DB::table('schools')->insertGetId(['name'=>'Old school','code'=>'OLD','timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);
  $id=DB::table('subscriptions')->insertGetId(['school_id'=>$school,'plan_id'=>$plan,'starts_on'=>'2026-01-01','ends_on'=>'2026-12-31','status'=>'active','price'=>'4500','student_limit'=>1200]);
  $m->up();$this->assertDatabaseHas('subscriptions',['id'=>$id,'student_limit'=>1200,'campus_limit'=>2,'package_name'=>'Old package','price'=>'4500.00']);
  $this->assertNotNull(DB::table('subscriptions')->where('id',$id)->value('license_id'));$this->artisan('database:verify')->assertSuccessful();
 }
 public function test_preflight_stops_before_schema_changes_and_does_not_rewrite_money():void {
  $m=require database_path('migrations/2026_10_16_000004_harden_agreed_scope_contract.php');$m->down();
  $sid=DB::table('schools')->insertGetId(['name'=>'Old school','code'=>'OLD','timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);
  $u=DB::table('users')->insertGetId(['name'=>'Cashier','email'=>'cashier@example.test','password'=>'not-a-login']);$member=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$u,'status'=>'active']);$campus=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main','code'=>'MAIN','status'=>'active']);
  $id=DB::table('cashier_closings')->insertGetId(['school_id'=>$sid,'campus_id'=>$campus,'cashier_membership_id'=>$member,'closing_date'=>'2026-01-01','expected_cash'=>'1000','counted_cash'=>'900','variance'=>'0','status'=>'draft']);
  try{$m->up();$this->fail('Expected preflight rejection.');}catch(\RuntimeException $e){$this->assertStringContainsString('cashier_closings record IDs',$e->getMessage());$this->assertFalse(Schema::hasTable('student_fee_assignments'));$this->assertDatabaseHas('cashier_closings',['id'=>$id,'variance'=>'0.00']);}
  finally{DB::table('cashier_closings')->where('id',$id)->update(['variance'=>'-100']);$m->up();}
 }
 public function test_inspector_detects_unique_fk_and_check_drift_even_when_fk_count_matches():void {
  $before=app(SchemaContractInspector::class)->inspect();$this->assertSame([],$before['issues']);
  DB::statement('ALTER TABLE guardians DROP INDEX guardian_cnic_school');
  DB::statement('ALTER TABLE role_permissions DROP FOREIGN KEY t14_fk0');DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT audit_wrong_parent FOREIGN KEY (role_id) REFERENCES permissions(id) ON DELETE RESTRICT');
  DB::statement('ALTER TABLE plans DROP CHECK plan_prices_limits');DB::statement('ALTER TABLE plans ADD CONSTRAINT plan_prices_limits CHECK (monthly_price >= -1)');
  try{$after=app(SchemaContractInspector::class)->inspect();$this->assertSame($before['foreign_keys'],$after['foreign_keys']);$all=implode('\n',$after['issues']);$this->assertStringContainsString('UNIQUE key: guardians',$all);$this->assertStringContainsString('restricted FK: role_permissions(role_id)',$all);$this->assertStringContainsString('CHECK: plans',$all);}
  finally{DB::statement('ALTER TABLE guardians ADD UNIQUE KEY guardian_cnic_school (school_id,cnic)');DB::statement('ALTER TABLE role_permissions DROP FOREIGN KEY audit_wrong_parent');DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT t14_fk0 FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT');DB::statement('ALTER TABLE plans DROP CHECK plan_prices_limits');DB::statement('ALTER TABLE plans ADD CONSTRAINT plan_prices_limits CHECK (monthly_price >= 0 AND annual_price >= 0 AND student_limit > 0 AND campus_limit > 0)');}
 }
}
