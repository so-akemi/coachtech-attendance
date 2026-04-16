<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\AttendanceCorrectRequest;
use App\Models\RestCorrectRequest;
use App\Http\Requests\CorrectionRequest;


class RequestController extends Controller
{
    public function index(Request $request)
    {
        //dd([
        //    'Gate判定' => Gate::allows('admin'),
        //    'ログインユーザーID' => auth()->id(),
        //    'is_adminカラムの値' => auth()->user()->is_admin
        //]);
        // 1. タブの状態（承認待ち or 承認済み）を取得
        $statusParam = $request->query('status', 'pending');
        $statusValue = ($statusParam === 'approved') ? 1 : 0;

        // 2. クエリビルダのベースを作る（まだ実行しない）
        $query = AttendanceCorrectRequest::with('user')
            ->where('user_id', auth()->id()) // ログイン中のユーザーIDで固定
            ->where('status', $statusValue);

        // 3. 管理者かどうかで取得するデータを分ける
        //●if (Gate::allows('admin')) {
            // 管理者は全ユーザーの申請を取得
            //●$requests = $query->where('status', $statusValue)->latest()->get();
        //●} else {
        // 一般ユーザーは自分の申請だけを取得
        $requests = $query//->where('user_id', Auth::id())
                          ->latest()
                          ->get();
        //●$requests = $query->where('status', $statusValue)
                          //●->where('user_id', auth()->id())
                          //●->latest()
                          //●->get();
        //●}

        //dd($requests->count() . '件のデータが見つかりました', $requests->toArray());

        return view('requests.index', compact('requests', 'statusParam'));

    }

    public function store(CorrectionRequest $request, $id)
    {
        // 1. 元の勤怠データを取得（日付を取得するために必要）
        $attendance = \App\Models\Attendance::findOrFail($id);
        $date = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

        // 2. 勤怠本体の「修正申請」を保存
        // $request->note (Bladeのname) を reason (DBのカラム名) に入れる
        $correctRequest = AttendanceCorrectRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $id,
            'start_time'    => $date . ' ' . $request->start_time, // 日付と時刻を合体
            'end_time'      => $date . ' ' . $request->end_time,   // 日付と時刻を合体
            'reason'        => $request->note,
            'status'        => 0, // 0: 承認待ち
        ]);

        // 3. 既存の休憩に対する「修正申請」をループで保存
        if ($request->has('rests')) {
            foreach ($request->rests as $restData) {
                // 時刻が入力されている場合のみ保存
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    $correctRequest->restCorrectRequests()->create([
                        'start_time' => $date . ' ' . $restData['start_time'],
                        'end_time'   => $date . ' ' . $restData['end_time'],
                    ]);
                }
            }
        }


        // 4. 新しく追加された休憩の申請も保存（もしBladeに new_rests があるなら）
        if ($request->has('new_rests')) {
            foreach ($request->new_rests as $newData) {
                if (!empty($newData['start_time']) && !empty($newData['end_time'])) {
                    $correctRequest->restCorrectRequests()->create([
                        'start_time' => $date . ' ' . $newData['start_time'],
                        'end_time'   => $date . ' ' . $newData['end_time'],
                    ]);
                }
            }
        }


        return redirect()->route('request.index')->with('success', '修正申請を出しました');
    }
}
