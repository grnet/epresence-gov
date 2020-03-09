<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsDailyMonthly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics_monthly', function (Blueprint $table) {
            $table->increments('id');
            $table->date('month');
            $table->integer('max_desktop')->nullable()->default(0);
            $table->integer('max_h323')->nullable()->default(0);
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
        Schema::drop('statistics_monthly');
    }
}
