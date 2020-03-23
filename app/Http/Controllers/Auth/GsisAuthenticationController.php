<?php

namespace App\Http\Controllers\Auth;

use App\Email;
use App\Http\Controllers\Controller;
use App\Institution;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use League\OAuth2\Client\Provider\GenericProvider;
use App\Traits\interactsWithEmploymentApi;


class GsisAuthenticationController extends Controller
{
    use interactsWithEmploymentApi;
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
        if (Auth::check()) {
            return redirect('/');
        }
        return $this->redirectToLoginForm();
    }

    /** This method redirects the user to the gsis login page with an activation token in the session
     * @param Request $request
     * @param $activation_token
     * @return RedirectResponse|Redirector
     */
    public function register(Request $request, $activation_token)
    {
        if (Auth::check()) {
            return redirect('/');
        }
        $user = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
        if ($user) {
            session()->put("activation_token", $activation_token);
            return $this->redirectToLoginForm();
        } else {
            session()->flash('invalid-activation-token');
            return redirect()->route('not-authorized');
        }
    }

    /** This method redirects the user to the gsis login page
     * @return RedirectResponse|Redirector
     */
    public function redirectToLoginForm()
    {
        $authorizationUrl = $this->server->getAuthorizationUrl();
        $state = $this->server->getState();
        session()->put("oauth2state", $state);
        $this->logMessage("Info", "Setting state: ".$state." to session and redirecting to authorization url: ".$authorizationUrl);
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
        $this->logMessage("Info", "Callback request: ".json_encode($request->all()));

        if ($request->has('code') && $request->has('state') && session()->has("oauth2state") && session()->get("oauth2state") == $request->input('state')) {
            try {
                $accessToken = $this->server->getAccessToken('authorization_code', [
                    'code' => $request->get('code')
                ]);
                $client = new Client(['headers' => ['Authorization' => 'Bearer ' . $accessToken]]);
                $response = $client->request('GET', config('services.gsis.urlResourceOwnerDetails'));
                $this->logMessage("Info","User details api response: " . $response->getBody());
                $parsedResponse = simplexml_load_string($response->getBody());
                $userInfo = $parsedResponse->userinfo;
                $validationResult = $this->validateParameters($userInfo);
                //If parameters are not valid logout from gsis and redirect to not-authorized page of our app
                if (!$validationResult) {
                    return $this->logoutAsNotAuthorized();
                }
                // Tax id of the account
                $taxId = trim($userInfo['taxid']);

                //When first name is 'null' mean that this account is a not physical person's account and is not allowed to
                //use the app
                $firstName = trim($userInfo['firstname']);
                $lastName = trim($userInfo['lastname']);
                $user = User::where("tax_id", $taxId)->first();
                // User is already registered, checking if user is invited with a new email if so merge the two users, log him in and redirect him to homepage
                if ($user) {
                    return  $this->logUserIn($user);
                } else {
                    return $this->registerUser($firstName,$lastName,$taxId);
                }
            } catch (Exception $e) {
                $this->logMessage("Error","GsisAuthenticationController callback IdentityProviderException:" . $e->getMessage());
            }
        }else{
            if(!$request->has('code')) {
                $this->logMessage("Error", "Code parameter missing from request");
            }
            if(!$request->has('code')) {
                $this->logMessage("Error", "State parameter missing from request");
            }
            if(!session()->has("oauth2state")) {
                $this->logMessage("Error", "Missing oauth2state from session");
            }
            if(session()->get("oauth2state") !== $request->input('state')) {
                $this->logMessage("Error", "Session state and request state does not match:");
                $this->logMessage("Error", "Session state: ".session()->get("oauth2state"));
                $this->logMessage("Error", "Request state: ".$request->input('state'));
            }
        }
        return $this->logoutAsNotAuthorized();
    }

    /**
     * @return Factory|View
     */
    public function notAuthorized()
    {
        return view('errors.not-authorized');
    }


    /**
     * @return Factory|View
     */
    public function notLoggedIn()
    {
        return view('errors.not-logged-in');
    }


    /** Account tax id exists try to login
     * @param $user
     * @return RedirectResponse|Redirector
     */
    private function logUserIn($user){
        $currentRole = $user->roles()->first();
        $currentInstitution = $user->institutions()->first();
        $currentDepartment = $user->departments()->first();

        //Handle invited users for the first time
        if (session()->has("activation_token") && !empty(session()->get("activation_token"))) {
            $activation_token = session()->pull("activation_token");
            $userOfToken = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
            if ($userOfToken) {
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
                                'role' => $tokenUserRole->label,
                                'email' => $userOfToken->email
                            ];
                            $email = Email::where('name', 'invitationRoleChangeRequestNotCompleted')->first();
                            Mail::send('emails.auth.invitationRequestNotCompleted', $parameters, function ($message) use ($email, $recipients) {
                                $message->from($email->sender_email, config('mail.from.name'))
                                    ->to($recipients)
                                    ->replyTo(env('RETURN_PATH_MAIL'), env('MAIL_FROM_NAME'))
                                    ->returnPath(env('RETURN_PATH_MAIL'))
                                    ->subject($email->title);
                            });
                        }
                        $user->merge_user($userOfToken->id, true);
                    } else {
                        //Current user is end user && invited user is Administrator
                        $user->roles()->sync([$tokenUserRole->id]);
                        $user->institutions()->sync([$tokenUserInstitution->id]);
                        $user->departments()->sync([$tokenUserDepartment->id]);
                        if (!$user->confirmed) {
                            $invitedEmail = $userOfToken->email;
                            $user->merge_user($userOfToken->id, false);
                            $user->update(['email' => $invitedEmail, 'confirmed' => true]);
                        } else {
                            $user->merge_user($userOfToken->id, true);
                        }
                        $recipients[] = $user->email;
                        $parameters = ['role' => $tokenUserRole->label, 'institution' => $tokenUserInstitution->title, 'department' => $tokenUserDepartment->title];
                        $email = Email::where('name', 'accountDetailsUpdated')->first();
                        Mail::send('emails.accountDetailsUpdated', $parameters, function ($message) use ($email, $recipients) {
                            $message->from($email->sender_email, config('mail.from.name'))
                                ->to($recipients)
                                ->replyTo(env('RETURN_PATH_MAIL'), env('MAIL_FROM_NAME'))
                                ->returnPath(env('RETURN_PATH_MAIL'))
                                ->subject($email->title);
                        });
                    }
                } else {
                    if (empty($user->email_verified_at)) {
                        $invitedEmail = $userOfToken->email;
                        $user->merge_user($userOfToken->id, false);
                        $user->update(['email' => $invitedEmail, 'email_verified_at' => Carbon::now()]);
                    } else {
                        $user->merge_user($userOfToken->id, true);
                    }
                }

            }
        }

        //Login user
        Auth::login($user);

        if(!$user->confirmed){
            if(!in_array($currentRole->name,["InstitutionAdministrator", "DepartmentAdministrator"])){
                $responseObject = $this->getEmploymentInfo($user->tax_id);
                if($responseObject !== false){
                    $institutionToAttach = $this->getPrimaryInstitution($responseObject);
                    $departmentToAttach = $institutionToAttach->departments()->first();
                    $this->matchInstitutionsAndSetToSession($responseObject);
                    $user->institutions()->sync([$institutionToAttach->id]);
                    $user->departments()->sync([$departmentToAttach->id]);
                }
            }
            return redirect()->route('account-activation');
        }else{
            return redirect('/');
        }
    }


    /** User is not registered checking if there is a valid activation token in the session, if so
     match authenticated user with the invited account if not use the API to determine if this user is
     civil servant if he is create an unconfirmed account for him and redirect him to account activation to enter his email address
     after that user receives a confirmation email on the address he entered when the user clicks the activation link, the account gets confirmed and the user gets access to the platform  as an End User
     * @param $firstName
     * @param $lastName
     * @param $taxId
     * @return RedirectResponse|Redirector
     */
    private function registerUser($firstName,$lastName,$taxId){

        if (session()->has("activation_token") && !empty(session()->get("activation_token"))) {
            $activation_token = session()->pull("activation_token");
            $user = User::where("confirmed", false)->where("activation_token", $activation_token)->first();
            if ($user) {
                $user->create_join_urls();
                Auth::login($user);
                $tokenUserRole = $user->roles()->first();
                $responseObject = $this->getEmploymentInfo($taxId);
                if (in_array($tokenUserRole->name, ["InstitutionAdministrator", "DepartmentAdministrator"])) {
                    $isCivilServant = false;
                    if($responseObject !== false){
                        $isCivilServant  = true;
                    }
                    $user->update([
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'tax_id' => $taxId,
                        'confirmed' => true,
                        'activation_token' => null,
                        'email_verified_at'=>Carbon::now(),
                        'civil_servant'=>$isCivilServant
                    ]);
                    return redirect('/');
                } else {
                    if($responseObject !== false){
                        $user->update([
                             'firstname' => $firstName,
                             'lastname' => $lastName,
                             'tax_id' => $taxId,
                             'confirmed' => false,
                             'activation_token' => null,
                             'civil_servant'=>true,
                             'email_verified_at'=>Carbon::now()
                        ]);
                        $institutionToAttach = $this->getPrimaryInstitution($responseObject);
                        $departmentToAttach = $institutionToAttach->departments()->first();
                        $this->matchInstitutionsAndSetToSession($responseObject);
                        $user->institutions()->sync([$institutionToAttach->id]);
                        $user->departments()->sync([$departmentToAttach->id]);
                        return redirect()->route('account-activation');
                    }else{
                        $this->logMessage("Info","Could not find any employment info from api or api exception occurred! Not changing invited user's institution, confirming account and logging in");
                        $user->update(['firstname' => $firstName, 'lastname' => $lastName, 'tax_id' => $taxId, 'confirmed' => true, 'activation_token' => null,'civil_servant'=>false,'email_verified_at'=>Carbon::now()]);
                        return redirect('/');
                    }
                }
            } else {
                //Invalid activation token
                $this->logMessage("Error","Invalid activation token: " . $activation_token);
                session()->flash('invalid-activation-token');
                return $this->logoutAsNotAuthorized();
            }
        } else {
            $responseObject = $this->getEmploymentInfo($taxId);
            if($responseObject !== false){
                $this->logMessage("Info","Not invited account with tax_id: " . $taxId . " is a civil servant continuing...");
                $institutionToAttach = $this->getPrimaryInstitution($responseObject);
                $departmentToAttach = $institutionToAttach->departments()->first();
                $this->matchInstitutionsAndSetToSession($responseObject);
                $this->logMessage("Info","Creating new user with tax_id: " . $taxId . " First name: " . $firstName . " Last name: " . $lastName);
                $nextUserId = User::count() > 0 ? User::orderBy("id", "desc")->first()->id : 0;
                $user = User::create(
                    [
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'email' => 'not-retrieved-' . $nextUserId,
                        'tax_id' => $taxId,
                        'confirmed' => false,
                        'state' => 'sso',
                        'status' => 1,
                        'password' => bcrypt(str_random(9)),
                        'civil_servant'=>true,
                        'email_verified_at'=>null
                    ]);
                $user->institutions()->sync([$institutionToAttach->id]);
                $user->departments()->sync([$departmentToAttach->id]);

                // Assign role to user
                $user->assignRole('EndUser');
                Auth::login($user);
                return redirect()->route('account-activation');
            }  else {
                $this->logMessage("Info","Not invited account with tax_id: " . $taxId . " is not a civil servant or an employee api exception occurred aborting!");
                return $this->logoutAsNotAuthorized();
            }
        }
    }


    /**Logs user out of gsis and redirects back to not - authorized page of the app
     * @return RedirectResponse|Redirector
     */
    private function logoutAsNotAuthorized(){
        return redirect(config('services.gsis.urlLogout') . config('services.gsis.clientId') . '/?url=' . route('not-authorized'));
    }

    /**
     * @param $userInfo
     * @return bool|RedirectResponse|Redirector
     */
    private function validateParameters($userInfo)
    {
        $this->logMessage("Info","Validating parameters: " . json_encode($userInfo));
        //Check that all parameters are there and are not empty
        if (!isset($userInfo['taxid']) || $this->checkIfEmptyParameter($userInfo['taxid'])) {
            return false;
        }
        if (!isset($userInfo['firstname']) || $this->checkIfEmptyParameter($userInfo['firstname'])) {
            $this->logMessage("Info","Gsis account was found with empty firstname aborting since this account is not a physical person's account");
            return false;
        }
        if (!isset($userInfo['lastname']) || $this->checkIfEmptyParameter($userInfo['lastname'])) {
            return false;
        }
        return true;
    }


    /**
     * @param $parameter
     * @return bool
     */
    private function checkIfEmptyParameter($parameter)
    {
        return empty($parameter) || is_null($parameter) || $parameter == 'null';
    }


    /**Try to matches the primary institution with one in our db else returns default institution (id:1)
     * @param $responseObject
     * @return mixed
     */
    private function getPrimaryInstitution($responseObject)
    {
        $institutionToAttach = Institution::first();
        $employmentInfoCollection = collect($responseObject->data->employmentInfos);
        $primaryOrganization = $employmentInfoCollection->where("primary", true)->first();
        $organizationToMatch = $primaryOrganization ? $primaryOrganization : $employmentInfoCollection->first();
        $institutionMatched = Institution::where("ws_id", $organizationToMatch->organicOrganizationId)->first();
        if ($institutionMatched) {
            $institutionToAttach = $institutionMatched;
        }
        return $institutionToAttach;
    }
}
