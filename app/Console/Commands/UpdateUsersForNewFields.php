<?php

namespace App\Console\Commands;

use App\Jobs\Users\UpdateCivilServantField;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateUsersForNewFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        User::where("confirmed",true)->update(['email_verified_at'=>Carbon::now()]);
        $users = User::whereNotNull("tax_id")->get();
        foreach($users as $user){
            UpdateCivilServantField::dispatch($user)->onQueue('low');
        }
    }
}
