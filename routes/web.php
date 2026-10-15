<?php
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Middleware\SetSchoolContext;
use Illuminate\Support\Facades\Route;
foreach (['/','/school','/campus','/users','/roles'] as $path) {
    Route::view($path, 'app');
}
Route::view('/admin/{path?}', 'app')->where('path','.*');
Route::prefix('app-api')->group(function (): void {
    Route::get('/session', [SessionController::class, 'show']);
    Route::post('/login', [SessionController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/logout', [SessionController::class, 'logout']);
    Route::post('/switch-school', [SessionController::class, 'switchSchool']);
    Route::prefix('platform')->middleware(\App\Http\Middleware\RequirePlatformAdmin::class)->group(function(): void {
        Route::get('/dashboard',[\App\Http\Controllers\Api\PlatformController::class,'dashboard']);
        Route::get('/plans',[\App\Http\Controllers\Api\PlatformController::class,'plans']);
        Route::post('/plans',[\App\Http\Controllers\Api\PlatformController::class,'savePlan']);
        Route::put('/plans/{plan}',[\App\Http\Controllers\Api\PlatformController::class,'savePlan'])->whereNumber('plan');
        Route::get('/schools',[\App\Http\Controllers\Api\PlatformController::class,'schools']);
        Route::post('/schools',[\App\Http\Controllers\Api\PlatformController::class,'createSchool']);
        Route::get('/schools/{school}',[\App\Http\Controllers\Api\PlatformController::class,'school'])->whereNumber('school');
        Route::post('/schools/{school}/subscriptions',[\App\Http\Controllers\Api\PlatformController::class,'renew'])->whereNumber('school');
        Route::put('/schools/{school}/status',[\App\Http\Controllers\Api\PlatformController::class,'status'])->whereNumber('school');
        Route::post('/schools/{school}/invoices/{invoice}/payments',[\App\Http\Controllers\Api\PlatformController::class,'payment'])->whereNumber(['school','invoice']);
    });
    Route::middleware(SetSchoolContext::class)->group(function (): void {
        Route::get('/bootstrap', [SchoolController::class, 'bootstrap']);
        Route::put('/school', [SchoolController::class, 'updateSchool']);
        Route::get('/campuses', [SchoolController::class, 'campuses']);
        Route::post('/campuses', [SchoolController::class, 'saveCampus']);
        Route::put('/campuses/{campus}', [SchoolController::class, 'saveCampus'])->whereNumber('campus');
        Route::delete('/campuses/{campus}', [SchoolController::class, 'deleteCampus'])->whereNumber('campus');
        Route::get('/members', [SchoolController::class, 'members']);
        Route::post('/members', [SchoolController::class, 'saveMember']);
        Route::put('/members/{member}', [SchoolController::class, 'saveMember'])->whereNumber('member');
        Route::get('/roles', [SchoolController::class, 'roleMatrix']);
        Route::put('/roles/{role}', [SchoolController::class, 'saveRole'])->whereNumber('role');
    });
});
