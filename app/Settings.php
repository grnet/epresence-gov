<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTime;
use Artisan;
use App\Conference;
use App\User;
use App\Email;
use Validator;
use Mail;
use URL;
use Log;

class Settings extends Model
{
    protected $fillable = [
		'title',
		'category',
		'option'
	];
	
	public static function option($title)
	{
		$option = Settings::where('title', $title)->value('option');
		return $option;
	}
	
	public static function getDate($date)
	{
		if(is_null($date)){
			return '';
		}else{
			return Carbon::createFromTimestamp($date)->format('d-m-Y');
		}
	}
	
	public static function getTime($date)
	{
		if(is_null($date)){
			return '';
		}else{
			return Carbon::createFromTimestamp($date)->format('H:i');
		}
	}

	public static function update_application_settings($settings)
    {
        // if (Gate::denies('create_conference')) {
            // abort(403);
        // }
		if($settings['maintenance_start_date'] == null || $settings['maintenance_start_time'] == null){
			$settings['maintenance_start'] = null;
		}else{
			$startDateTime = $settings['maintenance_start_date'].' '.$settings['maintenance_start_time'].':00';
			$newStartDateTime = new DateTime(Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime)->toDateTimeString());
			$settings['maintenance_start'] = $newStartDateTime->getTimestamp();
		}
		
		if($settings['maintenance_end_date'] == null || $settings['maintenance_end_time'] == null){
			$settings['maintenance_end'] = null;
		}else{
			$endDateTime = $settings['maintenance_end_date'].' '.$settings['maintenance_end_time'].':00';
			$newEndDateTime = new DateTime(Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime)->toDateTimeString());
			$settings['maintenance_end'] = $newEndDateTime->getTimestamp();
		}
		
		if(!isset($settings['maintenance_mode'])){
			$settings['maintenance_mode'] = '0';
			Artisan::call('up');
		}else{
			$settings['maintenance_mode'] = '1';
			// Service down
			Artisan::call('down');
		}
		
		foreach($settings as $key=>$value){
			if(strstr($key, '_', true) == 'maintenance'){
				Settings::where('title', $key)->update(['option' => $value]);
			}
		}
	}
	
	public static function notifyParticipants (){
		$maintenance_startDate = Carbon::createFromTimestamp(Settings::option('maintenance_start'))->toDateTimeString();
		$maintenance_endDate = Carbon::createFromTimestamp(Settings::option('maintenance_end'))->toDateTimeString();
		
		$conferences = Conference::whereBetween('start', [$maintenance_startDate, $maintenance_endDate])->get();
		
		$email = Email::where('name', 'conferenceMaintenanceMode')->first();
		
		foreach($conferences as $conference){
			
			$moderator = User::findOrFail($conference->user_id);
			$participants = $conference->participants;
			$start_date = Carbon::parse($conference->start)->format('d-m-Y H:i');
			
			$parameters = array('conference_url' => URL::to("/conferences/".$conference->id."/edit"), 'contact_url' =>URL::to("support"), 'start_date' => $start_date, 'title' => $conference->title);
			
			foreach($participants as $participant){

			    if($participant->status == 1){

                    Mail::send('emails.maintenanceNotification_participant', $parameters, function ($message) use ($participant, $email, $moderator){
                        $message->from($email->sender_email, 'e:Presence')
                            ->to($participant->email)
                            ->replyTo($moderator->email, $moderator->firstname.' '.$moderator->lastname)
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });

                }
			

				
			}

			if($moderator->status == 1){

                Mail::send('emails.maintenanceNotification_moderator', $parameters, function ($message) use ($moderator, $email){
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($moderator->email)
                        ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }
        }
		
		return 'OK';
	}
}
