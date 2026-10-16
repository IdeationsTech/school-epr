<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function():void {
            $modules=['student_admissions','fees','attendance','daily_diary','tests_results','staff_payroll','manual_whatsapp_sharing'];
            foreach([
                ['code'=>'STARTER-500','name'=>'Starter','student_limit'=>500,'campus_limit'=>1,'monthly_price'=>'3000.00','annual_price'=>'30000.00'],
                ['code'=>'STANDARD-1000','name'=>'Standard','student_limit'=>1000,'campus_limit'=>2,'monthly_price'=>'5000.00','annual_price'=>'50000.00'],
            ] as $package){
                $annual=$package['annual_price'];unset($package['annual_price']);
                // Re-running deployment seeders must never reset operator-edited prices or limits.
                DB::table('plans')->insertOrIgnore($package+[
                    'annual_price'=>$annual,'available'=>true,'modules'=>json_encode($modules),
                    'features'=>json_encode(['active'=>true,'annual_price'=>$annual,'modules'=>$modules]),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            // Retire only the original, untouched zero-price scaffold from new sales.
            // Existing subscriptions and invoice amounts are not changed.
            $legacy=DB::table('plans')->where('code','school-standard')->lockForUpdate()->first();
            if($legacy && $legacy->name==='School Standard' && (string)$legacy->monthly_price==='0.00'){
                $features=json_decode($legacy->features??'[]',true);
                if($features===['fees','diary','tests','payroll']){
                    DB::table('plans')->where('id',$legacy->id)->update([
                        'available'=>false,'features'=>json_encode(['active'=>false,'annual_price'=>'0.00','modules'=>$features]),'updated_at'=>now(),
                    ]);
                }
            }
        });
    }
}
