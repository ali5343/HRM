<?php

use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeekendController;
use App\Http\Controllers\WfhController;
use App\Http\Controllers\WorkFromHomeController;
use App\Models\Attendance;
use App\Models\Weekend;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\http\Controllers\AdminDashboardController;

/* Route::get('/', function () {
    return view('dashboard');
}); */

Route::get('/', [DashboardController::class, 'view'])->middleware(['auth', 'verified'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'view'])->middleware(['auth', 'verified'])->name('dashboard');

// Route to display the dashboard with weekend attendance

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::middleware('auth')->group(function () {
    Route::post('/request/create', [RequestController::class, 'create'])->name('request.create');
});
Route::get('/requests', function () {
    return view('requests');
});

Route::get('/pending-requests', [AdminRequestController::class, 'index'])->name('admin.requests.pending');
Route::post('/admin/requests/{id}/approve', [AdminRequestController::class, 'approve'])->name('admin.requests.approve');

Route::get('/admin-dashboard', [AdminDashboardController::class, 'view']);


/* Route::middleware('role:admin')->group(function (){
    Route::get('/admin/requests', [AdminRequestController::class, 'index'])->name('admin.requests');
    Route::post('/admin/requests/{id}/approve', [AdminRequestController::class, 'approve'])->name('admin.requests.approve');
}); */


/* Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminRequestController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/pending-requests', [AdminRequestController::class, 'index'])->name('admin.requests.pending');
    Route::post('/admin/requests/{id}/approve', [AdminRequestController::class, 'approve'])->name('admin.requests.approve');
}); */


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
Route::get('/daily', [AttendanceController::class, 'index'])->name('daily.index');



Route::get('/weekend', function () {
    return view('weekend');
});

Route::post('/weekend', [WeekendController::class, 'store'])->name('weekend');
Route::get('/weekend', [WeekendController::class, 'index'])->name('weekend.index');



Route::get('/workfh', function () {
    return view('wfh');
});

Route::post('/workfh', [WorkFromHomeController::class, 'store'])->name('wfh');
Route::get('/workfh', [WorkFromHomeController::class, 'index'])->name('wfh.index');



Route::get('/leaves', function () {
    return view('leaves');
});


require __DIR__ . '/auth.php';
