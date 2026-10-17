<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('attendance_sessions',function(Blueprint $t):void{$t->unsignedInteger('revision')->default(0);$t->dateTime('submitted_at')->nullable();$t->text('day_note')->nullable();});}
 public function down():void{Schema::table('attendance_sessions',fn(Blueprint $t)=>$t->dropColumn(['revision','submitted_at','day_note']));}
};
