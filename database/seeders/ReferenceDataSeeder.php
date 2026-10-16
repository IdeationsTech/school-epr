<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (['owner', 'principal', 'accountant', 'coordinator', 'teacher'] as $role) {
                DB::table('roles')->updateOrInsert(['name' => $role], ['scope' => 'school', 'updated_at' => now()]);
            }
            $grants = [
                'students.view' => ['owner','principal','accountant','coordinator','teacher'],
                'students.manage' => ['owner','principal'],
                'fees.view' => ['owner','principal','accountant'],
                'fees.collect' => ['owner','accountant'],
                'fees.approve_reversal' => ['owner'],
                'attendance.manage' => ['owner','principal','coordinator','teacher'],
                'diary.write' => ['owner','principal','coordinator','teacher'],
                'diary.publish' => ['owner','principal','coordinator'],
                'tests.enter_marks' => ['owner','principal','coordinator','teacher'],
                'tests.publish' => ['owner','principal','coordinator'],
                'reports.confirm_share' => ['owner','principal','coordinator','teacher'],
                'reports.export' => ['owner','principal','accountant'],
                'payroll.manage' => ['owner','accountant'],
                'payroll.approve' => ['owner'],
                'settings.manage' => ['owner'],
                'audit.view' => ['owner','principal'],
            ];
            foreach ($grants as $name => $roleNames) {
                DB::table('permissions')->updateOrInsert(['name'=>$name], ['description'=>str_replace('.', ' ', $name), 'updated_at'=>now()]);
                $pid = DB::table('permissions')->where('name', $name)->value('id');
                foreach ($roleNames as $role) {
                    DB::table('role_permissions')->updateOrInsert([
                        'role_id'=>DB::table('roles')->where('name',$role)->value('id'),
                        'permission_id'=>$pid,
                    ], ['updated_at'=>now()]);
                }
            }
            DB::table('plans')->insertOrIgnore(['code'=>'school-standard',
                'name'=>'School Standard','student_limit'=>1000,'campus_limit'=>2,
                'annual_price'=>'0.00','available'=>false,'modules'=>json_encode([]),'monthly_price'=>'0.00', // Placeholder, not commercial pricing.
                'features'=>json_encode(['active'=>false,'annual_price'=>'0.00','modules'=>['fees','diary','tests','payroll']]), 'created_at'=>now(), 'updated_at'=>now(),
            ]);
        });
    }
}
