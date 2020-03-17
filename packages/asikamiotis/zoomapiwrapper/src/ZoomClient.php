<?php

namespace Asikamiotis\ZoomApiWrapper;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;


class ZoomClient
{
    protected $client;
    protected $headers;

    public function __construct() {

        $this->client = new GuzzleHttpClient([
            'base_uri' => config('services.zoom.base_uri')
        ]);

        $expired_at_timestamp = Carbon::now()->addMinutes(50)->timestamp;
        $secret = config('services.zoom.api_secret');

        $token = array(
            "iss" => config('services.zoom.api_key'),
            "exp" => $expired_at_timestamp,
        );

        $jwt = JWT::encode($token, $secret);

        $this->headers = [
            'Authorization' => 'Bearer ' . $jwt,
            'Accept' => 'application/json',
            'content-type' => 'application/json'
        ];
    }


    //Meetings


    public function create_meeting($parameters,$zoom_user_id)
    {

        //Docs: https://zoom.github.io/api/#create-a-meeting
        //Docs: https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingcreate

        try {

            //Make the api call to zoom

            $api_response = $this->client->request('POST', '/v2/users/' . $zoom_user_id. '/meetings', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($api_response->getBody());

        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }

        return $response;
    }



    public function update_meeting($parameters,$zoom_meeting_id){

        //Docs https://zoom.github.io/api/#update-a-meeting

        try {
            //Make the api call to zoom

            $response = $this->client->request('PATCH', '/v2/meetings/' . $zoom_meeting_id, [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }

        return $response;
    }



    public function update_meeting_status($parameters,$zoom_meeting_id){

        //Docs https://zoom.github.io/api/#update-a-meetings-status

        try {
            //Make the api call to zoom

            $response = $this->client->request('PUT', '/v2/meetings/' . $zoom_meeting_id . '/status', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }

        return $response;
    }


    public function delete_meeting($zoom_meeting_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingdelete

        $parameters = [
            "occurrence_id"=>""
        ];

        try {
            //Make the api call to zoom

            $response = $this->client->request('DELETE', '/v2/meetings/' . $zoom_meeting_id, [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());

        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }

        return $response;
    }


    /**Participants
     * @param $zoom_meeting_id
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|null
     */
    public function get_participants($zoom_meeting_id)
    {
        Log::info("Zoom client GET  request: /v2/metrics/meetings/" . $zoom_meeting_id . "/participants");
        try {
            //Make the api call to zoom

            $get_participants_response = $this->client->request('GET', '/v2/metrics/meetings/' . $zoom_meeting_id . '/participants', [
                'headers' => $this->headers
            ]);

            $get_participants_response = json_decode($get_participants_response->getBody());

        } catch (ClientException $e) {
            $get_participants_response = $e->getResponse();
            $responseBodyAsString = $get_participants_response->getBody()->getContents();
            Log::error($e->getMessage());
            Log::error($responseBodyAsString);
            $get_participants_response = false;
        }

        return $get_participants_response;
    }


    //Registrants

    /**
     * @param $zoom_meeting_id
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get_registrants($zoom_meeting_id)
    {
        try {
            //Make the api call to zoom
            $get_registrants_response = $this->client->request('GET', '/v2/meetings/' . $zoom_meeting_id . '/registrants?page_size=100', [
                'headers' => $this->headers
            ]);
            $get_registrants_response = json_decode($get_registrants_response->getBody());
        } catch (ClientException $e) {
            $get_registrants_response = $e->getResponse();
            $responseBodyAsString = $get_registrants_response->getBody()->getContents();
            Log::error($responseBodyAsString);
            $get_registrants_response = false;
        }

        return $get_registrants_response;
    }


    public function add_participant($add_registrant_parameters,$zoom_meeting_id)
    {
        //Docs https://zoom.github.io/api/#add-a-meeting-registrant

        try {
            //Make the api call to zoom

            $add_participant_response = $this->client->request('POST', '/v2/meetings/' . $zoom_meeting_id. '/registrants', [
                'headers' => $this->headers,
                'json' => $add_registrant_parameters
            ]);

            $add_participant_response = json_decode($add_participant_response->getBody());

        } catch (ClientException $e) {

            $add_participant_response = $e->getResponse();
            $responseBodyAsString = $add_participant_response->getBody()->getContents();

            $add_participant_response = false;

            Log::error("Add participant api call error:");
            Log::error($responseBodyAsString);
        }


        return $add_participant_response;
    }


    public function update_participant_status($parameters,$zoom_meeting_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingregistrantstatus

        try {
            //Make the api call to zoom

            $update_registrant_status_response = $this->client->request('PUT', '/v2/meetings/' . $zoom_meeting_id . '/registrants/status', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $update_registrant_status_response = json_decode($update_registrant_status_response->getBody());


        } catch (ClientException $e) {

            $update_registrant_status_response = $e->getResponse();
            $update_registrant_status_response = $update_registrant_status_response->getBody()->getContents();

            Log::error($update_registrant_status_response);

            $update_registrant_status_response = false;
        }

        return $update_registrant_status_response;
    }



    //Zoom users

    public function get_users($parameters){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/users/users

        try{
            //Make the api call to zoom

            $request_url = '/v2/users';

            if(count($parameters)>0){
                $request_url .= "?".http_build_query($parameters);
            }

            $response = $this->client->request('GET', $request_url, [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        }
        catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }


        return $response;
    }


    public function create_user($parameters){

        //Docs https://zoom.github.io/api/#create-a-user

        try{
            //Make the api call to zoom

            $response = $this->client->request('POST', '/v2/users', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        }
        catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }

        return $response;
    }


    public function update_user_settings($parameters,$user_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/users/usersettingsupdate

        try{
            //Make the api call to zoom

            $response = $this->client->request('PATCH', '/v2/users/'.$user_id.'/settings', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        }
        catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }


        return $response;
    }



    public function delete_user($parameters,$user_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/users/userdelete

        try{
            //Make the api call to zoom

            $response = $this->client->request('DELETE', '/v2/users/'.$user_id, [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $response = json_decode($response->getBody());
        }
        catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $response = false;
        }


        return $response;
    }


    public function delete_user_from_group($parameters,$user_id,$group_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/groups/groupmembersdelete

        try {

            //Make the api call to zoom

            $remove_user_from_group_response = $this->client->request('DELETE', '/v2/groups/' . $group_id. '/members/'.$user_id, [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $remove_user_from_group_response = json_decode($remove_user_from_group_response->getBody());

        } catch (ClientException $e) {

            $remove_user_from_group_response = $e->getResponse();
            $responseBodyAsString = $remove_user_from_group_response->getBody()->getContents();

            Log::error($responseBodyAsString);

            $remove_user_from_group_response = false;
        }

        return $remove_user_from_group_response;
    }


    public function add_user_to_group($parameters,$group_id){

        //Docs https://marketplace.zoom.us/docs/api-reference/zoom-api/groups/groupmemberscreate

        try {

            //Make the api call to zoom

            $add_user_to_group_response = $this->client->request('POST', '/v2/groups/' . $group_id . '/members/', [
                'headers' => $this->headers,
                'json' => $parameters
            ]);

            $add_user_to_group_response = json_decode($add_user_to_group_response->getBody());

        } catch (ClientException $e) {

            $add_user_to_group_response = $e->getResponse();
            $responseBodyAsString = $add_user_to_group_response->getBody()->getContents();

            Log::error($responseBodyAsString);
            Log::error("Soap error: Could not add named user to blocking group.");

            $add_user_to_group_response = false;
        }

        return $add_user_to_group_response;
    }

}
