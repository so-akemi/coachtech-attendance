<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class StaffController extends Controller
{
    public function index()
    {
    // 全ユーザーを取得（管理者を除外するロジックがあれば where で追加）
        $users = User::where('is_admin', '!=', true)->get();

        return view('admin.staffs.index', compact('users'));
    }

    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 1. 月の基準点を作成（Bladeに合わせて $currentMonth に統一）
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month)->startOfMonth();

        // 2. 指定ユーザーの勤怠取得
        $attendances = Attendance::where('user_id', $id)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->orderBy('date', 'asc')
            ->get();

        // 3. 前月・翌月の文字列（リンク用）
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('admin.staffs.attendance', compact(
            'user',
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }
}
