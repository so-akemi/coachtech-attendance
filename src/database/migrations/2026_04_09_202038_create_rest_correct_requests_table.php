<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_correct_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_request_id')->constrained('attendance_correct_requests')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time')->nullable();
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
        Schema::dropIfExists('rest_correct_requests');
    }
}
