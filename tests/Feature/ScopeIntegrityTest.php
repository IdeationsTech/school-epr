<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class ScopeIntegrityTest extends TestCase
{
 use RefreshDatabase;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 private function graph():array {
  $sid=DB::table('schools')->insertGetId(['name'=>'Scope school','code'=>'S'.random_int(1000,9999999),'timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active']);
  $user=User::factory()->create();$mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$user->id,'status'=>'active']);
  $campus=DB::table('campuses')->insertGetId(['school_id'=>$sid,'code'=>'MAIN','name'=>'Main','status'=>'active']);
  $session=DB::table('academic_sessions')->insertGetId(['school_id'=>$sid,'name'=>'2026','starts_on'=>'2026-01-01','ends_on'=>'2026-12-31','status'=>'active']);
  $class=DB::table('classes')->insertGetId(['school_id'=>$sid,'name'=>'Class 1','position'=>1]);
  $section=DB::table('sections')->insertGetId(['school_id'=>$sid,'campus_id'=>$campus,'academic_session_id'=>$session,'class_id'=>$class,'name'=>'A']);
  $subject=DB::table('subjects')->insertGetId(['school_id'=>$sid,'name'=>'English','code'=>'ENG']);
  $student=DB::table('students')->insertGetId(['school_id'=>$sid,'admission_no'=>'ST-1','first_name'=>'Student','admitted_on'=>'2026-01-01','status'=>'active']);
  $enrollment=DB::table('enrollments')->insertGetId(['school_id'=>$sid,'student_id'=>$student,'section_id'=>$section,'starts_on'=>'2026-01-01','status'=>'active']);
  $plan=DB::table('fee_plans')->insertGetId(['school_id'=>$sid,'academic_session_id'=>$session,'class_id'=>$class,'campus_id'=>$campus,'name'=>'Monthly','starts_on'=>'2026-01-01']);
  return compact('sid','mid','campus','section','subject','student','enrollment','plan');
 }
 public function test_fee_assignment_cannot_link_another_schools_plan():void {
  $a=$this->graph();$b=$this->graph();$this->expectException(\Illuminate\Database\QueryException::class);
  DB::table('student_fee_assignments')->insert(['school_id'=>$a['sid'],'enrollment_id'=>$a['enrollment'],'fee_plan_id'=>$b['plan'],'starts_on'=>'2026-01-01']);
 }
 public function test_invoice_cannot_use_a_fee_assignment_for_another_enrollment_in_the_same_school():void {
  $a=$this->graph();$other=DB::table('students')->insertGetId(['school_id'=>$a['sid'],'admission_no'=>'ST-2','first_name'=>'Other','admitted_on'=>'2026-01-01','status'=>'active']);
  $enrollment=DB::table('enrollments')->insertGetId(['school_id'=>$a['sid'],'student_id'=>$other,'section_id'=>$a['section'],'starts_on'=>'2026-01-01','status'=>'active']);
  $assignment=DB::table('student_fee_assignments')->insertGetId(['school_id'=>$a['sid'],'enrollment_id'=>$a['enrollment'],'fee_plan_id'=>$a['plan'],'starts_on'=>'2026-01-01']);
  $this->expectException(\Illuminate\Database\QueryException::class);
  DB::table('invoices')->insert(['school_id'=>$a['sid'],'enrollment_id'=>$enrollment,'fee_assignment_id'=>$assignment,'number'=>'INV-1','period'=>'2026-01-01','kind'=>'monthly','installment_no'=>1,'issued_on'=>'2026-01-01','due_on'=>'2026-01-10','status'=>'unpaid','total'=>'1000']);
 }
 public function test_timetable_requires_an_offered_section_subject():void {
  $a=$this->graph();$staff=DB::table('staff_profiles')->insertGetId(['school_id'=>$a['sid'],'membership_id'=>$a['mid'],'employee_no'=>'EMP-1','status'=>'active']);
  $this->expectException(\Illuminate\Database\QueryException::class);
  DB::table('timetable_slots')->insert(['school_id'=>$a['sid'],'section_id'=>$a['section'],'subject_id'=>$a['subject'],'staff_profile_id'=>$staff,'weekday'=>1,'starts_at'=>'09:00:00','ends_at'=>'09:40:00']);
 }
 public function test_class_teacher_without_a_subject_is_still_allowed():void {
  $a=$this->graph();$staff=DB::table('staff_profiles')->insertGetId(['school_id'=>$a['sid'],'membership_id'=>$a['mid'],'employee_no'=>'EMP-1','status'=>'active']);
  DB::table('teacher_assignments')->insert(['school_id'=>$a['sid'],'section_id'=>$a['section'],'staff_profile_id'=>$staff,'subject_id'=>null,'is_class_teacher'=>true]);
  $this->assertDatabaseCount('teacher_assignments',1);
 }
 public function test_real_refund_requires_a_method_and_correction_is_distinct():void {
  $a=$this->graph();$payment=DB::table('payments')->insertGetId(['school_id'=>$a['sid'],'campus_id'=>$a['campus'],'student_id'=>$a['student'],'received_by'=>$a['mid'],'receipt_no'=>'RCPT-1','amount'=>'1000','method'=>'cash','paid_at'=>now(),'idempotency_key'=>'PAY-1']);
  DB::table('payment_reversals')->insert(['school_id'=>$a['sid'],'payment_id'=>$payment,'amount'=>'100','reason'=>'Wrong receipt correction','status'=>'posted','kind'=>'correction','idempotency_key'=>'REV-1']);
  $this->expectException(\Illuminate\Database\QueryException::class);
  DB::table('payment_reversals')->insert(['school_id'=>$a['sid'],'payment_id'=>$payment,'amount'=>'100','reason'=>'Actual returned cash','status'=>'posted','kind'=>'refund','refund_method'=>null,'idempotency_key'=>'REV-2']);
 }
 public function test_cashier_variance_must_match_the_recorded_amounts():void {
  $a=$this->graph();$this->expectException(\Illuminate\Database\QueryException::class);
  DB::table('cashier_closings')->insert(['school_id'=>$a['sid'],'campus_id'=>$a['campus'],'cashier_membership_id'=>$a['mid'],'closing_date'=>'2026-01-01','expected_cash'=>'1000','counted_cash'=>'900','variance'=>'0','status'=>'draft']);
 }
}
