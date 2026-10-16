<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Rules\PakistanPhone;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class PlatformController extends Controller
{
    private function audit(Request $r,string $action,string $type,int $id,array $changes=[]): void
    {
        DB::table('platform_audit_logs')->insert(['actor_user_id'=>$r->user()->id,'action'=>$action,'target_type'=>$type,'target_id'=>$id,'changes'=>json_encode($changes),'created_at'=>now(),'updated_at'=>now()]);
    }
    private function cents(string|int|float $amount): int
    {
        $parts=explode('.',(string)$amount);return ((int)$parts[0])*100+(int)str_pad(substr($parts[1]??'',0,2),2,'0');
    }
    public function dashboard()
    {
        $today=now('Asia/Karachi')->toDateString();
        return response()->json(['schools'=>DB::table('schools')->count(),'active_schools'=>DB::table('schools')->where('status','active')->count(),
            'expiring'=>DB::table('subscriptions')->where('status','active')->whereBetween('ends_on',[$today,now('Asia/Karachi')->addDays(30)->toDateString()])->distinct()->count('school_id'),
            'received'=>DB::table('subscription_payments as p')->whereNotExists(fn($q)=>$q->selectRaw('1')->from('subscription_payment_reversals as r')->whereColumn('r.payment_id','p.id'))->sum('p.amount'),
            'outstanding'=>DB::table('subscription_invoices')->get()->sum(fn($i)=>max(0,$this->cents($i->amount)-$this->cents(LicenseService::paid($i->school_id,$i->id))))/100,
            'suspended'=>DB::table('school_licenses')->where('status','suspended')->count(),
            'open_tickets'=>DB::table('support_tickets')->whereIn('status',['open','in_progress'])->count(),
            'audit'=>DB::table('platform_audit_logs')->join('users','users.id','=','platform_audit_logs.actor_user_id')->orderByDesc('platform_audit_logs.id')->limit(12)->get(['platform_audit_logs.*','users.name as actor_name'])]);
    }
    public function plans(){return response()->json(DB::table('plans')->orderBy('monthly_price')->get()->map(function($p){$old=json_decode($p->features??'{}',true);$p->features=json_encode(array_merge($old??[],['active'=>(bool)$p->available,'annual_price'=>$p->annual_price,'modules'=>json_decode($p->modules??'[]',true)]));return $p;}));}
    public function savePlan(Request $r,?int $plan=null)
    {
        if($plan)abort_unless(DB::table('plans')->where('id',$plan)->exists(),404);
        $d=$r->validate(['code'=>['required','alpha_dash','max:30',Rule::unique('plans','code')->ignore($plan)],'name'=>'required|string|min:2|max:160',
            'student_limit'=>'required|integer|min:1|max:100000','campus_limit'=>'required|integer|min:1|max:1000',
            'monthly_price'=>'required|numeric|min:0|max:99999999.99|decimal:0,2','annual_price'=>'required|numeric|min:0|max:99999999.99|decimal:0,2','active'=>'required|boolean','modules'=>'sometimes|array','modules.*'=>[Rule::in(LicenseService::MODULES)]]);
        DB::transaction(function()use($r,$d,$plan):void{
            if($plan)DB::table('plans')->where('id',$plan)->lockForUpdate()->first();
            $values=$d;unset($values['active'],$values['modules']);$values['available']=$d['active'];$values['modules']=json_encode($d['modules']??LicenseService::MODULES);
            $existing=$plan?json_decode(DB::table('plans')->where('id',$plan)->value('features')??'{}',true):[];
            $values['features']=json_encode(array_merge($existing??[],['annual_price'=>$d['annual_price'],'active'=>$d['active'],'modules'=>$d['modules']??LicenseService::MODULES]));$values['updated_at']=now();
            if($plan){DB::table('plans')->where('id',$plan)->update($values);$id=$plan;}
            else{$id=DB::table('plans')->insertGetId($values+['created_at'=>now()]);}
            $this->audit($r,'plan.saved','plan',$id,['fields'=>array_keys($d)]);
        });return response()->json(['message'=>'Package saved.']);
    }
    private function checkLimits(int $sid,array|object $plan): void
    {
        $p=(array)$plan;
        if(DB::table('campuses')->where('school_id',$sid)->where('status','active')->count()>(int)$p['campus_limit'])throw ValidationException::withMessages(['plan_id'=>'Package campus limit is below this school’s active campus count.']);
        if(LicenseService::studentUsage($sid)>(int)$p['student_limit'])throw ValidationException::withMessages(['plan_id'=>'Package student limit is below this school’s student count.']);
    }
    public function schools(Request $r)
    {
        $search=$r->validate(['search'=>'nullable|string|max:100'])['search']??'';
        $q=DB::table('schools')->orderByDesc('id');
        if($search!=='')$q->where(function($q)use($search){$q->where('name','like','%'.$search.'%')->orWhere('code','like','%'.$search.'%');});
        $page=$q->paginate(20);
        foreach($page->items() as $s){$s->license=LicenseService::summary((int)$s->id);$s->subscription=DB::table('subscriptions')->join('plans','plans.id','=','subscriptions.plan_id')->where('subscriptions.school_id',$s->id)->orderByDesc('ends_on')->first(['subscriptions.*','plans.name as plan_name']);}
        return response()->json($page);
    }
    public function school(int $school)
    {
        $s=DB::table('schools')->where('id',$school)->first();abort_unless($s,404);
        $s->contact=json_decode(DB::table('school_settings')->where('school_id',$school)->where('key','contact')->value('value')??'{}',true);
        return response()->json(['school'=>$s,'entitlement'=>LicenseService::summary($school),'adjustments'=>DB::table('subscription_invoice_adjustments')->where('school_id',$school)->orderByDesc('id')->get(),'campuses'=>DB::table('campuses')->where('school_id',$school)->get(['id','name','code','status']),
            'owners'=>DB::table('school_memberships')->join('membership_roles','membership_roles.membership_id','=','school_memberships.id')->join('roles','roles.id','=','membership_roles.role_id')->join('users','users.id','=','school_memberships.user_id')
                ->where('school_memberships.school_id',$school)->where('membership_roles.school_id',$school)->where('roles.name','owner')->get(['users.name','users.email','school_memberships.status']),
            'subscriptions'=>DB::table('subscriptions')->join('plans','plans.id','=','subscriptions.plan_id')->where('subscriptions.school_id',$school)->orderByDesc('ends_on')->get(['subscriptions.*','plans.name as plan_name']),
            'invoices'=>DB::table('subscription_invoices')->where('school_id',$school)->orderByDesc('id')->get()->map(function($i){$i->paid=LicenseService::paid($i->school_id,$i->id);return $i;}),
            'payments'=>DB::table('subscription_payments')->where('school_id',$school)->orderByDesc('id')->get()->map(function($p){$p->reversal=DB::table('subscription_payment_reversals')->where('school_id',$p->school_id)->where('payment_id',$p->id)->first();return $p;})]);
    }
    public function createSchool(Request $r)
    {
        $r->merge(['code'=>strtoupper((string)$r->input('code')),'owner_email'=>strtolower(trim((string)$r->input('owner_email')))]);
        $d=$r->validate(['name'=>'required|string|min:2|max:160','code'=>'required|alpha_dash|max:30|unique:schools,code','phone'=>['required',new PakistanPhone()],
            'address'=>'required|string|min:8|max:2000','owner_name'=>'required|string|min:2|max:160','owner_email'=>'required|email|max:160|unique:users,email',
            'password'=>['required','string','min:12','confirmed',new \App\Rules\PasswordByteLimit()],
            'plan_id'=>'required|integer|exists:plans,id','starts_on'=>'required|date_format:Y-m-d','ends_on'=>'required|date_format:Y-m-d|after_or_equal:starts_on',
            'price'=>'required|numeric|min:0|max:99999999.99|decimal:0,2']+$this->entitlementRules());
        $id=DB::transaction(function()use($r,$d){
            $plan=DB::table('plans')->where('id',$d['plan_id'])->lockForUpdate()->first();$this->activePlan($plan);
            $sid=DB::table('schools')->insertGetId(['name'=>$d['name'],'code'=>$d['code'],'status'=>'active','timezone'=>'Asia/Karachi','currency'=>'PKR','created_at'=>now(),'updated_at'=>now()]);
            $cid=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main Campus','code'=>'MAIN','phone'=>PakistanPhone::canonical($d['phone']),'address'=>$d['address'],'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            DB::table('school_settings')->insert(['school_id'=>$sid,'key'=>'contact','value'=>json_encode(['phone'=>PakistanPhone::canonical($d['phone']),'address'=>$d['address'],'email'=>$d['owner_email']]),'created_at'=>now(),'updated_at'=>now()]);
            $uid=DB::table('users')->insertGetId(['name'=>$d['owner_name'],'email'=>$d['owner_email'],'password'=>Hash::make($d['password']),'created_at'=>now(),'updated_at'=>now()]);
            $mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$uid,'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            $rid=DB::table('roles')->where('name','owner')->where('scope','school')->value('id');abort_unless($rid,422,'Run database seeders before onboarding schools.');
            DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$mid,'role_id'=>$rid,'created_at'=>now(),'updated_at'=>now()]);
            DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid,'created_at'=>now(),'updated_at'=>now()]);
            $this->subscription($r,$sid,$d);$this->audit($r,'school.created','school',$sid,['owner_user_id'=>$uid]);return $sid;
        });return response()->json(['id'=>$id,'message'=>'School, main campus, owner and subscription created.'],201);
    }
    private function entitlementRules():array {
        return ['kind'=>['sometimes',Rule::in(['paid','trial'])],'grace_days'=>'sometimes|integer|min:0|max:30',
            'student_limit'=>'sometimes|integer|min:1|max:100000','campus_limit'=>'sometimes|integer|min:1|max:1000',
            'modules'=>'sometimes|array','modules.*'=>[Rule::in(LicenseService::MODULES)]];
    }
    private function activePlan(object $plan): void
    {
        if(!(bool)$plan->available)throw ValidationException::withMessages(['plan_id'=>'Choose an active package.']);
    }
    private function subscription(Request $r,int $sid,array $d): void
    {
        $plan=DB::table('plans')->where('id',$d['plan_id'])->first();
        $license=LicenseService::ensure($sid);
        $studentLimit=$d['student_limit']??$plan->student_limit;$campusLimit=$d['campus_limit']??$plan->campus_limit;
        $this->checkLimits($sid,['student_limit'=>$studentLimit,'campus_limit'=>$campusLimit]);
        DB::table('school_licenses')->where('id',$license->id)->update(['legacy_access'=>false,'updated_at'=>now()]);
        $id=DB::table('subscriptions')->insertGetId(['school_id'=>$sid,'license_id'=>$license->id,'plan_id'=>$d['plan_id'],'package_name'=>$plan->name,
            'kind'=>$d['kind']??'paid','grace_days'=>$d['grace_days']??0,'student_limit'=>$studentLimit,'campus_limit'=>$campusLimit,
            'modules'=>json_encode($d['modules']??json_decode($plan->modules??'[]',true)),
            'starts_on'=>$d['starts_on'],'ends_on'=>$d['ends_on'],'price'=>$d['price'],'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
        DB::table('subscription_invoices')->insert(['school_id'=>$sid,'subscription_id'=>$id,'number'=>'SUB-'.$sid.'-'.$id,'issued_on'=>now('Asia/Karachi')->toDateString(),'due_on'=>$d['starts_on'],'amount'=>$d['price'],'status'=>$this->cents($d['price'])===0?'paid':'unpaid','created_at'=>now(),'updated_at'=>now()]);
        $this->audit($r,'subscription.created','subscription',$id,['school_id'=>$sid,'plan_id'=>$d['plan_id'],'price'=>$d['price']]);
    }
    public function renew(Request $r,int $school)
    {
        abort_unless(DB::table('schools')->where('id',$school)->exists(),404);
        $d=$r->validate(['plan_id'=>'required|integer|exists:plans,id','starts_on'=>'required|date_format:Y-m-d','ends_on'=>'required|date_format:Y-m-d|after_or_equal:starts_on','price'=>'required|numeric|min:0|max:99999999.99|decimal:0,2']+$this->entitlementRules());
        DB::transaction(function()use($r,$school,$d):void{
            $plan=DB::table('plans')->where('id',$d['plan_id'])->lockForUpdate()->first();$this->activePlan($plan);
            DB::table('schools')->where('id',$school)->lockForUpdate()->first();
            $overlap=DB::table('subscriptions')->where('school_id',$school)->where('status','active')->where('starts_on','<=',$d['ends_on'])->where('ends_on','>=',$d['starts_on'])->exists();
            if($overlap)throw ValidationException::withMessages(['starts_on'=>'Subscription periods cannot overlap. Start after the existing period ends.']);
            $this->subscription($r,$school,$d);
        });return response()->json(['message'=>'Subscription and invoice created.']);
    }
    public function changePackage(Request $r,int $school,int $subscription)
    {
        $d=$r->validate(['plan_id'=>'required|integer|exists:plans,id','starts_on'=>'required|date_format:Y-m-d|after_or_equal:'.now('Asia/Karachi')->toDateString(),
            'ends_on'=>'required|date_format:Y-m-d|after_or_equal:starts_on','price'=>'required|numeric|min:0|max:99999999.99|decimal:0,2','reason'=>'required|string|min:5|max:500']+$this->entitlementRules());
        DB::transaction(function()use($r,$school,$subscription,$d):void{
            $p=DB::table('plans')->where('id',$d['plan_id'])->lockForUpdate()->first();$this->activePlan($p);
            abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);
            $old=DB::table('subscriptions')->where('school_id',$school)->where('id',$subscription)->lockForUpdate()->first();abort_unless($old,404);
            if($old->status!=='active'||$d['starts_on']<$old->starts_on||$d['starts_on']>$old->ends_on)throw ValidationException::withMessages(['starts_on'=>'Choose an effective date within the active period.']);
            $overlap=DB::table('subscriptions')->where('school_id',$school)->where('id','<>',$subscription)->where('status','active')->where('starts_on','<=',$d['ends_on'])->where('ends_on','>=',$d['starts_on'])->exists();
            if($overlap)throw ValidationException::withMessages(['starts_on'=>'Replacement overlaps another period.']);
            if($d['starts_on']===$old->starts_on)DB::table('subscriptions')->where('id',$subscription)->update(['status'=>'replaced','updated_at'=>now()]);
            else DB::table('subscriptions')->where('id',$subscription)->update(['ends_on'=>\Carbon\Carbon::parse($d['starts_on'])->subDay()->toDateString(),'grace_days'=>0,'updated_at'=>now()]);
            $this->subscription($r,$school,$d);
            $this->audit($r,'subscription.package_changed','subscription',$subscription,['before'=>$old,'effective_on'=>$d['starts_on'],'reason'=>$d['reason'],'billing'=>'New period invoice created; previous invoice credits require an explicit adjustment.']);
        });return response()->json(['message'=>'Package change scheduled. Review the old invoice for any agreed credit.']);
    }
    public function status(Request $r,int $school)
    {
        $d=$r->validate(['status'=>['required',Rule::in(['active','inactive'])],'reason'=>'required|string|min:5|max:500']);
        DB::transaction(function()use($r,$school,$d):void{
            abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);
            $license=LicenseService::ensure($school,true);
            if($license->status==='closed')throw ValidationException::withMessages(['status'=>'Use license management to reopen a closed license.']);
            DB::table('school_licenses')->where('id',$license->id)->update(['status'=>$d['status']==='active'?'active':'suspended','updated_at'=>now()]);
            DB::table('schools')->where('id',$school)->update(['status'=>$d['status'],'updated_at'=>now()]);$this->audit($r,'school.status_changed','school',$school,$d);
        });return response()->json(['message'=>'School status updated.']);
    }
    public function payment(Request $r,int $school,int $invoice)
    {
        $d=$r->validate(['amount'=>'required|numeric|min:0.01|max:99999999.99|decimal:0,2','paid_on'=>'required|date_format:Y-m-d|before_or_equal:'.now('Asia/Karachi')->toDateString(),
            'method'=>['required',Rule::in(['cash','bank_transfer','jazzcash','easypaisa'])],'reference'=>'required|string|min:3|max:160']);
        DB::transaction(function()use($r,$school,$invoice,$d):void{
            // School lock also serializes reference checks across its invoices.
            abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);
            $i=DB::table('subscription_invoices')->where('school_id',$school)->where('id',$invoice)->lockForUpdate()->first();abort_unless($i,404);
            if(DB::table('subscription_payments')->where('school_id',$school)->where('reference',$d['reference'])->exists())throw ValidationException::withMessages(['reference'=>'This payment reference is already recorded for this school.']);
            $paid=LicenseService::paid($school,$invoice);
            $remaining=$this->cents($i->amount)-$this->cents($paid);
            if($this->cents($d['amount'])>$remaining)throw ValidationException::withMessages(['amount'=>'Payment exceeds the invoice balance.']);
            $id=DB::table('subscription_payments')->insertGetId(['school_id'=>$school,'subscription_invoice_id'=>$invoice,'amount'=>$d['amount'],'paid_at'=>$d['paid_on'].' 12:00:00','method'=>$d['method'],'reference'=>$d['reference'],'created_at'=>now(),'updated_at'=>now()]);
            DB::table('subscription_invoices')->where('school_id',$school)->where('id',$invoice)->update(['status'=>$this->cents($d['amount'])===$remaining?'paid':'partial','updated_at'=>now()]);
            $this->audit($r,'subscription.payment_recorded','subscription_payment',$id,['school_id'=>$school,'invoice_id'=>$invoice,'amount'=>$d['amount'],'reference'=>$d['reference']]);
        });return response()->json(['message'=>'Payment recorded.']);
    }
}
