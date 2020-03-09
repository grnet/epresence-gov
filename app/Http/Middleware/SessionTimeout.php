<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\Store;
    
class SessionTimeout {
	protected $session;
	protected $timeout=3600;
	
	public function __construct(Store $session){
		$this->session=$session;
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


		if(!$this->session->has('lastActivityTime'))
			$this->session->put('lastActivityTime',time());
		elseif((time() - $this->session->get('lastActivityTime') > $this->getTimeOut()) && Auth::check()){
			$this->session->forget('lastActivityTime');
			Auth::logout();
			return redirect('auth/login')->withErrors([trans('controllers.userLogout')]);
		}
		$this->session->put('lastActivityTime',time());
		return $next($request);
	}
     
	protected function getTimeOut()
	{
		return (env('TIMEOUT')) ?: $this->timeout;
	}
}
