<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUtilizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('utilization_statistics', 'former_utilization_statistics');
        Schema::create('utilization_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->date('month');
            $table->float('average_conferences')->default(0);
            $table->integer('max_concurrent_conferences')->default(0);
            $table->integer('active_days')->default(0);

            //Demo room

            $table->float('average_dm_connections')->default(0);
            $table->integer('max_dm_connections')->default(0);
            $table->date('max_dm_connections_day')->nullable();
            $table->integer('max_concurrent_dm')->default(0);
            $table->date('max_concurrent_dm_day')->nullable();

            //End demo room

            //H323

            $table->float('average_h323_connections')->default(0);
            $table->integer('max_h323_connections')->default(0);
            $table->date('max_h323_connections_day')->nullable();
            $table->integer('max_concurrent_h323')->default(0);
            $table->date('max_concurrent_h323_day')->nullable();

            //End h323

            //Utilization

            $table->float('host_cap_0')->default(0);
            $table->float('host_cap_20')->default(0);
            $table->float('host_cap_40')->default(0);
            $table->float('host_cap_60')->default(0);
            $table->float('host_cap_80')->default(0);


            $table->float('h323_cap_0')->default(0);
            $table->float('h323_cap_20')->default(0);
            $table->float('h323_cap_40')->default(0);
            $table->float('h323_cap_60')->default(0);
            $table->float('h323_cap_80')->default(0);


            //Calculation date utilization settings

            $table->integer('host_resources')->default(0);
            $table->integer('h323_resources')->default(0);

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
        Schema::rename('former_utilization_statistics', 'utilization_statistics');
    }
}
