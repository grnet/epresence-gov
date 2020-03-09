<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRolesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('label')->nullable();
			$table->timestamps();
		});
		
		Schema::create('permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('label')->nullable();
            $table->timestamp('timestamps')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
		
		Schema::create('permission_role', function(Blueprint $table)
		{
			$table->integer('permission_id')->unsigned();
			$table->integer('role_id')->unsigned();
			
			$table->foreign('permission_id')
				  ->references('id')
				  ->on('permissions')
				  ->onDelete('cascade');
				  
			$table->foreign('role_id')
				  ->references('id')
				  ->on('roles')
				  ->onDelete('cascade');
				  
			$table->primary(['permission_id', 'role_id']);
		});
		
		Schema::create('role_user', function(Blueprint $table)
		{
			$table->integer('role_id')->unsigned();
			$table->integer('user_id')->unsigned();
			
			$table->foreign('role_id')
				  ->references('id')
				  ->on('roles')
				  ->onDelete('cascade');
				  
			$table->foreign('user_id')
				  ->references('id')
				  ->on('users')
				  ->onDelete('cascade');
				  
			$table->primary(['role_id', 'user_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('role_user', function(Blueprint $table)
        {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::drop('role_user');

        Schema::table('permission_role', function(Blueprint $table)
        {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
        });

        Schema::drop('permission_role');



        Schema::drop('permissions');
        Schema::drop('roles');


    }
}
