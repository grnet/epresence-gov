<?php

use App\Department;
use App\Institution;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class zoomPresenceSqlSeeder extends Seeder
{
    public function run()
    {
        //Emails seeder from sql file

        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/emails.sql')));

        //Roles seeder from sql file

        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/roles.sql')));

        //Permissions seeder from sql file

        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/permissions.sql')));

        //Permissions - Roles pivot table seeder from sql file

        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/permission_role.sql')));

        //Settings

        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/settings.sql')));
    }
}
