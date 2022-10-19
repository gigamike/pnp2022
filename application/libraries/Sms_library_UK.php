<?php

defined('BASEPATH') or exit('No direct script access allowed');
include(__dir__ . '/Sms_library_base.php');

class Sms_library_uk extends Sms_library_base
{
    protected $country_code = "+44";

    public function __construct()
    {
        parent::__construct();
    }
}
