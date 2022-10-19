<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kleber_datatools_nz
{
    protected $CI;
    protected $country_code;
    protected $request_key;
    protected $url;

    public function __construct($params)
    {
        $this->CI = & get_instance();

        $this->country_code = $this->CI->config->item('mm8_country_code');
        $this->request_key = $this->CI->config->item('mm8_kleber_key');
        $this->url = $params['host'] . '/KleberWebService/DtKleberService.svc/ProcessQueryStringRequest';
    }

    //addressify
    private function get_url_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function search_address($term, $max_results = 10)
    {
        $params = [
            "Method" => "DataTools.Capture.Address.Predictive.AuNzPaf.SearchAddress",
            "AddressLine" => $term,
            "ResultLimit" => $max_results,
            "DisplayOnlyCountryCode" => $this->country_code,
            "RequestKey" => $this->request_key,
            "OutputFormat" => "json",
            "DepartmentCode" => $this->CI->config->item('mm8_system_prefix')
        ];

        $tmp_data = $this->get_url_contents($this->url . '?' . http_build_query($params));
        $dataset = $tmp_data ? json_decode($tmp_data) : [];

        $resultset = [];

        if (isset($dataset->{'DtResponse'}->{'Result'})) {
            foreach ($dataset->{'DtResponse'}->{'Result'} as $subset) {
                array_push($resultset, ['id' => $subset->{'RecordId'}, 'name' => $subset->{'AddressLine'} . " " . $subset->{'Locality'} . " " . $subset->{'State'} . " " . $subset->{'Postcode'}]);
            }
        }

        return json_encode($resultset);
    }

    public function retrieve_address($id, $value = "")
    {
        $params = [
            "Method" => "DataTools.Capture.Address.Predictive.NzPaf.RetrieveAddress",
            "RecordId" => $id,
            "RequestKey" => $this->request_key,
            "OutputFormat" => "json",
            "DepartmentCode" => $this->CI->config->item('mm8_system_prefix')
        ];

        $tmp_data = $this->get_url_contents($this->url . '?' . http_build_query($params));
        $dataset = $tmp_data ? json_decode($tmp_data) : [];

        $resultset = [];

        if (isset($dataset->{'DtResponse'}->{'Result'})) {
            foreach ($dataset->{'DtResponse'}->{'Result'} as $subset) {
                $resultset['DPID'] = $subset->{'DPID'};
                $resultset['UnitNumber'] = trim($subset->{'UnitType'} . " " . $subset->{'UnitNumber'});
                $resultset['StreetNumber'] = trim($subset->{'StreetNumber1'} . $subset->{'StreetNumberSuffix1'});
                $resultset['StreetName'] = trim($subset->{'StreetName'} . " " . $subset->{'StreetSuffix'});
                $resultset['StreetType'] = $subset->{'StreetType'};
                $resultset['Suburb'] = ucwords(strtolower($subset->{'Suburb'}));
                $resultset['State'] = $subset->{'TownCityMailtown'};
                $resultset['Postcode'] = $subset->{'Postcode'};
                $resultset['Country'] = $this->CI->config->item('mm8_country');
                //forPOBOX
                $resultset['POBoxNumber'] = trim($subset->{'PostBoxType'} . " " . $subset->{'PostBoxNumber'});

                if (!empty($resultset['POBoxNumber'])) {
                    $resultset['StreetNumber'] = $resultset['POBoxNumber'];
                }
                break;
            }
        }

        return json_encode($resultset);
    }

    public function search_and_retrieve_address($address)
    {
        //search
        $params = [
            "Method" => "DataTools.Capture.Address.Predictive.AuNzPaf.SearchAddress",
            "AddressLine" => $address,
            "ResultLimit" => 10,
            "DisplayOnlyCountryCode" => $this->country_code,
            "RequestKey" => $this->request_key,
            "OutputFormat" => "json",
            "DepartmentCode" => $this->CI->config->item('mm8_system_prefix')
        ];

        $tmp_data = $this->get_url_contents($this->url . '?' . http_build_query($params));
        $dataset = $tmp_data ? json_decode($tmp_data) : [];
        $record_id = null;

        if (isset($dataset->{'DtResponse'}->{'Result'})) {
            $match_address = count($dataset->{'DtResponse'}->{'Result'}) > 1 ? true : false;
            foreach ($dataset->{'DtResponse'}->{'Result'} as $subset) {
                if ($match_address) {
                    $address_from_api = strtoupper($subset->{'AddressLine'} . " " . $subset->{'Suburb'} . " " . $subset->{'TownCityMailtown'} . " " . $subset->{'Postcode'});
                    if ($address_from_api == strtoupper($address)) {
                        $record_id = $subset->{'RecordId'};
                    }
                } else {
                    $record_id = $subset->{'RecordId'};
                }
            }
        }

        if ($record_id == null) {
            return json_encode([]);
        }



        //retrieve
        $params = [
            "Method" => "DataTools.Capture.Address.Predictive.NzPaf.RetrieveAddress",
            "RecordId" => $record_id,
            "RequestKey" => $this->request_key,
            "OutputFormat" => "json",
            "DepartmentCode" => $this->CI->config->item('mm8_system_prefix')
        ];

        $tmp_data = $this->get_url_contents($this->url . '?' . http_build_query($params));
        $dataset = $tmp_data ? json_decode($tmp_data) : [];

        $resultset = [];

        if (isset($dataset->{'DtResponse'}->{'Result'})) {
            foreach ($dataset->{'DtResponse'}->{'Result'} as $subset) {
                $resultset = $subset;
                break;
            }
        }


        return json_encode($resultset);
    }

    public function search_locality($term, $max_results = 10)
    {
        // method not supported in NZ
        return json_encode([]);
    }

    public function retrieve_locality($suburb, $state)
    {
        // method not supported in NZ
        return json_encode([]);
    }

    public function repair_address($dataset)
    {
        return json_encode(false);
    }
}
