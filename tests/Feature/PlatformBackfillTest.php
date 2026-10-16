<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class PlatformBackfillTest extends TestCase
{
 use DatabaseMigrations;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 public function test_existing_records_survive_platform_upgrade_with_snapshot_and_license_backfill():void {
  $audit=require database_path('migrations/2026_10_16_000004_harden_agreed_scope_contract.php');$audit->down();
  $m=require database_path('migrations/2026_10_16_000003_complete_platform_contract.php');$m->down();
  $p=DB::table('plans')->insertGetId(['name'=>'Legacy package','code'=>'LEGACY','student_limit'=>800,'campus_limit'=>2,'monthly_price'=>'4000','features'=>json_encode(['annual_price'=>'40000','active'=>true,'modules'=>['fees']])]);
  $sid=DB::table('schools')->insertGetId(['name'=>'Legacy school','code'=>'LEGACY','timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);
  DB::table('guardians')->insert(['school_id'=>$sid,'name'=>'Old guardian','phone'=>'03001234567']);
  DB::table('subscriptions')->insert(['school_id'=>$sid,'plan_id'=>$p,'starts_on'=>'2026-01-01','ends_on'=>'2026-12-31','status'=>'active','price'=>'4000']);
  $m->up();$audit->up();$this->assertDatabaseHas('plans',['id'=>$p,'annual_price'=>'40000.00']);$this->assertDatabaseHas('school_licenses',['school_id'=>$sid,'legacy_access'=>false]);$this->assertDatabaseHas('subscriptions',['school_id'=>$sid,'student_limit'=>800,'campus_limit'=>2,'package_name'=>'Legacy package']);$this->assertDatabaseHas('guardians',['school_id'=>$sid,'name'=>'Old guardian','cnic'=>null]);$this->artisan('database:verify')->assertSuccessful();
 }
}
