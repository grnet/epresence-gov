<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App;
use Cookie;
use Session;
use Log;

class Language {

    public function __construct(Application $app, Redirector $redirector, Request $request) {
        $this->app = $app;
        $this->redirector = $redirector;
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		if(!$request->session()->has('locale')){

            $request->session()->put('locale','el');
		}

        $locale = $request->session()->get('locale');

		App::setLocale($locale);

        return $next($request);
    }

}