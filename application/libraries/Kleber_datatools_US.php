<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kleber_datatools_us
{
    protected $CI;
    protected $country_code;
    protected $request_key;
    protected $google_url;
    protected $usps_url;
    protected $states_map;

    public function __construct($params)
    {
        $this->CI = & get_instance();
        $this->google_url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?types=geocode&sensor=false&components=country:US&key=" . $this->CI->config->item('mm8_google_api_id');
        $this->usps_url = "https://secure.shippingapis.com/ShippingAPI.dll?API=Verify";

        $this->states_map = [
            "ALABAMA" => "AL",
            "ALASKA" => "AK",
            "ARIZONA" => "AZ",
            "ARKANSAS" => "AR",
            "CALIFORNIA" => "CA",
            "COLORADO" => "CO",
            "CONNECTICUT" => "CT",
            "DELAWARE" => "DE",
            "DISTRICT OF COLUMBIA" => "DC",
            "FLORIDA" => "FL",
            "GEORGIA" => "GA",
            "HAWAII" => "HI",
            "IDAHO" => "ID",
            "ILLINOIS" => "IL",
            "INDIANA" => "IN",
            "IOWA" => "IA",
            "KANSAS" => "KS",
            "KENTUCKY" => "KY",
            "LOUISIANA" => "LA",
            "MAINE" => "ME",
            "MONTANA" => "MT",
            "NEBRASKA" => "NE",
            "NEVADA" => "NV",
            "NEW HAMPSHIRE" => "NH",
            "NEW JERSEY" => "NJ",
            "NEW MEXICO" => "NM",
            "NEW YORK" => "NY",
            "NORTH CAROLINA" => "NC",
            "NORTH DAKOTA" => "ND",
            "OHIO" => "OH",
            "OKLAHOMA" => "OK",
            "OREGON" => "OR",
            "MARYLAND" => "MD",
            "MASSACHUSETTS" => "MA",
            "MICHIGAN" => "MI",
            "MINNESOTA" => "MN",
            "MISSISSIPPI" => "MS",
            "MISSOURI" => "MO",
            "PENNSYLVANIA" => "PA",
            "RHODE ISLAND" => "RI",
            "SOUTH CAROLINA" => "SC",
            "SOUTH DAKOTA" => "SD",
            "TENNESSEE" => "TN",
            "TEXAS" => "TX",
            "UTAH" => "UT",
            "VERMONT" => "VT",
            "VIRGINIA" => "VA",
            "WASHINGTON" => "WA",
            "WEST VIRGINIA" => "WV",
            "WISCONSIN" => "WI",
            "WYOMING" => "WY",
        ];
    }

    //addressify
    private function get_url_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Access-Control-Allow-Origin: *'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function search_address($term, $max_results = 10)
    {
        $url = $this->google_url .= "&input=" . urlencode($term);
        $tmp_data = $this->get_url_contents($url);
        $dataset = $tmp_data ? json_decode($tmp_data) : [];

        $resultset = [];

        if (isset($dataset->{'predictions'})) {
            foreach ($dataset->{'predictions'} as $subset) {
                array_push($resultset, ['id' => isset($subset->{'place_id'}) ? $subset->{'place_id'} : null, 'name' => $subset->{'description'}]);
            }

            //trim to set
            if (count($resultset) > $max_results) {
                $resultset = array_slice($resultset, 0, $max_results);
            }

            //add powered by google as T&Cs
            array_push($resultset, ['id' => 'google', 'name' => '']);
        }

        return json_encode($resultset);
    }

    public function retrieve_address($id, $value = "")
    {

        //retrieve adress info again from google
        $url = $this->google_url .= "&input=" . urlencode($value);
        $tmp_data = $this->get_url_contents($url);
        $dataset = $tmp_data ? json_decode($tmp_data) : [];


        $resultset = [];

        if (isset($dataset->{'predictions'})) {
            foreach ($dataset->{'predictions'} as $subset) {
                if ($subset->{'place_id'} == $id) {
                    $resultset['DPID'] = "";
                    $resultset['UnitNumber'] = "";
                    $resultset['StreetNumber'] = "";
                    $resultset['StreetName'] = "";
                    $resultset['StreetType'] = "";
                    $resultset['Suburb'] = "";
                    $resultset['State'] = "";
                    $resultset['Postcode'] = "";

                    if (isset($subset->{'terms'}) && count($subset->{'terms'}) >= 3) {
                        //we dont know the number of terms until we get the result so were parsing backwards
                        //the last item will always be the country
                        //the order then can be either:
                        //  ... city state zipcode country
                        //  ... city state country
                        $reverse_index = count($subset->{'terms'}) - 2; // skip the country

                        if (ctype_digit($subset->{'terms'}[$reverse_index]->{'value'})) {
                            //has zipcode set
                            $resultset['Suburb'] = $subset->{'terms'}[$reverse_index - 2]->{'value'};
                            $resultset['State'] = $subset->{'terms'}[$reverse_index - 1]->{'value'};
                            $resultset['Postcode'] = $subset->{'terms'}[$reverse_index]->{'value'};
                            $suburb_ptr = $reverse_index - 2;
                        } else {
                            $resultset['Suburb'] = $subset->{'terms'}[$reverse_index - 1]->{'value'};
                            $resultset['State'] = $subset->{'terms'}[$reverse_index]->{'value'};
                            $resultset['Postcode'] = "";
                            $suburb_ptr = $reverse_index - 1;
                        }

                        //see if we can get a street number
                        //this time were traversing from the front
                        if ($suburb_ptr > 0) {
                            for ($i = 0; $i < $suburb_ptr; $i++) {
                                $resultset['StreetNumber'] .= " " . $subset->{'terms'}[$i]->{'value'};
                            }
                            $resultset['StreetNumber'] = trim($resultset['StreetNumber']);
                        }
                    }

                    $resultset['Country'] = $this->CI->config->item('mm8_country');

                    //forPOBOX
                    $resultset['POBoxNumber'] = "";
                    break;
                }
            }
        }


        //repair address
        $repaired_resultset = $this->repair_address($resultset);
        return json_decode($repaired_resultset) == false ? json_encode($resultset) : $repaired_resultset;
        //return json_encode($resultset);
    }

    public function search_and_retrieve_address($address)
    {
        return json_encode([]);
    }

    public function search_locality($term, $max_results = 10)
    {
        return json_encode([]);
    }

    public function retrieve_locality($suburb, $state)
    {
        return json_encode([]);
    }

    public function repair_address($dataset)
    {
        //state
        if (isset($dataset['State'])) {
            $tmp_state = isset($this->states_map[strtoupper($dataset['State'])]) ? $this->states_map[strtoupper($dataset['State'])] : $dataset['State'];
        } else {
            $tmp_state = "";
        }

        //build dataset as xml obj
        $xml_entry = new SimpleXMLElement('<AddressValidateRequest USERID="' . $this->CI->config->item('mm8_usps_user_id') . '"></AddressValidateRequest>');
        $xml_entry->addChild('Revision', 1);
        $address = $xml_entry->addChild('Address');
        $address->addAttribute('ID', '0');
        $address->addChild('Address1', (isset($dataset['UnitNumber']) ? $dataset['UnitNumber'] : ''));
        $address->addChild('Address2', (isset($dataset['StreetNumber']) ? $dataset['StreetNumber'] : ''));
        $address->addChild('City', (isset($dataset['Suburb']) ? $dataset['Suburb'] : ''));
        $address->addChild('State', $tmp_state);
        $address->addChild('Zip5', (isset($dataset['Postcode']) ? $dataset['Postcode'] : ''));
        $address->addChild('Zip4', (isset($dataset['DPID']) ? $dataset['DPID'] : ''));

        //xml formatting
        $dom = new DOMDocument('1.0');
        $dom->loadXML($xml_entry->asXML());
        $dom->preserveWhiteSpace = false;
        $xml_parameter = $dom->saveXML($dom->documentElement);


        //call the usps api
        $url = $this->usps_url .= "&XML=" . urlencode($xml_parameter);
        $curl_response = $this->get_url_contents($url);
        if ($curl_response === false) {
            return json_encode(false);
        }

        //parse xml response
        $xml_response = [];
        $xml_obj = new SimpleXMLElement($curl_response);
        foreach ($xml_obj->xpath('Address') as $node) {
            foreach ($node->children() as $child_node) {
                $xml_response[(string) $child_node->getName()] = (string) $child_node;
            }
        }

        if (isset($xml_response['Error'])) {
            return json_encode(false);
        } else {
            $resultset = [];
            $resultset['UnitNumber'] = isset($xml_response['Address1']) ? $xml_response['Address1'] : "";
            $resultset['StreetNumber'] = isset($xml_response['Address2']) ? $xml_response['Address2'] : "";
            $resultset['StreetName'] = "";
            $resultset['StreetType'] = "";
            $resultset['Suburb'] = isset($xml_response['City']) ? $xml_response['City'] : "";
            $resultset['State'] = isset($xml_response['State']) ? $xml_response['State'] : "";
            $resultset['Postcode'] = isset($xml_response['Zip5']) ? $xml_response['Zip5'] : "";
            $resultset['DPID'] = isset($xml_response['Zip4']) ? $xml_response['Zip4'] : "";
            $resultset['Country'] = $this->CI->config->item('mm8_country');
            $resultset['POBoxNumber'] = ""; //forPOBOX
            return json_encode($resultset);
        }
    }

    /*
     * Additional Helpers
     */

    //note: first param is passed by reference
    private function parse_level(&$element, $node)
    {
        foreach ($node->children() as $child_node) {
            $key = (string) $child_node->getName();
            $val = (string) $child_node;

            if (array_key_exists($key, $element) && ($element[$key] == null || $val != "")) {
                $element[$key] = $val;
            }
        }
    }
}
