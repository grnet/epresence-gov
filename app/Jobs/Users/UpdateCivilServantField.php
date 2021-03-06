<?php

namespace App\Jobs\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\interactsWithEmploymentApi;
class UpdateCivilServantField implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,interactsWithEmploymentApi;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $responseObject = $this->getEmploymentInfo($this->user->tax_id);
        if($responseObject !== false){
            $this->user->update(['civil_servant'=>true]);
        }
    }
}
