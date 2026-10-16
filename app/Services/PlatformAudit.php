<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class PlatformAudit
{
 public static function school(string $type,int $id,array $changes=[]):?int {
  if($type==='school')return DB::table('schools')->where('id',$id)->value('id');
  $tables=['school_license'=>'school_licenses','subscription'=>'subscriptions','subscription_invoice'=>'subscription_invoices','subscription_payment'=>'subscription_payments','support_ticket'=>'support_tickets'];
  if(isset($tables[$type]))return DB::table($tables[$type])->where('id',$id)->value('school_id');
  return null; // Package/provider changes are global, never attributed by a guessed ID.
 }
 public static function record(int $actor,string $action,string $type,int $id,array $changes=[]):void {
  DB::table('platform_audit_logs')->insert(['school_id'=>self::school($type,$id,$changes),'actor_user_id'=>$actor,'action'=>$action,'target_type'=>$type,'target_id'=>$id,'changes'=>json_encode($changes),'created_at'=>now(),'updated_at'=>now()]);
 }
}
