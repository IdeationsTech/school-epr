<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\CarbonImmutable;
class SchoolFees
{
 private array $paidCache=[];
 public function __construct(public SchoolAccess $access){}
 public function fail(string $message):never{throw ValidationException::withMessages(['fees'=>$message]);}
 public static function cents(string|int $value):int{$parts=explode('.',(string)$value);return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');}
 public static function money(int $cents):string{$sign=$cents<0?'-':'';$cents=abs($cents);return $sign.intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT);}
 public function context(Request $r,string $permission='fees.view'):array {
  $allowed=$permission==='fees.view'?array_filter(['fees.view','fees.manage','fees.collect','fees.approve_reversal','fees.approve_concession','fees.request_reversal'],fn($p)=>$this->access->can($r,$p)):$this->access->can($r,$permission);abort_unless($allowed,403);
  $m=$this->access->membership($r);$sid=(int)$m->school_id;$period=LicenseService::period($sid);if($period)abort_unless(in_array('fees',json_decode($period->modules,true)??[],true),403,'Fees is not included in this school license.');
  $campuses=in_array('owner',$this->access->roles($m),true)?DB::table('campuses')->where('school_id',$sid)->pluck('id')->all():array_map(fn($c)=>$c->id,$this->access->campuses($m));return [$sid,array_map('intval',$campuses)];
 }
 public function lock(Request $r,string $permission,callable $fn){return DB::transaction(function()use($r,$permission,$fn){[$sid,$campuses]=$this->context($r,$permission);DB::table('schools')->where('id',$sid)->lockForUpdate()->first();return $fn($sid,$campuses);});}
 public function row(string $table,int $sid,int $id):object{return DB::table($table)->where('school_id',$sid)->where('id',$id)->first()??abort(404);}
 public function enrollment(int $sid,array $campuses,int $id):object {
  $e=DB::table('enrollments as e')->join('sections as s','s.id','=','e.section_id')->join('students as st','st.id','=','e.student_id')->join('classes as c','c.id','=','s.class_id')->join('campuses as p','p.id','=','s.campus_id')->join('academic_sessions as y','y.id','=','s.academic_session_id')->where('e.school_id',$sid)->where('e.id',$id)->select('e.*','s.campus_id','s.class_id','s.academic_session_id','st.admission_no','st.first_name','st.last_name','st.status as student_status','c.name as class_name','s.name as section_name','p.name as campus_name','y.name as session_name','y.starts_on as session_start','y.ends_on as session_end')->first()??abort(404);abort_unless(in_array((int)$e->campus_id,$campuses,true),404);return $e;
 }
 public function invoice(int $sid,array $campuses,int $id):object{$i=$this->row('invoices',$sid,$id);$i->enrollment=$this->enrollment($sid,$campuses,$i->enrollment_id);return $i;}
 public function bank(int $sid,array $campuses,?int $id,int $campus):void{if(!$id)return;$b=$this->row('bank_accounts',$sid,$id);if($b->campus_id!==null&&((int)$b->campus_id!==$campus||!in_array((int)$b->campus_id,$campuses,true)))$this->fail('Choose an account belonging to this campus or a school-wide account.');}
 public function netPayment(int $sid,object $p):int{return self::cents($p->amount)-(int)DB::table('payment_reversals')->where('school_id',$sid)->where('payment_id',$p->id)->where('status','approved')->selectRaw('COALESCE(SUM(amount)*100,0) AS cents')->value('cents');}
 public function primePaid(int $sid,array $ids):void {
  foreach($ids as $id)$this->paidCache[$sid][$id]=0;if(!$ids)return;
  $reversed=DB::table('payment_reversals')->where('school_id',$sid)->where('status','approved')->groupBy('payment_id')->selectRaw('payment_id,SUM(amount) as reversed_amount');
  $counts=DB::table('payment_allocations')->where('school_id',$sid)->groupBy('payment_id')->selectRaw('payment_id,COUNT(*) as allocation_count');
  $rows=DB::table('payment_allocations as a')->join('payments as p','p.id','=','a.payment_id')->leftJoinSub($reversed,'r',fn($join)=>$join->on('r.payment_id','=','p.id'))->joinSub($counts,'n',fn($join)=>$join->on('n.payment_id','=','p.id'))->where('a.school_id',$sid)->whereIn('a.invoice_id',$ids)->get(['a.invoice_id','a.amount as allocated','p.amount','r.reversed_amount','n.allocation_count']);
  foreach($rows as $row){$original=self::cents($row->amount);$rev=self::cents($row->reversed_amount??'0.00');if($rev>$original)$this->fail('A legacy payment is over-reversed; reconcile it before reporting.');if($rev>0&&$rev<$original&&$row->allocation_count>1)$this->fail('A legacy multi-invoice payment has a partial reversal requiring reconciliation.');$this->paidCache[$sid][$row->invoice_id]+=$rev===$original?0:self::cents($row->allocated)-$rev;}
 }
 public function paid(int $sid,int $invoice):int {
  if(array_key_exists($invoice,$this->paidCache[$sid]??[]))return $this->paidCache[$sid][$invoice];
  $sum=0;foreach(DB::table('payment_allocations as a')->join('payments as p','p.id','=','a.payment_id')->where('a.school_id',$sid)->where('a.invoice_id',$invoice)->get(['p.*','a.amount as allocated']) as $p){$original=self::cents($p->amount);$net=$this->netPayment($sid,$p);$allocated=self::cents($p->allocated);if($net===$original)$sum+=$allocated;elseif($net===0)continue;else{if(DB::table('payment_allocations')->where('school_id',$sid)->where('payment_id',$p->id)->count()!==1)$this->fail('Legacy multi-invoice payment has a partial reversal. Reconcile this record before collection.');$sum+=min($net,$allocated);}}
  return $sum;
 }
 public function totals(int $sid,object $i):array{$paid=$this->paid($sid,$i->id);$total=self::cents($i->total);$balance=$i->status==='void'?0:max(0,$total-$paid);return ['paid'=>self::money($paid),'balance'=>self::money($balance),'state'=>$i->status==='void'?'void':($balance===0?'paid':($paid>0?'partial':'unpaid'))];}
 public function refresh(int $sid,int $id):void{$i=$this->row('invoices',$sid,$id);if($i->status!=='void')DB::table('invoices')->where('school_id',$sid)->where('id',$id)->update(['status'=>$this->totals($sid,$i)['state'],'updated_at'=>now()]);}
 public function number(string $prefix,int $sid,string $table):string{return $prefix.'-'.str_pad((string)$sid,4,'0',STR_PAD_LEFT).'-'.str_pad((string)((int)DB::table($table)->where('school_id',$sid)->max('id')+1),7,'0',STR_PAD_LEFT);}
 public function generateRows(int $sid,array $campuses,array $d):array {
  $month=CarbonImmutable::createFromFormat('!Y-m',$d['month'],'Asia/Karachi');$start=$month->toDateString();$end=$month->endOfMonth()->toDateString();
  $q=DB::table('enrollments as e')->join('sections as s','s.id','=','e.section_id')->where('e.school_id',$sid)->whereIn('s.campus_id',$campuses)->where('e.starts_on','<=',$end)->where(fn($q)=>$q->whereNull('e.ends_on')->orWhere('e.ends_on','>=',$start));
  if(!empty($d['section_id']))$q->where('s.id',$d['section_id']);if(!empty($d['campus_id']))$q->where('s.campus_id',$d['campus_id']);if(!empty($d['student_id']))$q->where('e.student_id',$d['student_id']);
  $ids=$q->orderBy('e.starts_on')->orderBy('e.id')->get(['e.id','e.student_id'])->unique('student_id');$rows=[];
  foreach($ids as $id){$e=$this->enrollment($sid,$campuses,$id->id);$row=['enrollment_id'=>$e->id,'student_id'=>$e->student_id,'student_name'=>trim($e->first_name.' '.$e->last_name),'admission_no'=>$e->admission_no,'placement'=>$e->class_name.' · '.$e->section_name,'status'=>'ready','error'=>null,'total'=>'0.00','items'=>[]];
   if(DB::table('invoices as i')->join('enrollments as en','en.id','=','i.enrollment_id')->where('i.school_id',$sid)->where('en.student_id',$e->student_id)->where('i.period',$start)->where('i.kind','monthly')->where('i.status','<>','void')->exists()){$row['status']='already_billed';$rows[]=$row;continue;}
   $date=max($start,$e->starts_on);$assignment=DB::table('student_fee_assignments')->where('school_id',$sid)->where('enrollment_id',$e->id)->where('starts_on','<=',$date)->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$date))->orderByDesc('starts_on')->first();
   if(!$assignment){$row['status']='error';$row['error']='No fee plan assignment covers '.$date;$rows[]=$row;continue;}
   $plan=$this->row('fee_plans',$sid,$assignment->fee_plan_id);
   if($date<$plan->starts_on||($plan->ends_on&&$date>$plan->ends_on)||$date>$e->session_end||$date<$e->session_start){$row['status']='error';$row['error']='Plan or academic session does not cover billing date.';$rows[]=$row;continue;}
   $row['assignment_id']=$assignment->id;$row['date']=$date;$total=0;
   foreach(DB::table('fee_plan_items as item')->join('fee_heads as h','h.id','=','item.fee_head_id')->where('item.school_id',$sid)->where('item.fee_plan_id',$plan->id)->get(['item.*','h.name as head_name']) as $item){
    if(!in_array($item->frequency,['monthly','annual','once'],true)){$row['status']='error';$row['error']='Unsupported legacy frequency; create a reviewed fee plan.';break;}
    if($item->frequency!=='monthly'){$billed=DB::table('invoice_items as l')->join('invoices as i','i.id','=','l.invoice_id')->join('enrollments as en','en.id','=','i.enrollment_id')->join('sections as sec','sec.id','=','en.section_id')->where('l.school_id',$sid)->where('en.student_id',$e->student_id)->where('l.fee_head_id',$item->fee_head_id)->where('i.status','<>','void');if($item->frequency==='annual')$billed->where('sec.academic_session_id',$e->academic_session_id);if($billed->exists())continue;}
    $amount=self::cents($item->amount);$discount=0;$concessions=DB::table('student_concessions')->where('school_id',$sid)->where('enrollment_id',$e->id)->where('fee_head_id',$item->fee_head_id)->whereNotNull('approved_by')->where('starts_on','<=',$date)->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$date))->get();if($concessions->count()>1){$row['status']='error';$row['error']='Overlapping concessions need review.';break;}
    if($c=$concessions->first())$discount=$c->kind==='fixed'?min($amount,self::cents($c->value)):intdiv($amount*self::cents($c->value)+5000,10000);
    $net=$amount-$discount;$row['items'][]=['fee_head_id'=>$item->fee_head_id,'description'=>$item->head_name,'quantity'=>'1.00','unit_amount'=>self::money($amount),'discount_amount'=>self::money($discount),'line_total'=>self::money($net),'billing_frequency'=>$item->frequency];$total+=$net;
   }
   if(!$row['items']&&$row['status']==='ready'){$row['status']='error';$row['error']='No billable fee items for this period.';}$row['total']=self::money($total);$rows[]=$row;
  }return $rows;
 }
 public function issue(Request $r,int $sid,array $campuses,array $d):array {
  $rows=$this->generateRows($sid,$campuses,$d);if(!$rows)$this->fail('No enrollments match this billing month and scope.');foreach($rows as $row)if($row['status']==='error')$this->fail($row['admission_no'].': '.$row['error'].' No invoices were generated.');$issued=[];$period=$d['month'].'-01';
  foreach($rows as $row){if($row['status']!=='ready')continue;$en=$row['enrollment_id'];$inst=(int)DB::table('invoices')->where('school_id',$sid)->where('enrollment_id',$en)->where('period',$period)->where('kind','monthly')->max('installment_no')+1;
   $number=$this->number('INV',$sid,'invoices');$id=DB::table('invoices')->insertGetId(['school_id'=>$sid,'enrollment_id'=>$en,'number'=>$number,'period'=>$period,'kind'=>'monthly','installment_no'=>$inst,'issued_on'=>$d['issued_on'],'due_on'=>$d['due_on'],'status'=>self::cents($row['total'])===0?'paid':'unpaid','total'=>$row['total'],'fee_assignment_id'=>$row['assignment_id'],'created_at'=>now(),'updated_at'=>now()]);foreach($row['items'] as $line)DB::table('invoice_items')->insert($line+['school_id'=>$sid,'invoice_id'=>$id,'created_at'=>now(),'updated_at'=>now()]);$this->access->audit($r,'fees.invoice_issued','invoices',$id,['number'=>$number,'total'=>$row['total'],'period'=>$period]);$issued[]=$id;
  }return ['issued'=>count($issued),'skipped'=>count($rows)-count($issued),'invoice_ids'=>$issued];
 }
}
