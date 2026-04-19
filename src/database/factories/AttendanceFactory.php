<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 9:00〜11:00の間にランダムに出勤
        $attendanceTime = $this->faker->dateTimeBetween('09:00', '11:00');
        // 出勤から8〜10時間後に退勤
        $leaveTime = (clone $attendanceTime)->modify('+' . rand(8, 10) . ' hours');

        return [
            'user_id' => User::factory(), // ユーザーを自動生成
            'date' => $this->faker->date(),
            'start_time' => $attendanceTime->format('H:i'),
            'end_time' => $leaveTime->format('H:i'),
            'status' => 0,
        ];
    }
}
