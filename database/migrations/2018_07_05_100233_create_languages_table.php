<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active')->default(false);
            $table->boolean('primary')->default(false);
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('language_lines', function (Blueprint $table) {
            $table->integer('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->integer('original_id')->unsigned()->nullable();
            $table->foreign('original_id')->references('id')->on('language_lines')->onDelete('cascade');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('languages');
        Schema::enableForeignKeyConstraints();
    }
}
