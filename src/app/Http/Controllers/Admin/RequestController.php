<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    //PG12:/勤怠修正申請の一覧表示
    public function index(Request $request)
    {
        $statusParam = $request->query('status', 'pending');
        $statusCode = ($statusParam === 'approved') ? 1 : 0;

        $requests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', $statusCode)
            ->latest()
            ->get();

        // dd($requests);

        return view('requests.index', compact('requests', 'statusParam'));
    }

    /**
     * PG13: 修正申請承認画面（詳細表示）
     */
    public function showApprove($id)
    {
        // 修正申請のIDで取得
        $correctRequest = AttendanceCorrectRequest::with(['user', 'restCorrectRequests'])->findOrFail($id);

        // ステータスが1なら「承認済み」、0なら「承認ボタン」を出す判定に使う
        return view('admin.requests.approve', compact('correctRequest'));
    }

    /**
     * 承認実行ロジック
     */
    public function approve(Request $request, $id)
    {
        $correctRequest = AttendanceCorrectRequest::with('attendance.rests','restCorrectRequests')->findOrFail($id);
        $attendance = $correctRequest->attendance;

        $date = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

        DB::transaction(function () use ($correctRequest, $attendance , $date) {
            // 1. 本体の更新
            $attendance->update([
                'start_time' => $correctRequest->start_time,
                'end_time'   => $correctRequest->end_time,
                // 'note'       => $correctRequest->note,
                'note'      => $correctRequest->reason,
            ]);

            // 2. 既存の休憩データを一度削除（上書きするため）
            $attendance->rests()->delete();

            // 3. 申請された休憩データを Rest テーブルに保存
            foreach ($correctRequest->restCorrectRequests as $restRequest) {
                $attendance->rests()->create([
                    'start_time' => $date . ' ' . $restRequest->start_time,
                    'end_time'   => $restRequest->end_time ? $date . ' ' . $restRequest->end_time : null,
                ]);
            }

            // 2. ステータスを承認済み(1)に
            $correctRequest->update(['status' => 1]);
        });

        // 承認後、再度同じ画面を表示して「承認済み」ボタンに変わったことを確認させる
        return redirect()->back()->with('success', '承認が完了しました');
    }
}
