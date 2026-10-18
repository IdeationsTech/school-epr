<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('tests',function(Blueprint $t):void{$t->unsignedInteger('lock_version')->default(0);$t->unsignedInteger('publication_revision')->default(0);$t->decimal('passing_marks',12,2)->default(0);$t->text('correction_reason')->nullable();});
  Schema::table('exam_cycles',function(Blueprint $t):void{$t->unsignedInteger('lock_version')->default(0);$t->unsignedInteger('result_revision')->default(0);});
 }
 public function down():void{Schema::table('tests',fn(Blueprint $t)=>$t->dropColumn(['lock_version','publication_revision','passing_marks','correction_reason']));Schema::table('exam_cycles',fn(Blueprint $t)=>$t->dropColumn(['lock_version','result_revision']));}
};
