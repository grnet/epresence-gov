<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLanguageLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('language_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group');
            $table->index('group');
            $table->string('key');
            $table->index('key');
            $table->text('text');
            $table->boolean('dirty')->default(false);
            $table->boolean('has_tags')->default(false);

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
        Schema::drop('language_lines');
        Schema::enableForeignKeyConstraints();
    }
}
