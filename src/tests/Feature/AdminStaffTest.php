<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID15: 管理者がスタッフの勤怠詳細を確認できる
     */
    public function test_管理者がスタッフ個人の勤怠一覧を表示できる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['name' => 'スタッフA']);

        Attendance::create([
            'user_id' => $staff->id,
            'date' => '2026-04-01',
            'start_time' => '09:00'
        ]);

        // 管理者がスタッフAの一覧画面にアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['user_id' => $staff->id]));

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('04/01');
    }

    /**
     * ID16: 管理者が前月・翌月ボタンで表示月を切り替えられる
     */
    public function test_管理者がスタッフ勤怠一覧の表示月を切り替えられる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'user_id' => $staff->id,
            'month' => '2026-03'
        ]));

        $response->assertSee('2026');
        $response->assertSee('03');
    }
}
