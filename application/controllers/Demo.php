<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Demo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */

        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [];

        $this->load->view('demo/header', $view_data);
        $this->load->view('demo/index', $view_data);
        $this->load->view('demo/footer', $view_data);
    }
}
