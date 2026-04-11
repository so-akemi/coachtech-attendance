<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Rest;

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
    public function index(Request $request)
    {
        $userId = Auth::id();

        // クエリパラメータから月を取得、なければ今月
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        // 前月と翌月の月文字列を作成
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        return view('attendance.index', compact(
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
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
            // 1. Restsテーブルに新しい休憩レコードを作成（開始時刻のみ）
            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time'    => Carbon::now(),
            ]);

            // 2. 画面判定用に Attendance側のフラグを休憩中にする
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
            // この出勤に紐づく休憩のうち、まだ end_time が空のものを1つ取得
            $latestRest = Rest::where('attendance_id', $attendance->id)
                ->whereNull('end_time')
                ->latest()
                ->first();

            if ($latestRest) {
                $latestRest->update(['end_time' => Carbon::now()]);
            }
            // 画面判定用フラグを勤務中に戻す
            $attendance->update(['is_resting' => false]);
        }

        return redirect()->route('attendance.create');
    }

    public function show(Request $request, $id)
    {
        // Eager Loadで休憩データも一緒に取得
        $attendance = Attendance::with('rests')->findOrFail($id);

        // ログインユーザー本人のデータかチェック（セキュリティ）
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $isEditMode = $request->query('mode') === 'edit';

        return view('attendance.show', compact('attendance', 'isEditMode'));
    }

    public function updateRequest(Request $request, $id)
    {
        // 1. データの取得
        $attendance = Attendance::findOrFail($id);

        // セキュリティ：本人のデータか確認
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        // --- 日付を固定して時刻を合体させる関数 ---
        $date = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

        // 2. 勤怠本体の更新
        // ※ old() で返ってきた値や $request の値で更新します
        $attendance->update([
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'note'       => $request->note,
        ]);

        // 3. 休憩データの更新
        if ($request->has('rests')) {
            foreach ($request->rests as $restData) {
                // 修正対象の休憩レコードをIDで特定
                $rest = Rest::where('attendance_id', $attendance->id)
                            ->find($restData['id']);

                if ($rest) {
                    $rest->update([
                        'start_time' => $restData['start_time']? $date . ' ' . $restData['start_time'] : null,
                        'end_time'   => $restData['end_time']? $date . ' ' . $restData['end_time'] : null,
                    ]);
                }
            }
        }

        if ($request->has('new_rests')) {
        foreach ($request->new_rests as $newData) {
            // 開始時刻が入力されている場合のみ保存
            if (!empty($newData['start_time'])) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time'    => $date . ' ' . $newData['start_time'],
                    'end_time'      => !empty($newData['end_time']) ? $date . ' ' . $newData['end_time'] : null,
                ]);
                }
            }
        }

        // 4. 詳細画面に戻る（クエリパラメータ mode=edit を外してリダイレクト）
        return redirect()->route('attendance.show', ['id' => $id])
                         ->with('success', '勤怠を修正しました');
    }
}
