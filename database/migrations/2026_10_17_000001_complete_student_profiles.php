<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('students',function(Blueprint $t):void{$t->text('address')->nullable();$t->string('previous_school',160)->nullable();});
  Schema::table('guardians',fn(Blueprint $t)=>$t->string('whatsapp_phone',160)->nullable());
 }
 public function down():void {
  Schema::table('guardians',fn(Blueprint $t)=>$t->dropColumn('whatsapp_phone'));
  Schema::table('students',fn(Blueprint $t)=>$t->dropColumn(['address','previous_school']));
 }
};
