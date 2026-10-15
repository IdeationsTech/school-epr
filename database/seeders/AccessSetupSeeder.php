<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Additive and repeatable: never resets existing school overrides, users or passwords.
        $permissions = ['dashboard.view', 'school.view', 'school.manage', 'campuses.manage', 'members.manage', 'roles.manage'];
        DB::transaction(function () use ($permissions): void {
            foreach ($permissions as $name) {
                DB::table('permissions')->updateOrInsert(['name' => $name], ['description' => str_replace('.', ' ', $name), 'updated_at' => now()]);
                $pid = DB::table('permissions')->where('name', $name)->value('id');
                $roles = in_array($name, ['dashboard.view', 'school.view'], true)
                    ? ['owner', 'principal', 'accountant', 'coordinator', 'teacher'] : ['owner'];
                foreach ($roles as $role) {
                    $rid = DB::table('roles')->where('name', $role)->value('id');
                    if ($rid) { DB::table('role_permissions')->updateOrInsert(['role_id' => $rid, 'permission_id' => $pid], ['updated_at' => now()]); }
                }
            }
        });
    }
}
