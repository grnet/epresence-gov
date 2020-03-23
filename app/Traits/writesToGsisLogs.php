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
        Storage::disk('logs')->append('gsis.log',$type." ".$date.": (".$ipAddress.") ".$message);
    }
}
