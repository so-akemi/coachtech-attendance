<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    // テスト実行ごとにデータをリセットしてくれる魔法の言葉
    use RefreshDatabase;

    /**
     * ID4: 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('id="date"', false);
        $response->assertSee('id="time"', false);
    }

    /**
     * ID5: ステータス確認機能
     */
    public function test_ステータス確認機能_各ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $today = now()->toDateString();

        // 1. 勤務外
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('勤務外');

        // 2. 出勤中 (is_resting=false, end_time=null)
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => now(),
            'is_resting' => false
        ]);
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('出勤中');

        // 3. 休憩中 (is_resting=true)
        $attendance->update(['is_resting' => true]);
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('休憩中');

        // 4. 退勤済 (end_timeに値がある)
        $attendance->update(['is_resting' => false, 'end_time' => now()]);
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('退勤済');
    }

    /**
     * ID6: 出勤機能
     */
    public function test_出勤ボタンが正しく機能し一度打刻するとボタンが表示されない()
    {
        $user = User::factory()->create();

        // 出勤打刻を実行
        $this->actingAs($user)->post(route('attendance.store'));

        // ステータスが出勤中になっているか
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('出勤中');

        // 退勤済みの状態（end_timeを入れる）にする
        Attendance::where('user_id', $user->id)->first()->update(['end_time' => now()]);

        // 退勤後は「出勤」という文字列を含むボタン（btn-mainクラスなど）が出ないことを確認
        // ※「出勤」という言葉自体はナビ等にある可能性があるため、タグのクラスで判定
        $response = $this->actingAs($user)->get(route('attendance.create'));
        $response->assertDontSee('class="btn-main"', false);
    }

    /**
     * ID7: 休憩機能
     */
    public function test_休憩ボタンが正しく機能し一日に何度も取得できる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'is_resting' => false
        ]);

        // 1回目の休憩入
        $this->actingAs($user)->post(route('attendance.rest.store'));
        $this->assertTrue((bool)$attendance->refresh()->is_resting);

        // 1回目の休憩戻
        $this->actingAs($user)->patch(route('attendance.rest.update'));
        $this->assertFalse((bool)$attendance->refresh()->is_resting);

        // 2回目の休憩入
        $this->actingAs($user)->post(route('attendance.rest.store'));

        // 休憩レコードが2件あるか確認
        $this->assertCount(2, Rest::where('attendance_id', $attendance->id)->get());
    }

    /**
     * ID8: 退勤機能
     */
    public function test_退勤ボタンが正しく機能し退勤時刻が記録される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'is_resting' => false
        ]);

        // 退勤処理
        $this->actingAs($user)->patch(route('attendance.update'));

        $attendance->refresh();
        // 実装に合わせて end_time をチェック
        $this->assertNotNull($attendance->end_time);
    }
}
