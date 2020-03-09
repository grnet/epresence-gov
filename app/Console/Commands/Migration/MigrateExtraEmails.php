<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateExtraEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:extra-emails';

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
        Log::info("Migrating Extra emails...");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('users_extra_emails')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $old_extra_emails = DB::connection('vidyo')
            ->table('users_extra_emails')
            ->selectRaw('users_extra_emails.*')
            ->where("users_extra_emails.confirmed", 1)
            ->join("users","users.id","=","users_extra_emails.user_id")
            ->where("users.deleted",0)
            ->where("users.confirmed",1)
            ->get();

        $this->iterate_emails($old_extra_emails, 'old');


        $new_extra_emails = DB::connection('zoom')
            ->table('users_extra_emails')
            ->selectRaw('users_extra_emails.*')
            ->where("users_extra_emails.confirmed", true)
            ->join("users","users.id","=","users_extra_emails.user_id")
            ->where("users.deleted",0)
            ->get();

        $this->iterate_emails($new_extra_emails, 'new');


        Log::info("Migrated Extra emails!");
    }

    private function iterate_emails($emails, $type)
    {
        foreach ($emails as $extra_email) {
            $user_relation = DB::connection('helper')->table('user_helper')->where($type . "_user_id", $extra_email->user_id)->first();
            if (isset($user_relation->id)) {
                if (!DB::connection('result_epresence')->table('users')->where("email", $extra_email->email)->exists()) {
                    if (!DB::connection('result_epresence')->table('users_extra_emails')->where("email", $extra_email->email)->exists()) {
                        $extra_email->user_id = $user_relation->final_user_id;
                        unset($extra_email->id);
                        DB::connection('result_epresence')->table('users_extra_emails')->insert((array)$extra_email);
                    } else {
                       // Log::info(ucfirst($type) . " extra email already found in results db (extra emails):");
                       // Log::info(json_encode($extra_email));
                    }
                } else {
                    Log::info(ucfirst($type) . " extra email already found in results db (users):");
                    Log::info(json_encode($extra_email));
                }
            } else {
                Log::info(ucfirst($type) . " user relation not found for user_id:" . $extra_email->user_id);
                Log::info(json_encode($extra_email));
                if (DB::connection('result_epresence')->table('users_extra_emails')->where("email", $extra_email->email)->exists()) {
                    Log::info(ucfirst($type) . " though email was found in db");
                } else {
                    Log::info(ucfirst($type) . " though email was not found in db");
                }
            }
        }
    }
}


