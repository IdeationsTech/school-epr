<?php
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Middleware\SetSchoolContext;
use Illuminate\Support\Facades\Route;
Route::view('/', 'app');
Route::prefix('app-api')->group(function (): void {
    Route::get('/session', [SessionController::class, 'show']);
    Route::post('/login', [SessionController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/logout', [SessionController::class, 'logout']);
    Route::post('/switch-school', [SessionController::class, 'switchSchool']);
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
