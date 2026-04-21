<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- 管理者用 (PG07) ---
Route::get('/admin/login', function () {
    return view('admin.auth.login'); // 管理者専用のログイン画面を作成
})->middleware(['guest'])->name('admin.login');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'create'])->name('create'); // PG03
        Route::post('/', [AttendanceController::class, 'store'])->name('store');

        Route::patch('/', [AttendanceController::class, 'update'])->name('update');

        Route::post('/rest', [AttendanceController::class, 'restStore'])->name('rest.store');
        Route::patch('/rest', [AttendanceController::class, 'restUpdate'])->name('rest.update');

        Route::get('/list', [AttendanceController::class, 'index'])->name('index'); // PG04
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('show'); // PG05
        Route::post('/detail/{id}', [RequestController::class, 'store'])->name('updateRequest');// 修正申請を送るアクション (PG05からのPOST先)
    });


    Route::get('/stamp_correction_request/list', function (Illuminate\Http\Request $request) {
    if (auth()->user()->is_admin) {
        return app(\App\Http\Controllers\Admin\RequestController::class)->index($request);
    }

    return app(\App\Http\Controllers\RequestController::class)->index($request);
    })->middleware(['auth', 'verified'])->name('request.index');


    Route::middleware(['auth', 'verified', 'admin'])->group(function () {

        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'showApprove'])->name('admin.request.approve');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'approve'])->name('admin.request.approve.post');
    });


    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/logout', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
        Route::get('/attendance/staff/export/{id}', [AdminStaffController::class, 'exportCsv'])->name('attendance.staff.export');
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'staffAttendance'])->name('attendance.staff');
    });
});
