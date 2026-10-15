<?php
namespace App\Console\Commands;

use App\Rules\PasswordByteLimit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PlatformAdmin extends Command
{
    protected $signature='platform:admin';
    protected $description='Create a separate platform administrator account without school membership';

    public function handle(): int
    {
        $data=[
            'name'=>trim((string)$this->ask('Platform administrator name')),
            'email'=>strtolower(trim((string)$this->ask('New platform administrator email'))),
        ];
        // Reject existing school/admin accounts before asking for any password.
        $identity=Validator::make($data,[
            'name'=>'required|string|min:2|max:160',
            'email'=>'required|email|max:160|unique:users,email',
        ],['email.unique'=>'This email already belongs to an account. Use a new email for the separate platform administrator.']);
        if($identity->fails()){
            foreach($identity->errors()->all() as $error)$this->error($error);
            return self::FAILURE;
        }
        $data['password']=$this->secret('Platform administrator password (at least 12 characters)');
        $data['password_confirmation']=$this->secret('Confirm platform administrator password');
        $password=Validator::make($data,['password'=>['required','string','min:12','confirmed',new PasswordByteLimit()]]);
        if($password->fails()){
            foreach($password->errors()->all() as $error)$this->error($error);
            return self::FAILURE;
        }
        DB::transaction(function()use($data):void{
            $id=DB::table('users')->insertGetId([
                'name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('platform_admins')->insert(['user_id'=>$id,'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
            DB::table('platform_audit_logs')->insert([
                'actor_user_id'=>$id,'action'=>'platform.admin_created','target_type'=>'user','target_id'=>$id,
                'changes'=>json_encode(['source'=>'trusted_cli']),'created_at'=>now(),'updated_at'=>now(),
            ]);
        });
        $this->info('Separate platform administrator created. Sign in at /admin with the new email and password.');
        return self::SUCCESS;
    }
}
