<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
 public function up():void{
  Schema::create('staff_employments',function(Blueprint $t):void{$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('staff_profile_id');$t->unsignedBigInteger('campus_id');$t->date('starts_on');$t->date('ends_on')->nullable();$t->unsignedInteger('lock_version')->default(0);$t->timestamps();$t->unique(['school_id','id'],'p8_0_tenant');$t->unique(['school_id','staff_profile_id','starts_on'],'p8_0_u0');});
  Schema::table('staff_employments',function(Blueprint $t):void{$t->foreign('school_id','p8_0_school')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','staff_profile_id'],'p8_0_f0')->references(['school_id','id'])->on('staff_profiles')->restrictOnDelete();$t->foreign(['school_id','campus_id'],'p8_0_f1')->references(['school_id','id'])->on('campuses')->restrictOnDelete();});
  DB::statement("ALTER TABLE `staff_employments` ADD CONSTRAINT `p8_0_c0` CHECK (ends_on IS NULL OR ends_on >= starts_on)");
  Schema::create('payroll_policies',function(Blueprint $t):void{$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('campus_id');$t->date('starts_on');$t->unsignedInteger('version');$t->json('rules');$t->unsignedBigInteger('created_by');$t->timestamps();$t->unique(['school_id','id'],'p8_1_tenant');$t->unique(['school_id','campus_id','starts_on'],'p8_1_u0');});
  Schema::table('payroll_policies',function(Blueprint $t):void{$t->foreign('school_id','p8_1_school')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','campus_id'],'p8_1_f0')->references(['school_id','id'])->on('campuses')->restrictOnDelete();$t->foreign(['school_id','created_by'],'p8_1_f1')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();});
  DB::statement("ALTER TABLE `payroll_policies` ADD CONSTRAINT `p8_1_c0` CHECK (version > 0)");
  Schema::create('payroll_adjustments',function(Blueprint $t):void{$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('payroll_item_id');$t->unsignedBigInteger('advance_recovery_id')->nullable();$t->string('kind',160);$t->decimal('amount',12,2);$t->text('reason');$t->string('status',160);$t->unsignedBigInteger('requested_by');$t->unsignedBigInteger('approved_by')->nullable();$t->datetime('reviewed_at')->nullable();$t->timestamps();$t->unique(['school_id','id'],'p8_2_tenant');});
  Schema::table('payroll_adjustments',function(Blueprint $t):void{$t->foreign('school_id','p8_2_school')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','payroll_item_id'],'p8_2_f0')->references(['school_id','id'])->on('payroll_items')->restrictOnDelete();$t->foreign(['school_id','advance_recovery_id'],'p8_2_f1')->references(['school_id','id'])->on('advance_recoveries')->restrictOnDelete();$t->foreign(['school_id','requested_by'],'p8_2_f2')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();$t->foreign(['school_id','approved_by'],'p8_2_f3')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();});
  DB::statement("ALTER TABLE `payroll_adjustments` ADD CONSTRAINT `p8_2_c0` CHECK (kind IN ('credit','debit','recovery_refund'))");
  DB::statement("ALTER TABLE `payroll_adjustments` ADD CONSTRAINT `p8_2_c1` CHECK (amount > 0)");
  DB::statement("ALTER TABLE `payroll_adjustments` ADD CONSTRAINT `p8_2_c2` CHECK (status IN ('requested','approved','rejected'))");
  Schema::create('salary_payment_reversals',function(Blueprint $t):void{$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('salary_payment_id');$t->decimal('amount',12,2);$t->text('reason');$t->unsignedBigInteger('approved_by');$t->datetime('reversed_at');$t->timestamps();$t->unique(['school_id','id'],'p8_3_tenant');$t->unique(['school_id','salary_payment_id'],'p8_3_u0');});
  Schema::table('salary_payment_reversals',function(Blueprint $t):void{$t->foreign('school_id','p8_3_school')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','salary_payment_id'],'p8_3_f0')->references(['school_id','id'])->on('salary_payments')->restrictOnDelete();$t->foreign(['school_id','approved_by'],'p8_3_f1')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();});
  DB::statement("ALTER TABLE `salary_payment_reversals` ADD CONSTRAINT `p8_3_c0` CHECK (amount > 0)");
  Schema::table('staff_attendance_records',function(Blueprint $t):void{$t->unsignedInteger('lock_version')->default(0);});
  Schema::table('leave_requests',function(Blueprint $t):void{$t->unsignedInteger('lock_version')->default(0);$t->text('review_note')->nullable();$t->datetime('reviewed_at')->nullable();});
  Schema::table('staff_advances',function(Blueprint $t):void{$t->unsignedBigInteger('campus_id')->nullable();$t->date('requested_on')->nullable();$t->text('reason')->nullable();$t->string('method',160)->nullable();$t->unsignedBigInteger('bank_account_id')->nullable();$t->string('reference',160)->nullable();$t->string('disbursement_key',160)->nullable();$t->unsignedInteger('lock_version')->default(0);});
  Schema::table('payroll_runs',function(Blueprint $t):void{$t->unsignedInteger('lock_version')->default(0);$t->string('source_digest',64)->nullable();$t->json('snapshot')->nullable();});
  Schema::table('payroll_items',function(Blueprint $t):void{$t->json('snapshot')->nullable();});
  Schema::table('salary_payments',function(Blueprint $t):void{$t->string('request_hash',64)->nullable();$t->json('snapshot')->nullable();});
  Schema::table('staff_advances',function(Blueprint $t):void{$t->unique(['school_id','disbursement_key'],'p8_advance_key');$t->foreign(['school_id','campus_id'],'p8_advance_campus')->references(['school_id','id'])->on('campuses')->restrictOnDelete();$t->foreign(['school_id','bank_account_id'],'p8_advance_bank')->references(['school_id','id'])->on('bank_accounts')->restrictOnDelete();});
 }
 public function down():void{
  Schema::table('staff_advances',function(Blueprint $t):void{$t->dropForeign('p8_advance_campus');$t->dropForeign('p8_advance_bank');$t->dropUnique('p8_advance_key');});
  Schema::table('salary_payments',fn(Blueprint $t)=>$t->dropColumn(['request_hash','snapshot']));
  Schema::table('payroll_items',fn(Blueprint $t)=>$t->dropColumn(['snapshot']));
  Schema::table('payroll_runs',fn(Blueprint $t)=>$t->dropColumn(['lock_version','source_digest','snapshot']));
  Schema::table('staff_advances',fn(Blueprint $t)=>$t->dropColumn(['campus_id','requested_on','reason','method','bank_account_id','reference','disbursement_key','lock_version']));
  Schema::table('leave_requests',fn(Blueprint $t)=>$t->dropColumn(['lock_version','review_note','reviewed_at']));
  Schema::table('staff_attendance_records',fn(Blueprint $t)=>$t->dropColumn(['lock_version']));
  Schema::dropIfExists('salary_payment_reversals');
  Schema::dropIfExists('payroll_adjustments');
  Schema::dropIfExists('payroll_policies');
  Schema::dropIfExists('staff_employments');
 }
};
