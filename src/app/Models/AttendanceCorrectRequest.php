<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'start_time',
        'end_time',
        'reason',
        'status',
    ];

    // Userモデルとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Attendanceモデルとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // RestCorrectRequestモデルとのリレーション
    public function restCorrectRequests()
    {
        return $this->hasMany(RestCorrectRequest::class);
    }
}
