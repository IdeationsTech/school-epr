<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
class LicenseService
{
 public const MODULES=['student_admissions','fees','attendance','daily_diary','tests_results','staff_payroll','manual_whatsapp_sharing'];
 public static function listing(string $state=''):\Illuminate\Database\Query\Builder {
  $today=now('Asia/Karachi')->toDateString();
  $base=function($q){$q->selectRaw('1')->from('subscriptions as sub')->whereColumn('sub.school_id','school_licenses.school_id')->where('sub.status','active');};
  $current=function($q)use($base,$today){$base($q);$q->where('sub.starts_on','<=',$today)->whereRaw('DATE_ADD(sub.ends_on, INTERVAL sub.grace_days DAY) >= ?',[$today]);};
  $q=DB::table('school_licenses')->join('schools','schools.id','=','school_licenses.school_id');
  if(in_array($state,['active','suspended','closed']))$q->where('school_licenses.status',$state);
  elseif(in_array($state,['valid','grace','expiring']))$q->where('school_licenses.status','active')->where('schools.status','active')->whereExists(function($sub)use($current,$today,$state){$current($sub);if($state==='grace')$sub->where('sub.ends_on','<',$today);else $sub->where('sub.ends_on','>=',$today);if($state==='expiring')$sub->where('sub.ends_on','<=',now('Asia/Karachi')->addDays(30)->toDateString());});
  if($state==='grace')$q->whereNotExists(function($sub)use($base,$today){$base($sub);$sub->where('sub.starts_on','<=',$today)->where('sub.ends_on','>=',$today);});
  if($state==='upcoming')$q->whereNotExists($current)->whereExists(function($sub)use($base,$today){$base($sub);$sub->where('sub.starts_on','>',$today);});
  elseif($state==='expired')$q->whereNotExists($current)->whereNotExists(function($sub)use($base,$today){$base($sub);$sub->where('sub.starts_on','>',$today);})->whereExists($base);
  elseif($state==='unassigned')$q->whereNotExists($base);
  return $q;
 }
 public static function ensure(int $sid,bool $legacy=false):object {
  $found=DB::table('school_licenses')->where('school_id',$sid)->first();if($found)return $found;
  DB::table('school_licenses')->insertOrIgnore(['school_id'=>$sid,'number'=>'LIC-'.str_pad((string)$sid,8,'0',STR_PAD_LEFT),'status'=>'active','legacy_access'=>$legacy,'issued_on'=>now('Asia/Karachi')->toDateString(),'created_at'=>now(),'updated_at'=>now()]);
  return DB::table('school_licenses')->where('school_id',$sid)->first();
 }
 public static function period(int $sid):?object {
  $today=CarbonImmutable::now('Asia/Karachi')->startOfDay();
  foreach(DB::table('subscriptions')->where('school_id',$sid)->where('status','active')->orderByDesc('starts_on')->get() as $s){
   if($s->starts_on<=$today->toDateString() && CarbonImmutable::parse($s->ends_on,'Asia/Karachi')->addDays($s->grace_days)->endOfDay()->greaterThanOrEqualTo($today))return $s;
  }return null;
 }
 public static function summary(int $sid):array {
  $l=DB::table('school_licenses')->where('school_id',$sid)->first();$s=self::period($sid);
  $latest=DB::table('subscriptions')->where('school_id',$sid)->where('status','active')->orderByDesc('ends_on')->first();
  $state=$s?($s->ends_on<now('Asia/Karachi')->toDateString()?'grace':'valid'):($latest?($latest->starts_on>now('Asia/Karachi')->toDateString()?'upcoming':'expired'):'unassigned');
  return ['license'=>$l,'period'=>$s,'latest'=>$latest,'state'=>$state,'allowed'=>self::allowed($sid,$l,$s),'usage'=>[
   'students'=>self::studentUsage($sid),
   'campuses'=>DB::table('campuses')->where('school_id',$sid)->where('status','active')->count()]];
 }
 public static function allowed(int $sid,?object $license=null,?object $period=null):bool {
  $license??=DB::table('school_licenses')->where('school_id',$sid)->first();
  if($license && $license->status!=='active')return false;
  if(!DB::table('subscriptions')->where('school_id',$sid)->exists())return $license?(bool)$license->legacy_access:true;
  return ($period??self::period($sid))!==null;
 }
 public static function studentUsage(int $sid):int {
  $today=now('Asia/Karachi')->toDateString();
  return DB::table('students as s')->where('s.school_id',$sid)->where('s.status','active')->whereExists(function($q)use($today){$q->selectRaw('1')->from('enrollments as e')->whereColumn('e.school_id','s.school_id')->whereColumn('e.student_id','s.id')->where('e.status','active')->where('e.starts_on','<=',$today)->where(fn($q)=>$q->whereNull('e.ends_on')->orWhere('e.ends_on','>=',$today));})->count();
 }
 public static function paid(int $sid,int $invoice):string {
  return (string)DB::table('subscription_payments as p')->where('p.school_id',$sid)->where('p.subscription_invoice_id',$invoice)->whereNotExists(function($q){$q->selectRaw('1')->from('subscription_payment_reversals as r')->whereColumn('r.school_id','p.school_id')->whereColumn('r.payment_id','p.id');})->sum('p.amount');
 }
 public static function cents(string|int|float $v):int {$p=explode('.',(string)$v);return (int)$p[0]*100+(int)str_pad(substr($p[1]??'',0,2),2,'0');}
 public static function refreshInvoice(int $sid,int $id):void {
  $i=DB::table('subscription_invoices')->where('school_id',$sid)->where('id',$id)->first();$paid=self::cents(self::paid($sid,$id));
  DB::table('subscription_invoices')->where('id',$id)->where('school_id',$sid)->update(['status'=>$paid===self::cents($i->amount)?'paid':($paid>0?'partial':'unpaid'),'updated_at'=>now()]);
 }
}
