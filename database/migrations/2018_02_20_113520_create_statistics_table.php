<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('conference_id')->unsigned();
            $table->foreign('conference_id')
                ->references('id')
                ->on('conferences')
                ->onDelete('cascade');
            $table->integer('institution_id')->unsigned();
            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions')
                ->onDelete('cascade');
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');
            $table->bigInteger('duration')->nullable();
            $table->integer('users_no_desktop')->nullable();
            $table->integer('users_no_h323')->nullable();
            $table->integer('users_no_v_room')->nullable();
            $table->boolean("active")->default(0);
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
        Schema::drop('statistics');
    }
}
