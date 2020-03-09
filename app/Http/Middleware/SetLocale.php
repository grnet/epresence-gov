<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

use App;
use Session;

class SetLocale {

    public function __construct() {
        
    }
	
	protected $languages = ['en','el'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       if (!empty(Session::get('locale'))) {
            $locale = Session::get('locale', Config::get('app.locale'));
        } else {
            $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

            if ($locale != 'el' && $locale != 'en') {
                $locale = 'en';
            }
        }

        App::setLocale($locale);

        return $next($request);
    }

}