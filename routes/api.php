<?php
use Illuminate\Support\Facades\Route;
// Domain endpoints will be added only with authentication, membership checks and policies.
Route::get('/status', fn () => ['application' => 'school-erp', 'stage' => 'database-foundation']);
