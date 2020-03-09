<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entity_code');
            $table->string('entity_subunit');
            $table->string('transaction_id');
            $table->string('transaction_reason');
            $table->string('server_hostname');
            $table->string('server_host_ip');
            $table->string('end_user_device_info');
            $table->string('end_user_device_ip');
            $table->string('end_user_id');
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
        Schema::dropIfExists('audit_logs');
    }
}
