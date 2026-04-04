<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
