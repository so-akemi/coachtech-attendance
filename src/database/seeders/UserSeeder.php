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
        $testUser =User::create([
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

        $extraUsers = User::factory()->count(5)->create(['is_admin' => false]);

        $allUsers = collect([$testUser])->concat($extraUsers);

        // 今月の開始日から終了日までを取得
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($allUsers as $user) {
            foreach ($period as $date) {
                // 土日を除外
                if ($date->isWeekend()) {
                    continue;
                }

                if ($date->isToday()) {
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

        $faker = \Faker\Factory::create('ja_JP');

        // 特定のユーザーに「承認待ち」の申請を作る
        $targetUsers = collect([
            $testUser,
            $extraUsers->random(), // 追加したユーザーのうちの最初
        ]);

        foreach ($targetUsers as $user) {

            for ($dayOffset = 1; $dayOffset <= 3; $dayOffset++) {
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id, // $targetUser->id ではなく $user->id に修正
                    'date'    => Carbon::now()->addMonth()->startOfMonth()->addDays($dayOffset)->format('Y-m-d'),
                    'status'  => 0, // 承認待ち
                    'note'    => $faker->randomElement([
                        '打刻ミス',
                        '電車遅延',
                        '入力忘れ',
                        '直行のため',
                        '直帰のため',
                        '寝坊です',
                        '体調不良',
                    ]),
                ]);

                 \DB::table('attendance_correct_requests')->insert([
                    'user_id'       => $user->id,
                    'attendance_id' => $attendance->id,
                    'start_time'    => '09:00:00',
                    'end_time'      => '18:00:00',
                    'status'        => 0, // 承認待ち
                    'reason'        => $attendance->note,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 特定のユーザーに「承認済み」の申請を作る
            for ($dayOffset = 11; $dayOffset <= 12; $dayOffset++) {
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date'    => Carbon::now()->addMonth()->startOfMonth()->addDays($dayOffset)->format('Y-m-d'),
                    'status'  => 1, // 承認済み
                    'note'    => $attendance->note?? '修正済み',
                ]);

                \DB::table('attendance_correct_requests')->insert([
                    'user_id'       => $user->id,
                    'attendance_id' => $attendance->id,
                    'start_time'    => '10:00:00',
                    'end_time'      => '19:00:00',
                    'status'        => 1, // 承認済み
                    'reason'        => $attendance->note?? '修正済み',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
