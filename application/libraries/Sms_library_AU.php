<?php

defined('BASEPATH') or exit('No direct script access allowed');
include(__dir__ . '/Sms_library_base.php');

class Sms_library_au extends Sms_library_base
{
    protected $country_code = "+61";

    public function __construct()
    {
        parent::__construct();
    }
}
