<?php

namespace Asikamiotis\JiraClient;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;


class JiraClient
{
    protected $client;
    protected $headers;

    public function __construct() {
        $this->client = new GuzzleHttpClient([
            'base_uri' => config('services.jira.endpoint')
        ]);
        $this->headers = [
            'auth' => [config('services.jira.username'),config('services.jira.password')]
        ];
    }

    //Test Api

    /**
     * @return bool|\Psr\Http\Message\ResponseInterface|null
     */
    public function test_api()
    {
        //Docs: https://docs.atlassian.com/software/jira/docs/api/REST/8.5.4/#api/2/issue-createIssue
        try {
            $response = $this->client->request('GET','issue/createmeta',$this->headers);
            Log::info($response->getBody());

        } catch (ClientException $e) {
            Log::error("Jira api exception: ".$e->getMessage());
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
            $response = false;
        }
        return $response;
    }


    /**
     * @param $createParameters
     */
    public function createIssue($createParameters){



    }



}
