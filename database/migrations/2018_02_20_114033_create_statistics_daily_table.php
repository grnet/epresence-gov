<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics_daily', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('distinct_users_no_desktop')->nullable()->default(0);
            $table->integer('users_no_desktop')->nullable()->default(0);
            $table->integer('distinct_users_no_h323')->nullable()->default(0);
            $table->integer('users_no_h323')->nullable()->default(0);
            $table->integer('conferences_no')->nullable()->default(0);
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
        Schema::drop('statistics_daily');
    }
}
