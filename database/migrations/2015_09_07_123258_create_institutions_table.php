<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInstitutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('institutions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->integer('ws_id')->nullable();
            $table->integer('api_code')->nullable();
            $table->timestamps();
		});
		
		//Table for connecting user to institution (get role of user `from role_user` table)
		Schema::create('institution_user', function(Blueprint $table)
		{
			$table->integer('institution_id')->unsigned();
			$table->integer('user_id')->unsigned();
			
			$table->foreign('institution_id')
				  ->references('id')
				  ->on('institutions')
				  ->onDelete('cascade');
				  
			$table->foreign('user_id')
				  ->references('id')
				  ->on('users')
				  ->onDelete('cascade');
				  
			$table->primary(['institution_id', 'user_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('institution_user', function(Blueprint $table)
        {
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::drop('institution_user');
        Schema::drop('institutions');
    }
}
