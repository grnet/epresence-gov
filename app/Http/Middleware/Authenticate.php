<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
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
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }
		
		if ($request->user()->confirmed == 0 && !str_contains( Request::path(), 'users/'.$request->user()->id)){
			return redirect('account_activation');
		}elseif($request->user()->status == 0){
			$this->auth->logout();
			return redirect('auth/login')->withErrors(trans('controllers.userEmailDeactivated'));
		}



        return $next($request);
    }
}
