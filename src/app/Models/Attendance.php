<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     * 規約：各カラム名はスネークケース（DBの慣習）、変数命名などはキャメルケース
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',    // ユーザーID
        'date',       // 勤務日
        'start_time', // 出勤時刻
        'end_time',   // 退勤時刻
        'note',       // 備考（任意で追加）
        'is_resting', // 休憩中フラグ（真偽値：規約のis+形容詞）
    ];

    /**
     * データの型変換（キャスト）
     * 規約：真偽値として正しく扱うために指定
     */
    protected $casts = [
        'date'       => 'date',
        'is_resting' => 'boolean',
    ];

    /**
     * ユーザーとのリレーション（多対1）
     * 規約：メソッド名は単数形（1つの勤怠は1人のユーザーに属するため）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩とのリレーション（1対多）
     * 規約：メソッド名は複数形（1つの勤怠には複数の休憩があるため）
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function getTotalRestTime()
    {
        $totalMinutes = 0;

        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = Carbon::parse($rest->start_time);
                $end   = Carbon::parse($rest->end_time);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * 実働時間（勤務合計）を「H:i」形式で返す
     * 計算式：(退勤 - 出勤) - 休憩合計
     */
    public function getWorkingTime()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);

        // 1. 総拘束時間（分）
        $stayMinutes = $start->diffInMinutes($end);

        // 2. 休憩合計（分）を計算
        $restMinutes = 0;
        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $restMinutes += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }

        // 3. 実働時間 = 拘束 - 休憩
        $workingMinutes = $stayMinutes - $restMinutes;

        // マイナスにならないよう一応ケア
        if ($workingMinutes < 0) $workingMinutes = 0;

        $hours = floor($workingMinutes / 60);
        $minutes = $workingMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function isPending()
    {
    // この勤怠に紐づく修正申請のうち、statusが0（承認待ち）のものが存在するか
    return $this->correctionRequests()->where('status', 0)->exists();
    }

    // AttendanceCorrectRequestモデルとのリレーション
    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}
