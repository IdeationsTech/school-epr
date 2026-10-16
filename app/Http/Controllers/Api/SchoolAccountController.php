<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SchoolAccountController extends Controller
{
 private function membership(Request $r):object {
  abort_unless($r->user(),401);$m=DB::table('school_memberships')->where('school_id',$r->session()->get('school_id'))->where('user_id',$r->user()->id)->where('status','active')->first();abort_unless($m,403);
  $owner=DB::table('membership_roles as mr')->join('roles as r','r.id','=','mr.role_id')->where('mr.school_id',$m->school_id)->where('mr.membership_id',$m->id)->where('r.name','owner')->exists();abort_unless($owner,403,'School owner access required.');return $m;
 }
 public function show(Request $r){$m=$this->membership($r);$sid=$m->school_id;return response()->json(['school'=>DB::table('schools')->where('id',$sid)->first(['id','name','code','status']),'entitlement'=>LicenseService::summary($sid),'provider'=>json_decode(DB::table('provider_settings')->where('key','profile')->value('value')??'{}',true),'invoices'=>DB::table('subscription_invoices')->where('school_id',$sid)->orderByDesc('id')->get()->map(function($i){$i->paid=LicenseService::paid($i->school_id,$i->id);return $i;}),'payments'=>DB::table('subscription_payments')->where('school_id',$sid)->orderByDesc('id')->get(['id','subscription_invoice_id','amount','paid_at','method','reference'])->map(function($p)use($sid){$p->reversed=DB::table('subscription_payment_reversals')->where('school_id',$sid)->where('payment_id',$p->id)->exists();return $p;}),'tickets'=>DB::table('support_tickets')->where('school_id',$sid)->orderByDesc('id')->get(['id','subject','status','priority','created_at'])]);}
 public function document(Request $r,int $invoice){$m=$this->membership($r);return app(ProviderOperationsController::class)->document($r,(int)$m->school_id,$invoice);}
 public function createTicket(Request $r){$m=$this->membership($r);$d=$r->validate(['subject'=>'required|string|min:3|max:160','description'=>'required|string|min:5|max:10000']);$id=DB::table('support_tickets')->insertGetId($d+['school_id'=>$m->school_id,'opened_by'=>$m->id,'status'=>'open','priority'=>'normal','created_at'=>now(),'updated_at'=>now()]);return response()->json(['id'=>$id,'message'=>'Support request created.'],201);}
 public function ticket(Request $r,int $ticket){$m=$this->membership($r);$t=DB::table('support_tickets')->where('school_id',$m->school_id)->where('id',$ticket)->first();abort_unless($t,404);return response()->json(['ticket'=>$t,'replies'=>DB::table('support_replies as r')->join('users as u','u.id','=','r.author_user_id')->where('r.school_id',$m->school_id)->where('support_ticket_id',$ticket)->orderBy('r.id')->get(['r.body','r.created_at','u.name as author_name'])]);}
 public function reply(Request $r,int $ticket){$m=$this->membership($r);$d=$r->validate(['body'=>'required|string|min:1|max:10000']);DB::transaction(function()use($r,$m,$ticket,$d){$t=DB::table('support_tickets')->where('school_id',$m->school_id)->where('id',$ticket)->lockForUpdate()->first();abort_unless($t,404);abort_if($t->status==='closed',422,'Create a new request for a closed ticket.');DB::table('support_replies')->insert(['school_id'=>$m->school_id,'support_ticket_id'=>$ticket,'author_user_id'=>$r->user()->id,'body'=>$d['body'],'created_at'=>now(),'updated_at'=>now()]);});return response()->json(['message'=>'Reply added.']);}
}
