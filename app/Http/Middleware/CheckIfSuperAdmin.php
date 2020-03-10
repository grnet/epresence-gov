<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (backpack_auth()->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response(trans('backpack::base.unauthorized'), 401);
            } else {
                return redirect()->guest('/');
            }
        }else{

            if(!backpack_auth()->user()->hasRole('SuperAdmin')){

                return redirect('/');
            }

        }

        return $next($request);
    }
}
