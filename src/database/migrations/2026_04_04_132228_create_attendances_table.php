<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date'); // 勤務日
            $table->time('start_time')->nullable(); // 出勤時刻
            $table->time('end_time')->nullable();   // 退勤時刻
            $table->text('note')->nullable();       // 備考（任意）
            $table->boolean('is_resting')->default(false); // 休憩中
            $table->integer('status')->default(0)->comment('0:勤務外, 1:出勤中, 2:休憩中, 3:退勤済');
            $table->unique(['user_id', 'date']);// 同じユーザーが同じ日に2回出勤レコードを作らないための制約（任意）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
