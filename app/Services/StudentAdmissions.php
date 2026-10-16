<?php
namespace App\Services;

use App\Rules\{PakistanIdentity,PakistanPhone};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Validator};
use Illuminate\Validation\{Rule,ValidationException};

/** School-row locks serialize admission numbers, license usage and enrollment capacity. */
class StudentAdmissions
{
 public function __construct(public SchoolAccess $access){}
 public function fail(string $message):never{throw ValidationException::withMessages(['students'=>$message]);}
 public function context(Request $r,bool $write=false):array {
  abort_unless($this->access->can($r,'students.manage')||(!$write&&$this->access->can($r,'students.view')),403);
  $m=$this->access->membership($r);$period=LicenseService::period((int)$m->school_id);
  if($period)abort_unless(in_array('student_admissions',json_decode($period->modules,true)??[],true),403,'Student admissions is not included in this school license.');
  return [(int)$m->school_id,array_map(fn($c)=>(int)$c->id,$this->access->campuses($m))];
 }
 public function visible(int $sid,array $campuses){return DB::table('students as s')->where('s.school_id',$sid)->whereExists(fn($q)=>$q->selectRaw('1')->from('enrollments as e')->join('sections as sec','sec.id','=','e.section_id')->whereColumn('e.school_id','s.school_id')->whereColumn('e.student_id','s.id')->whereIn('sec.campus_id',$campuses));}
 public function student(int $sid,array $campuses,int $id,bool $write=false):object {
  $s=$this->visible($sid,$campuses)->where('s.id',$id)->first()??abort(404);
  if($write&&DB::table('enrollments as e')->join('sections as sec','sec.id','=','e.section_id')->where('e.school_id',$sid)->where('e.student_id',$id)->whereNotIn('sec.campus_id',$campuses)->exists())abort(403,'Access to all campuses in this student history is required to change the shared profile.');
  return $s;
 }
 public function guardian(int $sid,array $campuses,int $id,bool $write=false):object {
  $g=DB::table('guardians')->where('school_id',$sid)->where('id',$id)->first()??abort(404);
  $students=DB::table('student_guardians')->where('school_id',$sid)->where('guardian_id',$id)->pluck('student_id');
  abort_unless($students->isNotEmpty(),404);
  foreach($students as $student){if($write)$this->student($sid,$campuses,$student,true);}
  if(!$write)abort_unless($this->visible($sid,$campuses)->whereIn('s.id',$students)->exists(),404);
  return $g;
 }
 public function profile(array $data,int $sid,?int $id=null):array {
  $data['identity_number']=PakistanIdentity::normalize($data['identity_number']??null);if(!$data['identity_number'])$data['identity_type']=null;
  return Validator::make($data,[
   'admission_no'=>['required','string','max:40','regex:/^[A-Za-z0-9][A-Za-z0-9_\/-]*$/',Rule::unique('students')->where('school_id',$sid)->ignore($id)],
   'first_name'=>'required|string|min:2|max:160','last_name'=>'nullable|string|max:160',
   'birth_date'=>'required|date_format:Y-m-d|before_or_equal:admitted_on','admitted_on'=>'required|date_format:Y-m-d|before_or_equal:'.now('Asia/Karachi')->toDateString(),
   'gender'=>['required',Rule::in(['male','female','other'])],
   'identity_number'=>['nullable',new PakistanIdentity,Rule::unique('students')->where('school_id',$sid)->ignore($id)],
   'identity_type'=>['nullable','required_with:identity_number',Rule::in(['b_form','cnic'])],
   'address'=>'required|string|min:5|max:2000','previous_school'=>'nullable|string|max:160',
  ])->validate();
 }
 public function guardianData(array $data,int $sid,?int $id=null):array {
  $data['cnic']=PakistanIdentity::normalize($data['cnic']??null);
  foreach(['phone','alternate_phone','whatsapp_phone'] as $key)$data[$key]=empty($data[$key])?null:PakistanPhone::canonical($data[$key]);
  return Validator::make($data,['name'=>'required|string|min:2|max:160','phone'=>['required',new PakistanPhone],
   'alternate_phone'=>['nullable',new PakistanPhone],'whatsapp_phone'=>['nullable','regex:/^\+923[0-9]{9}$/D'],
   'cnic'=>['nullable',new PakistanIdentity,Rule::unique('guardians')->where('school_id',$sid)->ignore($id)],
   'email'=>'nullable|email|max:160','address'=>'required|string|min:5|max:2000'])->validate();
 }
 public function relation(array $d):array{return Validator::make($d,['relationship'=>['required',Rule::in(['father','mother','guardian','grandparent','sibling','other'])],'is_primary'=>'required|boolean','can_receive_reports'=>'required|boolean','can_pick_up'=>'required|boolean'])->validate();}
 public function section(int $sid,array $campuses,int $id,string $date):object {
  $sec=DB::table('sections as sec')->join('academic_sessions as y','y.id','=','sec.academic_session_id')->join('campuses as c','c.id','=','sec.campus_id')->where('sec.school_id',$sid)->where('sec.id',$id)->select('sec.*','y.starts_on as session_start','y.ends_on as session_end','y.status as session_status','c.status as campus_status')->first()??abort(404);
  abort_unless(in_array((int)$sec->campus_id,$campuses,true),403,'Campus access required.');
  if($sec->session_status!=='active'||$sec->campus_status!=='active')$this->fail('Choose an active session and campus.');
  if($date<$sec->session_start||$date>$sec->session_end)$this->fail('Enrollment date must be inside the academic session.');
  return $sec;
 }
 public function capacity(object $sec,int $sid,string $date,?string $roll=null):void {
  $q=DB::table('enrollments')->where('school_id',$sid)->where('section_id',$sec->id)->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$date));
  if($sec->capacity!==null&&(clone $q)->count()>=(int)$sec->capacity)$this->fail('This section has reached its student capacity.');
  if($roll!==null&&(clone $q)->where('roll_no',$roll)->exists())$this->fail('This roll number is already assigned in the section.');
 }
 public function limit(int $sid):void {
  $period=LicenseService::period($sid);
  // Count active registrations, including future starts, so advance admissions cannot bypass limits.
  if($period&&LicenseService::studentUsage($sid)>=(int)$period->student_limit)$this->fail('School student limit reached. Contact the provider to upgrade.');
 }
 public function prepare(array $input,int $sid,array $campuses):array {
  $d=$this->profile($input,$sid);
  $en=Validator::make($input,['section_id'=>'required|integer','starts_on'=>'required|date_format:Y-m-d|after_or_equal:admitted_on','roll_no'=>'nullable|string|max:30','guardian'=>'required|array'])->validate();
  $sec=$this->section($sid,$campuses,(int)$en['section_id'],$en['starts_on']);$this->capacity($sec,$sid,$en['starts_on'],$en['roll_no']??null);$this->limit($sid);
  $raw=$input['guardian'];$rel=$this->relation($raw);if(!$rel['is_primary'])$this->fail('Admission requires a primary guardian.');
  if(!empty($raw['id'])){$g=$this->guardian($sid,$campuses,(int)$raw['id'],true);$guardian=['id'=>(int)$g->id];}
  else $guardian=$this->guardianData($raw,$sid);
  return ['student'=>$d,'enrollment'=>array_intersect_key($en,array_flip(['section_id','starts_on','roll_no'])),'guardian'=>$guardian,'relation'=>$rel];
 }
 public function admit(Request $r,array $input,int $sid,array $campuses):int {
  $d=$this->prepare($input,$sid,$campuses);$now=now();
  $id=DB::table('students')->insertGetId($d['student']+['school_id'=>$sid,'status'=>'active','created_at'=>$now,'updated_at'=>$now]);
  $gid=$d['guardian']['id']??DB::table('guardians')->insertGetId($d['guardian']+['school_id'=>$sid,'created_at'=>$now,'updated_at'=>$now]);
  DB::table('student_guardians')->insert($d['relation']+['school_id'=>$sid,'student_id'=>$id,'guardian_id'=>$gid,'created_at'=>$now,'updated_at'=>$now]);
  DB::table('enrollments')->insert($d['enrollment']+['school_id'=>$sid,'student_id'=>$id,'status'=>'active','created_at'=>$now,'updated_at'=>$now]);
  $this->access->audit($r,'students.admitted','students',$id,['section_id'=>$d['enrollment']['section_id']]);return $id;
 }
}
