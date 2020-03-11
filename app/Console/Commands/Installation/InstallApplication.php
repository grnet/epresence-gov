<?php

namespace App\Console\Commands\Installation;

use Illuminate\Console\Command;

class InstallApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:application';

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
        $this->call("install:institutions");
        $this->info("Installed default institution and departments...");
        $this->call("install:languages");
        $this->call("install:videos");
        $this->call("install:documents");
        $this->call("install:downloads");
        $this->call("install:statistics");
        $this->call("install:notifications");
        $this->info("Installed default videos, languages, documents, downloads, statistics and notifications...");
        //Keep sql seeders last
        $this->call("install:settings");
        $this->call("install:roles");
        $this->call("install:permissions");
        $this->call("install:role_permission");
        $this->call("install:emails");
        $this->info("Application installed successfully!");

    }
}
