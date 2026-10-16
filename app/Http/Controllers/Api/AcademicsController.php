<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule,ValidationException};
use Illuminate\Database\QueryException;
class AcademicsController extends Controller
{
 private const TABLES=['sessions'=>'academic_sessions','terms'=>'academic_terms','classes'=>'classes','sections'=>'sections','subjects'=>'subjects','offerings'=>'section_subjects','teachers'=>'staff_profiles','assignments'=>'teacher_assignments'];
 public function __construct(private SchoolAccess $access){}
 private function fail(string $message):never{throw ValidationException::withMessages(['academic_setup'=>$message]);}
 private function context(Request $r,bool $write=false):array{abort_unless($this->access->can($r,'academics.manage')||(!$write&&$this->access->can($r,'academics.view')),403);$m=$this->access->membership($r);return [(int)$m->school_id,array_map(fn($c)=>(int)$c->id,$this->access->campuses($m))];}
 private function row(string $table,int $id,int $sid):object{return DB::table($table)->where('school_id',$sid)->where('id',$id)->first()??abort(404);}
 private function campus(object $section,array $campuses):void{abort_unless(in_array((int)$section->campus_id,$campuses,true),403,'Campus access required.');}
 private function linked(string $table,int $id,int $sid):bool {
  // Inspect real FKs, including future modules, rather than guessing every dependent table.
  $refs=DB::select('SELECT TABLE_NAME AS t,COLUMN_NAME AS c FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?', [DB::connection()->getDatabaseName(),$table,'id']);
  foreach($refs as $ref)if(DB::table($ref->t)->where('school_id',$sid)->where($ref->c,$id)->exists())return true;return false;
 }
 public function index(Request $r){
  [$sid,$campuses]=$this->context($r);$sections=DB::table('sections')->where('school_id',$sid)->whereIn('campus_id',$campuses)->orderBy('name')->get();$ids=$sections->pluck('id');
  $members=DB::table('school_memberships as m')->join('users as u','u.id','=','m.user_id')->where('m.school_id',$sid)->where('m.status','active')->whereExists(fn($q)=>$q->selectRaw('1')->from('membership_roles as mr')->join('roles as r','r.id','=','mr.role_id')->whereColumn('mr.membership_id','m.id')->whereColumn('mr.school_id','m.school_id')->where('r.name','teacher'))->whereExists(fn($q)=>$q->selectRaw('1')->from('membership_campuses as mc')->whereColumn('mc.membership_id','m.id')->whereColumn('mc.school_id','m.school_id')->whereIn('mc.campus_id',$campuses))->get(['m.id','u.name','u.email']);
  foreach($members as $member)$member->campus_ids=DB::table('membership_campuses')->where('school_id',$sid)->where('membership_id',$member->id)->whereIn('campus_id',$campuses)->pluck('campus_id')->map(fn($id)=>(int)$id)->all();
  $current=json_decode(DB::table('school_settings')->where('school_id',$sid)->where('key','academic_context')->value('value')??'{}',true)['current_session_id']??null;
  return response()->json(['can_manage'=>$this->access->can($r,'academics.manage'),'current_session_id'=>$current,'campuses'=>$this->access->campuses($this->access->membership($r)),'sessions'=>DB::table('academic_sessions')->where('school_id',$sid)->orderByDesc('starts_on')->get(),'terms'=>DB::table('academic_terms')->where('school_id',$sid)->orderBy('position')->get(),'classes'=>DB::table('classes')->where('school_id',$sid)->orderBy('position')->get(),'subjects'=>DB::table('subjects')->where('school_id',$sid)->orderBy('name')->get(),'sections'=>$sections,'offerings'=>DB::table('section_subjects')->where('school_id',$sid)->whereIn('section_id',$ids)->get(),'assignments'=>DB::table('teacher_assignments')->where('school_id',$sid)->whereIn('section_id',$ids)->get(),'teachers'=>DB::table('staff_profiles')->where('school_id',$sid)->whereIn('membership_id',$members->pluck('id'))->get(),'teacher_members'=>$members]);
 }
 public function current(Request $r){[$sid]=$this->context($r,true);$d=$r->validate(['academic_session_id'=>'required|integer']);DB::transaction(function()use($r,$sid,$d){DB::table('schools')->where('id',$sid)->lockForUpdate()->first();$s=$this->row('academic_sessions',$d['academic_session_id'],$sid);if($s->status!=='active')$this->fail('Activate the session before selecting it as current.');$old=DB::table('school_settings')->where('school_id',$sid)->where('key','academic_context')->value('value');DB::table('school_settings')->updateOrInsert(['school_id'=>$sid,'key'=>'academic_context'],['value'=>json_encode(['current_session_id'=>$s->id]),'updated_at'=>now()]);$this->access->audit($r,'academics.current_session','academic_sessions',$s->id,['before'=>$old,'current_session_id'=>$s->id]);});return response()->json(['message'=>'Current academic session updated.']);}
 public function save(Request $r,string $resource,?int $id=null){
  [$sid,$campuses]=$this->context($r,true);$table=self::TABLES[$resource]??abort(404);
  $unique=fn($column,$extra=[])=>Rule::unique($table,$column)->where(fn($q)=>$q->where('school_id',$sid)->where($extra))->ignore($id);
  $rules=match($resource){
   'sessions'=>['name'=>['required','string','min:2','max:160',$unique('name')],'starts_on'=>'required|date_format:Y-m-d','ends_on'=>'required|date_format:Y-m-d|after_or_equal:starts_on','status'=>['required',Rule::in(['active','closed'])]],
   'terms'=>['academic_session_id'=>'required|integer','name'=>['required','string','max:160',$unique('name',['academic_session_id'=>$r->input('academic_session_id')])],'starts_on'=>'required|date_format:Y-m-d','ends_on'=>'required|date_format:Y-m-d|after_or_equal:starts_on','position'=>'required|integer|min:1|max:100'],
   'classes'=>['name'=>['required','string','min:1','max:160',$unique('name')],'position'=>'required|integer|min:1|max:1000'],
   'sections'=>['campus_id'=>'required|integer','academic_session_id'=>'required|integer','class_id'=>'required|integer','name'=>['required','string','max:160',$unique('name',['campus_id'=>$r->input('campus_id'),'academic_session_id'=>$r->input('academic_session_id'),'class_id'=>$r->input('class_id')])],'capacity'=>'nullable|integer|min:1|max:1000'],
   'subjects'=>['name'=>'required|string|min:1|max:160','code'=>['required','alpha_dash','max:30',$unique('code')]],
   'offerings'=>['section_id'=>'required|integer','subject_id'=>'required|integer','required_for_diary'=>'required|boolean'],
   'teachers'=>['membership_id'=>['required','integer',$unique('membership_id')],'employee_no'=>['required','alpha_dash','max:30',$unique('employee_no')],'designation'=>'nullable|string|max:160','hired_on'=>'nullable|date_format:Y-m-d|before_or_equal:'.now('Asia/Karachi')->toDateString(),'status'=>['required',Rule::in(['active','inactive'])]],
   'assignments'=>['staff_profile_id'=>'required|integer','section_id'=>'required|integer','subject_id'=>'nullable|integer','is_class_teacher'=>'required|boolean'],
  };
  $d=$r->validate($rules);
  try{$saved=DB::transaction(function()use($r,$resource,$table,$sid,$campuses,$d,$id){
   DB::table('schools')->where('id',$sid)->lockForUpdate()->first();$before=$id?$this->row($table,$id,$sid):null;
   if($resource==='sessions'){
    if(DB::table($table)->where('school_id',$sid)->when($id,fn($q)=>$q->where('id','<>',$id))->where('starts_on','<=',$d['ends_on'])->where('ends_on','>=',$d['starts_on'])->exists())$this->fail('Academic sessions cannot overlap.');
    if($before&&($before->starts_on!==$d['starts_on']||$before->ends_on!==$d['ends_on'])&&$this->linked($table,$id,$sid))$this->fail('Session dates are linked to setup or records. Keep their history; create a new session instead.');
    $current=json_decode(DB::table('school_settings')->where('school_id',$sid)->where('key','academic_context')->value('value')??'{}',true)['current_session_id']??null;if($d['status']==='closed'&&$current==$id&&$id)$this->fail('Select another current session before closing this session.');
   }
   if($resource==='terms'){
    $s=$this->row('academic_sessions',$d['academic_session_id'],$sid);if($s->status!=='active')$this->fail('This academic session is closed.');if($d['starts_on']<$s->starts_on||$d['ends_on']>$s->ends_on)$this->fail('Term dates must stay inside the academic session.');
    if(DB::table($table)->where('school_id',$sid)->where('academic_session_id',$s->id)->when($id,fn($q)=>$q->where('id','<>',$id))->where('starts_on','<=',$d['ends_on'])->where('ends_on','>=',$d['starts_on'])->exists())$this->fail('Terms in a session cannot overlap.');
    if($before&&$this->linked($table,$id,$sid)&&($before->academic_session_id!=$s->id||$before->starts_on!==$d['starts_on']||$before->ends_on!==$d['ends_on']))$this->fail('Published or linked term records must retain their dates and session.');
   }
   if($resource==='sections'){
    if($before)$this->campus($before,$campuses);abort_unless(in_array((int)$d['campus_id'],$campuses,true),403,'Campus access required.');$this->row('campuses',$d['campus_id'],$sid);$s=$this->row('academic_sessions',$d['academic_session_id'],$sid);$this->row('classes',$d['class_id'],$sid);if($s->status!=='active')$this->fail('Use an active academic session.');
    if($before&&($before->campus_id!=$d['campus_id']||$before->academic_session_id!=$d['academic_session_id']||$before->class_id!=$d['class_id'])&&$this->linked($table,$id,$sid))$this->fail('A linked section cannot move campus, class or session. Create a new section.');
    if($id&&isset($d['capacity'])&&DB::table('enrollments')->where('school_id',$sid)->where('section_id',$id)->where('status','active')->count()>$d['capacity'])$this->fail('Capacity is below existing active enrollments.');
   }
   if(in_array($resource,['offerings','assignments'])){
    if($before)$this->campus($this->row('sections',$before->section_id,$sid),$campuses);$section=$this->row('sections',$d['section_id'],$sid);$this->campus($section,$campuses);if($this->row('academic_sessions',$section->academic_session_id,$sid)->status!=='active')$this->fail('This academic session is closed.');
    if(isset($d['subject_id']))$this->row('subjects',$d['subject_id'],$sid);
   }
   if($resource==='offerings'&&DB::table($table)->where('school_id',$sid)->where('section_id',$d['section_id'])->where('subject_id',$d['subject_id'])->when($id,fn($q)=>$q->where('id','<>',$id))->exists())$this->fail('This subject is already offered in the section.');
   if($resource==='teachers'){
    $this->teacherMember($sid,$d['membership_id'],$campuses);if($before)$this->teacherMember($sid,$before->membership_id,$campuses);
    if($before&&$before->membership_id!=$d['membership_id']&&$this->linked($table,$id,$sid))$this->fail('A linked teacher profile cannot move to another account.');
    if($before&&$d['status']==='inactive'&&DB::table('teacher_assignments as a')->join('sections as s','s.id','=','a.section_id')->join('academic_sessions as y','y.id','=','s.academic_session_id')->where('a.school_id',$sid)->where('a.staff_profile_id',$id)->where('y.status','active')->exists())$this->fail('Remove active-session teaching assignments before deactivating this profile.');
   }
   if($resource==='assignments'){
    $staff=$this->row('staff_profiles',$d['staff_profile_id'],$sid);$this->teacherMember($sid,$staff->membership_id,[(int)$section->campus_id]);if($staff->status!=='active')$this->fail('Use an active teacher profile.');
    if(empty($d['subject_id'])&&!$d['is_class_teacher'])$this->fail('Choose a subject or mark this as a class-teacher assignment.');
    if(isset($d['subject_id'])&&!DB::table('section_subjects')->where('school_id',$sid)->where('section_id',$section->id)->where('subject_id',$d['subject_id'])->exists())$this->fail('Offer the subject in this section before assigning a teacher.');
    $others=DB::table($table)->where('school_id',$sid)->where('section_id',$section->id)->when($id,fn($q)=>$q->where('id','<>',$id));
    if($d['is_class_teacher']&&(clone $others)->where('is_class_teacher',true)->exists())$this->fail('This section already has a class teacher.');
    if(isset($d['subject_id'])&&(clone $others)->where('staff_profile_id',$staff->id)->where('subject_id',$d['subject_id'])->exists())$this->fail('This teaching assignment already exists.');
   }
   if($id){DB::table($table)->where('school_id',$sid)->where('id',$id)->update($d+['updated_at'=>now()]);$saved=$id;}else $saved=DB::table($table)->insertGetId($d+['school_id'=>$sid,'created_at'=>now(),'updated_at'=>now()]);
   $this->access->audit($r,'academics.'.$resource.'.saved',$table,$saved,['before'=>$before,'after'=>$d]);return $saved;
  });}catch(QueryException $e){if(in_array($e->getCode(),['23000','23505']))$this->fail('Duplicate or linked academic record. Keep referenced subject/section history intact.');throw $e;}
  return response()->json(['id'=>$saved,'message'=>'Academic setup saved.']);
 }
 private function teacherMember(int $sid,int $mid,array $campuses):void {
  $m=$this->row('school_memberships',$mid,$sid);if($m->status!=='active')$this->fail('Teacher account is inactive.');
  if(!DB::table('membership_roles as mr')->join('roles as r','r.id','=','mr.role_id')->where('mr.school_id',$sid)->where('mr.membership_id',$mid)->where('r.name','teacher')->exists())$this->fail('Assign the teacher role to this account first.');
  if(!DB::table('membership_campuses')->where('school_id',$sid)->where('membership_id',$mid)->whereIn('campus_id',$campuses)->exists())$this->fail('Teacher must have access to the section campus.');
 }
 public function destroy(Request $r,string $resource,int $id){
  [$sid,$campuses]=$this->context($r,true);$table=self::TABLES[$resource]??abort(404);
  try{DB::transaction(function()use($r,$sid,$campuses,$table,$resource,$id){DB::table('schools')->where('id',$sid)->lockForUpdate()->first();$row=$this->row($table,$id,$sid);
   if($resource==='sections')$this->campus($row,$campuses);if(in_array($resource,['offerings','assignments']))$this->campus($this->row('sections',$row->section_id,$sid),$campuses);if($resource==='teachers')$this->teacherMember($sid,$row->membership_id,$campuses);
   $current=json_decode(DB::table('school_settings')->where('school_id',$sid)->where('key','academic_context')->value('value')??'{}',true)['current_session_id']??null;if($resource==='sessions'&&$current==$id)$this->fail('Select another current session before deletion.');
   DB::table($table)->where('school_id',$sid)->where('id',$id)->delete();$this->access->audit($r,'academics.'.$resource.'.deleted',$table,$id,['before'=>$row]);
  });}catch(QueryException $e){if($e->getCode()==='23000')$this->fail('This record has linked data and cannot be deleted. Retain it for history.');throw $e;}return response()->json(['message'=>'Unused academic record deleted.']);
 }
}
