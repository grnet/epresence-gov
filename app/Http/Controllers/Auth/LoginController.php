<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    /**
     * @param Request $request
     * @return RedirectResponse|void
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            //Extra validations added
            //to Handle sso and disabled users
            $user = User::where('email', $request->email)->first();
            if($user->status == 0){
                $errorMessage = trans('controllers.userEmailDeactivated');
            }elseif(!$user->hasRole("SuperAdmin")){
                $errorMessage = trans('controllers.userMustLoginSso');
            }

            if(isset($errorMessage)){
                Auth::logout();
                throw ValidationException::withMessages([$errorMessage]);
            }else{
                return $this->sendLoginResponse($request);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param Request $request
     * @return void
     * @throws ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [Lang::get('auth.throttle', ['seconds' => $seconds])],
        ])->status(423);
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }


    /**
     * @param Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
                $this->username() => [trans('controllers.wrongPassword')],
            ]);
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }


    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $this->guard()->logout();
        $request->session()->invalidate();
        if($user->state=="sso")
        return redirect(config('services.gsis.urlLogout').config('services.gsis.clientId').'/?url='.url('/'));
        else
        return redirect(url('/'));
    }
}
