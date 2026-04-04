<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RequestController;

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
// --- 一般ユーザー用 (Fortifyが自動で作る /login を使用) ---

// --- 管理者用 (PG07) ---
Route::get('/admin/login', function () {
    return view('admin.auth.login'); // 管理者専用のログイン画面を作成
})->middleware(['guest'])->name('admin.login');

// ログイン処理自体はFortifyのコントローラーを再利用できます
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest']);

Route::middleware(['auth', 'verified'])->group(function () {

    // --- 一般ユーザー専用 (PG03 - PG06) ---
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'create'])->name('create'); // PG03
        // 出勤処理 (store)
        Route::post('/', [AttendanceController::class, 'store'])->name('store');

        // 退勤処理 (update)
        Route::patch('/', [AttendanceController::class, 'update'])->name('update');

        // 休憩開始 (rest.store) ※Bladeで使っている場合
        Route::post('/rest', [AttendanceController::class, 'restStore'])->name('rest.store');

        // 休憩終了 (rest.update) ※Bladeで使っている場合
        Route::patch('/rest', [AttendanceController::class, 'restUpdate'])->name('rest.update');

        Route::get('/list', [AttendanceController::class, 'index'])->name('index'); // PG04
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('show'); // PG05
    });

    // PG06 & PG12: 申請一覧（共通パス）
    // ※コントローラー内で Gate::allows('admin') を使って取得データやViewを分岐させる
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('request.index');


    // --- 管理者専用 (PG08 - PG11, PG13) ---
    // 先ほど作った 'admin' ミドルウェアでガード
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

        // PG08, PG09: 勤怠管理
        Route::get('/attendance/list', [AttendanceController::class, 'adminIndex'])->name('attendance.index');
        Route::get('/attendance/{id}', [AttendanceController::class, 'adminShow'])->name('attendance.show');

        // PG10, PG11: スタッフ管理
        Route::get('/staff/list', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/attendance/staff/{id}', [StaffController::class, 'attendance'])->name('staff.attendance');

        // PG13: 承認画面
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [RequestController::class, 'approveForm'])->name('request.approve');
    });
});
