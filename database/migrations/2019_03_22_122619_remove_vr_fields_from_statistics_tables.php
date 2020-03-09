<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveVrFieldsFromStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn(["users_no_v_room"]);
        });

        Schema::table('statistics_daily', function (Blueprint $table) {
            $table->dropColumn(["users_no_v_room","distinct_users_no_v_room"]);
        });

        Schema::table('statistics_monthly', function (Blueprint $table) {
            $table->dropColumn(["max_vidyoRoom","max_desktop_100","max_desktop_70","max_desktop_50","max_h323_100","max_h323_70","max_h323_50"]);
        });

        Schema::table('service_usage', function (Blueprint $table) {
            $table->renameColumn('vr_h323', 'h323');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
