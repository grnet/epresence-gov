<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateConferencesTableZoom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->integer('named_user_id')->unsigned()->nullable();
            $table->foreign('named_user_id')->references('id')->on('named_users');
            $table->string('start_url',1000);
            $table->string('join_url',255);
            $table->string('zoom_meeting_id',255);
            $table->boolean('host_url_accessible')->default(false);
            $table->boolean('test')->default(false);
            $table->dropColumn(['users_no','users_h323','users_vidyo_room','max_vidyo_room','max_users','max_h323','extension','vRoomID','moderator_pin','moderator_url','room_url']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vidyoID']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropForeign(['named_user_id']);
            $table->dropColumn(['named_user_id','start_url','join_url','zoom_meeting_id','host_url_accessible']);
            $table->string('users_no')->nullable();
            $table->string('users_h323')->nullable();
            $table->string('users_vidyo_room')->nullable();
            $table->string('max_vidyo_room')->nullable();
            $table->string('max_users')->nullable();
            $table->string('max_h323')->nullable();
            $table->string('extension')->nullable();
            $table->string('vRoomID')->nullable();
            $table->string('moderator_pin')->nullable();
            $table->string('moderator_url')->nullable();
            $table->string('room_url')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('vidyoID');
        });

    }
}
