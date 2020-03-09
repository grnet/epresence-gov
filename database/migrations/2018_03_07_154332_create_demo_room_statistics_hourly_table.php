<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateDemoRoomStatisticsHourlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demo_room_statistics_hourly', function (Blueprint $table) {
            $table->time('hour')->unique()->index();
            $table->integer('connections');
        });
        Schema::create('demo_room_statistics_monthly', function (Blueprint $table) {
            $table->date('month')->unique()->index();
            $table->integer('connections');
        });
        Schema::create('demo_room_connections', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->unique();
            $table->integer('total_connections');
            $table->integer('last_month_connections');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('demo_room_statistics_hourly');
        Schema::drop('demo_room_statistics_monthly');
        Schema::drop('demo_room_connections');
    }
}
