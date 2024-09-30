<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WfhController;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('dashboard');
}); */

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* Route::resource('attendance', AttendanceController::class)->only(['index', 'store']); */

Route::get('/attendance', [AttendanceController::class, 'index']);
Route::post('/attendance/clockin', [AttendanceController::class, 'clockIn']);
Route::post('/attendance/clockout', [AttendanceController::class, 'clockOut']);

Route::post('/leave/request', [LeavesController::class, 'requestLeave']);
Route::get('/leave/status', [LeavesController::class, 'leaveStatus']);

Route::post('/wfh/request', [WfhController::class, 'requestWFH']);
Route::get('/wfh/status', [WfhController::class, 'statusWFH']);

Route::post('/overtime/log', [OvertimeController::class, 'logOvertime']);

Route::get('/daily', function () {
    return view('daily');
});

Route::post('/daily', [AttendanceController::class, 'store'])->name('daily');

Route::get('/weekend', function () {
    return view('weekend');
});
Route::get('/home', function () {
    return view('home');
});
Route::get('/leaves', function () {
    return view('leaves');
});

require __DIR__.'/auth.php';
