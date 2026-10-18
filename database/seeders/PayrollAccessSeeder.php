<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PayrollAccessSeeder extends Seeder {
 public function run():void{DB::transaction(function(){foreach(['staff.manage'=>['owner','principal','coordinator'],'staff.attendance'=>['owner','principal','coordinator'],'staff.leave_approve'=>['owner','principal','coordinator'],'staff.self'=>['owner','principal','coordinator','teacher','accountant'],'payroll.view'=>[]] as $name=>$roles){DB::table('permissions')->updateOrInsert(['name'=>$name],['description'=>str_replace('.',' ',$name),'updated_at'=>now()]);$pid=DB::table('permissions')->where('name',$name)->value('id');foreach($roles as $role){$rid=DB::table('roles')->where('scope','school')->where('name',$role)->value('id');if($rid)DB::table('role_permissions')->insertOrIgnore(['role_id'=>$rid,'permission_id'=>$pid,'created_at'=>now(),'updated_at'=>now()]);}}});}
}
