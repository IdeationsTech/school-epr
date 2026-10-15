<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class PlatformBrowserSeeder extends Seeder
{
    public function run(): void
    {
        if(!app()->environment(['local','testing']) || !str_ends_with((string)config('database.connections.mysql.database'),'_test'))throw new \RuntimeException('Browser fixture requires a local/testing _test database.');
        $password=env('DEMO_ADMIN_PASSWORD');if(!is_string($password)||strlen($password)<12)throw new \RuntimeException('Provide a test fixture password.');
        DB::table('users')->updateOrInsert(['email'=>'platform@example.test'],['name'=>'Platform Operator','password'=>Hash::make($password),'created_at'=>now(),'updated_at'=>now()]);
        $id=DB::table('users')->where('email','platform@example.test')->value('id');
        DB::table('platform_admins')->updateOrInsert(['user_id'=>$id],['active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
}
