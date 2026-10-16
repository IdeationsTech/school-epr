<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('platform_audit_logs',function(Blueprint $t):void{$t->unsignedBigInteger('school_id')->nullable();$t->foreign('school_id','platform_audit_school_fk')->references('id')->on('schools')->restrictOnDelete();$t->index(['school_id','created_at'],'platform_audit_school_time');});
  DB::table('platform_audit_logs')->orderBy('id')->chunkById(200,function($rows):void{foreach($rows as $r){$school=\App\Services\PlatformAudit::school($r->target_type,$r->target_id);if($school!==null)DB::table('platform_audit_logs')->where('id',$r->id)->update(['school_id'=>$school]);}});
 }
 public function down():void{Schema::table('platform_audit_logs',function(Blueprint $t):void{$t->dropForeign('platform_audit_school_fk');$t->dropIndex('platform_audit_school_time');$t->dropColumn('school_id');});}
};
