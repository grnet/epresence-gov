<?php

namespace App\Console\Commands;

use App\Settings;
use Illuminate\Console\Command;

class LockLanguageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lock:language_files {option}';

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
        $option = $this->argument('option');

        if(in_array($option,['0','1'])){

            Settings::where('category','admin')->where('title','locked_language_files')->update(["option"=>$option]);

            if($option == 1)
            $this->info('Language files locked!');
            else
            $this->info('Language files unlocked!');

        }else{
            $this->error('Wrong value in option');
        }
    }
}
