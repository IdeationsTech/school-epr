<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class FinanceAccessSeeder extends Seeder {
 public function run():void{DB::transaction(function(){foreach(['finance.view'=>['owner','accountant'],'finance.manage'=>['owner','accountant'],'finance.approve'=>['owner']] as $name=>$roles){DB::table('permissions')->updateOrInsert(['name'=>$name],['description'=>str_replace('.',' ',$name),'updated_at'=>now()]);$pid=DB::table('permissions')->where('name',$name)->value('id');foreach($roles as $role){$rid=DB::table('roles')->where('scope','school')->where('name',$role)->value('id');if($rid)DB::table('role_permissions')->insertOrIgnore(['role_id'=>$rid,'permission_id'=>$pid,'created_at'=>now(),'updated_at'=>now()]);}}});}
}
