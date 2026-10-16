<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('guardians',function(Blueprint $t):void{$t->string('cnic',13)->nullable();$t->unique(['school_id','cnic'],'guardian_cnic_school');});
  Schema::table('students',function(Blueprint $t):void{$t->string('identity_number',13)->nullable();$t->string('identity_type',160)->nullable();$t->unique(['school_id','identity_number'],'student_identity_school');});
  DB::statement("ALTER TABLE guardians ADD CONSTRAINT guardian_cnic_format CHECK (cnic IS NULL OR cnic REGEXP '^[0-9]{13}$')");
  DB::statement("ALTER TABLE students ADD CONSTRAINT student_identity_format CHECK ((identity_number IS NULL AND identity_type IS NULL) OR (identity_number IS NOT NULL AND identity_number REGEXP '^[0-9]{13}$' AND identity_type IS NOT NULL AND identity_type IN ('cnic','b_form')))");
  Schema::table('plans',function(Blueprint $t):void{$t->decimal('annual_price',12,2)->default(0);$t->boolean('available')->default(true);$t->json('modules')->nullable();});
  DB::table('plans')->orderBy('id')->each(function($p):void{$f=json_decode($p->features??'{}',true);DB::table('plans')->where('id',$p->id)->update(['annual_price'=>$f['annual_price']??((float)$p->monthly_price*12),'available'=>$f['active']??true,'modules'=>json_encode($f['modules']??(array_is_list($f)?$f:[]))]);});
  DB::statement('ALTER TABLE plans ADD CONSTRAINT plan_prices_limits CHECK (monthly_price >= 0 AND annual_price >= 0 AND student_limit > 0 AND campus_limit > 0)');
  Schema::create('school_licenses',function(Blueprint $t):void{
   $t->id();$t->foreignId('school_id')->unique()->constrained('schools')->restrictOnDelete();$t->string('number',160)->unique();$t->string('status',160)->default('active');$t->boolean('legacy_access')->default(false);$t->date('issued_on');$t->timestamps();$t->unique(['school_id','id'],'license_school_id');
  });
  DB::statement("ALTER TABLE school_licenses ADD CONSTRAINT license_status_valid CHECK (status IN ('active','suspended','closed'))");
  Schema::table('subscriptions',function(Blueprint $t):void{
   $t->unsignedBigInteger('license_id')->nullable();$t->string('kind',160)->default('paid');$t->unsignedInteger('grace_days')->default(0);$t->unsignedInteger('student_limit')->nullable();$t->unsignedInteger('campus_limit')->nullable();$t->json('modules')->nullable();$t->string('package_name',160)->nullable();
   $t->foreign(['school_id','license_id'],'subscription_license_fk')->references(['school_id','id'])->on('school_licenses')->restrictOnDelete();
  });
  DB::table('schools')->orderBy('id')->each(function($s):void{
   $lid=DB::table('school_licenses')->insertGetId(['school_id'=>$s->id,'number'=>'LIC-'.str_pad((string)$s->id,8,'0',STR_PAD_LEFT),'status'=>$s->status==='active'?'active':'suspended','legacy_access'=>!DB::table('subscriptions')->where('school_id',$s->id)->exists(),'issued_on'=>now('Asia/Karachi')->toDateString(),'created_at'=>now(),'updated_at'=>now()]);
   DB::table('subscriptions')->where('school_id',$s->id)->orderBy('id')->each(function($sub)use($lid):void{$p=DB::table('plans')->where('id',$sub->plan_id)->first();DB::table('subscriptions')->where('id',$sub->id)->update(['license_id'=>$lid,'student_limit'=>$p->student_limit,'campus_limit'=>$p->campus_limit,'modules'=>$p->modules,'package_name'=>$p->name]);});
  });
  DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscription_entitlements_valid CHECK (kind IN ('paid','trial') AND grace_days <= 30 AND (student_limit IS NULL OR student_limit > 0) AND (campus_limit IS NULL OR campus_limit > 0))");
  Schema::create('subscription_payment_reversals',function(Blueprint $t):void{
   $t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('payment_id');$t->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();$t->string('reason',500);$t->timestamps();$t->unique(['school_id','id'],'spr_school_id');$t->unique('payment_id','spr_payment_unique');$t->foreign('school_id')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','payment_id'],'spr_payment_fk')->references(['school_id','id'])->on('subscription_payments')->restrictOnDelete();
  });
  Schema::create('subscription_invoice_adjustments',function(Blueprint $t):void{
   $t->id();$t->unsignedBigInteger('school_id');$t->unsignedBigInteger('invoice_id');$t->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();$t->decimal('previous_amount',12,2);$t->decimal('new_amount',12,2);$t->string('reason',500);$t->timestamps();$t->unique(['school_id','id'],'sia_school_id');$t->foreign('school_id')->references('id')->on('schools')->restrictOnDelete();$t->foreign(['school_id','invoice_id'],'sia_invoice_fk')->references(['school_id','id'])->on('subscription_invoices')->restrictOnDelete();
  });
  DB::statement('ALTER TABLE subscription_invoice_adjustments ADD CONSTRAINT invoice_adjustment_nonnegative CHECK (previous_amount >= 0 AND new_amount >= 0)');
  Schema::create('provider_settings',function(Blueprint $t):void{$t->id();$t->string('key',160)->unique();$t->json('value');$t->timestamps();});
  Schema::table('support_tickets',function(Blueprint $t):void{$t->string('priority',160)->default('normal');$t->foreignId('assigned_user_id')->nullable()->constrained('users')->restrictOnDelete();$t->dateTime('resolved_at')->nullable();$t->index(['status','priority'],'support_queue');});
 }
 public function down():void {
  Schema::table('support_tickets',function(Blueprint $t):void{$t->dropForeign(['assigned_user_id']);$t->dropIndex('support_queue');$t->dropColumn(['priority','assigned_user_id','resolved_at']);});
  Schema::dropIfExists('provider_settings');Schema::dropIfExists('subscription_invoice_adjustments');Schema::dropIfExists('subscription_payment_reversals');
  DB::statement('ALTER TABLE subscriptions DROP CHECK subscription_entitlements_valid');
  Schema::table('subscriptions',function(Blueprint $t):void{$t->dropForeign('subscription_license_fk');$t->dropColumn(['license_id','kind','grace_days','student_limit','campus_limit','modules','package_name']);});
  Schema::dropIfExists('school_licenses');DB::statement('ALTER TABLE plans DROP CHECK plan_prices_limits');Schema::table('plans',fn(Blueprint $t)=>$t->dropColumn(['annual_price','available','modules']));
  DB::statement('ALTER TABLE students DROP CHECK student_identity_format');Schema::table('students',function(Blueprint $t):void{$t->dropUnique('student_identity_school');$t->dropColumn(['identity_number','identity_type']);});
  DB::statement('ALTER TABLE guardians DROP CHECK guardian_cnic_format');Schema::table('guardians',function(Blueprint $t):void{$t->dropUnique('guardian_cnic_school');$t->dropColumn('cnic');});
 }
};
