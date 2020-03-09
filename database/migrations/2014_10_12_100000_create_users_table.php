<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
			$table->string('email')->unique();
			$table->string('password', 60);
			$table->string('firstname', 50)->nullable();
			$table->string('lastname', 50)->nullable();
			$table->string('telephone')->nullable();
			$table->string('tax_id', 255)->nullable();
			$table->string('thumbnail')->nullable();
            $table->integer('status')->default(0);
			$table->string('state')->nullable();
            $table->integer('creator_id')->unsigned()->nullable();
            $table->text('comment')->nullable();
			$table->text('admin_comment')->nullable();
			$table->boolean('confirmed')->default(0);
			$table->string('activation_token', 100)->nullable();
            $table->string('confirmation_code')->nullable();
            $table->boolean('deleted')->default(0);
			$table->rememberToken();
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
        Schema::drop('users');
    }
}
