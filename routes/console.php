<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
Artisan::command('school:schema-status', function () {
    $tables = json_decode(file_get_contents(database_path('schema/schema.json')), true, 512, JSON_THROW_ON_ERROR);
    $missing = array_values(array_filter(array_column($tables, 'name'), fn ($name) => !Schema::hasTable($name)));
    $this->info(count($tables).' expected domain tables; '.count($missing).' missing.');
    foreach ($missing as $name) { $this->warn($name); }
    return count($missing) === 0 ? 0 : 1;
})->purpose('Check installed domain table inventory without exposing school records');
