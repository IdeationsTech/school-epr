<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class PlatformAuditBackfillTest extends TestCase
{
 use DatabaseMigrations;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 public function test_backfill_uses_real_targets_and_leaves_global_or_missing_targets_unlinked():void {
  $m=require database_path('migrations/2026_10_16_000005_link_platform_audit_to_schools.php');$m->down();
  $u=User::factory()->create();$sid=DB::table('schools')->insertGetId(['name'=>'Historic school','code'=>'HIST','status'=>'active','timezone'=>'Asia/Karachi','currency'=>'PKR']);
  $base=['actor_user_id'=>$u->id,'action'=>'historical.change','created_at'=>now(),'updated_at'=>now()];
  $id=DB::table('platform_audit_logs')->insertGetId($base+['target_type'=>'school','target_id'=>$sid,'changes'=>json_encode(['school_id'=>999999])]);
  $global=DB::table('platform_audit_logs')->insertGetId($base+['target_type'=>'plan','target_id'=>$sid]);
  $missing=DB::table('platform_audit_logs')->insertGetId($base+['target_type'=>'subscription','target_id'=>999999]);
  $m->up();$this->assertDatabaseHas('platform_audit_logs',['id'=>$id,'school_id'=>$sid]);$this->assertDatabaseHas('platform_audit_logs',['id'=>$global,'school_id'=>null]);$this->assertDatabaseHas('platform_audit_logs',['id'=>$missing,'school_id'=>null]);
  $this->artisan('database:verify')->assertSuccessful();
 }
}
