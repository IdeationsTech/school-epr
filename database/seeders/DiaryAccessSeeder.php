<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DiaryAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Existing diary.write / diary.publish already permit reading. This optional
        // permission grants read-only access within a staff member's assigned sections.
        // No default grants or school overrides are changed by the module upgrade.
        DB::table('permissions')->updateOrInsert(['name' => 'diary.view'], ['description' => 'View diary in assigned sections', 'updated_at' => now()]);
    }
}
