<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AcademicSetupSeeder extends Seeder
{
 public function run():void{DB::transaction(function(){foreach(['academics.view'=>['owner','principal','coordinator','teacher'],'academics.manage'=>['owner','principal','coordinator']] as $name=>$roles){DB::table('permissions')->updateOrInsert(['name'=>$name],['description'=>str_replace('.',' ',$name),'updated_at'=>now()]);$pid=DB::table('permissions')->where('name',$name)->value('id');foreach($roles as $name){$rid=DB::table('roles')->where('name',$name)->where('scope','school')->value('id');if($rid)DB::table('role_permissions')->updateOrInsert(['role_id'=>$rid,'permission_id'=>$pid],['updated_at'=>now()]);}}});}
}
