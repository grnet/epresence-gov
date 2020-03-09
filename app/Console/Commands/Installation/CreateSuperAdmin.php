<?php

namespace App\Console\Commands\Installation;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:super_admin {first_name} {last_name} {email} {password}';

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
        $super_admin = User::create([
            "firstname" => $this->argument("first_name"),
            "lastname" => $this->argument("last_name"),
            "telephone" => null,
            "email" => $this->argument("email"),
            "password" => Hash::make($this->argument("password")),
            "tax_id" => null,
            "thumbnail" => null,
            "status"=>1,
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>null,
            "confirmed"=>1,
            "activation_token"=>null,
            "confirmation_code"=>null,
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);
        $super_admin->roles()->attach(1);
        $super_admin->institutions()->attach(1);
        $super_admin->departments()->attach(1);

        $this->info("Super admin created successfully!");
    }
}
