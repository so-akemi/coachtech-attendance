<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザーの作成
        User::create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('testpassword'),
            'is_admin' => false,
        ]);

        // 管理者の作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
            'is_admin' => true,
        ]);

        $users = User::factory()->count(5)->create(['is_admin' => false]);

        // 今月の開始日から終了日までを取得
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($users as $user) {
            foreach ($period as $date) {
                // 土日を除外
                if ($date->isWeekend()) {
                    continue;
                }

                // 勤怠データの作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    // ID5のテスト用に一部を「承認待ち」にする
                    'status' => ($date->day % 5 == 0) ? 1 : 0,
                ]);

                // 休憩データの作成
                // 1回目：お昼休み
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $date->format('Y-m-d') . ' 12:00:00',
                    'end_time'   => $date->format('Y-m-d') . ' 13:00:00',
                ]);

                // 2回目：15時台 (日付を付与するように修正)
                $timeStr = collect(['00', '15', '30'])->random();
                $secondStart = $date->copy()->setTime(15, (int)$timeStr, 0);
                $secondEnd = (clone $secondStart)->addMinutes(15);

                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $secondStart->format('Y-m-d H:i:s'),
                    'end_time'   => $secondEnd->format('Y-m-d H:i:s'),
                ]);

                // 3回目：たまに追加 (30%の確率)
                if (rand(1, 100) <= 30) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $date->format('Y-m-d') . ' 17:00:00',
                        'end_time'   => $date->format('Y-m-d') . ' 17:15:00',
                    ]);
                }
            }
        }

        // 特定のユーザーに「承認待ち」の申請を作る
        $targetUser = $users->first();

        for ($i = 1; $i <= 3; $i++) {
            Attendance::factory()->create([
                'user_id' => $targetUser->id,
                'date'    => Carbon::now()->addMonth()->startOfMonth()->addDays($i)->format('Y-m-d'),
                'status'  => 1, // 承認待ち
             ]);
        }

        // 特定のユーザーに「承認済み」の申請を作る
        for ($i = 11; $i <= 12; $i++) {
            Attendance::factory()->create([
                'user_id' => $targetUser->id,
                'date'    => Carbon::now()->addMonth()->startOfMonth()->addDays($i)->format('Y-m-d'),
                'status'  => 2, // 承認済み
            ]);
        }
    }
}
