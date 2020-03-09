<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateTranslationCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translation_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->integer('language_line_id')->unsigned();
            $table->foreign('language_line_id')->references('id')->on('language_lines')->onDelete('cascade');
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
        Schema::dropIfExists('translation_comments');
        Schema::enableForeignKeyConstraints();
    }
}
