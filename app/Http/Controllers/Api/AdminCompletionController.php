<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\{LicenseService,SchoolOnboarding,PlatformAudit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule,ValidationException};
class AdminCompletionController extends Controller
{
 public function onboarding(int $school){abort_unless(DB::table('schools')->where('id',$school)->exists(),404);return response()->json(SchoolOnboarding::summary($school));}
 public function saveOnboarding(Request $r,int $school){
  $d=$r->validate(['handover_confirmed'=>'required|boolean','notes'=>'nullable|string|max:2000']);
  DB::transaction(function()use($r,$school,$d):void{
   abort_unless(DB::table('schools')->where('id',$school)->lockForUpdate()->first(),404);
   $before=SchoolOnboarding::summary($school);
   if($d['handover_confirmed']&&!$before['prerequisites_ready'])throw ValidationException::withMessages(['handover_confirmed'=>'Complete school contact, active campus, owner, license and current/scheduled subscription first.']);
   $saved=['handover_confirmed'=>$d['handover_confirmed'],'notes'=>$d['notes']??'','confirmed_at'=>$d['handover_confirmed']?($before['confirmed_at']??now()->toIso8601String()):null];
   DB::table('school_settings')->updateOrInsert(['school_id'=>$school,'key'=>'provider_onboarding'],['value'=>json_encode($saved),'created_at'=>now(),'updated_at'=>now()]);
   PlatformAudit::record($r->user()->id,'school.onboarding_updated','school',$school,['before'=>['handover_confirmed'=>$before['handover_confirmed'],'notes'=>$before['notes']],'after'=>$saved]);
  });return response()->json(['message'=>'Onboarding checklist saved.','onboarding'=>SchoolOnboarding::summary($school)]);
 }
 public function licenseHistory(int $school){
  abort_unless(DB::table('schools')->where('id',$school)->exists(),404);
  $events=DB::table('platform_audit_logs as a')->join('users as u','u.id','=','a.actor_user_id')->where('a.school_id',$school)->where(function($q){$q->where('a.action','like','license.%')->orWhere('a.action','like','subscription.%')->orWhere('a.action','school.status_changed');})->orderByDesc('a.id')->paginate(15,['a.id','a.action','a.created_at','a.changes','u.name as actor_name']);
  return response()->json(['license'=>DB::table('school_licenses')->where('school_id',$school)->first(),'events'=>$events]);
 }
 public function subscriptions(Request $r){
  $d=$r->validate(['search'=>'nullable|string|max:100','state'=>['nullable',Rule::in(['current','grace','upcoming','expired','cancelled','replaced'])],'kind'=>['nullable',Rule::in(['paid','trial'])],'school_id'=>'nullable|integer|exists:schools,id']);$today=now('Asia/Karachi')->toDateString();
  $q=DB::table('subscriptions as p')->join('schools as s','s.id','=','p.school_id')->join('school_licenses as l','l.id','=','p.license_id')->when($d['school_id']??null,fn($q,$v)=>$q->where('p.school_id',$v))->when($d['kind']??'',fn($q,$v)=>$q->where('p.kind',$v))->when($d['search']??'',fn($q,$v)=>$q->where(fn($q)=>$q->where('s.name','like','%'.$v.'%')->orWhere('s.code','like','%'.$v.'%')->orWhere('p.package_name','like','%'.$v.'%')->orWhere('l.number','like','%'.$v.'%')));
  $state=$d['state']??'';
  if(in_array($state,['cancelled','replaced']))$q->where('p.status',$state);
  elseif($state){$q->where('p.status','active');if($state==='upcoming')$q->where('p.starts_on','>',$today);else{$q->where('p.starts_on','<=',$today);if($state==='current')$q->where('p.ends_on','>=',$today);elseif($state==='grace')$q->where('p.ends_on','<',$today)->whereRaw('DATE_ADD(p.ends_on, INTERVAL p.grace_days DAY) >= ?',[$today]);else $q->whereRaw('DATE_ADD(p.ends_on, INTERVAL p.grace_days DAY) < ?',[$today]);}}
  $page=$q->orderByDesc('p.starts_on')->orderByDesc('p.id')->paginate(20,['p.*','s.name as school_name','s.code as school_code','l.number as license_number','l.status as license_status']);
  foreach($page->items() as $p)$p->state=$p->status!=='active'?$p->status:($p->starts_on>$today?'upcoming':($p->ends_on>=$today?'current':(\Carbon\CarbonImmutable::parse($p->ends_on)->addDays($p->grace_days)->toDateString()>=$today?'grace':'expired')));
  return response()->json($page);
 }
}
