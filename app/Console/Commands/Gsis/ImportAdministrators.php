<?php

namespace App\Console\Commands\Gsis;

use Illuminate\Console\Command;
use App\Traits\parsesCsv;

class ImportAdministrators extends Command
{
    use parsesCsv;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsis:import_administrators {storage_file_path}';

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
        $storage_file_path = $this->argument('storage_file_path');
        $file_path = storage_path($storage_file_path);
        $iterator = $this->readFile($file_path);
        $counter = 0;
        foreach ($iterator as $iteration) {
            if ($counter > 0 && !empty($iteration)) {
             $csv_row = str_getcsv($iteration, ",", '"');
           //     $afm = $csv_row[0];
           //     $first_name = $csv_row[1];
           //     $last_name = $csv_row[2];
           //     $email = $csv_row[4];
            }
        }

    }
}
