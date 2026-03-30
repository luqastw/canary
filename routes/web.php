<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FlagController;
use App\Http\Controllers\Web\GroupController;
use App\Http\Controllers\Web\TargetingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome/Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Flags
    Route::resource('flags', FlagController::class);
    Route::patch('/flags/{flag}/toggle', [FlagController::class, 'toggle'])->name('flags.toggle');

    // Flags Targeting
    Route::get('/flags/{flag}/targeting', [TargetingController::class, 'manage'])->name('flags.targeting.manage');
    Route::post('/flags/{flag}/targeting', [TargetingController::class, 'store'])->name('flags.targeting.store');
    Route::put('/flags/{flag}/targeting', [TargetingController::class, 'replace'])->name('flags.targeting.replace');
    Route::delete('/flags/{flag}/targeting/{group}', [TargetingController::class, 'destroy'])->name('flags.targeting.destroy');

    // Groups
    Route::resource('groups', GroupController::class)->except(['show']);
});
