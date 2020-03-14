<?php

namespace App\Http\Controllers\Auth;

use App\Email;
use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;


class GsisAuthenticationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Gsis Authentication Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for authenticating users for the application via Gsis oAuth2 system
    */

    // oAuth2 Server
    protected $server;

    public function __construct()
    {
        // Instantiated gsis oAuth2 server
        $this->server = new GenericProvider([
            'clientId' => config('services.gsis.clientId'),
            'clientSecret' => config('services.gsis.clientSecret'),
            'redirectUri' => config('services.gsis.redirectUri'),
            'urlAuthorize' => config('services.gsis.urlAuthorize'),
            'urlAccessToken' => config('services.gsis.urlAccessToken'),
            'urlResourceOwnerDetails' => config('services.gsis.urlResourceOwnerDetails'),
            'scopes' => 'read'
        ]);
    }

    /** This method redirects the user to the gsis login page
     * @return RedirectResponse|Redirector
     */
    public function login()
    {
        return $this->redirectToLoginForm();
    }

    /** This method redirects the user to the gsis login page with an activation token in the session
     * @param Request $request
     * @param $activation_token
     * @return RedirectResponse|Redirector
     */
    public function register(Request $request, $activation_token)
    {
        $user = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
        if ($user) {
            session()->put("activation_token", $activation_token);
            return $this->redirectToLoginForm();
        } else {
            return redirect('/')->with("error", "invalid-token");
        }
    }

    /** This method redirects the user to the gsis login page
     * @return RedirectResponse|Redirector
     */
    public function redirectToLoginForm()
    {
        $authorizationUrl = $this->server->getAuthorizationUrl();
        session()->put("oauth2state", $this->server->getState());
        return redirect($authorizationUrl);
    }

    /** This method is responsible for sign a user in the application.
     *  Also match an invited user with the signed user that came from Gsis oAuth2 endpoint,
     *  this process acts as the registration process of the user
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function callback(Request $request)
    {
        if ($request->has('code') && $request->has('state') && session()->has("oauth2state") && session()->get("oauth2state") == $request->input('state')) {
            try {
                $accessToken = $this->server->getAccessToken('authorization_code', [
                    'code' => $request->get('code')
                ]);
                $client = new Client(['headers' => ['Authorization' => 'Bearer ' . $accessToken]]);
                $response = $client->request('GET', config('services.gsis.urlResourceOwnerDetails'));
                $parsedResponse = simplexml_load_string($response->getBody());
                // Tax id of the user
                $taxId = trim($parsedResponse->userinfo['taxid']);
                $firstName = trim($parsedResponse->userinfo['firstname']);
                $lastName = trim($parsedResponse->userinfo['lastname']);
                $user = User::where("tax_id", $taxId)->first();
                // User is already registered, checking if user is invited with a new email if so merge the two users, log him in and redirect him to homepage
                if ($user) {
                    if (session()->has("activation_token") && !empty(session()->get("activation_token"))) {
                        $activation_token = session()->pull("activation_token");
                        $userOfToken = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
                        if ($userOfToken) {
                            $currentRole = $user->roles()->first();
                            $currentInstitution = $user->institutions()->first();
                            $currentDepartment = $user->departments()->first();
                            $tokenUserRole = $userOfToken->roles()->first();
                            $tokenUserInstitution = $userOfToken->institutions()->first();
                            $tokenUserDepartment = $userOfToken->departments()->first();
                            if (in_array($tokenUserRole->name, ["InstitutionAdministrator", "DepartmentAdministrator"])) {
                                //Invited user is Administrator
                                if (in_array($currentRole->name, ["InstitutionAdministrator", "DepartmentAdministrator"])) {
                                    //Current user is Administrator && invited user is Administrator
                                    $isSame = $currentRole->id == $tokenUserRole->id &&
                                        $currentInstitution->id == $tokenUserInstitution->id &&
                                        $currentDepartment->id == $tokenUserDepartment->id;
                                    if (!$isSame) {
                                        //Notify invited user creator & support that the request could not be completed
                                        $recipients[] = $userOfToken->creator->email;
                                        $recipients[] = env('RETURN_PATH_MAIL');
                                        $parameters = [
                                            'invitation_email_address' => $userOfToken->email,
                                            'requested_role_name' => $tokenUserRole->name,
                                            'requested_institution_name' => $tokenUserInstitution->title,
                                            'requested_department_name' => $tokenUserDepartment->title,
                                            'email_address' => $user->email,
                                            'role_name' => $currentRole->name,
                                            'institution_name' => $currentInstitution->title,
                                            'department_name' => $currentDepartment->title,
                                        ];
                                        $email = Email::where('name', 'invitationRoleChangeRequestNotCompleted')->first();
                                        Mail::send('emails.auth.invitationRequestNotCompleted', $parameters, function ($message) use ($email, $recipients) {
                                            $message->from($email->sender_email,config('mail.from.name'))
                                                ->to($recipients)
                                                ->replyTo(env('RETURN_PATH_MAIL'), env('MAIL_FROM_NAME'))
                                                ->returnPath(env('RETURN_PATH_MAIL'))
                                                ->subject($email->title);
                                        });
                                    }

                                } else {
                                    //Current user is end user && invited user is Administrator
                                    $user->roles()->sync([$tokenUserRole->id]);
                                    $user->institutions()->sync([$tokenUserInstitution->id]);
                                    $user->departments()->sync([$tokenUserDepartment->id]);
                                    $recipients[] = $user->email;
                                    $parameters = ['new_role' => $tokenUserRole->label, 'institution' => $tokenUserInstitution->title, 'department' => $tokenUserDepartment->title];
                                    $email = Email::where('name', 'invitationRoleChangeRequestNotCompleted')->first();
                                    Mail::send('emails.accountDetailsUpdated', $parameters, function ($message) use ($email, $recipients) {
                                        $message->from($email->sender_email,config('mail.from.name'))
                                            ->to($recipients)
                                            ->replyTo(env('RETURN_PATH_MAIL'), env('MAIL_FROM_NAME'))
                                            ->returnPath(env('RETURN_PATH_MAIL'))
                                            ->subject($email->title);
                                    });
                                }
                            }
                            $user->merge_user($userOfToken->id, true);
                        }
                    }
                    Auth::login($user);
                    return redirect('/');
                } else {
                    // User is not registered checking if there is a valid activation token in the session, if so
                    // match authenticated user with the invited account if not use the API to determine if this user is
                    // civil servant if he is create an unconfirmed account for him and redirect him to account activation to enter his email address
                    // after that user receives a confirmation email on the address he entered when the user clicks the activation link, the account gets confirmed and the user gets access to the platform
                    // as an End User
                    if (session()->has("activation_token") && !empty(session()->get("activation_token"))) {
                        $activation_token = session()->pull("activation_token");
                        $user = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
                        if ($user) {
                            $user->update(['firstname' => $firstName, 'lastname' => $lastName, 'tax_id' => $taxId, 'confirmed' => true, 'activation_token' => null]);
                            $user->create_join_urls();
                            Auth::login($user);
                            return redirect('/');
                        }
                    }else{
                      // toDO Call real webservice here







                    }
                }
            } catch
            (IdentityProviderException $e) {
                Log::error("GsisAuthenticationController callback IdentityProviderException:" . $e->getMessage());
            }
        }
        return redirect(config('services.gsis.urlLogout').config('services.gsis.clientId').'/?url='.route('not-authorized'));
    }


    /**
     * @return Factory|View
     */
    public function notAuthorized(){
        return view('errors.not-authorized');
    }


    /**
     * @return Factory|View
     */
    public function notLoggedIn(){
        return view('errors.not-logged-in');
    }
}
