<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Rules\PakistanPhone;
class SchoolOnboarding
{
 public static function summary(int $sid):array {
  $contact=json_decode(DB::table('school_settings')->where('school_id',$sid)->where('key','contact')->value('value')??'{}',true);
  $saved=json_decode(DB::table('school_settings')->where('school_id',$sid)->where('key','provider_onboarding')->value('value')??'{}',true);
  $checks=[
   ['key'=>'contact','label'=>'School contact phone and address','done'=>Validator::make($contact,['phone'=>['required',new PakistanPhone],'address'=>'required|string|min:8'])->passes()],
   ['key'=>'campus','label'=>'At least one active campus','done'=>DB::table('campuses')->where('school_id',$sid)->where('status','active')->exists()],
   ['key'=>'owner','label'=>'Active first owner account','done'=>DB::table('school_memberships as m')->join('membership_roles as mr',fn($j)=>$j->on('mr.membership_id','=','m.id')->on('mr.school_id','=','m.school_id'))->join('roles as r','r.id','=','mr.role_id')->where('m.school_id',$sid)->where('m.status','active')->where('r.name','owner')->where('r.scope','school')->exists()],
   ['key'=>'license','label'=>'Permanent license issued','done'=>DB::table('school_licenses')->where('school_id',$sid)->exists()],
   ['key'=>'subscription','label'=>'Trial or paid period assigned (current or scheduled)','done'=>DB::table('subscriptions')->where('school_id',$sid)->where('status','active')->where('ends_on','>=',now('Asia/Karachi')->toDateString())->exists()],
  ];
  $prerequisites=collect($checks)->every(fn($c)=>$c['done']);$checks[]=['key'=>'handover','label'=>'Owner sign-in checked and credentials handed over','done'=>(bool)($saved['handover_confirmed']??false)];
  $complete=collect($checks)->where('done',true)->count();
  return ['status'=>$complete===count($checks)?'ready':($complete===0?'pending':'in_progress'),'checks'=>$checks,'completed'=>$complete,'total'=>count($checks),'prerequisites_ready'=>$prerequisites,'handover_confirmed'=>(bool)($saved['handover_confirmed']??false),'notes'=>$saved['notes']??'','confirmed_at'=>$saved['confirmed_at']??null];
 }
}
