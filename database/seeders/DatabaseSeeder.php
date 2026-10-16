<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reference data only. Demo data requires a separate explicit command.
        $this->call([ReferenceDataSeeder::class, AccessSetupSeeder::class, ServicePackageSeeder::class]);
    }
}
