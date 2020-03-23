<?php namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

trait writesToGsisLogs
{
    /**
     * @param $type
     * @param $message
     */
    private function logMessage($type,$message){
        $date = Carbon::now();
        $ipAddress = request()->ip();
        $host = request()->getHttpHost();
        Storage::disk('logs')->append('gsis-'.Carbon::today()->toDateString().'.log',"$type $date: ($ipAddress $host) $message");
    }
}
