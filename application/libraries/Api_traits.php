<?php

defined('BASEPATH') or exit('No direct script access allowed');

// before using this trait:
// 1. declare "protected $api_token = null;" in your class 
// 2. load curl library in your constructor, e.g. $this->load->library('curl_library');

trait Api_traits
{
    protected function get_api_token()
    {
        $auth_headers = [];
        $auth_headers[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $auth_headers[CURLOPT_USERPWD] = $this->CI->config->item('mm8_api_username') . ":" . $this->CI->config->item('mm8_api_password');

        $auth_result = $this->CI->curl_library->simple_post($this->CI->config->item('mhub_api_url') . "v2/auth", [], $auth_headers);
        if ($auth_result['successful']) {
            $tmp_response = json_decode($auth_result['http_response'], true);
            $this->api_token = $tmp_response['token'];
            return true;
        } else {
            return $auth_result;
        }
    }

    protected function talk_to_api_v2($method, $url_segment, $data, $add_headers = [])
    {
        //get token
        if (empty($this->api_token)) {
            $tmp_result = $this->get_api_token();
            if ($tmp_result !== true) {
                return $tmp_result;
            }
        }

        //run!
        $tmp_headers = ['MHUB-API-KEY: ' . $this->CI->config->item('mm8_api_api_key'), 'token: ' . $this->api_token];
        if (count($add_headers) > 0) {
            $tmp_headers = array_merge($tmp_headers, $add_headers);
        }

        $method = 'simple_' . $method;
        $url = $this->CI->config->item('mhub_api_url') . $url_segment;
        $headers = [CURLOPT_HTTPHEADER => $tmp_headers];
        $result = $this->CI->curl_library->{$method}($url, $data, $headers);
     
        if (!$result['successful'] && (int) $result['http_response_code'] == 401) {
            //lets try one more time, but lets get a new token first
            if ($this->get_api_token() !== true) {
                return $result;
            }

            $result = $this->CI->curl_library->{$method}($url, $data, $headers);
        }

        return $result;
    }
}