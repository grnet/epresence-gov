<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckIfConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && !in_array(Route::currentRouteName(),['account-activation','terms','privacy-policy','contact','cookies','access','support','logout','confirm-sso-email','accept-terms-ajax'])) {
            if(!request()->user()->confirmed){
                return redirect()->route('account-activation');
            }
        }
        return $next($request);
    }
}
