<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Register extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('communications_model');
        $this->load->model('dashboard_user_model');
        $this->load->library('form_validation');
        $this->load->library('email_library');
    }

    public function index()
    {
        if (count($this->input->post()) <= 0) {
            $view_data = [];
            $view_data['is_error'] = $this->input->get('error', true);

            $this->load->view('utilities/register/main_page', $view_data);
            return;
        }


        /**
         *
         * FORM ACTION
         *
         */
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');


        $this->form_validation->set_rules('registerFirstName', 'Firstname', 'trim|required');
        $this->form_validation->set_rules('registerLastName', 'Lastname', 'trim|required');
        $this->form_validation->set_rules('registerEmail', 'Email', 'trim|required|valid_email|callback_user_email_available');

        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->config->item('mm8_country_code')) {
            case "AU":
            case "US":
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|required|numeric|exact_length[10]');
                break;
            case "NZ":
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|required|numeric|min_length[9]|max_length[11]');
                break;
            case "UK":
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|required|numeric|exact_length[11]');
                break;
            default:
                break;
        }


        if ($this->form_validation->run() == false) {
            $this->load->view('utilities/register/main_page');
            return;
        }


        //form data
        $dataset = $this->input->post();

        //start session
        $this->db->trans_begin();


        $agent_data = [];
        $agent_data['first_name'] = trim($dataset['registerFirstName']);
        $agent_data['last_name'] = trim($dataset['registerLastName']);
        $agent_data['full_name'] = $dataset['registerFirstName'] . " " . $dataset['registerLastName'];
        $agent_data['mobile_phone'] = $dataset['registerMobilePhone'];
        $agent_data['email'] = $dataset['registerEmail'];
        $agent_data['login_method'] = USER_LOGIN_BASICAUTH;
        $agent_data['google_user_id'] = null;
        $agent_data['profile_photo'] = null;
        $agent_data['active'] = STATUS_OK;
        $agent_data['confirmed'] = STATUS_NG;
        $agent_data['verified'] = STATUS_NG;
        $agent_data['role'] = USER_MANAGER;

        // generate password
        $agent_password = random_string('alnum', $this->config->item('mm8_system_password_length'));
        $agent_data['password'] = better_crypt($agent_password);

        $agent_id = $this->dashboard_user_model->set_user_profile($agent_data);
        if ($agent_id === false || $agent_id === -1) {
            $this->db->trans_rollback();
            redirect(current_url() . '?error=1');
        }


        //send email
        $token1 = $this->encryption->url_encrypt($agent_id);
        $token2 = $this->encryption->url_encrypt($agent_password);
        $url_link = $this->config->item('mhub_hub_url') . "register/verify-account/1/" . $token1 . "/" . $token2;

        $template = $this->communications_model->get_email_template('hub_register_manager');
        if (!$template) {
            $this->db->trans_rollback();
            redirect(current_url() . '?error=1');
        }

        $search_for = ["[NAME]", "[LINK]", "[SYSTEMHOTLINE]", "[SYSTEMNAME]"];
        $replace_with = [$agent_data['first_name'], $url_link, $this->config->item('mm8_system_hotline'), $this->config->item('mm8_system_name')];
        $html_template = str_replace($search_for, $replace_with, $template['html_template']);


        //send email!
        $email_result = $this->email_library->send_mpa_email_styled("", "", "", "", "", "", $agent_data['email'], "", "", $template['subject'], $html_template);
        if ($email_result['status'] != STATUS_OK) {
            $this->db->trans_rollback();
            redirect(current_url() . '?error=1');
        }


        //commit session
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            redirect(current_url() . '?error=1');
        }

        $this->db->trans_commit();



        //WERE DONE HERE!
        $id_token = $this->encryption->url_encrypt($agent_id);
        redirect($this->config->item('mhub_hub_url') . 'utilities/register/confirmation/' . $id_token, 'refresh');
    }

    public function confirmation($token)
    {
        $agent_id = $this->encryption->url_decrypt($token);
        $agent_data = $this->dashboard_user_model->get_user_profile($agent_id);

        if ($agent_data === false || count($agent_data) <= 0) {
            redirect($this->config->item('mhub_hub_url') . 'utilities/register?error=1', 'refresh');
        }


        $this->load->view('utilities/register/confirmation_page', $agent_data);
    }

    /**
     *
     * Utils - Form validation Callbacks()
     *
     */
    public function user_email_available($email)
    {
        if ($this->dashboard_user_model->user_email_used($email, USER_MANAGER)) {
            $this->form_validation->set_message('user_email_available', ERROR_600);
            return false;
        } else {
            return true;
        }
    }
}
