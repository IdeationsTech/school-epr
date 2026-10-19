<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
final class EnrollmentTransitionAccess {
 public function __construct(public SchoolAccess $access){}
 public function fail(string $s):never{throw ValidationException::withMessages(['transition'=>$s]);}
 public function can(Request $r,string $p='students.transitions'):bool{$k='transition.can.'.$p;if(!$r->attributes->has($k))$r->attributes->set($k,$this->access->can($r,$p));return $r->attributes->get($k);}
 public function context(Request $r,bool $write=false):array{if($write)abort_unless($this->can($r),403);if($r->attributes->has('transition.context'))return $r->attributes->get('transition.context');abort_unless($this->can($r)||$this->can($r,'students.transitions_view'),403);$m=$this->access->membership($r);$period=LicenseService::period((int)$m->school_id);if($period)abort_unless(in_array('student_admissions',json_decode($period->modules,true)??[],true),403,'Student admissions is required for enrollment transitions.');$scope=in_array('owner',$this->access->roles($m),true)?DB::table('campuses')->where('school_id',$m->school_id)->pluck('id')->all():array_map(fn($c)=>$c->id,$this->access->campuses($m));$v=[(int)$m->school_id,array_map('intval',$scope),$m];$r->attributes->set('transition.context',$v);return $v;}
 public function lock(Request $r):int{$sid=(int)$this->access->membership($r)->school_id;DB::table('schools')->where('id',$sid)->lockForUpdate()->first();$this->context($r,true);return $sid;}
 public function row(string $table,int $sid,int $id):object{return DB::table($table)->where('school_id',$sid)->where('id',$id)->first()??abort(404);}
 public function section(Request $r,int $id):object{[$sid,$scope]=$this->context($r);$s=DB::table('sections as s')->join('academic_sessions as y','y.id','=','s.academic_session_id')->join('classes as c','c.id','=','s.class_id')->join('campuses as p','p.id','=','s.campus_id')->where('s.school_id',$sid)->where('s.id',$id)->first(['s.*','y.name as session_name','y.starts_on as session_start','y.ends_on as session_end','y.status as session_status','c.name as class_name','c.position as class_position','p.name as campus_name','p.status as campus_status'])??abort(404);abort_unless(in_array((int)$s->campus_id,$scope,true),404);return $s;}
 public function batch(Request $r,int $id):object{[$sid]=$this->context($r);$b=$this->row('enrollment_transition_batches',$sid,$id);$this->section($r,$b->source_section_id);foreach(DB::table('enrollment_transition_items')->where('school_id',$sid)->where('batch_id',$id)->whereNotNull('target_section_id')->pluck('target_section_id')->unique() as $target)$this->section($r,$target);return $b;}
 public function today():string{return now('Asia/Karachi')->toDateString();}
 public function audit(Request $r,string $action,string $table,int $id,array $d):void{$this->access->audit($r,'transitions.'.$action,$table,$id,$d);}
}
