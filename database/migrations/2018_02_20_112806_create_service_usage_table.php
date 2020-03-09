<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateServiceUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_usage', function (Blueprint $table) {
            $table->string("option");
            $table->integer("total_conferences");
            $table->integer("desktop_mobile");
            $table->integer("h323");
            $table->float("average_participants");
            $table->integer("euro_saved");
            $table->date("updated_at");
            $table->primary('option');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('service_usage');
    }
}
