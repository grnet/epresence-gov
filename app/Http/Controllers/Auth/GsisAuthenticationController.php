<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\GenericProvider;


class GsisAuthenticationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GsisAuthentication Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application via Gsis sso authentication system
    */

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function testLogin(){
        $GsisServer = new GenericProvider([
            'clientId'                => config('services.gsis.clientId'),    // The client ID assigned to you by the provider
            'clientSecret'            => config('services.gsis.clientSecret'),   // The client password assigned to you by the provider
            'redirectUri'             => config('services.gsis.redirectUri'),
            'urlAuthorize'            => config('services.gsis.urlAuthorize'),
            'urlAccessToken'          => config('services.gsis.urlAccessToken'),
            'urlResourceOwnerDetails' => config('services.gsis.urlResourceOwnerDetails'),
            'scopes'=>'read'
        ]);
        $authorizationUrl = $GsisServer->getAuthorizationUrl();
        session()->put("oauth2state",$GsisServer->getState());
        return redirect($authorizationUrl);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function callback(Request $request){

        if($request->has('code') && session()->has("oauth2state") && $request->has('state') && session()->get("oauth2state") == $request->get('state')){
            $GsisServer = new GenericProvider([
                'clientId'                => config('services.gsis.clientId'),    // The client ID assigned to you by the provider
                'clientSecret'            => config('services.gsis.clientSecret'),   // The client password assigned to you by the provider
                'redirectUri'             => config('services.gsis.redirectUri'),
                'urlAuthorize'            => config('services.gsis.urlAuthorize'),
                'urlAccessToken'          => config('services.gsis.urlAccessToken'),
                'urlResourceOwnerDetails' => config('services.gsis.urlResourceOwnerDetails'),
                'scopes'=>'read'
            ]);
            $accessToken = $GsisServer->getAccessToken('authorization_code', [
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
            return redirect('/');
        }
        Log::info("Gsis oAuth2 callback: ".json_encode($request->all()));
    }
}
