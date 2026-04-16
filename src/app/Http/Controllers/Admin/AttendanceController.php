<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\Admin\AdminAttendanceUpdateRequest;
use App\Models\Rest;

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

    public function show(Request $request, $id)
    {
        // 指定された勤怠データと、それに紐づく休憩データを取得
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $isEditMode = $request->query('mode') === 'edit';

        return view('admin.attendance.show', compact('attendance', 'isEditMode'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // FN038: 念のためサーバー側でも「承認待ち」の更新をブロック
        if ($attendance->isPending()) {
            return redirect()->back()->with('error', '承認待ちの修正申請があるため、直接の修正はできません。');
        }

        $targetDate = \Carbon\Carbon::parse($attendance->date);
        $dateStr = $targetDate->format('Y-m-d'); // 文字列として保持

        // 1. 勤怠本体の更新
        $attendance->update([
            'start_time' => $request->start_time ? $targetDate->format('Y-m-d') . ' ' . $request->start_time : $attendance->start_time,
            'end_time'   => $request->end_time ? $targetDate->format('Y-m-d') . ' ' . $request->end_time : $attendance->end_time,
            'reason'      => $request->note, // Bladeのname属性はnoteですが、DBのカラム名はreasonなので注意
        ]);

        // 2. 休憩データの更新（既存のものをループして更新）
        if ($request->has('rests')) {
            foreach ($request->rests as $restData) {
                if (isset($restData['id'])) {
                    Rest::where('id', $restData['id'])->update([
                        'start_time' => $restData['start_time'] ? $dateStr . ' ' . $restData['start_time'] : null,
                        'end_time'   => $restData['end_time'] ? $dateStr . ' ' . $restData['end_time'] : null,
                    ]);
                }
            }
        }

        // ★ 3. 新規追加された休憩（new_rests）を保存する処理を追記
        if ($request->has('new_rests')) {
            foreach ($request->new_rests as $newData) {
                // 開始時間と終了時間の両方が入力されている場合のみ保存
                if (!empty($newData['start_time']) && !empty($newData['end_time'])) {
                    $attendance->rests()->create([
                        'start_time' => $dateStr . ' ' . $newData['start_time'],
                        'end_time'   => $dateStr . ' ' . $newData['end_time'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.show', ['id' => $attendance->id])->with('success', '勤怠データを修正しました');
    }

    public function approve(Request $request, $id)
    {
        // $id は Attendance の ID ではなく、AttendanceCorrectRequest の ID を想定
        $correctRequest = \App\Models\AttendanceCorrectRequest::findOrFail($id);
        $attendance = Attendance::findOrFail($correctRequest->attendance_id);

        // データの不整合を防ぐためトランザクションを使用
        \DB::transaction(function () use ($correctRequest, $attendance) {
            // (A) 勤怠データを申請内容で上書き
            $attendance->update([
                'start_time' => $correctRequest->start_time,
                'end_time'   => $correctRequest->end_time,
                // 'note'       => $correctRequest->note,
                'reason'      => $correctRequest->reason, // Bladeのname属性はnoteですが、DBのカラム名はreasonなので注意
            ]);

            // 2. 既存の休憩データを一度削除（上書きするため）
            $attendance->rests()->delete();

            // 3. 申請された休憩データを Rest テーブルに保存
            foreach ($correctRequest->restCorrectRequests as $restRequest) {
                $attendance->rests()->create([
                    'start_time' => $restRequest->start_time,
                    'end_time'   => $restRequest->end_time,
                ]);
            }

            // (B) 申請ステータスを承認済み(1)にする
            $correctRequest->update([
                'status' => 1,
            ]);
        });

        return redirect()->route('admin.requests.approve')->with('success', '勤怠修正申請を承認しました。');
    }

    public function showApprove($id)
    {
        // 申請データを取得
        $correctRequest = AttendanceCorrectRequest::with(['user', 'attendance.rests'])->findOrFail($id);

        // 対応表に従い、管理者の修正申請承認画面を表示
        return view('admin.requests.approve', compact('correctRequest'));
    }
}
