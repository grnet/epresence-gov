<?php

namespace App\Console\Commands;

use App\Application;
use App\Cdr;
use App\DemoRoomCdr;
use App\ExtraEmail;
use Illuminate\Console\Command;
use App\User;
use App\Conference;
use Illuminate\Support\Facades\DB;

class MergeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge:users {userToDelete} {userToKeep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Draw all the data from userFrom to userTo and delete userFrom afterwards';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //php artisan merge:users {userToDelete} {userToKeep}

        $userToKeep = User::findOrFail($this->argument('userToKeep'));

        if($userToKeep->merge_user($this->argument('userToDelete')))
        $this->info('All done :)');
        else
        $this->error('Something went wrong!');

    }
}
