<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderFieldToAdminResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
                    $table->integer('order')->default(0);
            });
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('downloads', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
