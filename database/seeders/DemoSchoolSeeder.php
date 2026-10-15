<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
class DemoSchoolSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local','testing'])) {
            throw new RuntimeException('Demo seeding is local/testing only.');
        }
        $password = config('school.demo_password');
        if (!is_string($password) || strlen($password)<12) {
            throw new RuntimeException('Set DEMO_ADMIN_PASSWORD to at least 12 characters in local .env.');
        }
        $this->call(ReferenceDataSeeder::class);
        DB::transaction(function () use ($password): void {
            $now = now();
            $sid = DB::table('schools')->where('code','DEMO')->value('id')
                ?? DB::table('schools')->insertGetId([
                    'code'=>'DEMO','name'=>'Bright Future School (Demo)','timezone'=>'Asia/Karachi',
                    'currency'=>'PKR','status'=>'active','created_at'=>$now,'updated_at'=>$now,
                ]);
            $uid = DB::table('users')->where('email','owner@demo.example')->value('id')
                ?? DB::table('users')->insertGetId([
                    'name'=>'Demo Owner','email'=>'owner@demo.example','password'=>Hash::make($password),
                    'created_at'=>$now,'updated_at'=>$now,
                ]); // Rerunning never resets existing user passwords.
            DB::table('campuses')->updateOrInsert(['school_id'=>$sid,'code'=>'MAIN'],
                ['name'=>'Main Campus','status'=>'active','updated_at'=>$now]);
            DB::table('school_memberships')->updateOrInsert(['school_id'=>$sid,'user_id'=>$uid],
                ['status'=>'active','updated_at'=>$now]);
            $mid=DB::table('school_memberships')->where('school_id',$sid)->where('user_id',$uid)->value('id');
            $cid=DB::table('campuses')->where('school_id',$sid)->where('code','MAIN')->value('id');
            DB::table('membership_roles')->updateOrInsert([
                'school_id'=>$sid,'membership_id'=>$mid,'role_id'=>DB::table('roles')->where('name','owner')->value('id'),
            ],['updated_at'=>$now]);
            DB::table('membership_campuses')->updateOrInsert([
                'school_id'=>$sid,'membership_id'=>$mid,'campus_id'=>$cid,
            ],['updated_at'=>$now]);
            DB::table('academic_sessions')->updateOrInsert(['school_id'=>$sid,'name'=>'2026–2027'],[
                'starts_on'=>'2026-04-01','ends_on'=>'2027-03-31','status'=>'active','updated_at'=>$now,
            ]);
            foreach (['Tuition','Admission','Annual','Transport'] as $name) {
                DB::table('fee_heads')->updateOrInsert(['school_id'=>$sid,'code'=>strtolower($name)],
                    ['name'=>$name,'updated_at'=>$now]);
            }
        });
    }
}
