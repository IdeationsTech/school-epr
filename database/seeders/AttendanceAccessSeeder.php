<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AttendanceAccessSeeder extends Seeder {
 public function run():void{DB::transaction(function(){foreach(['attendance.view'=>['owner','principal','coordinator','teacher'],'attendance.oversee'=>['owner','principal','coordinator'],'attendance.correct'=>['owner','principal','coordinator']] as $permission=>$roles){DB::table('permissions')->updateOrInsert(['name'=>$permission],['description'=>str_replace('.',' ',$permission),'updated_at'=>now()]);$pid=DB::table('permissions')->where('name',$permission)->value('id');foreach($roles as $role){$rid=DB::table('roles')->where('name',$role)->where('scope','school')->value('id');if($rid)DB::table('role_permissions')->updateOrInsert(['role_id'=>$rid,'permission_id'=>$pid],['updated_at'=>now()]);}}
  // Respect existing school-level attendance denials; do not silently restore access
  // by introducing a newly named default grant. Explicit new overrides win.
  $manage=DB::table('permissions')->where('name','attendance.manage')->value('id');
  if($manage){$denials=DB::table('school_role_permissions')->where('permission_id',$manage)->where('enabled',false)->get(['school_id','role_id']);$new=DB::table('permissions')->whereIn('name',['attendance.view','attendance.oversee','attendance.correct'])->pluck('id');foreach($denials as $denial)foreach($new as $pid)DB::table('school_role_permissions')->insertOrIgnore(['school_id'=>$denial->school_id,'role_id'=>$denial->role_id,'permission_id'=>$pid,'enabled'=>false,'created_at'=>now(),'updated_at'=>now()]);}
 });}
}
