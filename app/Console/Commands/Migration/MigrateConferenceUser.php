<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateConferenceUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:conference-user';

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
        Log::info("Migrating old conference_user rows...");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();

        DB::connection('result_epresence')
            ->table('conference_user')
            ->truncate();

        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $old_conference_user_rows = DB::connection('vidyo')
            ->table('conference_user')
            ->join('users', 'users.id', '=', 'conference_user.user_id')
            ->join('conferences','conferences.id','=','conference_user.conference_id')
            ->where('conferences.end','<','2019-09-30 14:30:00')
            ->where('users.deleted', 0)
            ->where('users.confirmed', 1)
            ->select(['conference_user.*'])
            ->get();

        Log::info("Found (vidyo)" . count($old_conference_user_rows) . " rows!");

        $this->iterate_conference_user_rows($old_conference_user_rows, 'old');

        $new_conference_user_rows = DB::connection('zoom')
            ->table('conference_user')
            ->join('users', 'users.id', '=', 'conference_user.user_id')
            ->where('users.deleted', 0)
            ->where('users.confirmed', 1)
            ->select(['conference_user.*'])
            ->get();

        Log::info("Found (zoom)" . count($new_conference_user_rows) . " rows!");

        $this->iterate_conference_user_rows($new_conference_user_rows, 'new');

        Log::info("Done migration conference_user rows");
    }


    private function iterate_conference_user_rows($rows, $type)
    {

        foreach ($rows as $row) {

            $conference_relation_row = DB::connection('helper')->table('conference_helper')->where($type . "_conference_id", $row->conference_id)->first();
            $user_relation_row = DB::connection('helper')->table('user_helper')->where($type . "_user_id", $row->user_id)->first();

            if (isset($conference_relation_row->id) && isset($user_relation_row->id)) {

                $row->user_id = $user_relation_row->final_user_id;
                $row->conference_id = $conference_relation_row->final_conference_id;

                unset($row->active);
                unset($row->participantID);

                if (DB::connection('result_epresence')->table('users')->where("id", $row->user_id)->exists() &&
                    DB::connection('result_epresence')->table('conferences')->where("id", $row->conference_id)->exists()
                ) {

                    DB::connection('result_epresence')
                        ->table('conference_user')
                        ->insert((array)$row);
                } else {
                    Log::info("Foreign model missing (user_id or conference_id)! Can't migrate this row:");
                    Log::info(json_encode($row));
                }

            } else {
//                Log::info("Have not found a suitable relation for the user or the conference! Can't migrate this row:");
//                Log::info(json_encode($row));

                if (!isset($conference_relation_row->id)) {
                    Log::info("Could not find a conference relation for conference_id: " . $row->conference_id);
                }

                if (!isset($user_relation_row->id)) {
                    $connection = $type === "old" ? "vidyo" : "zoom";
                    $user = DB::connection($connection)->table('users')->where("id", $row->user_id)->first();
                    if ($user->confirmed == 1 && $user->deleted = 0) {
                        Log::info("Could not find a user relation for user_id: " . $row->user_id);
                    }
                }
            }
        }
    }
}
