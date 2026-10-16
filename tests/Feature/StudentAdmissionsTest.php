<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB,Storage};
use Tests\TestCase;
class StudentAdmissionsTest extends TestCase
{
 use RefreshDatabase;
 protected function beforeRefreshingDatabase():void{if(!str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Dedicated test DB required.');}
 protected function setUp():void{parent::setUp();\Carbon\Carbon::setTestNow('2026-10-17 09:00:00');\Carbon\CarbonImmutable::setTestNow('2026-10-17 09:00:00');}
 protected function tearDown():void{\Carbon\Carbon::setTestNow();\Carbon\CarbonImmutable::setTestNow();parent::tearDown();}
 private function fixture(string $code='A',string $role='owner'):array {
  if(!DB::table('roles')->exists())$this->seed(DatabaseSeeder::class);
  $sid=DB::table('schools')->insertGetId(['name'=>'School '.$code,'code'=>$code,'status'=>'active','timezone'=>'Asia/Karachi','currency'=>'PKR']);$cid=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main','code'=>'MAIN','status'=>'active']);$user=User::factory()->create();$mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$user->id,'status'=>'active']);$rid=DB::table('roles')->where('name',$role)->value('id');DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$mid,'role_id'=>$rid]);DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid]);
  $session=DB::table('academic_sessions')->insertGetId(['school_id'=>$sid,'name'=>'2026-27','starts_on'=>'2026-04-01','ends_on'=>'2027-03-31','status'=>'active']);$class=DB::table('classes')->insertGetId(['school_id'=>$sid,'name'=>'Class 1','position'=>1]);$section=DB::table('sections')->insertGetId(['school_id'=>$sid,'campus_id'=>$cid,'academic_session_id'=>$session,'class_id'=>$class,'name'=>'A','capacity'=>20]);return compact('sid','cid','user','mid','rid','session','class','section');
 }
 private function login(array $f):void{$this->actingAs($f['user'])->withSession(['school_id'=>$f['sid']]);}
 private function data(array $f,string $no='ADM-001'):array{return ['admission_no'=>$no,'first_name'=>'Ali','last_name'=>'Ahmed','birth_date'=>'2018-05-01','gender'=>'male','admitted_on'=>'2026-04-01','identity_type'=>'b_form','identity_number'=>'3520212345671','address'=>'Lahore Pakistan','previous_school'=>'Old school','section_id'=>$f['section'],'starts_on'=>'2026-04-01','roll_no'=>'1','guardian'=>['name'=>'Ahmed Khan','cnic'=>'3520212345672','phone'=>'03001234567','whatsapp_phone'=>'+923001234567','address'=>'Lahore Pakistan','relationship'=>'father','is_primary'=>true,'can_receive_reports'=>true,'can_pick_up'=>true]];}
 private function admit(array $d):int{return $this->postJson('/app-api/students',$d)->assertOk()->json('id');}
 private function csv(array $f,array $overrides=[]):UploadedFile {$d=['admission_no'=>'CSV-1','first_name'=>'Sara','last_name'=>'Ahmed','birth_date'=>'2018-05-01','gender'=>'female','admitted_on'=>'2026-04-01','identity_type'=>'','identity_number'=>'','address'=>'Lahore Pakistan','previous_school'=>'','section_id'=>(string)$f['section'],'starts_on'=>'2026-04-01','roll_no'=>'','guardian_name'=>'Ahmed Khan','guardian_cnic'=>'3520212345672','guardian_phone'=>'03001234567','guardian_whatsapp'=>'','guardian_email'=>'','relationship'=>'father'];$rows=$overrides?:[$d];$h=fopen('php://temp','r+');fputcsv($h,array_keys($d),',','"','');foreach($rows as $row)fputcsv($h,array_values($row),',','"','');rewind($h);$content=stream_get_contents($h);fclose($h);return UploadedFile::fake()->createWithContent('students.csv',$content);}
 public function test_admission_validation_family_reuse_and_identity_privacy():void {
  $f=$this->fixture();$this->login($f);$d=$this->data($f);$id=$this->admit($d);$gid=DB::table('student_guardians')->where('student_id',$id)->value('guardian_id');$this->assertDatabaseHas('guardians',['id'=>$gid,'phone'=>'+923001234567']);
  $this->postJson('/app-api/students',$d)->assertUnprocessable();$this->postJson('/app-api/students',array_replace($d,['admission_no'=>'ADM-2']))->assertUnprocessable();
  $sibling=array_replace($d,['admission_no'=>'ADM-2','first_name'=>'Sara','identity_number'=>null,'identity_type'=>null,'roll_no'=>'2','guardian'=>['id'=>$gid,'relationship'=>'father','is_primary'=>true,'can_receive_reports'=>true,'can_pick_up'=>false]]);$sib=$this->admit($sibling);$this->getJson('/app-api/students/'.$id)->assertJsonPath('guardians.0.siblings.0.id',$sib);$this->assertDatabaseCount('guardians',1);
  $this->putJson('/app-api/guardians/'.$gid,array_replace($d['guardian'],['phone'=>'03007654321']))->assertOk();$this->getJson('/app-api/students/'.$sib)->assertJsonPath('guardians.0.phone','+923007654321');
  $this->deleteJson('/app-api/students/'.$id.'/guardians/'.$gid)->assertUnprocessable();
  DB::table('membership_roles')->where('membership_id',$f['mid'])->update(['role_id'=>DB::table('roles')->where('name','teacher')->value('id')]);
  $this->getJson('/app-api/students/'.$id)->assertOk()->assertJsonMissingPath('student.identity_number')->assertJsonMissingPath('guardians.0.cnic');$this->postJson('/app-api/students',$sibling)->assertForbidden();$this->getJson('/app-api/guardians')->assertForbidden();
 }
 public function test_admission_rejects_bad_dates_identity_phone_and_full_sections():void {
  $f=$this->fixture();$this->login($f);$d=$this->data($f);
  foreach([['birth_date'=>'2027-01-01'],['identity_number'=>'123'],['starts_on'=>'2025-01-01'],['address'=>''],['guardian'=>array_replace($d['guardian'],['phone'=>'+92123'])]] as $bad)$this->postJson('/app-api/students',array_replace($d,$bad))->assertUnprocessable();
  DB::table('sections')->where('id',$f['section'])->update(['capacity'=>1]);$this->admit($d);$d['admission_no']='ADM-2';$d['identity_number']=null;$d['identity_type']=null;$d['guardian']['cnic']=null;$d['roll_no']='2';$this->postJson('/app-api/students',$d)->assertUnprocessable();$this->assertDatabaseCount('students',1);
 }
 public function test_cross_school_and_unassigned_campus_cannot_read_or_modify_students_or_guardians():void {
  $a=$this->fixture();$b=$this->fixture('B');$this->login($b);$id=$this->admit($this->data($b));$gid=DB::table('student_guardians')->where('student_id',$id)->value('guardian_id');$this->login($a);$this->getJson('/app-api/students/'.$id)->assertNotFound();$this->getJson('/app-api/students')->assertJsonPath('total',0);$this->putJson('/app-api/guardians/'.$gid,$this->data($a)['guardian'])->assertNotFound();$this->postJson('/app-api/students',array_replace($this->data($a),['section_id'=>$b['section']]))->assertNotFound();
  $this->login($b);DB::table('membership_roles')->where('membership_id',$b['mid'])->update(['role_id'=>DB::table('roles')->where('name','principal')->value('id')]);DB::table('membership_campuses')->where('membership_id',$b['mid'])->delete();$this->getJson('/app-api/students/'.$id)->assertNotFound();$this->getJson('/app-api/students')->assertJsonPath('total',0);
 }
 public function test_moves_withdrawals_and_readmission_retain_dated_history():void {
  $f=$this->fixture();$this->login($f);$id=$this->admit($this->data($f));$other=DB::table('sections')->insertGetId(['school_id'=>$f['sid'],'campus_id'=>$f['cid'],'academic_session_id'=>$f['session'],'class_id'=>$f['class'],'name'=>'B','capacity'=>20]);
  $this->postJson('/app-api/students/'.$id.'/enrollments',['action'=>'move','section_id'=>$other,'effective_on'=>'2026-04-01','reason'=>'Class section change'])->assertUnprocessable();
  $this->postJson('/app-api/students/'.$id.'/enrollments',['action'=>'move','section_id'=>$other,'effective_on'=>'2026-09-01','reason'=>'Class section change'])->assertOk();$this->assertDatabaseHas('enrollments',['student_id'=>$id,'section_id'=>$f['section'],'status'=>'completed','ends_on'=>'2026-08-31']);
  $this->postJson('/app-api/students/'.$id.'/enrollments',['action'=>'withdraw','effective_on'=>'2026-10-01','reason'=>'Family moved city'])->assertOk();$this->assertDatabaseHas('students',['id'=>$id,'status'=>'withdrawn']);
  $this->postJson('/app-api/students/'.$id.'/enrollments',['action'=>'readmit','section_id'=>$f['section'],'effective_on'=>'2026-09-15','reason'=>'Returning to school'])->assertUnprocessable();
  $this->postJson('/app-api/students/'.$id.'/enrollments',['action'=>'readmit','section_id'=>$f['section'],'effective_on'=>'2026-10-17','reason'=>'Returning to school'])->assertOk();$this->assertDatabaseCount('enrollments',3);$this->assertDatabaseHas('audit_logs',['action'=>'students.readmit','target_id'=>$id]);
 }
 public function test_private_documents_are_scoped_and_photo_type_is_validated():void {
  Storage::fake('local');$f=$this->fixture();$this->login($f);$id=$this->admit($this->data($f));
  $this->post('/app-api/students/'.$id.'/documents',['document_type'=>'photo','file'=>UploadedFile::fake()->create('photo.pdf',20,'application/pdf')],['Accept'=>'application/json'])->assertUnprocessable();
  $this->post('/app-api/students/'.$id.'/documents',['document_type'=>'b_form','file'=>UploadedFile::fake()->create('birth.pdf',20,'application/pdf')],['Accept'=>'application/json'])->assertOk();$file=DB::table('files')->first();Storage::disk('local')->assertExists($file->path);$this->get('/app-api/students/'.$id.'/files/'.$file->id)->assertOk();
  $b=$this->fixture('B');$this->login($b);$this->getJson('/app-api/students/'.$id.'/files/'.$file->id)->assertNotFound();$this->get('/storage/'.$file->path)->assertForbidden();
 }
 public function test_csv_import_previews_then_atomically_revalidates_capacity_and_reuses_family():void {
  $f=$this->fixture();$this->login($f);$file=$this->csv($f);$preview=$this->post('/app-api/students/import/preview',['file'=>$file],['Accept'=>'application/json'])->assertOk()->assertJsonPath('valid',true);$this->assertDatabaseCount('students',0);$this->postJson('/app-api/students/import/commit',['token'=>$preview->json('token')])->assertOk();$this->assertDatabaseCount('students',1);$this->postJson('/app-api/students/import/commit',['token'=>$preview->json('token')])->assertUnprocessable();
  $p=$this->post('/app-api/students/import/preview',['file'=>$this->csv($f)],['Accept'=>'application/json'])->assertOk()->assertJsonPath('valid',false);$this->postJson('/app-api/students/import/commit',['token'=>$p->json('token')])->assertUnprocessable();
  $r=\App\Services\StudentImportReader::HEADERS;$base=array_combine($r,['CSV-2','Sara','Ahmed','2018-05-01','female','2026-04-01','','','Lahore Pakistan','',(string)$f['section'],'2026-04-01','','Ahmed Khan','3520212345672','03001234567','','','father']);$second=array_replace($base,['admission_no'=>'CSV-3']);$preview=$this->post('/app-api/students/import/preview',['file'=>$this->csv($f,[$base,$second])],['Accept'=>'application/json'])->assertOk()->assertJsonPath('valid',true);DB::table('sections')->where('id',$f['section'])->update(['capacity'=>2]);$this->postJson('/app-api/students/import/commit',['token'=>$preview->json('token')])->assertUnprocessable();$this->assertDatabaseCount('students',1);
  DB::table('sections')->where('id',$f['section'])->update(['capacity'=>20]);$this->postJson('/app-api/students/import/commit',['token'=>$preview->json('token')])->assertOk();$this->assertDatabaseCount('students',3);$this->assertDatabaseCount('guardians',1);
 }
 public function test_xlsx_reader_accepts_text_cells_and_rejects_formulas():void {
  $f=$this->fixture();$file=$this->csv($f);$lines=explode("\n",trim(file_get_contents($file->getRealPath())));$rows=array_map(fn($line)=>str_getcsv($line,',','"',''),$lines);$xml='<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';foreach($rows as $i=>$row){$xml.='<row r="'.($i+1).'">';foreach($row as $col=>$value){$ref=chr(65+$col).($i+1);$xml.='<c r="'.$ref.'" t="inlineStr"><is><t>'.htmlspecialchars($value,ENT_XML1).'</t></is></c>';}$xml.='</row>';}$xml.='</sheetData></worksheet>';$path=tempnam(sys_get_temp_dir(),'xlsx');$z=new \ZipArchive;$z->open($path,\ZipArchive::OVERWRITE);$z->addFromString('xl/worksheets/sheet1.xml',$xml);$z->close();
  try{$this->login($f);$this->post('/app-api/students/import/preview',['file'=>new UploadedFile($path,'students.xlsx',null,null,true)],['Accept'=>'application/json'])->assertOk()->assertJsonPath('valid',true);$z->open($path);$z->addFromString('xl/worksheets/sheet1.xml',str_replace('<is><t>CSV-1','<f>1+1</f><is><t>CSV-1',$xml));$z->close();$this->post('/app-api/students/import/preview',['file'=>new UploadedFile($path,'students.xlsx',null,null,true)],['Accept'=>'application/json'])->assertUnprocessable();}finally{unlink($path);}
 }
 public function test_license_limit_and_module_entitlement_are_enforced():void {
  $f=$this->fixture();$this->login($f);$license=\App\Services\LicenseService::ensure($f['sid']);$plan=DB::table('plans')->first();DB::table('subscriptions')->insert(['school_id'=>$f['sid'],'plan_id'=>$plan->id,'license_id'=>$license->id,'kind'=>'paid','grace_days'=>0,'student_limit'=>1,'campus_limit'=>2,'modules'=>json_encode(['student_admissions']),'package_name'=>'Test','price'=>'1000.00','starts_on'=>'2026-01-01','ends_on'=>'2027-12-31','status'=>'active']);$this->admit($this->data($f));$d=$this->data($f,'ADM-2');$d['identity_number']=null;$d['identity_type']=null;$this->postJson('/app-api/students',$d)->assertUnprocessable();DB::table('subscriptions')->where('school_id',$f['sid'])->update(['modules'=>'[]']);$this->getJson('/app-api/students')->assertForbidden();$this->artisan('database:verify')->assertSuccessful();
 }
 public function test_shared_profiles_require_all_campus_access_and_permission_overrides_apply():void {
  $f=$this->fixture();$this->login($f);$id=$this->admit($this->data($f));$gid=DB::table('student_guardians')->where('student_id',$id)->value('guardian_id');
  $campus=DB::table('campuses')->insertGetId(['school_id'=>$f['sid'],'name'=>'Branch','code'=>'BRANCH','status'=>'active']);$section=DB::table('sections')->insertGetId(['school_id'=>$f['sid'],'campus_id'=>$campus,'academic_session_id'=>$f['session'],'class_id'=>$f['class'],'name'=>'B','capacity'=>20]);
  $d=$this->data($f,'ADM-2');$d['identity_number']=null;$d['identity_type']=null;$d['section_id']=$section;$d['guardian']=['id'=>$gid,'relationship'=>'father','is_primary'=>true,'can_receive_reports'=>true,'can_pick_up'=>true];$this->admit($d);
  $rid=DB::table('roles')->where('name','principal')->value('id');DB::table('membership_roles')->where('membership_id',$f['mid'])->update(['role_id'=>$rid]);
  $this->putJson('/app-api/guardians/'.$gid,$this->data($f)['guardian'])->assertNotFound();
  $this->getJson('/app-api/students/'.$id)->assertOk()->assertJsonCount(0,'guardians.0.siblings');
  DB::table('school_role_permissions')->insert(['school_id'=>$f['sid'],'role_id'=>$rid,'permission_id'=>DB::table('permissions')->where('name','students.manage')->value('id'),'enabled'=>false]);
  $this->putJson('/app-api/students/'.$id,$this->data($f))->assertForbidden();$this->getJson('/app-api/students/setup')->assertJsonPath('can_manage',false);
 }
 public function test_guest_and_provider_have_no_implicit_student_access():void {
  $this->getJson('/app-api/students')->assertUnauthorized();$this->seed(DatabaseSeeder::class);$user=User::factory()->create();DB::table('platform_admins')->insert(['user_id'=>$user->id,'active'=>true]);$this->actingAs($user)->getJson('/app-api/students')->assertForbidden();
 }

}
