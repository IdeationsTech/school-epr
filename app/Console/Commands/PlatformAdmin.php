<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class PlatformAdmin extends Command
{
    protected $signature='platform:admin';
    protected $description='Grant platform access to an existing local user; no password is changed';
    public function handle(): int
    {
        $email=strtolower(trim((string)$this->ask('Existing account email for platform administrator')));
        $user=DB::table('users')->where('email',$email)->first();
        if(!$user){$this->error('Account not found. Use an existing owner account email.');return self::FAILURE;}
        DB::table('platform_admins')->updateOrInsert(['user_id'=>$user->id],['active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        $this->info('Platform access granted. Sign in with the same password at /admin.');return self::SUCCESS;
    }
}
