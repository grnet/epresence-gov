<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUtilizationStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utilization_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->date('month');
            $table->float('average_conferences');
            $table->integer('active_days');
            //Demo room

            $table->float('average_dm_connections');
            $table->integer('max_dm_connections');
            $table->date('max_dm_connections_day')->nullable();
            $table->integer('max_concurrent_dm');
            $table->date('max_concurrent_dm_day')->nullable();

            //End demo room


            //H323

            $table->float('average_h323_connections');
            $table->integer('max_h323_connections');
            $table->date('max_h323_connections_day')->nullable();
            $table->integer('max_concurrent_h323');
            $table->date('max_concurrent_h323_day')->nullable();

            //End h323

            //Utilization

            $table->float('dm_cap_0');
            $table->float('dm_cap_20');
            $table->float('dm_cap_40');
            $table->float('dm_cap_60');
            $table->float('dm_cap_80');


            $table->float('h323_cap_0');
            $table->float('h323_cap_20');
            $table->float('h323_cap_40');
            $table->float('h323_cap_60');
            $table->float('h323_cap_80');

            //Calculation date utilization settings

            $table->integer('desktop_resources');
            $table->integer('h323_resources');
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
        Schema::drop('utilization_statistics');
    }
}
