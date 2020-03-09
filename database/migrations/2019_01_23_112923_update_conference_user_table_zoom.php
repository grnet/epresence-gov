<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateConferenceUserTableZoom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conference_user', function (Blueprint $table) {
            $table->string('join_url',1000)->nullable();
            $table->string('registrant_id')->nullable();
            $table->boolean('in_meeting')->default(false);
            $table->dropColumn(['participantID','active']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conference_user', function (Blueprint $table) {
            $table->string('participantID')->nullable();
            $table->boolean('active');
            $table->dropColumn(['join_url','registrant_id','in_meeting']);
        });
    }
}
