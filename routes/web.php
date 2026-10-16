<?php
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Middleware\SetSchoolContext;
use Illuminate\Support\Facades\Route;
foreach (['/','/school','/campus','/users','/roles','/license'] as $path) {
    Route::view($path, 'app');
}
Route::view('/admin/{path?}', 'app')->where('path','.*');
Route::prefix('app-api')->group(function (): void {
    Route::get('/session', [SessionController::class, 'show']);
    Route::post('/login', [SessionController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/logout', [SessionController::class, 'logout']);
    Route::post('/switch-school', [SessionController::class, 'switchSchool']);
    Route::get('/account',[\App\Http\Controllers\Api\SchoolAccountController::class,'show']);
    Route::post('/account/tickets',[\App\Http\Controllers\Api\SchoolAccountController::class,'createTicket'])->middleware('throttle:20,1');
    Route::get('/account/tickets/{ticket}',[\App\Http\Controllers\Api\SchoolAccountController::class,'ticket'])->whereNumber('ticket');
    Route::post('/account/tickets/{ticket}/replies',[\App\Http\Controllers\Api\SchoolAccountController::class,'reply'])->whereNumber('ticket')->middleware('throttle:30,1');
    Route::prefix('platform')->middleware(\App\Http\Middleware\RequirePlatformAdmin::class)->group(function(): void {
        Route::post('/schools/{school}/subscriptions/{subscription}/change',[\App\Http\Controllers\Api\PlatformController::class,'changePackage'])->whereNumber(['school','subscription']);
        Route::get('/licenses',[\App\Http\Controllers\Api\ProviderOperationsController::class,'licenses']);
        Route::put('/schools/{school}/license',[\App\Http\Controllers\Api\ProviderOperationsController::class,'licenseStatus'])->whereNumber('school');
        Route::post('/schools/{school}/subscriptions/{subscription}/cancel',[\App\Http\Controllers\Api\ProviderOperationsController::class,'cancelPeriod'])->whereNumber(['school','subscription']);
        Route::get('/schools/{school}/invoices/{invoice}/document',[\App\Http\Controllers\Api\ProviderOperationsController::class,'document'])->whereNumber(['school','invoice']);
        Route::get('/billing',[\App\Http\Controllers\Api\ProviderOperationsController::class,'billing']);
        Route::post('/schools/{school}/payments/{payment}/reverse',[\App\Http\Controllers\Api\ProviderOperationsController::class,'reverse'])->whereNumber(['school','payment']);
        Route::post('/schools/{school}/invoices/{invoice}/adjust',[\App\Http\Controllers\Api\ProviderOperationsController::class,'adjust'])->whereNumber(['school','invoice']);
        Route::get('/audit',[\App\Http\Controllers\Api\ProviderOperationsController::class,'auditLog']);
        Route::get('/settings',[\App\Http\Controllers\Api\ProviderOperationsController::class,'settings']);
        Route::put('/settings',[\App\Http\Controllers\Api\ProviderOperationsController::class,'saveSettings']);
        Route::get('/support',[\App\Http\Controllers\Api\ProviderOperationsController::class,'tickets']);
        Route::post('/support',[\App\Http\Controllers\Api\ProviderOperationsController::class,'saveTicket']);
        Route::get('/support/{ticket}',[\App\Http\Controllers\Api\ProviderOperationsController::class,'ticket'])->whereNumber('ticket');
        Route::put('/support/{ticket}',[\App\Http\Controllers\Api\ProviderOperationsController::class,'saveTicket'])->whereNumber('ticket');
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
