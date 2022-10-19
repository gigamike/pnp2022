<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kleber_datatools_uk
{
    protected $CI;
    protected $request_key;
    protected $url;

    public function __construct($params)
    {
        $this->CI = & get_instance();

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
            "Method" => "DataTools.Capture.Address.Predictive.UkPaf.SearchAddress",
            "AddressLine" => $term,
            "ResultLimit" => $max_results,
            "RequestKey" => $this->request_key,
            "OutputFormat" => "json",
            "DepartmentCode" => $this->CI->config->item('mm8_system_prefix')
        ];

        $tmp_data = $this->get_url_contents($this->url . '?' . http_build_query($params));
        $dataset = $tmp_data ? json_decode($tmp_data) : [];

        $resultset = [];

        if (isset($dataset->{'DtResponse'}->{'Result'})) {
            foreach ($dataset->{'DtResponse'}->{'Result'} as $subset) {
                array_push($resultset, ['id' => $subset->{'RecordId'}, 'name' => $subset->{'AddressLine'}]);
            }
        }

        return json_encode($resultset);
    }

    public function retrieve_address($id, $value = "")
    {
        $params = [
            "Method" => "DataTools.Capture.Address.Predictive.UkPaf.RetrieveAddress",
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
                $resultset['DPID'] = $subset->{'DomesticId'};
                $resultset['UnitNumber'] = $subset->{'UnitLevelNumber'};
                $resultset['StreetNumber'] = trim(preg_replace('/\s+/', ' ', $subset->{'BuildingName'} . " " . $subset->{'StreetNumber'} . " " . $subset->{'StreetName'} . " " . $subset->{'SecondaryStreetName'} . " " . $subset->{'District'}));
                $resultset['StreetName'] = "";
                $resultset['StreetType'] = "";
                $resultset['Suburb'] = $subset->{'LocalityCityTown'};
                $resultset['State'] = $subset->{'StateProvince'};
                $resultset['Postcode'] = $subset->{'Postcode'};
                $resultset['Country'] = !empty($subset->{'CountryName'}) ? $subset->{'CountryName'} : $this->CI->config->item('mm8_country');
                //forPOBOX
                $resultset['POBoxNumber'] = $subset->{'PostBoxNumber'};

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
            "Method" => "DataTools.Capture.Address.Predictive.UkPaf.SearchAddress",
            "AddressLine" => $address,
            "ResultLimit" => 10,
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
                    if (strtoupper($subset->{'AddressLine'}) == strtoupper($address)) {
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
            "Method" => "DataTools.Capture.Address.Predictive.UkPaf.RetrieveAddress",
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
