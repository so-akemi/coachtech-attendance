<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request, $date = null)
{
    // 1. 日付の決定（URLになければ今日）
        // $date が '2026-04-12' のような文字列で来ることを想定
        $dateInput = $request->query('date') ?? $date;
        $targetDate = $dateInput ? Carbon::parse($dateInput) : Carbon::today();

        //dd($dateInput, $targetDate->format('Y-m-d'));

        // 2. 前日・翌日の日付を作成（Bladeのリンク用）
        $prevDate = $targetDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

        // 3. 全ユーザーのその日の勤怠を取得
        $attendances = Attendance::with('user')
                        ->whereDate('date', $targetDate->format('Y-m-d'))
                        ->get();

        // 4. Viewに渡す
        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'currentDate'  => $targetDate, // Carbonインスタンスのまま渡すとBladeでフォーマットしやすい
            'prevDate'    => $prevDate,
            'nextDate'    => $nextDate,
        ]);
    }
}
