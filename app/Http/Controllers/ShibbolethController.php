<?php

namespace App\Http\Controllers;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\User;
use App\Conference;
use App\ExtraEmail;
use App\Institution;
use App\Department;

class ShibbolethController extends Controller
{
    /**
     * Create the session, send the user away to the IDP
     * for authentication.
     */
    public function create()
    {
        if (config('shibboleth.emulate_idp') == true) {
            return Redirect::to(action('\\' . __CLASS__ . '@emulateLogin') . '?target=' . action('\\' . __CLASS__ . "@idpAuthorize"));
        } else {
            return Redirect::to('https://' . Request::server('SERVER_NAME') . ':' . Request::server('SERVER_PORT') . config('shibboleth.idp_login') . '?target=' . action('\\' . __CLASS__ . '@idpAuthorize'));
        }
    }

    /**
     * @param $token
     * @return mixed
     */
    public function activate($token)
    {
        if (config('shibboleth.emulate_idp') == true) {
            return Redirect::to(action('\\' . __CLASS__ . '@emulateLogin') . '?target=' . action('\\' . __CLASS__ . "@activateAccount"));
        } else {
            return Redirect::to('https://' . Request::server('SERVER_NAME') . ':' . Request::server('SERVER_PORT') . config('shibboleth.idp_login') . '?target=' . action('\\' . __CLASS__ . '@activateAccount', ['token' => $token]));
        }
    }

    /** This method is called when a user login through the IdP
     * Setup authorization based on returned server variables
     * from the IdP.
     */
    public function idpAuthorize()
    {
        $IdpVariables = $this->getIdpVariables();
        $user = User::where('persistent_id', $IdpVariables['persistentId'])->first();
        $institution = Institution::where('shibboleth_domain', $IdpVariables['organization'])->first();
        if (!isset($institution->id)) {
            return redirect('/auth/login')->withErrors([trans('controllers.wrongDeclaredInstitutionSso')]);
        }
        $emails = array();
        if (!empty($this->getServerVariable(config('shibboleth.idp_login_email')))) {
            foreach ($IdpVariables['sso_emails'] as $email) {
                if ($email != null && !empty($email) && $email != "")
                    $emails[] = $email;
            }
        }

        if (!isset($user->id)) {
            /**
             * Register
             */
            if (count($emails) > 0) {
                return view('new_sso_account',
                    [
                        'lastname' => $IdpVariables['last_name'],
                        'name' => $IdpVariables['first_name'],
                        'emails' => $emails,
                        'institution' => $institution,
                        'telephone' => $IdpVariables['telephone'],
                        'persistent_id' => $IdpVariables['persistentId']
                    ]);

            } else {
                return view('new_sso_confirm_email',
                    [
                        'lastname' => $IdpVariables['last_name'],
                        'name' => $IdpVariables['first_name'],
                        'institution' => $institution,
                        'telephone' => $IdpVariables['telephone'],
                        'persistent_id' => $IdpVariables['persistentId']
                    ]);
            }
        } else {
            /**
             * Login
             */
            if ($user->state == 'local') {
                return redirect('/auth/login')->withErrors([trans('controllers.userDefinedLocal')]);
            }
            if (empty($user->firstname) || empty($user->lastname)) {
                $user->update(['firstname' => $IdpVariables['first_name'], 'lastname' => $IdpVariables['last_name']]);
            }

            //Check institution

            if($user->institutions()->count() == 0 || $institution->id !== $user->institutions()->first()->id) {
                return redirect('/auth/login')->withErrors([trans('controllers.wrongDeclaredInstitutionSso')]);
            }

            $invited_email_already_in_list = false;
            foreach ($emails as $email) {
                if (trim(strtolower($email)) == trim(strtolower($user->email))) {
                    $invited_email_already_in_list = true;
                }
            }
            if (!$invited_email_already_in_list) {
                $emails[] = $user->email;
                Session::put('invited_email_key', count($emails) - 1);
            }
            Auth::loginUsingId($user->id);
            if (session()->has('redirect_to_account_to_apply') && session()->get('redirect_to_account_to_apply') == 1 && $user->confirmed) {
                session()->forget('redirect_to_account_to_apply');
                session()->put("pop_role_change", 1);
                return redirect('/account');
            }
            Session::put('emails', implode(';', $emails));
            return redirect('/account_activation');
        }
    }

