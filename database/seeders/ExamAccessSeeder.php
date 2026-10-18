<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ExamAccessSeeder extends Seeder {
 public function run():void{DB::table('permissions')->updateOrInsert(['name'=>'tests.view'],['description'=>'Read tests and reports in assigned sections','updated_at'=>now()]);}
}
