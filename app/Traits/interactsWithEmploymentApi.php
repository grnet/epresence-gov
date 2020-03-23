<?php namespace App\Traits;

use App\Institution;
use GuzzleHttp\Client;

trait interactsWithEmploymentApi
{
    use writesToGsisLogs;

    /**
     * @param $taxId
     * @return bool|mixed
     */
    public function getEmploymentInfo($taxId)
    {
        try {
            $client = new Client();
            $response = $client->get(config('services.gov-employees-api.endpoint') . $taxId, [
                'auth' => [
                    config('services.gov-employees-api.username'), config('services.gov-employees-api.password')
                ]
            ]);
            $this->logMessage("Info", "Employee api response: " . $response->getBody());
            $responseObject = json_decode($response->getBody());
            //Check if user is civil servant
            if (!isset($responseObject->errorCode) && isset($responseObject->data->employmentInfos) && count($responseObject->data->employmentInfos) > 0) {
                return $responseObject;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logMessage("Error", "Employee api exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $responseObject
     * @return array
     */
    public function matchInstitutionsAndSetToSession($responseObject)
    {
        $matchedInstitutions = [];
        foreach ($responseObject->data->employmentInfos as $employmentInfo) {
            $institutionMatched = Institution::where("ws_id", $employmentInfo->organicOrganizationId)->first();
            if ($institutionMatched) {
                $matchedInstitutions[] = $institutionMatched->id;
            }
        }
        if (count($matchedInstitutions) > 0) {
            session()->put("matched_institution_ids", implode(",", $matchedInstitutions));
        }
        return $matchedInstitutions;
    }
}
