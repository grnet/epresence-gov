<?php

namespace App\Console\Commands;

use App\Jobs\Users\ResendAnonymizeMailJob;
use Illuminate\Console\Command;

class ResendAnonymizeMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:anonymize_emails';

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
        $emails_array = array (
          //  'id' => 'email',
        );

        foreach($emails_array as $key=>$email){
            ResendAnonymizeMailJob::dispatch($email,$key);
        }

    }
}
