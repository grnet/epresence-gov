<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateConferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conferences', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
			$table->text('desc')->nullable();
            $table->text('descEn')->nullable();
			$table->integer('user_id')->unsigned();
			$table->integer('institution_id')->unsigned()->nullable();
			$table->integer('department_id')->unsigned()->nullable();


			$table->timestamp('start')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('end')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('max_duration')->default(360)->unsigned();
			$table->integer('max_users')->default(15)->unsigned();
			$table->integer('max_h323')->default(0)->unsigned();
			$table->integer('max_vidyo_room')->unsigned()->nullable();
			$table->string('room_url')->nullable();

			$table->integer('room_enabled')->nullable()->default(0);


			$table->string('extension')->nullable();

			$table->string('pin')->nullable();
			$table->integer('vRoomID')->nullable();
			$table->string('moderator_pin')->nullable();
			$table->string('moderator_url')->nullable();


			$table->integer('users_no')->nullable();
			$table->integer('users_h323')->nullable();
			$table->integer('users_vidyo_room')->nullable();
			$table->integer('instantActivation')->default(0)->unsigned();
			$table->boolean('invisible')->default(0);
            $table->boolean('locked')->default(0);
			$table->timestamp('forced_end')->nullable();
			$table->timestamps();
			
			$table->foreign('user_id')
				  ->references('id')
				  ->on('users')
				  ->onDelete('cascade');

            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');
		});
		
		//Table for the users that are going to participate in a conference
		Schema::create('conference_user', function(Blueprint $table)
		{
			$table->integer('conference_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->integer('invited')->unsigned()->default(0);
			$table->integer('confirmed')->unsigned()->default(0);
			$table->integer('joined_once')->unsigned()->default(0);

            $table->integer('duration')->unsigned()->nullable();

            $table->text('intervals')->nullable();

            $table->string('address')->nullable();

            $table->integer('active')->nullable()->default(0);
			$table->integer('enabled')->unsigned()->default(1);
			$table->integer('participantID')->nullable();
			$table->string('device')->nullable();
			$table->text('confirmation_code')->nullable();
			$table->string('identifier')->nullable();
			
			$table->foreign('conference_id')
				  ->references('id')
				  ->on('conferences')
				  ->onDelete('cascade');
				  
			$table->foreign('user_id')
				  ->references('id')
				  ->on('users')
				  ->onDelete('cascade');
				  

				  
			$table->primary(['conference_id', 'user_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('conference_user', function(Blueprint $table)
        {
            $table->dropForeign(['conference_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::drop('conference_user');

        Schema::table('conferences', function(Blueprint $table)
        {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::drop('conferences');

    }
}
