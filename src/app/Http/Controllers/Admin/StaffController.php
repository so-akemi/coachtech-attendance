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
        //$attendances = Attendance::where('user_id', $id)
            //->whereMonth('date', $currentMonth->month)
            //->whereYear('date', $currentMonth->year)
            //->orderBy('date', 'asc')
            //->get();

        $attendanceList = Attendance::getMonthlyListForUser($id, $month);

        // 3. 前月・翌月の文字列（リンク用）
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('admin.staffs.attendance', compact(
            'user',
            'attendanceList',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function exportCsv(Request $request, $id)
{
    $user = User::findOrFail($id);
    $month = $request->query('month', now()->format('Y-m'));

    // 先ほど作った共通メソッドで「1ヶ月分のデータ」を取得
    $attendanceList = Attendance::getMonthlyListForUser($id, $month);

    // CSVのファイル名
    $fileName = "attendance_{$user->name}_{$month}.csv";

    // ヘッダー（CSVの1行目）
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ];

    $callback = function() use ($attendanceList) {
        $file = fopen('php://output', 'w');

        // Excelで開いた時の文字化け防止（BOM）
        fputs($file, "\xEF\xBB\xBF");

        // カラム名（1行目）
        fputcsv($file, ['日付', '出勤時間', '退勤時間', '休憩合計', '実働時間']);

        // データを1行ずつ書き込む
        foreach ($attendanceList as $item) {
            $att = $item['attendance'];
            fputcsv($file, [
                $item['date']->format('Y/m/d'),
                $att ? \Carbon\Carbon::parse($att->start_time)->format('H:i') : '',
                ($att && $att->end_time) ? \Carbon\Carbon::parse($att->end_time)->format('H:i') : '',
                $att ? $att->getTotalRestTime() : '',
                $att ? $att->getWorkingTime() : '',
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
