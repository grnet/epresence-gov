<?php

namespace App\Console\Commands;

use App\Imports\PersistentIdImport;
use Illuminate\Console\Command;
use Excel;

class matchPersistentIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:persistent_ids';

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
        Excel::import(new PersistentIdImport, 'persistent_ids/teipir-uniwa-epresence-formated.csv');
        Excel::import(new PersistentIdImport, 'persistent_ids/teiath-uniwa-epresence-formated.csv');
    }
}
