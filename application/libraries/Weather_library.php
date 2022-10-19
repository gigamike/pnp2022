<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Weather_library
{
    protected $CI;
    private $key;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->key = $this->CI->config->item('mm8_openweather_api_id');
    }

    public function get_weather($lat, $lng, $units = "metric", $days = 5)
    {
        $url = "http://api.openweathermap.org/data/2.5/forecast/daily?lat=" . $lat . "&lon=" . $lng . "&cnt=" . $days . "&mode=json&units=" . $units . "&APPID=" . $this->key;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        //$curl_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);


        $json_obj = json_decode($curl_response);

        $weather_data = [];

        $days_fetched = isset($json_obj->{'cnt'}) ? (int) $json_obj->{'cnt'} : 0;

        if ($days_fetched > 0) {
            for ($i = 0; $i < $days_fetched; $i++) {
                $datestamp = date($this->CI->config->item('mm8_php_default_date_format'), $json_obj->{'list'}[$i]->{'dt'});
                $weather_data[$datestamp]['weather'] = ucwords($json_obj->{'list'}[$i]->{'weather'}[0]->{'description'});
                //$weather_data[$datestamp]['weather'] = $json_obj->{'list'}[$i]->{'weather'}[0]->{'main'};
                $weather_data[$datestamp]['icon'] = "https://openweathermap.org/img/w/" . $json_obj->{'list'}[$i]->{'weather'}[0]->{'icon'} . ".png";
                $weather_data[$datestamp]['min_temp'] = round((float) $json_obj->{'list'}[$i]->{'temp'}->{'min'}, 1);
                $weather_data[$datestamp]['max_temp'] = round((float) $json_obj->{'list'}[$i]->{'temp'}->{'max'}, 1);
            }
        }

        return $weather_data;
    }
}
