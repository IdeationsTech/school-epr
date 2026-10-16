<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 private function preflight():void {
  $checks=[
   'invoice_items'=>DB::table('invoice_items')->whereRaw('discount_amount > ROUND(quantity * unit_amount, 2) OR line_total <> ROUND(quantity * unit_amount, 2) - discount_amount'),
   'cashier_closings'=>DB::table('cashier_closings')->whereRaw('variance <> counted_cash - expected_cash'),
   'students'=>DB::table('students')->whereColumn('birth_date','>','admitted_on'),
  ];
  foreach(['teacher_assignments','tests','timetable_slots'] as $table){$checks[$table]=DB::table($table.' as c')->whereNotNull('c.subject_id')->whereNotExists(function($q){$q->selectRaw('1')->from('section_subjects as s')->whereColumn('s.school_id','c.school_id')->whereColumn('s.section_id','c.section_id')->whereColumn('s.subject_id','c.subject_id');});}
  $issues=[];foreach($checks as $name=>$q){$ids=(clone $q)->limit(5)->pluck(in_array($name,['teacher_assignments','tests','timetable_slots'])?'c.id':'id');if($ids->isNotEmpty())$issues[]=$name.' record IDs: '.$ids->implode(', ');}
  if($issues)throw new RuntimeException('Scope integrity preflight failed before schema changes. Review these existing records; no financial values were rewritten: '.implode('; ',$issues));
 }
 public function up():void {
  $this->preflight();
  // Backfill only missing references/snapshots, retaining every already-agreed value.
  DB::table('subscriptions as s')->join('plans as p','p.id','=','s.plan_id')->orderBy('s.id')->select(['s.*','p.name as fallback_name','p.student_limit as fallback_students','p.campus_limit as fallback_campuses','p.modules as fallback_modules'])->each(function($s):void{
   $license=DB::table('school_licenses')->where('school_id',$s->school_id)->first();
   if(!$license){$status=DB::table('schools')->where('id',$s->school_id)->value('status');DB::table('school_licenses')->insertOrIgnore(['school_id'=>$s->school_id,'number'=>'LIC-'.str_pad((string)$s->school_id,8,'0',STR_PAD_LEFT),'status'=>$status==='active'?'active':'suspended','legacy_access'=>false,'issued_on'=>now('Asia/Karachi')->toDateString(),'created_at'=>now(),'updated_at'=>now()]);$license=DB::table('school_licenses')->where('school_id',$s->school_id)->first();}
   DB::table('subscriptions')->where('id',$s->id)->update(['license_id'=>$s->license_id??$license->id,'student_limit'=>$s->student_limit??$s->fallback_students,'campus_limit'=>$s->campus_limit??$s->fallback_campuses,'package_name'=>$s->package_name??$s->fallback_name,'modules'=>$s->modules??$s->fallback_modules??'[]']);
  });
  Schema::table('subscriptions',fn(Blueprint $t)=>$t->dropForeign('subscription_license_fk'));
  Schema::table('subscriptions',function(Blueprint $t):void{$t->unsignedBigInteger('license_id')->nullable(false)->change();$t->unsignedInteger('student_limit')->nullable(false)->change();$t->unsignedInteger('campus_limit')->nullable(false)->change();$t->string('package_name',160)->nullable(false)->change();$t->json('modules')->nullable(false)->change();});
  Schema::table('subscriptions',fn(Blueprint $t)=>$t->foreign(['school_id','license_id'],'subscription_license_fk')->references(['school_id','id'])->on('school_licenses')->restrictOnDelete());
  Schema::create('student_fee_assignments',function(Blueprint $t):void{
   $t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('enrollment_id');$t->unsignedBigInteger('fee_plan_id');$t->unsignedBigInteger('assigned_by')->nullable();$t->date('starts_on');$t->date('ends_on')->nullable();$t->text('reason')->nullable();$t->timestamps();
   $t->unique(['school_id','id'],'sfa_school_id');$t->unique(['school_id','enrollment_id','id'],'sfa_enrollment_id');$t->unique(['school_id','enrollment_id','starts_on'],'sfa_effective_start');
   $t->foreign('school_id','sfa_school_fk')->references('id')->on('schools')->restrictOnDelete();
   $t->foreign(['school_id','enrollment_id'],'sfa_enrollment_fk')->references(['school_id','id'])->on('enrollments')->restrictOnDelete();
   $t->foreign(['school_id','fee_plan_id'],'sfa_plan_fk')->references(['school_id','id'])->on('fee_plans')->restrictOnDelete();
   $t->foreign(['school_id','assigned_by'],'sfa_actor_fk')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();
  });
  DB::statement('ALTER TABLE student_fee_assignments ADD CONSTRAINT sfa_dates CHECK (ends_on IS NULL OR ends_on >= starts_on)');
  Schema::table('invoices',function(Blueprint $t):void{$t->unsignedBigInteger('fee_assignment_id')->nullable();$t->foreign(['school_id','enrollment_id','fee_assignment_id'],'invoice_fee_assignment_fk')->references(['school_id','enrollment_id','id'])->on('student_fee_assignments')->restrictOnDelete();});
  foreach(['teacher_assignments'=>'teacher_offered_subject_fk','tests'=>'test_offered_subject_fk','timetable_slots'=>'timetable_offered_subject_fk'] as $table=>$name){Schema::table($table,fn(Blueprint $t)=>$t->foreign(['school_id','section_id','subject_id'],$name)->references(['school_id','section_id','subject_id'])->on('section_subjects')->restrictOnDelete());}
  DB::statement('ALTER TABLE invoice_items ADD CONSTRAINT invoice_line_arithmetic CHECK (discount_amount <= ROUND(quantity * unit_amount, 2) AND line_total = ROUND(quantity * unit_amount, 2) - discount_amount)');
  DB::statement('ALTER TABLE cashier_closings ADD CONSTRAINT cashier_variance_arithmetic CHECK (variance = counted_cash - expected_cash)');
  DB::statement('ALTER TABLE students ADD CONSTRAINT student_birth_before_admission CHECK (birth_date IS NULL OR birth_date <= admitted_on)');
  Schema::table('payment_reversals',function(Blueprint $t):void{
   $t->string('kind',160)->nullable();$t->string('refund_method',160)->nullable();$t->string('refund_reference',160)->nullable();$t->unsignedBigInteger('refund_bank_account_id')->nullable();$t->unsignedBigInteger('reversed_by')->nullable();
   $t->foreign(['school_id','refund_bank_account_id'],'refund_bank_fk')->references(['school_id','id'])->on('bank_accounts')->restrictOnDelete();
   $t->foreign(['school_id','reversed_by'],'refund_actor_fk')->references(['school_id','id'])->on('school_memberships')->restrictOnDelete();
  });
  DB::statement("ALTER TABLE payment_reversals ADD CONSTRAINT reversal_kind_valid CHECK (kind IS NULL OR kind IN ('correction','refund'))");
  DB::statement("ALTER TABLE payment_reversals ADD CONSTRAINT refund_method_valid CHECK (refund_method IS NULL OR refund_method IN ('cash','bank_transfer','jazzcash','easypaisa'))");
  DB::statement("ALTER TABLE payment_reversals ADD CONSTRAINT refund_method_required CHECK (kind IS NULL OR kind <> 'refund' OR refund_method IS NOT NULL)");
  Schema::table('subscriptions',fn(Blueprint $t)=>$t->index(['school_id','status','starts_on','ends_on'],'subscription_period_lookup'));
  Schema::table('subscription_invoices',fn(Blueprint $t)=>$t->index(['status','due_on'],'service_invoice_due_lookup'));
 }
 public function down():void {
  Schema::table('subscription_invoices',fn(Blueprint $t)=>$t->dropIndex('service_invoice_due_lookup'));Schema::table('subscriptions',fn(Blueprint $t)=>$t->dropIndex('subscription_period_lookup'));
  foreach(['reversal_kind_valid','refund_method_valid','refund_method_required'] as $check)DB::statement('ALTER TABLE payment_reversals DROP CHECK '.$check);
  Schema::table('payment_reversals',function(Blueprint $t):void{$t->dropForeign('refund_bank_fk');$t->dropForeign('refund_actor_fk');$t->dropColumn(['kind','refund_method','refund_reference','refund_bank_account_id','reversed_by']);});
  DB::statement('ALTER TABLE students DROP CHECK student_birth_before_admission');DB::statement('ALTER TABLE cashier_closings DROP CHECK cashier_variance_arithmetic');DB::statement('ALTER TABLE invoice_items DROP CHECK invoice_line_arithmetic');
  foreach(['teacher_assignments'=>'teacher_offered_subject_fk','tests'=>'test_offered_subject_fk','timetable_slots'=>'timetable_offered_subject_fk'] as $table=>$name)Schema::table($table,fn(Blueprint $t)=>$t->dropForeign($name));
  Schema::table('invoices',function(Blueprint $t):void{$t->dropForeign('invoice_fee_assignment_fk');$t->dropColumn('fee_assignment_id');});Schema::dropIfExists('student_fee_assignments');
  Schema::table('subscriptions',fn(Blueprint $t)=>$t->dropForeign('subscription_license_fk'));
  Schema::table('subscriptions',function(Blueprint $t):void{$t->unsignedBigInteger('license_id')->nullable()->change();$t->unsignedInteger('student_limit')->nullable()->change();$t->unsignedInteger('campus_limit')->nullable()->change();$t->string('package_name',160)->nullable()->change();$t->json('modules')->nullable()->change();});
  Schema::table('subscriptions',fn(Blueprint $t)=>$t->foreign(['school_id','license_id'],'subscription_license_fk')->references(['school_id','id'])->on('school_licenses')->restrictOnDelete());
 }
};
