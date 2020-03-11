<?php

namespace App\Console\Commands\Installation\Models;

use App\Department;
use App\Institution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Traits\parsesCsv;

class Institutions extends Command
{
    use parsesCsv;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:institutions';

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
        //Seed initial institutions
        Schema::disableForeignKeyConstraints();
        Db::table('institutions')->truncate();
        Db::table('departments')->truncate();
        Institution::create(["title" => "Δημόσια Διοίκηση"]);
        Department::create(["title" => "Γενικό Τμήμα", "institution_id" => 1]);
        $iterator = $this->readFile(storage_path('app/institutions_list.csv'));
        $counter = 0;
        foreach ($iterator as $iteration) {
            if ($counter > 0 && !empty($iteration)) {
                $csv_row = str_getcsv($iteration, ",", '"');
                $ws_id = $csv_row[0];
                $title = $csv_row[1];
                $api_code = $csv_row[2];
                $category = $csv_row[3];
                $type = $csv_row[4];
                $institutionCreated = Institution::create(["title" => $title, 'ws_id' => $ws_id, 'api_code' => $api_code, 'category' => $category, 'type' => $type]);
                Department::create(["title" => "Γενικό Τμήμα", "institution_id" => $institutionCreated->id]);
            }
            $counter++;
        }
        Schema::enableForeignKeyConstraints();
    }
}
