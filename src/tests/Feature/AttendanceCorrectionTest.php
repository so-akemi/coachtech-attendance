<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID11: 出勤時間が退勤時間より後、もしくは休憩時間が勤務時間より長い場合
     */
    /**
     * ID11: 出勤時間が退勤時間より後の場合はバリデーションエラー
     */
    public function test_出勤時間が退勤時間より後の場合はバリデーションエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-21',
            'start_time' => '09:00',
            'end_time' => '18:00'
        ]);

        $response = $this->actingAs($user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'date' => '2026-04-21', // prepareForValidationで必要
            'start_time' => '19:00',
            'end_time' => '18:00',
            'note' => '修正理由のテスト入力' // 'reason' から 'note' に修正
        ]);

        // Laravelのafterルールは、後の項目(end_time)にエラーがつきます
        $response->assertSessionHasErrors(['end_time']);
    }

    /**
     * ID11: 修正理由が未入力の場合はバリデーションエラー
     */
    public function test_修正理由が未入力の場合はバリデーションエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-21']);

        $response = $this->actingAs($user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'date' => '2026-04-21',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '' // 'reason' から 'note' に修正
        ]);

        $response->assertSessionHasErrors(['note']);
    }
}
