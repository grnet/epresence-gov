<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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

    protected $server;

    public function __construct()
    {
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

    /**
     * @return RedirectResponse|Redirector
     */
    public function login(){
        $authorizationUrl = $this->server->getAuthorizationUrl();
        session()->put("oauth2state",$this->server->getState());
        return redirect($authorizationUrl);
    }


    /**
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function callback(Request $request){
        Log::info("Gsis oAuth2 callback: ".json_encode($request->all()));
        if($request->has('code') && session()->has("oauth2state") && $request->has('state') && session()->get("oauth2state") == $request->get('state')){
            try {
                $accessToken = $this->server->getAccessToken('authorization_code', [
                    'code' => $request->get('code')
                ]);
                $client = new Client(['headers' => ['Authorization' => 'Bearer '.$accessToken]]);
                $response = $client->request('GET',config('services.gsis.urlResourceOwnerDetails'));
                $parsedResponse = simplexml_load_string($response->getBody());
                $taxId = trim($parsedResponse->userinfo['taxid']);
                $firstName = trim($parsedResponse->userinfo['firstname']);
                $lastName = trim($parsedResponse->userinfo['lastname']);
                Log::info("Tax id:".$taxId);
                Log::info("First Name:".$firstName);
                Log::info("Last Name:".$lastName);
            } catch (IdentityProviderException $e) {
                Log::error("GsisAuthenticationController callback IdentityProviderException:".$e->getMessage());
            }
            return redirect('/');
        }
    }
}
