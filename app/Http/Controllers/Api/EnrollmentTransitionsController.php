<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\{EnrollmentTransitions,EnrollmentTransitionPreview,EnrollmentTransitionAccess,EnrollmentSessionSetup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class EnrollmentTransitionsController extends Controller {
 public function __construct(private EnrollmentTransitions $service,private EnrollmentTransitionPreview $preview,private EnrollmentTransitionAccess $access,private EnrollmentSessionSetup $sessions){}
 private function setupInput(Request $r,bool $copy=false):array{return $r->validate(['source_session_id'=>'required|integer','target_session_id'=>'required|integer','campus_ids'=>'required|array|min:1|max:20','campus_ids.*'=>'required|integer|distinct','copy_offerings'=>'required|boolean']+($copy?['source_digest'=>'required|string|size:64']:[]));}
 public function setupPreview(Request $r){$this->access->context($r,true);$d=$this->setupInput($r);return DB::transaction(fn()=>response()->json($this->sessions->preview($r,$d)));}
 public function copySections(Request $r){$this->access->context($r,true);return response()->json($this->sessions->copy($r,$this->setupInput($r,true)));}
 public function setup(Request $r){return response()->json($this->service->setup($r));}
 public function roster(Request $r){$d=$r->validate(['source_section_id'=>'required|integer']);return response()->json($this->preview->roster($r,$d['source_section_id']));}
 public function index(Request $r){$this->access->context($r);return response()->json($this->service->listing($r,$r->validate(['page'=>'nullable|integer|min:1','status'=>['nullable',Rule::in(['draft','applied','cancelled'])]])));}
 public function show(Request $r,int $id){return response()->json($this->service->detail($r,$id));}
 public function save(Request $r,?int $id=null){$this->access->context($r,true);$d=$r->validate(['title'=>'required|string|min:3|max:160','source_section_id'=>($id?'nullable':'required').'|integer','kind'=>[($id?'nullable':'required'),Rule::in(['promotion','transfer'])],'target_session_id'=>'required|integer','effective_on'=>'required|date_format:Y-m-d','reason'=>'required|string|min:5|max:2000','lock_version'=>'required|integer|min:0','entry_key'=>($id?'nullable':'required').'|uuid','items'=>'required|array|min:1|max:1000','items.*.source_enrollment_id'=>'required|integer|distinct','items.*.decision'=>['required',Rule::in(['hold','promote','repeat','graduate','withdraw','transfer'])],'items.*.target_section_id'=>'nullable|integer','items.*.roll_no'=>'nullable|string|max:30|regex:/^[A-Za-z0-9][A-Za-z0-9_\/-]*$/D','items.*.reason'=>'nullable|string|max:1000']);return response()->json($this->service->save($r,$d,$id));}
 public function preview(Request $r,int $id){$this->access->context($r);return DB::transaction(fn()=>response()->json($this->preview->calculate($r,$id)));}
 public function apply(Request $r,int $id){$this->access->context($r,true);return response()->json($this->service->apply($r,$id,$r->validate(['lock_version'=>'required|integer|min:0','source_digest'=>'required|string|size:64','confirm_decisions'=>'accepted','reviewed_fees'=>'accepted'])));}
 public function cancel(Request $r,int $id){$this->access->context($r,true);return response()->json($this->service->cancel($r,$id,$r->validate(['lock_version'=>'required|integer|min:0','reason'=>'required|string|min:5|max:1000'])));}
 public function history(Request $r,int $id){[$sid]=$this->access->context($r);$this->access->batch($r,$id);$r->validate(['page'=>'nullable|integer|min:1']);return response()->json(DB::table('audit_logs as a')->leftJoin('school_memberships as m','m.id','=','a.actor_membership_id')->leftJoin('users as u','u.id','=','m.user_id')->where('a.school_id',$sid)->where('a.target_type','enrollment_transition_batches')->where('a.target_id',$id)->orderByDesc('a.id')->paginate(15,['a.id','a.action','a.occurred_at','u.name as actor_name']));}
}
