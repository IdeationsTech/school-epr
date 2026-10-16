<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\{StudentAdmissions,StudentImportReader};
use App\Rules\PakistanIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class StudentImportController extends Controller
{
 public function __construct(private StudentAdmissions $students,private StudentImportReader $reader){}
 public function template(Request $r){$this->students->context($r,true);return response()->streamDownload(function(){$h=fopen('php://output','w');fputcsv($h,StudentImportReader::HEADERS,',','"','');fclose($h);},'students-import-template.csv',['Content-Type'=>'text/csv; charset=UTF-8']);}
 private function input(array $d,int $sid,array $campuses):array {
  $d=array_map(fn($v)=>$v===''?null:$v,$d);$gid=null;
  if(!empty($d['guardian_cnic'])){$g=DB::table('guardians')->where('school_id',$sid)->where('cnic',PakistanIdentity::normalize($d['guardian_cnic']))->first();if($g){$this->students->guardian($sid,$campuses,$g->id,true);if(mb_strtolower(trim($g->name))!==mb_strtolower(trim($d['guardian_name']??''))||$g->phone!==\App\Rules\PakistanPhone::canonical($d['guardian_phone']??''))$this->students->fail('Existing guardian CNIC has a different name or phone. Review the guardian profile first.');$gid=$g->id;}}
  return array_intersect_key($d,array_flip(['admission_no','first_name','last_name','birth_date','gender','admitted_on','identity_type','identity_number','address','previous_school','section_id','starts_on','roll_no']))+['guardian'=>['id'=>$gid,'name'=>$d['guardian_name'],'cnic'=>$d['guardian_cnic'],'phone'=>$d['guardian_phone'],'whatsapp_phone'=>$d['guardian_whatsapp'],'email'=>$d['guardian_email'],'address'=>$d['address'],'relationship'=>$d['relationship'],'is_primary'=>true,'can_receive_reports'=>true,'can_pick_up'=>true]];
 }
 public function preview(Request $r){[$sid,$campuses]=$this->students->context($r,true);$r->validate(['file'=>'required|file|max:5120']);$rows=$this->reader->read($r->file('file'));$preview=[];$seen=[];$hasErrors=false;
  foreach($rows as $row){$errors=[];try{$input=$this->input($row['data'],$sid,$campuses);$this->students->prepare($input,$sid,$campuses);}catch(ValidationException $e){$errors=array_merge(...array_values($e->errors()));}
   foreach(['admission_no','identity_number'] as $key){$v=$key==='identity_number'?PakistanIdentity::normalize($row['data'][$key]):strtolower($row['data'][$key]);if($v){if(isset($seen[$key][$v]))$errors[]='Duplicate '.$key.' in this file.';$seen[$key][$v]=true;}}
   $hasErrors=$hasErrors||count($errors)>0;$preview[]=['line'=>$row['line'],'admission_no'=>$row['data']['admission_no'],'name'=>trim($row['data']['first_name'].' '.$row['data']['last_name']),'errors'=>$errors];
  }
  $token=bin2hex(random_bytes(24));$r->session()->put('student_import',['token'=>$token,'school_id'=>$sid,'membership_id'=>$this->students->access->membership($r)->id,'expires'=>time()+1800,'rows'=>$rows,'valid'=>!$hasErrors]);
  return response()->json(['token'=>$token,'valid'=>!$hasErrors,'count'=>count($rows),'rows'=>$preview,'message'=>$hasErrors?'Correct errors and preview the file again.':'Preview ready. Confirm to import. Capacity and permissions are checked again at confirmation.']);
 }
 public function commit(Request $r){[$sid,$campuses]=$this->students->context($r,true);$d=$r->validate(['token'=>'required|string|size:48']);$saved=$r->session()->get('student_import');if(!$saved||!hash_equals($saved['token'],$d['token'])||$saved['school_id']!==$sid||$saved['membership_id']!=$this->students->access->membership($r)->id||$saved['expires']<time()||!$saved['valid'])$this->students->fail('Preview expired or invalid. Upload and preview again.');
  DB::transaction(function()use($r,$sid,$campuses,$saved){DB::table('schools')->where('id',$sid)->lockForUpdate()->first();foreach($saved['rows'] as $row){try{$this->students->admit($r,$this->input($row['data'],$sid,$campuses),$sid,$campuses);}catch(ValidationException $e){$this->students->fail('Row '.$row['line'].': '.implode(' ',array_merge(...array_values($e->errors()))).' No students from this file were imported.');}}});$r->session()->forget('student_import');return response()->json(['message'=>count($saved['rows']).' students imported.','count'=>count($saved['rows'])]);
 }
}
