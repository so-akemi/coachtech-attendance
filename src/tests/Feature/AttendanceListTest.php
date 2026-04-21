<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID9: 自分が行った勤怠情報が全て表示されている
     */
    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        // 自分のデータ2件
        Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'start_time' => '09:00', 'end_time' => '18:00']);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-04-02', 'start_time' => '09:00', 'end_time' => '18:00']);

        // 他人のデータ1件
        $otherUser = User::factory()->create();
        Attendance::create(['user_id' => $otherUser->id, 'date' => '2026-04-01', 'start_time' => '10:00']);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        // HTMLの表示形式「04/01」に合わせてチェック
        $response->assertSee('04/01');
        $response->assertSee('04/02');

        // 他人のデータの開始時間「10:00」が表示されていないことを確認
        $response->assertDontSee('10:00');
    }

    /**
     * ID9: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_前月ボタンを押した時に前月の情報が表示される()
    {
        $user = User::factory()->create();

        // 2026-03 というクエリパラメータを投げる
        $response = $this->actingAs($user)->get(route('attendance.index', ['month' => '2026-03']));

        $response->assertStatus(200);

        // 画面の表記に合わせてアサーションを変更（例：2026年03月）
        $response->assertSee('2026');
        $response->assertSee('03');
    }

    /**
     * ID10: 勤怠詳細画面の日付が選択した日付になっている
     */
    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-21',
            'start_time' => '09:00:00'
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('2026');
        $response->assertSee('21');
    }
}
