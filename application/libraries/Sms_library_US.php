<?php

defined('BASEPATH') or exit('No direct script access allowed');
include(__dir__ . '/Sms_library_base.php');

class Sms_library_us extends Sms_library_base
{
    protected $country_code = "+1";

    public function __construct()
    {
        parent::__construct();
    }

    public function convert_e164($phone_number)
    {
        $phone_number = preg_replace('/\s+/', '', $phone_number);

        if (preg_match("/^(\+)?1(\d{10})$/", $phone_number, $match)) {
            return $this->country_code . $match[2];
        } elseif (preg_match("/^(\d{10})$/", $phone_number, $match)) {
            return $this->country_code . $match[1];
        } else {
            return $phone_number;
        }
    }
}
