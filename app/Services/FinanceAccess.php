<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
final class FinanceAccess {
 public function __construct(public SchoolAccess $access){}
 public function fail(string $s):never{throw ValidationException::withMessages(['finance'=>$s]);}
 public function can(Request $r,string $p):bool{$key='finance.can.'.$p;if(!$r->attributes->has($key))$r->attributes->set($key,$this->access->can($r,$p));return $r->attributes->get($key);}
 public function context(Request $r,?string $permission=null):array{if($permission)abort_unless($this->can($r,$permission),403);if($r->attributes->has('finance.context'))return $r->attributes->get('finance.context');abort_unless($this->can($r,'finance.view')||$this->can($r,'finance.manage')||$this->can($r,'finance.approve'),403);$m=$this->access->membership($r);$period=LicenseService::period($m->school_id);if($period)abort_unless(in_array('fees',json_decode($period->modules,true)??[],true),403,'School finance requires the fees module.');$campuses=in_array('owner',$this->access->roles($m),true)?DB::table('campuses')->where('school_id',$m->school_id)->pluck('id')->all():array_map(fn($c)=>$c->id,$this->access->campuses($m));$c=[(int)$m->school_id,array_map('intval',$campuses),$m];$r->attributes->set('finance.context',$c);return $c;}
 public function lock(Request $r,string $permission):int{[$sid]=$this->context($r,$permission);DB::table('schools')->where('id',$sid)->lockForUpdate()->first();return $sid;}
 public function campus(Request $r,int $id,bool $active=false):object{[$sid,$ids]=$this->context($r);abort_unless(in_array($id,$ids,true),404);$c=$this->row('campuses',$sid,$id);if($active&&$c->status!=='active')$this->fail('Choose an active campus for new financial entries.');return $c;}
 public function row(string $table,int $sid,int $id):object{return DB::table($table)->where('school_id',$sid)->where('id',$id)->first()??abort(404);}
 public function bank(Request $r,int $campus,string $method,?int $id,?string $reference):void{[$sid]=$this->context($r);$this->campus($r,$campus);if($method==='cash'){if($id)$this->fail('Cash entries cannot use a bank account.');return;}if(!$id||!trim($reference??''))$this->fail('Non-cash entries require an account and transaction reference.');$b=$this->row('bank_accounts',$sid,$id);if($b->campus_id!==null&&(int)$b->campus_id!==$campus)$this->fail('Choose this campus account or a school-wide account.');}
 public function cents(string|int $s,bool $signed=false):int{if(!preg_match($signed?'/^-?\d{1,10}(\.\d{1,2})?$/D':'/^\d{1,8}(\.\d{1,2})?$/D',(string)$s))$this->fail('Enter a valid PKR amount with at most two decimal places.');$negative=str_starts_with((string)$s,'-');$n=SchoolFees::cents(ltrim((string)$s,'-'));return $negative?-$n:$n;}
 public function money(int $n):string{return SchoolFees::money($n);}
 public function today():string{return now('Asia/Karachi')->toDateString();}
 public function audit(Request $r,string $action,string $table,int $id,array $changes):void{$this->access->audit($r,'finance.'.$action,$table,$id,$changes);}
}
