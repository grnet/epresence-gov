<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use App\Settings;

use Longman\IPTools\Ip;

class CheckForMaintenanceMode{

    protected $request;
    protected $app;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    public function handle(Request $request, Closure $next)
    {

        if($this->app->isDownForMaintenance()){

            $all_IPs = explode( ',', Settings::option('maintenance_excludeIPs'));
            $user_IP = $this->request->getClientIp();

            $passes = false;

            foreach($all_IPs as $ip_or_ip_range){

                if(Ip::match($user_IP,trim($ip_or_ip_range)))
                    $passes = true;

            }

            if(!$passes){
                abort(503);
            }
         }

//        $userLongIP = ip2long($this->request->getClientIp());
//
//        $pureIPs = array_where($all_IPs, function ($value) {
//            return !str_contains($value, '-');
//        });
//
//        $IPs = array();
//
//        foreach($pureIPs as $pureIP){
//            $IPs[] = ip2long(trim($pureIP, " "));
//        }
//
//        // IP ranges
//        $IP_ranges = array_where($all_IPs, function ($value) {
//            return str_contains($value, '-');
//        });
//
//        $longIPs_ranges = array();
//
//        foreach($IP_ranges as $IP_range){
//            $range = explode( '-', $IP_range);
//            $range_low = ip2long(trim($range[0], " "));
//            $range_high = ip2long(trim($range[1], " "));
//            $longIPs_ranges [] = range($range_low, $range_high);
//        }
//
//        $all_longIPs_ranges = array_flatten($longIPs_ranges);
//        $all_longIPs = array_merge($all_longIPs_ranges, $IPs);

//        if ($this->app->isDownForMaintenance() && !in_array($userLongIP, $all_longIPs))
//        {
//            abort(503);
//        }


        return $next($request);
    }

}