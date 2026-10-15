<?php

namespace App\Console\Commands;

use Database\Seeders\AccessSetupSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SetupSchool extends Command
{
    protected $signature = 'school:setup';
    protected $description = 'Create a school, main campus and first owner without a default password';

    public function handle(): int
    {
        $data=['school'=>$this->ask('School name'),'code'=>strtoupper((string)$this->ask('Unique school code (letters, numbers, dash)')),
            'name'=>$this->ask('Owner name'),'email'=>strtolower(trim((string)$this->ask('Owner email'))),
            'password'=>$this->secret('Owner password (at least 12 characters)'),
            'password_confirmation'=>$this->secret('Confirm owner password')];
        $v=Validator::make($data,['school'=>'required|string|max:160','code'=>'required|alpha_dash|max:30|unique:schools,code',
            'name'=>'required|string|max:160','email'=>'required|email|max:160|unique:users,email','password'=>['required','string','min:12','confirmed',new \App\Rules\PasswordByteLimit()]]);
        if($v->fails()) { foreach($v->errors()->all() as $error) { $this->error($error); } return self::FAILURE; }
        $this->call('db:seed',['--class'=>ReferenceDataSeeder::class,'--force'=>true]);
        $this->call('db:seed',['--class'=>AccessSetupSeeder::class,'--force'=>true]);
        DB::transaction(function() use($data): void {
            $sid=DB::table('schools')->insertGetId(['name'=>$data['school'],'code'=>$data['code'],'timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            $cid=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main Campus','code'=>'MAIN','status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            $uid=DB::table('users')->insertGetId(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'created_at'=>now(),'updated_at'=>now()]);
            $mid=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$uid,'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$mid,'role_id'=>DB::table('roles')->where('name','owner')->value('id'),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid,'created_at'=>now(),'updated_at'=>now()]);
        });
        $this->info('School and owner created. Sign in at your local application URL using the chosen credentials.');
        return self::SUCCESS;
    }
}
