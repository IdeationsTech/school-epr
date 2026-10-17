<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AttendanceAccessSeeder extends Seeder {
 public function run():void{DB::transaction(function(){foreach(['attendance.view'=>['owner','principal','coordinator','teacher'],'attendance.oversee'=>['owner','principal','coordinator'],'attendance.correct'=>['owner','principal','coordinator']] as $permission=>$roles){DB::table('permissions')->updateOrInsert(['name'=>$permission],['description'=>str_replace('.',' ',$permission),'updated_at'=>now()]);$pid=DB::table('permissions')->where('name',$permission)->value('id');foreach($roles as $role){$rid=DB::table('roles')->where('name',$role)->where('scope','school')->value('id');if($rid)DB::table('role_permissions')->updateOrInsert(['role_id'=>$rid,'permission_id'=>$pid],['updated_at'=>now()]);}}});}
}
