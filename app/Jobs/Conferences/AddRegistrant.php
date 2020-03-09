<?php

namespace App\Jobs\Conferences;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class AddRegistrant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $conference,$participant;
    public function __construct($conference,$participant)
    {
        $this->conference = $conference;
        $this->participant = $participant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $participant = $this->participant;
        $conference = $this->conference;
        $enabled = $participant->participantValues($conference->id)->enabled;
        $status = $enabled ? "approve" : "deny";
        $zoom_api_response = $conference->assignParticipant($participant->id, $status);
        $join_url = isset($zoom_api_response->join_url) ? $zoom_api_response->join_url : null;
        $registrant_id = isset($zoom_api_response->registrant_id) ? $zoom_api_response->registrant_id : null;
        DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->where('user_id', $participant->id)
            ->update(['join_url' => $join_url, 'registrant_id' => $registrant_id]);
    }
}
