<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Abn_lookup_library extends SoapClient
{
    private $guid = "";
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->guid = $this->CI->config->item('mm8_abr_guid');

        $params = [
            'soap_version' => SOAP_1_1,
            'exceptions' => true,
            'trace' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE
        ];

        parent::__construct('https://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?WSDL', $params);
    }

    public function searchByAbn($abn, $historical = 'N')
    {
        try {
            try {
                $params = new stdClass();
                $params->searchString = $abn;
                $params->includeHistoricalDetails = $historical;
                $params->authenticationGuid = $this->guid;
                $result = $this->SearchByABNv201408($params);


                if (!isset($result->ABRPayloadSearchResults->response->businessEntity201408) || empty($result->ABRPayloadSearchResults->response->businessEntity201408)) {
                    return json_encode(['successful' => false]);
                } else {
                    //reformat and simplify dataset
                    $ptr = $result->ABRPayloadSearchResults->response->businessEntity201408;

                    //make sure its still active
                    if (strtoupper($ptr->entityStatus->entityStatusCode) != "ACTIVE") {
                        return json_encode(['successful' => false]);
                    }

                    $dataset = [];

                    switch ($ptr->entityType->entityTypeCode) {
                        case "IND":
                            $dataset['businessType'] = $ptr->entityType->entityDescription;
                            $dataset['businessCompanyName'] = trim(preg_replace('/\s+/', ' ', $ptr->legalName->familyName . ", " . $ptr->legalName->givenName . " " . $ptr->legalName->otherGivenName));
                            break;
                        case "PRV":
                            $dataset['businessType'] = $ptr->entityType->entityDescription;
                            $dataset['businessCompanyName'] = $ptr->mainName->organisationName;
                            break;
                        default:
                            $dataset['businessType'] = $ptr->entityType->entityDescription;
                            $dataset['businessCompanyName'] = $ptr->mainName->organisationName;
                            break;
                    }


                    $dataset['businessTradingName'] = [];
                    if (isset($ptr->mainTradingName->organisationName) && !empty($ptr->mainTradingName->organisationName)) {
                        array_push($dataset['businessTradingName'], $ptr->mainTradingName->organisationName);
                    }

                    if (isset($ptr->otherTradingName) && is_array($ptr->otherTradingName) && count($ptr->otherTradingName) > 0) {
                        foreach ($ptr->otherTradingName as $tn) {
                            array_push($dataset['businessTradingName'], $tn->organisationName);
                        }
                    }



                    return json_encode(['successful' => true, 'dataset' => $dataset]);
                }
            } catch (Exception $e) {
                throw $e;
            }
        } catch (Exception $e) {
            //echo $e->getMessage();
            return json_encode(['successful' => false]);
        }
    }
}
