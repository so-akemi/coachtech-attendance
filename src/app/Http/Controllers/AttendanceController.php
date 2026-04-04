<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 規約：定数は大文字のスネークケース（マジックナンバーの回避）
    const STATUS_BEFORE_WORK = 'before_work';
    const STATUS_WORKING     = 'working';
    const STATUS_RESTING     = 'resting';
    const STATUS_AFTER_WORK  = 'after_work';

    /**
     * 勤怠登録画面（PG03）を表示する
     */
    public function create()
    {
        $status = $this->getAttendanceStatus(Auth::id());

        // 規約：変数はキャメルケース / 真偽値はisを用いる
        return view('attendance.create', [
            'status'       => $status,
            'isBeforeWork' => $status === self::STATUS_BEFORE_WORK,
            'isWorking'    => $status === self::STATUS_WORKING,
            'isResting'    => $status === self::STATUS_RESTING,
            'isAfterWork'  => $status === self::STATUS_AFTER_WORK,
        ]);
    }

    /**
     * 現在の勤務ステータスを判定して返す
     * 規約：関数名は動詞からはじめる
     */
    private function getAttendanceStatus($userId)
    {
        $today = Carbon::today();

        // 当日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // 規約：アーリーリターンで条件が満たされないケースを先に処理
        if (!$attendance) {
            return self::STATUS_BEFORE_WORK;
        }

        if ($attendance->end_time) {
            return self::STATUS_AFTER_WORK;
        }

        // 休憩中かどうかの判定（休憩テーブルやフラグがある想定）
        // 例: $attendance->is_resting が true なら休憩中
        if ($attendance->is_resting) {
            return self::STATUS_RESTING;
        }

        return self::STATUS_WORKING;
    }

    /**
     * 勤怠一覧画面を表示する
     */
    public function index()
    {
        return view('attendance.index');
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $today  = Carbon::today();

        // 1. 二重出勤チェック（規約：アーリーリターン）
        $exists = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'すでに出勤済みです。');
        }

        // 2. 勤怠レコードの作成（ここで実際にDBへ保存されます）
        Attendance::create([
            'user_id'    => $userId,
            'date'       => $today,
            'start_time' => Carbon::now(), // 現在時刻
            'is_resting' => false,
        ]);

        return redirect()->route('attendance.create');
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $today  = Carbon::today();

        // 1. 今日の出勤レコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // 2. アーリーリターン：もしレコードがなければ（通常ありえませんが）戻す
        if (!$attendance) {
            return redirect()->back();
        }

        // 3. 退勤時刻を現在時刻で更新
        $attendance->update([
            'end_time' => Carbon::now(),
        ]);

        return redirect()->route('attendance.create');
    }

    public function restStore(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        if ($attendance) {
            $attendance->update(['is_resting' => true]);
        }

        return redirect()->route('attendance.create');
    }

    public function restUpdate(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        if ($attendance) {
            $attendance->update(['is_resting' => false]);
        }

        return redirect()->route('attendance.create');
    }
}