    /** This method is called when a sso user clicks the account activation link in his email
     *  'epresence.gr/login/{activation_token}'
     * @param $token
     * @return RedirectResponse|Redirector
     */
    public function activateAccount($token)
    {
        $IdpVariables = $this->getIdpVariables();
        $user = User::where('activation_token', $token)->first();

        if (!isset($user->id)) {
            return redirect('/auth/login')->withErrors([trans('controllers.tokenMismatch')]);
        }

        $emails = [];
        foreach ($IdpVariables['sso_emails'] as $email) {
            if ($email != null && !empty($email) && $email != "")
                $emails[] = $email;
        }
        $invited_email_already_in_list = false;
        foreach ($emails as $email) {
            if (trim(strtolower($email)) == trim(strtolower($user->email))) {
                $invited_email_already_in_list = true;
            }
        }
        if (!$invited_email_already_in_list) {
            $emails[] = $user->email;
            Session::put('invited_email_key', count($emails) - 1);
        }

        $ExistingUser = User::where('persistent_id', $IdpVariables['persistentId'])->first();

        /**
         * Found a user with the same persistent id as the invited user -> merging existing user on the invited user
         */
        if (isset($ExistingUser->id)) {
            $ExistingUser->merge_user($user->id, true);
            Auth::loginUsingId($ExistingUser->id);
            return redirect('/account');
        } else {

            $user->update(['firstname' => $IdpVariables['first_name'], 'lastname' => $IdpVariables['last_name'], 'persistent_id' => $IdpVariables['persistentId']]);
            $institution = Institution::where('shibboleth_domain', $IdpVariables['organization'])->first();

            /**
             * If institution not matched from shibboleth
             */
            if (!isset($institution->id)) {
                return redirect('/auth/login')->withErrors([trans('controllers.wrongDeclaredInstitutionSso')]);
            } else {
                if ($user->institutions()->count() == 0) {
                    $user->institutions()->attach($institution->id);
                } else {
                    /** Assign institution to user **/
                    if ($institution->id != $user->institutions()->first()->id) {
                        if ($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator')) {
                            return redirect('/auth/login')->withErrors([trans('controllers.wrongDeclaredInstitutionSso')]);
                        } else {
                            $user->departments()->detach($user->departments()->pluck('id')->toArray());
                            $user->institutions()->sync($institution->id);
                        }
                    }
                }
            }
            Auth::loginUsingId($user->id);
            Session::put('emails', implode(';', $emails));
            return redirect('/account_activation');
        }
    }


    /** Returns the used idp variables in an array
     * @return array
     */
    private function getIdpVariables()
    {
        $variables = [];
        $variables['sso_emails'] = explode(';', $this->getServerVariable(config('shibboleth.idp_login_email')));
        $variables['first_name'] = $this->getServerVariable(config('shibboleth.idp_login_firstname'));
        $variables['last_name'] = $this->getServerVariable(config('shibboleth.idp_login_lastname'));
        $variables['telephone'] = $this->getServerVariable(config('shibboleth.idp_login_telephone'));
        $variables['organization'] = $this->getServerVariable(config('shibboleth.idp_login_home_organization'));
        $variables['persistentId'] = $_SERVER['REDIRECT_REMOTE_USER'];
        return $variables;
    }

    /**
     * Wrapper function for getting server variables.
     * Since Shibalike injects $_SERVER variables Laravel
     * doesn't pick them up. So depending on if we are
     * using the emulated IdP or a real one, we use the
     * appropriate function.
     * @param $variableName
     * @return mixed|null
     */
    private function getServerVariable($variableName)
    {
        if (config('shibboleth.emulate_idp') == true) {
            return isset($_SERVER[$variableName]) ?
                $_SERVER[$variableName] : null;
        } else {
            return (!empty(Request::server($variableName))) ?
                Request::server($variableName) :
                Request::server('REDIRECT_' . $variableName);
        }
    }
}
