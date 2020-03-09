<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\User;
use Mail;


class NotifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails to the users who have duplicate persistent id so they can choose which one they want to keep as primary Argument {path} required USE: php artisan email:send \'/app/PIDS.txt\' ';

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
        //php artisan email:send {arg1}

        $path = $this->argument('path');
        $this->info($path);
        $user_mails = Array();
        $handle = fopen(storage_path($path), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.
                $perId=trim($line);

                $this->info($perId);

                if(!empty($perId) && $perId !== "" && isset($perId))
                $user_mails[$perId] = User::where('persistent_id',$perId)->pluck('email')->toArray();
            }
            fclose($handle);

            foreach($user_mails as $mails ){
                $parameters['emails'] = $mails;
                Mail::send('emails.askAboutMerge', $parameters, function ($message) use ($mails){
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                        ->cc($mails)
                        ->replyTo(env('SUPPORT_MAIL'),env('MAIL_FROM_NAME'))
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject('e:Presence: Merge notification');
                });
            }
        } else {
            $this->info('Error opening the file - check path');
        }
        $this->info('Mails Send :)');
    }
}
