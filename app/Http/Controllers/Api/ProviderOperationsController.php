<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class ProviderOperationsController extends Controller
{
 private function audit(Request $r,string $action,string $type,int $id,array $changes):void{\App\Services\PlatformAudit::record($r->user()->id,$action,$type,$id,$changes);}
 public function licenses(Request $r){
  $d=$r->validate(['search'=>'nullable|string|max:100','state'=>['nullable',Rule::in(['active','suspended','closed','valid','grace','expired','upcoming','expiring','unassigned'])]]);
  $q=LicenseService::listing($d['state']??'')->when($d['search']??'',fn($q,$v)=>$q->where(fn($q)=>$q->where('schools.name','like','%'.$v.'%')->orWhere('school_licenses.number','like','%'.$v.'%')));
  $page=$q->orderByDesc('school_licenses.id')->paginate(20,['school_licenses.*','schools.name as school_name']);
  $page->setCollection($page->getCollection()->map(fn($l)=>['school_id'=>$l->school_id,'school_name'=>$l->school_name]+LicenseService::summary($l->school_id)));
  return response()->json($page);

 }
 public function schoolInfo(Request $r,int $school){
  $d=$r->validate(['name'=>'required|string|min:2|max:160','phone'=>['required',new \App\Rules\PakistanPhone()],'email'=>'nullable|email|max:160','address'=>'required|string|min:8|max:2000']);
  DB::transaction(function()use($r,$school,$d){$before=DB::table('schools')->where('id',$school)->lockForUpdate()->first();abort_unless($before,404);DB::table('schools')->where('id',$school)->update(['name'=>$d['name'],'updated_at'=>now()]);DB::table('school_settings')->updateOrInsert(['school_id'=>$school,'key'=>'contact'],['value'=>json_encode(['phone'=>\App\Rules\PakistanPhone::canonical($d['phone']),'email'=>$d['email']??'','address'=>$d['address']]),'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'school.profile_updated','school',$school,['previous_name'=>$before->name,'name'=>$d['name'],'fields'=>array_keys($d)]);});return response()->json(['message'=>'School contact details updated.']);
 }
 public function licenseStatus(Request $r,int $school){
  $d=$r->validate(['status'=>['required',Rule::in(['active','suspended','closed'])],'reason'=>'required|string|min:5|max:500']);
  DB::transaction(function()use($r,$school,$d){abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);$l=LicenseService::ensure($school,true);DB::table('school_licenses')->where('id',$l->id)->update(['status'=>$d['status'],'updated_at'=>now()]);DB::table('schools')->where('id',$school)->update(['status'=>$d['status']==='active'?'active':'inactive','updated_at'=>now()]);$this->audit($r,'license.status_changed','school_license',$l->id,['school_id'=>$school,'before'=>$l->status,'after'=>$d['status'],'reason'=>$d['reason']]);});return response()->json(['message'=>'License status updated.']);
 }
 public function cancelPeriod(Request $r,int $school,int $subscription){
  $d=$r->validate(['reason'=>'required|string|min:5|max:500']);DB::transaction(function()use($r,$school,$subscription,$d){abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);$s=DB::table('subscriptions')->where('school_id',$school)->where('id',$subscription)->lockForUpdate()->first();abort_unless($s,404);if($s->status!=='active')throw ValidationException::withMessages(['subscription'=>'Period is already cancelled.']);DB::table('subscriptions')->where('id',$subscription)->update(['status'=>'cancelled','updated_at'=>now()]);$this->audit($r,'subscription.cancelled','subscription',$subscription,['school_id'=>$school,'reason'=>$d['reason'],'previous'=>$s]);});return response()->json(['message'=>'Period cancelled. Invoice/payment history is retained.']);
 }
 public function billing(Request $r){
  $d=$r->validate(['status'=>['nullable',Rule::in(['paid','partial','unpaid'])],'school_id'=>'nullable|integer','search'=>'nullable|string|max:100']);
  $q=DB::table('subscription_invoices as i')->join('schools as s','s.id','=','i.school_id')->when($d['status']??'',fn($q,$v)=>$q->where('i.status',$v))->when($d['school_id']??null,fn($q,$v)=>$q->where('i.school_id',$v))->when($d['search']??'',fn($q,$v)=>$q->where(fn($q)=>$q->where('s.name','like','%'.$v.'%')->orWhere('i.number','like','%'.$v.'%')))->orderByDesc('i.id');
  $p=$q->paginate(20,['i.*','s.name as school_name']);foreach($p->items() as $i){$i->paid=LicenseService::paid($i->school_id,$i->id);$i->balance=number_format((LicenseService::cents($i->amount)-LicenseService::cents($i->paid))/100,2,'.','');}return response()->json($p);
 }
 public function reverse(Request $r,int $school,int $payment){
  $d=$r->validate(['reason'=>'required|string|min:5|max:500']);DB::transaction(function()use($r,$school,$payment,$d){
   abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);$p=DB::table('subscription_payments')->where('school_id',$school)->where('id',$payment)->lockForUpdate()->first();abort_unless($p,404);
   if(DB::table('subscription_payment_reversals')->where('payment_id',$payment)->exists())throw ValidationException::withMessages(['payment'=>'Payment is already reversed.']);
   DB::table('subscription_payment_reversals')->insert(['school_id'=>$school,'payment_id'=>$payment,'actor_user_id'=>$r->user()->id,'reason'=>$d['reason'],'created_at'=>now(),'updated_at'=>now()]);LicenseService::refreshInvoice($school,$p->subscription_invoice_id);$this->audit($r,'payment.reversed','subscription_payment',$payment,['school_id'=>$school,'amount'=>$p->amount,'reason'=>$d['reason']]);
  });return response()->json(['message'=>'Payment reversed. Original record retained.']);
 }
 public function adjust(Request $r,int $school,int $invoice){
  $d=$r->validate(['amount'=>'required|numeric|min:0|max:99999999.99|decimal:0,2','reason'=>'required|string|min:5|max:500']);DB::transaction(function()use($r,$school,$invoice,$d){
   abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);$i=DB::table('subscription_invoices')->where('school_id',$school)->where('id',$invoice)->lockForUpdate()->first();abort_unless($i,404);
   if(LicenseService::cents($d['amount'])<LicenseService::cents(LicenseService::paid($school,$invoice)))throw ValidationException::withMessages(['amount'=>'New amount cannot be below the net paid amount. Reverse the incorrect receipt first.']);
   DB::table('subscription_invoice_adjustments')->insert(['school_id'=>$school,'invoice_id'=>$invoice,'actor_user_id'=>$r->user()->id,'previous_amount'=>$i->amount,'new_amount'=>$d['amount'],'reason'=>$d['reason'],'created_at'=>now(),'updated_at'=>now()]);DB::table('subscription_invoices')->where('id',$invoice)->update(['amount'=>$d['amount'],'updated_at'=>now()]);LicenseService::refreshInvoice($school,$invoice);$this->audit($r,'invoice.adjusted','subscription_invoice',$invoice,['school_id'=>$school,'before'=>$i->amount,'after'=>$d['amount'],'reason'=>$d['reason']]);
  });return response()->json(['message'=>'Invoice adjustment recorded.']);
 }
 public function document(Request $r,int $school,int $invoice){
  $i=DB::table('subscription_invoices')->where('school_id',$school)->where('id',$invoice)->first();abort_unless($i,404);
  $d=$r->validate(['payment_id'=>'nullable|integer']);$payments=DB::table('subscription_payments')->where('school_id',$school)->where('subscription_invoice_id',$invoice)->orderBy('id')->get()->map(function($p){$p->reversed=DB::table('subscription_payment_reversals')->where('school_id',$p->school_id)->where('payment_id',$p->id)->exists();return $p;});
  $receipt=null;if(isset($d['payment_id'])){$receipt=$payments->firstWhere('id',$d['payment_id']);abort_unless($receipt,404);}
  $i->paid=LicenseService::paid($school,$invoice);return response()->json(['invoice'=>$i,'receipt'=>$receipt,'payments'=>$payments,'school'=>DB::table('schools')->where('id',$school)->first(['name','code']),'provider'=>json_decode(DB::table('provider_settings')->where('key','profile')->value('value')??'{}',true)]);
 }
 public function auditLog(Request $r){
  $d=$r->validate(['search'=>'nullable|string|max:100','actor'=>'nullable|integer|exists:users,id','school_id'=>'nullable|integer|exists:schools,id','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d'.($r->filled('from')?'|after_or_equal:from':'')]);
  $q=DB::table('platform_audit_logs as a')->join('users as u','u.id','=','a.actor_user_id')->leftJoin('schools as s','s.id','=','a.school_id')->when($d['search']??'',fn($q,$v)=>$q->where(fn($q)=>$q->where('a.action','like','%'.$v.'%')->orWhere('a.target_type','like','%'.$v.'%')->orWhere('s.name','like','%'.$v.'%')->orWhere('s.code','like','%'.$v.'%')))->when($d['actor']??null,fn($q,$v)=>$q->where('a.actor_user_id',$v))->when($d['school_id']??null,fn($q,$v)=>$q->where('a.school_id',$v));
  if(!empty($d['from']))$q->where('a.created_at','>=',\Carbon\CarbonImmutable::parse($d['from'],'Asia/Karachi')->startOfDay()->utc());
  if(!empty($d['to']))$q->where('a.created_at','<=',\Carbon\CarbonImmutable::parse($d['to'],'Asia/Karachi')->endOfDay()->utc());
  return response()->json($q->orderByDesc('a.id')->paginate(25,['a.*','u.name as actor_name','s.name as school_name','s.code as school_code'])->toArray()+['actors'=>DB::table('users as u')->whereExists(fn($q)=>$q->selectRaw('1')->from('platform_audit_logs as a')->whereColumn('a.actor_user_id','u.id'))->orderBy('u.name')->get(['u.id','u.name'])]);
 }
 public function settings(){return response()->json(json_decode(DB::table('provider_settings')->where('key','profile')->value('value')??'{}',true));}
 public function saveSettings(Request $r){$d=$r->validate(['name'=>'required|string|min:2|max:160','phone'=>['required',new \App\Rules\PakistanPhone()],'email'=>'required|email|max:160','address'=>'required|string|min:8|max:2000','receipt_footer'=>'nullable|string|max:500']);$d['phone']=\App\Rules\PakistanPhone::canonical($d['phone']);DB::transaction(function()use($r,$d){DB::table('provider_settings')->updateOrInsert(['key'=>'profile'],['value'=>json_encode($d),'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'provider.profile_updated','provider_settings',1,['fields'=>array_keys($d)]);});return response()->json(['message'=>'Provider profile saved.']);}
 public function tickets(Request $r){$d=$r->validate(['status'=>['nullable',Rule::in(['open','in_progress','resolved','closed'])],'search'=>'nullable|string|max:100']);return response()->json(DB::table('support_tickets as t')->join('schools as s','s.id','=','t.school_id')->when($d['status']??'',fn($q,$v)=>$q->where('t.status',$v))->when($d['search']??'',fn($q,$v)=>$q->where(fn($q)=>$q->where('t.subject','like','%'.$v.'%')->orWhere('s.name','like','%'.$v.'%')))->orderByDesc('t.id')->paginate(20,['t.*','s.name as school_name']));}
 public function ticket(int $ticket){$t=DB::table('support_tickets')->where('id',$ticket)->first();abort_unless($t,404);return response()->json(['ticket'=>$t,'replies'=>DB::table('support_replies as r')->join('users as u','u.id','=','r.author_user_id')->where('r.school_id',$t->school_id)->where('support_ticket_id',$ticket)->orderBy('r.id')->get(['r.*','u.name as author_name']),'admins'=>DB::table('platform_admins as a')->join('users as u','u.id','=','a.user_id')->where('a.active',true)->get(['u.id','u.name'])]);}
 public function saveTicket(Request $r,?int $ticket=null){
  $d=$r->validate(['school_id'=>'required|integer|exists:schools,id','subject'=>'required|string|min:3|max:160','description'=>'required|string|min:5|max:10000','status'=>['required',Rule::in(['open','in_progress','resolved','closed'])],'priority'=>['required',Rule::in(['low','normal','high','urgent'])],'assigned_user_id'=>['nullable','integer',Rule::exists('platform_admins','user_id')->where('active',true)],'reply'=>'nullable|string|max:10000']);
  DB::transaction(function()use($r,$d,$ticket){
   $target=$ticket?DB::table('support_tickets')->where('id',$ticket)->lockForUpdate()->first():null;if($ticket)abort_unless($target,404);if($target&&$target->school_id!=$d['school_id'])throw ValidationException::withMessages(['school_id'=>'A ticket cannot be moved to another school.']);
   $values=['subject'=>$d['subject'],'description'=>$d['description'],'status'=>$d['status'],'priority'=>$d['priority'],'assigned_user_id'=>$d['assigned_user_id']??null,'resolved_at'=>in_array($d['status'],['resolved','closed'])?($target->resolved_at??now()):null,'updated_at'=>now()];
   if($ticket){DB::table('support_tickets')->where('id',$ticket)->update($values);$id=$ticket;}
   else{$mid=DB::table('school_memberships')->where('school_id',$d['school_id'])->orderBy('id')->value('id');if(!$mid)throw ValidationException::withMessages(['school_id'=>'School has no member to associate with this ticket.']);$id=DB::table('support_tickets')->insertGetId($values+['school_id'=>$d['school_id'],'opened_by'=>$mid,'created_at'=>now()]);}
   if(!empty($d['reply']))DB::table('support_replies')->insert(['school_id'=>$d['school_id'],'support_ticket_id'=>$id,'author_user_id'=>$r->user()->id,'body'=>$d['reply'],'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'support.ticket_updated','support_ticket',$id,['status'=>$d['status'],'priority'=>$d['priority'],'assigned_user_id'=>$d['assigned_user_id']??null]);
  });return response()->json(['message'=>'Support ticket saved.']);
 }
}
