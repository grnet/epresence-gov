<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('departments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
			$table->integer('institution_id')->unsigned();
			$table->timestamps();
			
			$table->foreign('institution_id')
				  ->references('id')
				  ->on('institutions')
				  ->onDelete('cascade');
		});
		
		//Table for connecting user to department (get role of user `from role_user` table)
		Schema::create('department_user', function(Blueprint $table)
		{
			$table->integer('department_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->foreign('department_id')
				  ->references('id')
				  ->on('departments')
				  ->onDelete('cascade');
			$table->foreign('user_id')
				  ->references('id')
				  ->on('users')
				  ->onDelete('cascade');
				  
			$table->primary(['department_id', 'user_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('department_user', function(Blueprint $table)
        {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::drop('department_user');
        Schema::table('departments', function(Blueprint $table)
        {
            $table->dropForeign(['institution_id']);
        });
        Schema::drop('departments');
    }
}
