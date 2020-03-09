<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            'clientId'                => config('services.gsis.clientId'),
            'clientSecret'            => config('services.gsis.clientSecret'),
            'redirectUri'             => config('services.gsis.redirectUri'),
            'urlAuthorize'            => config('services.gsis.urlAuthorize'),
            'urlAccessToken'          => config('services.gsis.urlAccessToken'),
            'urlResourceOwnerDetails' => config('services.gsis.urlResourceOwnerDetails'),
            'scopes'=>'read'
        ]);
    }

    /** This method redirects the user to the gsis login page
     * @return RedirectResponse|Redirector
     */
    public function login(){
        return $this->redirectToLoginForm();
    }

    /** This method redirects the user to the gsis login page with an activation token in the session
     * @param Request $request
     * @param $activation_token
     * @return RedirectResponse|Redirector
     */
    public function register(Request $request,$activation_token){
        $user = User::where("confirmed",false)->where("activation_token",$activation_token)->first();
        if($user){
            session()->put("activation_token",$activation_token);
            return $this->redirectToLoginForm();
        }else{
            return redirect('/')->with("error","invalid-token");
        }
    }

    /** This method redirects the user to the gsis login page
     * @return RedirectResponse|Redirector
     */
    public function redirectToLoginForm(){
        $authorizationUrl = $this->server->getAuthorizationUrl();
        session()->put("oauth2state",$this->server->getState());
        return redirect($authorizationUrl);
    }

    /** This method is responsible for sign a user in the application.
     *  Also match an invited user with the signed user that came from Gsis oAuth2 endpoint,
     *  this process acts as the registration process of the user
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function callback(Request $request){
        if($request->has('code') && $request->has('state') && session()->has("oauth2state")  && session()->get("oauth2state") == $request->input('state')){
            try {
                $accessToken = $this->server->getAccessToken('authorization_code', [
                    'code' => $request->get('code')
                ]);
                $client = new Client(['headers' => ['Authorization' => 'Bearer '.$accessToken]]);
                $response = $client->request('GET',config('services.gsis.urlResourceOwnerDetails'));
                $parsedResponse = simplexml_load_string($response->getBody());
                // Tax id of the user
                $taxId = trim($parsedResponse->userinfo['taxid']);
                $firstName = trim($parsedResponse->userinfo['firstname']);
                $lastName = trim($parsedResponse->userinfo['lastname']);
                $user = User::where("tax_id",$taxId)->first();

                // User is already registered, checking if user is invited with a new email if so merge the two users, log him in and redirect him to homepage
                if($user){
                    if(session()->has("activation_token") && !empty(session()->get("activation_token"))){
                        $activation_token = session()->pull("activation_token");
                        $userOfToken = User::where("confirmed",false)->where("activation_token",$activation_token)->first();
                        if($userOfToken){
                            $user->merge_user($userOfToken->id,true);
                        }
                    }
                    Auth::login($user);
                    return redirect('/');
                }else{
                // User is not registered checking is there is a valid activation token in the session, if so
                // match authenticated user with the invited account
                    if(session()->has("activation_token") && !empty(session()->get("activation_token"))){
                        $activation_token = session()->pull("activation_token");
                        $user = User::where("confirmed",false)->where("activation_token",$activation_token)->first();
                        if($user){
                            $user->update(['firstname'=>$firstName,'lastname'=>$lastName,'tax_id'=>$taxId,'confirmed'=>true,'activation_token'=>null]);
                            Auth::login($user);
                            return redirect('/');
                        }
                    }
                }
            } catch (IdentityProviderException $e) {
                Log::error("GsisAuthenticationController callback IdentityProviderException:".$e->getMessage());
            }
        }
        return redirect('/')->with("error","auth-error");
    }
}
