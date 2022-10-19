<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Register extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('communications_model');
        $this->load->model('manager_model');
        $this->load->model('partner_model');
        $this->load->model('account_manager_refer_and_earn_referrals_model');
        $this->load->model('account_manager_refer_and_earn_clicks_model');
        $this->load->model('account_manager_model');
        $this->load->model('account_manager_association_model');
        $this->load->model('account_manager_permissions_model');
        $this->load->model('crm_user_model');

        $this->load->library('form_validation');
        $this->load->library('email_library');
        $this->load->library('user_agent');
        $this->load->library('curl_library');
        $this->load->library('customer_portal_v2_library');

        $this->load->helper('cookie');

        //form lookup
        $library_name = '/Form_lookup_library_' . $this->config->item('mm8_country_code');
        if (!file_exists(APPPATH . "libraries/" . $library_name . ".php")) {
            $library_name = '/Form_lookup_library_AU';
        }
        $this->load->library($library_name, '', 'form_lookup_library');

        //address lookup
        $library_name = '/Kleber_datatools_' . $this->config->item('mm8_country_code');
        if (!file_exists(APPPATH . "libraries/" . $library_name . ".php")) {
            $library_name = '/Kleber_datatools_AU';
        }
        $host = ENVIRONMENT == "production" ? $this->config->item('mm8_kleber_prod_url') : $this->config->item('mm8_kleber_dev_url');
        $this->load->library($library_name, ['host' => $host], 'kleber_datatools');
    }

    public function index()
    {
        $view_data = [];
        $view_data['industry_list'] = $this->form_lookup_library->partner_industries();
        $view_data['country_list'] = $this->form_lookup_library->countries();

        if (count($this->input->post()) <= 0) {
            $this->load->view('register/main_page', $view_data);
            $this->session->sess_destroy();
            return;
        }

        if (ENVIRONMENT == "production") {
            //validate captcha
            $form_dataset = $this->input->post();
            if (!isset($form_dataset['g-recaptcha-response']) || empty($form_dataset['g-recaptcha-response'])) {
                $this->load->view('register/main_page', $view_data);
                $this->session->sess_destroy();
                return;
            }

            //verifying captcha response with google
            $recaptcha_verify_result = $this->curl_library->simple_post("https://www.google.com/recaptcha/api/siteverify", ['secret' => $this->config->item('mm8_g_recaptcha_secret_key'), 'response' => $form_dataset['g-recaptcha-response']], []);

            $verify_result = json_decode($recaptcha_verify_result['http_response'], true);
            if (!isset($verify_result['success']) || !$verify_result['success']) {
                $this->load->view('register/main_page', $view_data);
                $this->session->sess_destroy();
                return;
            }
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
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|numeric|exact_length[10]');
                break;
            case "NZ":
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|numeric|min_length[9]|max_length[11]');
                break;
            case "UK":
                $this->form_validation->set_rules('registerMobilePhone', 'Mobile Phone', 'trim|numeric|exact_length[11]');
                break;
            default:
                break;
        }


        if ($this->form_validation->run() == false) {
            $this->load->view('register/main_page');
            $this->session->sess_destroy();
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
        $agent_data['mobile_phone'] = isset($dataset['registerMobilePhone']) ? $dataset['registerMobilePhone'] : null;
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
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(current_url());
        }

        // Refer and Earn
        $click_id = $this->input->cookie('click_id', null);
        if ($click_id) {
            $click_id = $this->encryption->url_decrypt($click_id);
            $click = $this->account_manager_refer_and_earn_clicks_model->getById($click_id);
            if ($click) {
                $data = [
                    'click_id' => $click->id,
                    'referee_partner_agent_id' => $agent_id,
                ];
                $referAndEarnReferralId = $this->account_manager_refer_and_earn_referrals_model->save($data);
                if (!$referAndEarnReferralId) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error_message', ERROR_601);
                    redirect(current_url());
                }
            }
        }

        //MANAGER DETAILS
        $manager_data = [];
        $manager_data['sales_mgr_id'] = $this->config->item('hub_default_manager_sales_mgr_id');
        $manager_data['manager_agent'] = $agent_id;

        //defaults
        $manager_data['hotline'] = $this->config->item('mm8_system_hotline');
        $manager_data['support_email'] = $this->config->item('mm8_system_support_email');
        $manager_data['ops_email'] = $this->config->item('mm8_system_ops_email');
        $manager_data['sms_sender'] = $this->config->item('hub_default_manager_sms_sender');
        $manager_data['img_url'] = asset_url() . "img/default/partner-logo.png";
        $manager_data['widget_theme'] = $this->config->item('hub_default_manager_widget_theme');
        $manager_data['widget_type'] = $this->config->item('hub_default_manager_widget_type');
        $manager_data['email_theme'] = $this->config->item('hub_default_manager_email_theme');
        $manager_data['email_banner'] = asset_url() . "img/default/partner-email-banner.jpg";
        $manager_data['wallet_home_url'] = null;

        $manager_data['manager_code'] = "";
        while (true) {
            $manager_data['manager_code'] = strtolower(random_string('alnum', 5));
            if ($this->manager_model->map_manager($manager_data['manager_code'], false) === false) {
                break;
            }
        }

        // Refer and Earn. Get referrer and assign referrer's assigned IS/ES
        $managerAgent = $this->dashboard_user_model->get_user_profile($agent_id);
        if ($managerAgent) {
            // should always an ES
            $filter = [
                'referee_partner_agent_id' => $managerAgent['id'],
            ];
            $referal = $this->account_manager_refer_and_earn_referrals_model->fetch($filter, [], 1);
            if ($referal) {
                $click = $this->account_manager_refer_and_earn_clicks_model->getById($referal[0]->click_id);
                if ($click) {
                    $referrerProfile = $this->dashboard_user_model->get_user_profile($click->referrer_partner_agent_id);
                    if ($referrerProfile || count($referrerProfile) > 0) {
                        $externalSales = $this->account_manager_model->getByAccountManagerAgent($referrerProfile['id']);
                        if ($externalSales) {
                            $associatedIS = $this->account_manager_association_model->getByExternalSales($externalSales->id);
                            if ($associatedIS) {
                                // IS
                                if ($associatedIS->internal_sales) {
                                    $internalSales = $this->account_manager_model->getById($associatedIS->internal_sales);
                                    if ($internalSales) {
                                        $manager_data['internal_account_manager_id'] = $internalSales->id;
                                    }
                                }

                                $manager_data['external_account_manager_id'] = $externalSales->id;
                            }
                        }
                    }
                }
            }
        }

        $manager_id = $this->manager_model->set_manager_info($manager_data);
        if ($manager_id === false || (int) $manager_id <= 0) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(current_url());
        }

        // Refer and Earn. Get assigned IS/ES and notify
        $manager = $this->manager_model->get_manager_info($manager_id);
        if ($manager) {
            $managerAgent = $this->dashboard_user_model->get_user_profile($manager['manager_agent']);
            if ($managerAgent) {
                if (isset($internalSales)) {
                    $internalSalesAgent = $this->dashboard_user_model->get_user_profile($internalSales->account_manager_agent);
                    if ($internalSalesAgent) {
                        $this->_emailNotifyAssignedAccountManager($manager, $managerAgent, $internalSalesAgent['email']);
                    }

                    // add remote access
                    $this->grant_temporary_remote_access($manager_data['internal_account_manager_id'], $manager_id);
                }

                if (isset($externalSales)) {
                    $externalSalesAgent = $this->dashboard_user_model->get_user_profile($externalSales->account_manager_agent);
                    if ($externalSalesAgent) {
                        $this->_emailNotifyAssignedAccountManager($manager, $managerAgent, $externalSalesAgent['email']);
                    }
                }
            }
        }

        //send email
        $token1 = $this->encryption->url_encrypt($agent_id);
        $token2 = $this->encryption->url_encrypt($agent_password);
        $url_link = base_url() . "register/verify-account/1/" . $token1 . "/" . $token2;

        $template = $this->communications_model->get_email_template('hub_register_manager');
        if (!$template) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(current_url());
        }

        $search_for = ["[NAME]", "[LINK]", "[SYSTEMHOTLINE]", "[SYSTEMNAME]"];
        $replace_with = [$agent_data['first_name'], $url_link, $this->config->item('mm8_system_hotline'), $this->config->item('mm8_system_name')];
        $html_template = str_replace($search_for, $replace_with, $template['html_template']);

        //send email!
        $email_result = $this->email_library->send_mpa_email_styled("", "", "", "", "", "", $agent_data['email'], "", "", $template['subject'], $html_template);
        if ($email_result['status'] != STATUS_OK) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(current_url());
        }

        //commit session
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(current_url());
        }

        $this->db->trans_commit();

        //WERE DONE HERE!
        $id_token = $this->encryption->url_encrypt($agent_id);
        redirect(base_url() . 'register/confirmation/' . $id_token, 'refresh');
    }

    /**
     *
     * Register - Google Auth
     *
     */
    public function google_auth()
    {
        //Step 1: Configure the client object
        $client = new Google_Client();
        $client->setAuthConfig(APPPATH . '/config/google_client_secrets.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->setRedirectUri(base_url() . 'register/google-auth-validate');

        //Step 2: Redirect to Google's OAuth 2.0 server
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

        //Step 3: Google prompts user for consent
        //Step 4: Handle the OAuth 2.0 server response
    }

    public function google_auth_validate()
    {
        $generic_g_error = "Something went wrong here. (Invalid Grant)";

        if ((int) $this->config->item('mm8_hub_account_google_sso') != STATUS_OK) {
            $this->session->set_flashdata('error_message', $generic_g_error);
            redirect(base_url() . 'register', 'refresh');
        }

        $client = new Google_Client();
        $client->setAuthConfig(APPPATH . '/config/google_client_secrets.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->setRedirectUri(base_url() . 'register/google-auth-validate');

        if (!isset($_GET['code'])) {
            $this->session->set_flashdata('error_message', $generic_g_error);
            redirect(base_url() . 'register', 'refresh');
        } else {
            //Step 5: Exchange authorization code for refresh and access tokens
            $client->authenticate($_GET['code']);
            $tokens = $client->getAccessToken();
            if (!$tokens || !isset($tokens['access_token'])) {
                $this->session->set_flashdata('error_message', $generic_g_error);
                redirect(base_url() . 'register', 'refresh');
            }


            $curl = curl_init("https://www.googleapis.com/userinfo/v2/me");
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $tokens['access_token']]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $curl_response = curl_exec($curl);
            curl_close($curl);
            $dataset = json_decode($curl_response, true);

            //validate: make sure email can be used
            if ($this->dashboard_user_model->user_email_used($dataset['email'], USER_MANAGER)) {
                $this->session->set_flashdata('error_message', ERROR_912);
                redirect(base_url() . 'register', 'refresh');
            }


            //start session
            $this->db->trans_begin();

            $agent_data = [];
            $agent_data['first_name'] = isset($dataset['given_name']) ? trim($dataset['given_name']) : "";
            $agent_data['last_name'] = isset($dataset['family_name']) ? trim($dataset['family_name']) : "";
            $agent_data['full_name'] = trim($agent_data['first_name'] . " " . $agent_data['last_name']);
            $agent_data['mobile_phone'] = null;
            $agent_data['email'] = $dataset['email'];
            $agent_data['login_method'] = USER_LOGIN_GOOGLEAUTH;
            $agent_data['google_user_id'] = $dataset['id'];
            $agent_data['profile_photo'] = isset($dataset['picture']) && !empty($dataset['picture']) ? $dataset['picture'] : null;
            $agent_data['active'] = STATUS_OK;
            $agent_data['confirmed'] = STATUS_NG;
            $agent_data['verified'] = STATUS_NG;
            $agent_data['role'] = USER_MANAGER;

            //generate password
            $agent_password = random_string('alnum', $this->config->item('mm8_system_password_length'));
            $agent_data['password'] = better_crypt($agent_password);

            $agent_id = $this->dashboard_user_model->set_user_profile($agent_data);
            if ($agent_id === false || $agent_id === -1) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error_message', ERROR_601);
                redirect(base_url() . 'register', 'refresh');
            }

            // Refer and Earn
            $click_id = $this->input->cookie('click_id', null);
            if ($click_id) {
                $click_id = $this->encryption->url_decrypt($click_id);
                $click = $this->account_manager_refer_and_earn_clicks_model->getById($click_id);
                if ($click) {
                    $data = [
                        'click_id' => $click->id,
                        'referee_partner_agent_id' => $agent_id,
                    ];
                    $referAndEarnReferralId = $this->account_manager_refer_and_earn_referrals_model->save($data);
                    if (!$referAndEarnReferralId) {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('error_message', ERROR_601);
                        redirect(current_url());
                    }
                }
            }

            //send email
            $token1 = $this->encryption->url_encrypt($agent_id);
            $token2 = $this->encryption->url_encrypt($agent_password);
            $url_link = base_url() . "register/verify-account/1/" . $token1 . "/" . $token2;

            $template = $this->communications_model->get_email_template('hub_register_manager');
            if (!$template) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error_message', ERROR_601);
                redirect(base_url() . 'register', 'refresh');
            }

            $search_for = ["[NAME]", "[LINK]", "[SYSTEMHOTLINE]", "[SYSTEMNAME]"];
            $replace_with = [$agent_data['first_name'], $url_link, $this->config->item('mm8_system_hotline'), $this->config->item('mm8_system_name')];
            $html_template = str_replace($search_for, $replace_with, $template['html_template']);

            //send email!
            $email_result = $this->email_library->send_mpa_email_styled("", "", "", "", "", "", $agent_data['email'], "", "", $template['subject'], $html_template);
            if ($email_result['status'] != STATUS_OK) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error_message', ERROR_601);
                redirect(base_url() . 'register', 'refresh');
            }


            //commit session
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error_message', ERROR_601);
                redirect(base_url() . 'register', 'refresh');
            }

            $this->db->trans_commit();

            //WERE DONE HERE!
            $id_token = $this->encryption->url_encrypt($agent_id);
            redirect(base_url() . 'register/confirmation/' . $id_token, 'refresh');
        }
    }

    public function confirmation($token)
    {
        $agent_id = $this->encryption->url_decrypt($token);
        $agent_data = $this->dashboard_user_model->get_user_profile($agent_id);

        if ($agent_data === false || count($agent_data) <= 0) {
            $this->session->set_flashdata('error_message', ERROR_601);
            redirect(base_url() . 'register', 'refresh');
        }


        $this->load->view('register/confirmation_page', $agent_data);
        $this->session->sess_destroy();
    }

    /**
     *
     * Register - Verify Account
     *
     */
    public function verify_account($step, $token1, $token2, $token3 = null)
    {
        if ((int) $step == 1 && !$this->session->utilihub_hub_registration_session) {
            $this->session->utilihub_hub_registration_session = session_id();
            $this->session->utilihub_hub_registration_data = [];
        } else {
            if (!$this->session->utilihub_hub_registration_session) {
                log_message('error', __FILE__ . ':' . __LINE__ . 'this->session->utilihub_hub_registration_session is not set');
                $this->load->view('errors/link_expired');
                $this->session->sess_destroy();
                return;
            }
        }


        //validate first
        $agent_id = $this->encryption->url_decrypt($token1);
        $password = $this->encryption->url_decrypt($token2);
        $internalSalesId = $this->encryption->url_decrypt($token3);
        if (!$agent_id || !$password) {
            $this->dashboard_user_model->log_audit_trail(null, "verify_account_failed", null);
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            log_message('error', __FILE__ . ':' . __LINE__ . 'agent_id=' . $agent_id . ', password=' . $password);
            return;
        }

        //note: register is for MANAGER accounts only
        //note: account shouldnt be confirmed/verified at this point
        $dataset = $this->dashboard_user_model->get_user_profile($agent_id);
        if ($dataset === false || count($dataset) <= 0 || $dataset['role'] != USER_MANAGER || (int) $dataset['active'] == STATUS_NG || (int) $dataset['confirmed'] == STATUS_OK || (int) $dataset['verified'] == STATUS_OK || crypt($password, $dataset['password']) != $dataset['password']) {
            $this->dashboard_user_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            log_message('error', __FILE__ . ':' . __LINE__ . ' role=' . $dataset['role'] . ' - invalid role');
            $this->session->sess_destroy();
            return;
        }

        if ($internalSalesId) {
            $tmp_data = $this->session->utilihub_hub_registration_data;
            $this->session->utilihub_hub_registration_data = array_merge($tmp_data, ['internal_sales_id' => $internalSalesId]);
        }

        $method = 'verify_step_' . $step;
        if (!method_exists($this, $method)) {
            $this->dashboard_user_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $this->{$method}($dataset, $token1, $token2);
    }

    protected function verify_step_1($dataset, $token1, $token2)
    {
        if (!$this->session->utilihub_hub_registration_session) {
            redirect('login', 'refresh');
        }

        if (count($this->input->post()) <= 0) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $view_data['lookup_industry'] = $this->form_lookup_library->partner_industries();
            $view_data['lookup_business_type'] = $this->form_lookup_library->partner_business_types();
            $view_data['lookup_partners_estimate'] = $this->form_lookup_library->reseller_partners_estimate();

            $this->load->view('register/verify_step1', $view_data);
            return;
        }

        $tmp_data = $this->session->utilihub_hub_registration_data;
        $this->session->utilihub_hub_registration_data = array_merge($tmp_data, $this->input->post());

        //ACTION
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
        $this->form_validation->set_rules('business_address', 'Business Address', 'trim|required');
        $this->form_validation->set_rules('business_phone', 'Business Phone', 'trim|required|numeric');

        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->config->item('mm8_country_code')) {
            case "AU":
                $this->form_validation->set_rules('business_abn', 'Business ABN', 'trim|numeric|exact_length[11]');
                break;
            case "NZ":
                $this->form_validation->set_rules('business_irdn', 'Business IRDN', 'trim|numeric|min_length[8]|max_length[9]');
                break;
            case "UK":
                $this->form_validation->set_rules('business_abn', 'Business CRN', 'trim|alpha_numeric|exact_length[8]');
                break;
            case "US":
            default:
                break;
        }

        if ($this->form_validation->run() == false) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $view_data['lookup_industry'] = $this->form_lookup_library->partner_industries();
            $view_data['lookup_business_type'] = $this->form_lookup_library->partner_business_types();
            $view_data['lookup_partners_estimate'] = $this->form_lookup_library->reseller_partners_estimate();
            $this->load->view('register/verify_step1', $view_data);
            return;
        }


        //NEXT: STEP 2
        //lets keep changing the tokens
        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
        redirect(base_url() . 'register/verify-account/2/' . $new_token1 . '/' . $new_token2);
    }

    protected function verify_step_2($dataset, $token1, $token2)
    {
        if (!$this->session->utilihub_hub_registration_session) {
            redirect('login', 'refresh');
        }

        //do we need to skip this page? lets find out
        if (!$this->session->has_userdata('utilihub_hub_registration_skip_administrator_step')) {
            $this->session->utilihub_hub_registration_skip_administrator_step = empty($dataset['full_name']) || empty($dataset['mobile_phone']) || empty($dataset['email']) ? false : true;
        }

        if ($this->session->utilihub_hub_registration_skip_administrator_step) {
            //NEXT STEP:
            //lets keep changing the tokens
            $next_step = (int) $this->input->get('parent') > 2 ? 1 : 3;
            $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
            $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
            redirect(base_url() . 'register/verify-account/' . $next_step . '/' . $new_token1 . '/' . $new_token2);
        }


        if (count($this->input->post()) <= 0) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;

            if (!isset($view_data['form_data']['first_name'])) {
                $view_data['form_data']['first_name'] = $dataset['first_name'];
            }
            if (!isset($view_data['form_data']['last_name'])) {
                $view_data['form_data']['last_name'] = $dataset['last_name'];
            }
            if (!isset($view_data['form_data']['user_email'])) {
                $view_data['form_data']['user_email'] = $dataset['email'];
            }
            if (!isset($view_data['form_data']['mobile_phone'])) {
                $view_data['form_data']['mobile_phone'] = $dataset['mobile_phone'];
            }

            $this->load->view('register/verify_step2', $view_data);
            return;
        }



        $tmp_data = $this->session->utilihub_hub_registration_data;
        $this->session->utilihub_hub_registration_data = array_merge($tmp_data, $this->input->post());

        //ACTION
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email|callback_change_user_email_available[' . $dataset['email'] . ']');

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
                $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|exact_length[10]');
                break;
            case "NZ":
                $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|min_length[9]|max_length[11]');
                break;
            case "UK":
                $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|exact_length[11]');
                break;
            default:
                break;
        }


        if ($this->form_validation->run() == false) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $this->load->view('register/verify_step2', $view_data);
            return;
        }


        //NEXT: STEP 3
        //lets keep changing the tokens
        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
        redirect(base_url() . 'register/verify-account/3/' . $new_token1 . '/' . $new_token2);
    }

    protected function verify_step_3($dataset, $token1, $token2)
    {
        if (!$this->session->utilihub_hub_registration_session) {
            redirect('login', 'refresh');
        }

        if (count($this->input->post()) <= 0) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $this->load->view('register/verify_step3', $view_data);
            return;
        }

        $tmp_data = $this->session->utilihub_hub_registration_data;
        $this->session->utilihub_hub_registration_data = array_merge($tmp_data, $this->input->post());

        //ACTION
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');

        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->config->item('mm8_country_code')) {
            case "AU":
                switch ((int) $this->input->post('payment_method')) {
                    case PAY_BY_DEBIT_MASTERCARD:
                        $this->form_validation->set_rules('user_abn', 'Administrator\'s ABN', 'trim|exact_length[11]');
                        $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                        break;
                    case PAY_BY_BANK_TRANSFER:
                        $this->form_validation->set_rules('user_abn', 'Administrator\'s ABN', 'trim|required|exact_length[11]');
                        $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                        $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                        $this->form_validation->set_rules('bank_bsb', 'BSB', 'trim|required|numeric|exact_length[6]');
                        break;
                    case PAY_BY_PAYPAL:
                        $this->form_validation->set_rules('user_abn', 'Administrator\'s ABN', 'trim|required|exact_length[11]');
                        $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                        break;
                    default:
                        $this->form_validation->set_rules('user_abn', 'Administrator\'s ABN', 'trim|exact_length[11]');
                        break;
                }
                break;
            case "NZ":
                switch ((int) $this->input->post('payment_method')) {
                    case PAY_BY_DEBIT_MASTERCARD:
                        $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                        break;
                    case PAY_BY_BANK_TRANSFER:
                        $this->form_validation->set_rules('user_irdn', 'Administrator\'s IRDN', 'trim|min_length[8]|max_length[9]');
                        $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                        $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                        break;
                    case PAY_BY_PAYPAL:
                        $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                        break;
                    default:
                        break;
                }
                break;
            case "US":
                switch ((int) $this->input->post('payment_method')) {
                    case PAY_BY_DEBIT_MASTERCARD:
                        $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                        break;
                    case PAY_BY_BANK_TRANSFER:
                        $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                        $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
                        $this->form_validation->set_rules('bank_routing_number', 'Routing Number', 'trim|required|numeric');
                        break;
                    case PAY_BY_PAYPAL:
                        $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                        break;
                    default:
                        break;
                }
                break;
            case "UK":
                switch ((int) $this->input->post('payment_method')) {
                    case PAY_BY_DEBIT_MASTERCARD:
                        $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                        break;
                    case PAY_BY_BANK_TRANSFER:
                        $this->form_validation->set_rules('user_crn', 'Administrator\'s CRN', 'trim|exact_length[8]');
                        $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                        $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                        $this->form_validation->set_rules('bank_sort_code', 'Sort Code', 'trim|required|numeric|exact_length[6]');
                        break;
                    case PAY_BY_PAYPAL:
                        $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }

        if ($this->form_validation->run() == false) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $this->load->view('register/verify_step3', $view_data);
            return;
        }


        //NEXT: STEP 4
        //lets keep changing the tokens
        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
        redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
    }

    protected function verify_step_4($dataset, $token1, $token2)
    {
        if (!$this->session->utilihub_hub_registration_session) {
            redirect('login', 'refresh');
        }

        //do we need to skip this page? lets find out
        if (!$this->session->has_userdata('utilihub_hub_registration_skip_password_step')) {
            $this->session->utilihub_hub_registration_skip_password_step = empty($dataset['google_user_id']) ? false : true;
        }

        if ($this->session->utilihub_hub_registration_skip_password_step) {
            //NEXT STEP: 5
            //lets keep changing the tokens
            $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
            $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
            redirect(base_url() . 'register/verify-account/5/' . $new_token1 . '/' . $new_token2);
        }


        if (count($this->input->post()) <= 0) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['show_password_meter'] = true;
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $this->load->view('register/verify_step4', $view_data);
            return;
        }



        $tmp_data = $this->session->utilihub_hub_registration_data;
        $this->session->utilihub_hub_registration_data = array_merge($tmp_data, $this->input->post());

        //ACTION
        $this->form_validation->set_message('matches', 'Passwords do not match');
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('user_password', 'Password', 'trim|required|max_length[128]|callback_check_password_complexity');
        $this->form_validation->set_rules('user_password_confirm', 'Password', 'trim|required|matches[user_password]');

        if ($this->form_validation->run() == false) {
            $view_data = ['dataset' => $dataset, 'token1' => $token1, 'token2' => $token2];
            $view_data['show_password_meter'] = true;
            $view_data['form_data'] = $this->session->utilihub_hub_registration_data;
            $this->load->view('register/verify_step4', $view_data);
            return;
        }


        //NEXT: STEP 5
        //lets keep changing the tokens
        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));
        redirect(base_url() . 'register/verify-account/5/' . $new_token1 . '/' . $new_token2);
    }

    protected function verify_step_5($dataset, $token1, $token2)
    {
        if (!$this->session->utilihub_hub_registration_session) {
            redirect('login', 'refresh');
        }

        $new_dataset = $this->session->utilihub_hub_registration_data;
        if (count($new_dataset) <= 0) {
            redirect('login', 'refresh');
        }

        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token1));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($token2));

        //START
        $this->db->trans_begin();

        //MANAGER AGENT
        $agent_data = [];
        $agent_data['active'] = STATUS_OK;
        $agent_data['verified'] = STATUS_OK;
        $agent_data['confirmed'] = STATUS_OK;
        $agent_data['date_confirmed'] = $this->database_tz_model->now();

        if (isset($new_dataset['first_name']) && !empty($new_dataset['first_name'])) {
            $agent_data['first_name'] = trim($new_dataset['first_name']);
        }

        if (isset($new_dataset['last_name']) && !empty($new_dataset['last_name'])) {
            $agent_data['last_name'] = trim($new_dataset['last_name']);
        }

        if (isset($agent_data['first_name']) && isset($agent_data['last_name'])) {
            $agent_data['full_name'] = $agent_data['first_name'] . " " . $agent_data['last_name'];
        }

        if (isset($new_dataset['user_email']) && !empty($new_dataset['user_email']) && $new_dataset['user_email'] != $dataset['email']) {
            $agent_data['email'] = $new_dataset['user_email'];

            //make sure email can be used
            if ($this->dashboard_user_model->user_email_used($agent_data['email'], USER_MANAGER)) {
                $this->db->trans_rollback();

                //REDIRECT TO STEP 2
                //make sure it doesnt skip
                $this->session->utilihub_hub_registration_skip_administrator_step = false;
                $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_600);
                redirect(base_url() . 'register/verify-account/2/' . $new_token1 . '/' . $new_token2);
            }
        }

        if (isset($new_dataset['mobile_phone']) && !empty($new_dataset['mobile_phone'])) {
            $agent_data['mobile_phone'] = $new_dataset['mobile_phone'];
        }

        if (isset($new_dataset['position']) && !empty($new_dataset['position'])) {
            $agent_data['position'] = $new_dataset['position'];
        }

        if (isset($new_dataset['user_password']) && !empty($new_dataset['user_password'])) {
            $agent_data['password'] = better_crypt($new_dataset['user_password']);
            $agent_data['last_password_reset'] = $this->database_tz_model->now();
            $agent_data['login_method'] = USER_LOGIN_BASICAUTH;
            $agent_data['google_user_id'] = null;
        }


        //payment method
        $agent_data['abn'] = isset($new_dataset['user_abn']) && $new_dataset['user_abn'] != "" ? $new_dataset['user_abn'] : null;
        $agent_data['irdn'] = isset($new_dataset['user_irdn']) && $new_dataset['user_irdn'] != "" ? $new_dataset['user_irdn'] : null;
        $agent_data['crn'] = isset($new_dataset['user_crn']) && $new_dataset['user_crn'] != "" ? $new_dataset['user_crn'] : null;

        if (isset($new_dataset['payment_method'])) {
            switch ((int) $new_dataset['payment_method']) {
                case PAY_BY_DEBIT_MASTERCARD:
                    $agent_data['payment_method'] = $new_dataset['payment_method'];
                    $agent_data['prepaid_mastercard_debit_address'] = $new_dataset['prepaid_mastercard_debit_address'];
                    break;
                case PAY_BY_BANK_TRANSFER:
                    /**
                     * CODE BRANCHING HERE - COUNTRY
                     *      AU
                     *      NZ
                     *      US
                     *      UK
                     */
                    switch ($this->config->item('mm8_country_code')) {
                        case "AU":
                            $agent_data['payment_method'] = $new_dataset['payment_method'];
                            $agent_data['bank_acc_name'] = $this->encryption->encrypt($new_dataset['bank_acc_name']);
                            $agent_data['bank_acc_no'] = $this->encryption->encrypt($new_dataset['bank_acc_no']);
                            $agent_data['bank_bsb'] = $this->encryption->encrypt($new_dataset['bank_bsb']);
                            break;
                        case "NZ":
                            $agent_data['payment_method'] = $new_dataset['payment_method'];
                            $agent_data['bank_acc_name'] = $this->encryption->encrypt($new_dataset['bank_acc_name']);
                            $agent_data['bank_acc_no'] = $this->encryption->encrypt($new_dataset['bank_acc_no']);
                        // no break
                        case "US":
                            $agent_data['payment_method'] = $new_dataset['payment_method'];
                            $agent_data['bank_acc_name'] = $this->encryption->encrypt($new_dataset['bank_acc_name']);
                            $agent_data['bank_acc_no'] = $this->encryption->encrypt($new_dataset['bank_acc_no']);
                            $agent_data['bank_name'] = $this->encryption->encrypt($new_dataset['bank_name']);
                            $agent_data['bank_routing_number'] = $this->encryption->encrypt($new_dataset['bank_routing_number']);
                            break;
                        case "UK":
                            $agent_data['payment_method'] = $new_dataset['payment_method'];
                            $agent_data['bank_acc_name'] = $this->encryption->encrypt($new_dataset['bank_acc_name']);
                            $agent_data['bank_acc_no'] = $this->encryption->encrypt($new_dataset['bank_acc_no']);
                            $agent_data['bank_sort_code'] = $this->encryption->encrypt($new_dataset['bank_sort_code']);
                            break;
                        default:
                            break;
                    }
                    break;
                case PAY_BY_PAYPAL:
                    $agent_data['payment_method'] = $new_dataset['payment_method'];
                    $agent_data['paypal_account'] = $new_dataset['paypal_account'];
                    break;
                case PAY_BY_SKIP:
                    $agent_data['payment_method'] = $new_dataset['payment_method'];
                    break;
                case PAY_BY_DEBIT_VISA:
                    $agent_data['payment_method'] = $new_dataset['payment_method'];
                    $agent_data['prepaid_visa_debit_address'] = $new_dataset['prepaid_visa_debit_address'];
                    break;
                default:
                    break;
            }
        }


        //update agent data
        if (!$this->dashboard_user_model->set_user_profile($agent_data, $dataset['id'])) {
            $this->db->trans_rollback();

            //REDIRECT TO STEP 4
            $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
            redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
        }

        $manager_id = $this->manager_model->map_manager_by_agent($dataset['id']);

        //MANAGER DETAILS
        $manager_data = [];
        $manager_data['referring_agent'] = $dataset['referring_agent'];
        $manager_data['sales_mgr_id'] = $this->config->item('hub_default_manager_sales_mgr_id');
        $manager_data['manager_agent'] = $dataset['id'];
        $manager_data['name'] = isset($new_dataset['business_name']) ? $new_dataset['business_name'] : "";
        $manager_data['industry'] = isset($new_dataset['business_industry']) ? $new_dataset['business_industry'] : null;
        $manager_data['business_type'] = isset($new_dataset['business_type']) ? $new_dataset['business_type'] : null;
        $manager_data['partners_estimate'] = isset($new_dataset['partners_estimate']) ? $new_dataset['partners_estimate'] : null;
        $manager_data['website_url'] = isset($new_dataset['business_website']) ? $new_dataset['business_website'] : null;
        $manager_data['contact_phone'] = isset($new_dataset['business_phone']) ? $new_dataset['business_phone'] : null;
        $manager_data['address'] = isset($new_dataset['business_address']) ? $new_dataset['business_address'] : null;
        $manager_data['abn'] = isset($new_dataset['business_abn']) ? $new_dataset['business_abn'] : null;
        $manager_data['irdn'] = isset($new_dataset['business_irdn']) ? $new_dataset['business_irdn'] : null;
        $manager_data['crn'] = isset($new_dataset['business_crn']) ? $new_dataset['business_crn'] : null;
        $manager_data['active'] = STATUS_OK;
        $manager_data['verified'] = STATUS_OK;

        //defaults
        $manager_data['hotline'] = $this->config->item('mm8_system_hotline');
        $manager_data['support_email'] = $this->config->item('mm8_system_support_email');
        $manager_data['ops_email'] = $this->config->item('mm8_system_ops_email');
        $manager_data['sms_sender'] = $this->config->item('hub_default_manager_sms_sender');
        $manager_data['feature_email'] = STATUS_OK;
        $manager_data['feature_quick_email'] = STATUS_OK;
        $manager_data['feature_sms'] = STATUS_OK;
        $manager_data['feature_quick_sms'] = STATUS_OK;
        $manager_data['feature_tagged_notes'] = STATUS_OK;
        $manager_data['nps_allowed'] = STATUS_OK;
        $manager_data['nps_widget_allowed'] = STATUS_OK;

        $manager_data['widget_theme'] = $this->config->item('hub_default_manager_widget_theme');
        $manager_data['widget_type'] = $this->config->item('hub_default_manager_widget_type');
        $manager_data['email_theme'] = $this->config->item('hub_default_manager_email_theme');
        $manager_data['email_banner'] = asset_url() . "img/default/partner-email-banner.jpg";
        $manager_data['img_url'] = asset_url() . "img/default/partner-logo.png";
        $manager_data['wallet_home_url'] = null;

        $manager_data['sla_template'] = $this->config->item('hub_default_partner_sla_template');

        $manager_data['default_manager_share'] = $this->config->item('hub_default_manager_commission');
        $manager_data['default_partner_share'] = $this->config->item('hub_default_partner_commission');
        $manager_data['default_agent_share'] = $this->config->item('hub_default_agent_commission');

        if (!$manager_id) {
            // manager does not exists
            $manager_data['manager_code'] = "";
            while (true) {
                $manager_data['manager_code'] = strtolower(random_string('alnum', 5));
                if ($this->manager_model->map_manager($manager_data['manager_code'], false) === false) {
                    break;
                }
            }

            // Refer and Earn. Get referrer and assign referrer's assigned IS/ES
            $managerAgent = $this->dashboard_user_model->get_user_profile($dataset['id']);
            if ($managerAgent) {
                // should always an ES
                $filter = [
                    'referee_partner_agent_id' => $managerAgent['id'],
                ];
                $referal = $this->account_manager_refer_and_earn_referrals_model->fetch($filter, [], 1);
                if ($referal) {
                    $click = $this->account_manager_refer_and_earn_clicks_model->getById($referal[0]->click_id);
                    if ($click) {
                        $referrerProfile = $this->dashboard_user_model->get_user_profile($click->referrer_partner_agent_id);
                        if ($referrerProfile || count($referrerProfile) > 0) {
                            $externalSales = $this->account_manager_model->getByAccountManagerAgent($referrerProfile['id']);
                            if ($externalSales) {
                                $associatedIS = $this->account_manager_association_model->getByExternalSales($externalSales->id);
                                if ($associatedIS) {
                                    // IS
                                    if ($associatedIS->internal_sales) {
                                        $internalSales = $this->account_manager_model->getById($associatedIS->internal_sales);
                                        if ($internalSales) {
                                            $manager_data['internal_account_manager_id'] = $internalSales->id;
                                        }
                                    }

                                    $manager_data['external_account_manager_id'] = $externalSales->id;
                                }
                            }
                        }
                    }
                }
            }

            $manager_id = $this->manager_model->set_manager_info($manager_data);
            if ($manager_id === false || (int) $manager_id <= 0) {
                $this->db->trans_rollback();

                //REDIRECT TO STEP 4
                $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
                redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
            }

            //whitelist manager to default team(1)
            $default_team = 1;
            if ($this->config->item('mm8_system_prefix') == "origin") {
                $default_team = 200;
            }
            if (!$this->crm_user_model->add_team_managers([['team_id' => $default_team, 'reseller_id' => $manager_id]])) {
                $this->db->trans_rollback();

                //REDIRECT TO STEP 4
                $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
                redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
            }

            // Refer and Earn. Get assigned IS/ES and notify
            $manager = $this->manager_model->get_manager_info($manager_id);
            if ($manager) {
                $managerAgent = $this->dashboard_user_model->get_user_profile($manager['manager_agent']);
                if ($managerAgent) {
                    if (isset($internalSales)) {
                        $internalSalesAgent = $this->dashboard_user_model->get_user_profile($internalSales->account_manager_agent);
                        if ($internalSalesAgent) {
                            $this->_emailNotifyAssignedAccountManager($manager, $managerAgent, $internalSalesAgent['email']);
                        }

                        // add remote access
                        $this->grant_temporary_remote_access($manager_data['internal_account_manager_id'], $manager_id);
                    }

                    if (isset($externalSales)) {
                        $externalSalesAgent = $this->dashboard_user_model->get_user_profile($externalSales->account_manager_agent);
                        if ($externalSalesAgent) {
                            $this->_emailNotifyAssignedAccountManager($manager, $managerAgent, $externalSalesAgent['email']);
                        }
                    }
                }
            }

            // Customer Portal V2
            $this->customer_portal_v2_library->managerDefaults($manager_id);
        } else {
            //whitelist manager to default team(1)
            if (!$this->crm_user_model->add_team_managers([['team_id' => '1', 'reseller_id' => $manager_id]])) {
                $this->db->trans_rollback();

                //REDIRECT TO STEP 4
                $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
                redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
            }

            // manager already exists
            $manager_id = $this->manager_model->set_manager_info($manager_data, $manager_id);
            if ($manager_id === false || (int) $manager_id <= 0) {
                $this->db->trans_rollback();

                //REDIRECT TO STEP 4
                $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
                redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
            }
        }

        // Customer Portal V2
        $this->customer_portal_v2_library->managerDefaults($manager_id);

        if (isset($this->session->utilihub_hub_registration_data['internal_sales_id'])) {
            $accountManager = $this->account_manager_model->getById($this->session->utilihub_hub_registration_data['internal_sales_id']);
            if ($accountManager) {
                $agentIS = $this->dashboard_user_model->get_user_profile($accountManager->account_manager_agent);
                if ($agentIS) {
                    $filter = [
                        'internal_account_manager_id' => $accountManager->id,
                        'reseller_id' => $manager_id,
                    ];
                    $limit = 1;
                    $accountManagerPermissions = $this->account_manager_permissions_model->fetch($filter, [], $limit);
                    if (count($accountManagerPermissions) > 0) {
                        $hours = !is_null($this->config->item('ams_default_hours_to_admin_access')) ? intval($this->config->item('ams_default_hours_to_admin_access')) : 72;
                        $date_expires = date("Y-m-d H:i:s", strtotime($this->database_tz_model->now() . " +$hours hours"));

                        $data = [
                            'id' => $accountManagerPermissions[0]->id,
                            'date_expires' => $date_expires,
                        ];
                        $permission_id = $this->account_manager_permissions_model->save($data);
                        if (!$permission_id) {
                            $this->db->trans_rollback();

                            //REDIRECT TO STEP 4
                            $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
                            redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
                        }
                    }
                }
            }
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            //REDIRECT TO STEP 4
            $this->session->set_flashdata('error_message', 'There was an error finalising your registration: ' . ERROR_502);
            redirect(base_url() . 'register/verify-account/4/' . $new_token1 . '/' . $new_token2);
        }

        $this->db->trans_commit();

        //SUCCESS!!
        //REDIRECT TO LOGIN
        $this->session->sess_destroy();

        if (isset($new_dataset['user_password']) && !empty($new_dataset['user_password'])) {
            $new_token2 = $this->encryption->url_encrypt($new_dataset['user_password']);
        }

        if (isset($this->session->utilihub_hub_registration_data['internal_sales_id'])) {
            $accountManager = $this->account_manager_model->getById($this->session->utilihub_hub_registration_data['internal_sales_id']);
            if ($accountManager) {
                $agentIS = $this->dashboard_user_model->get_user_profile($accountManager->account_manager_agent);
                if ($agentIS) {
                    $new_token3 = $this->encryption->url_encrypt($accountManager->id);
                    redirect(base_url() . 'login/register-account-verified/' . $new_token1 . '/' . $new_token2 . '/' . $new_token3);
                }
            }
        }

        redirect(base_url() . 'login/register-account-verified/' . $new_token1 . '/' . $new_token2);
    }

    public function ajax_complete_address()
    {
        $term = $this->input->post('term');
        echo $this->kleber_datatools->search_address($term, 4);
    }

    public function ajax_parse_address()
    {
        echo $this->kleber_datatools->retrieve_address($this->input->post('id'), $this->input->post('value'));
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

    public function change_user_email_available($email, $original_email)
    {
        if ($email != $original_email && $this->dashboard_user_model->user_email_used($email, USER_MANAGER)) {
            $this->form_validation->set_message('change_user_email_available', ERROR_600);
            return false;
        } else {
            return true;
        }
    }

    public function check_password_complexity($password)
    {
        $lengthOk = strlen($password) >= $this->config->item('mm8_system_password_length') ? true : false;
        $containsLower = preg_match('/[a-z]/', $password);
        $containsUpper = preg_match('/[A-Z]/', $password);
        $containsDigit = preg_match('/\d/', $password);
        $containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

        if ($lengthOk && $containsLower && $containsUpper && ($containsDigit || $containsSpecial)) {
            return true;
        } else {
            $this->form_validation->set_message('check_password_complexity', 'The password does not meet the password policy requirements.');
            return false;
        }
    }

    private function _emailNotifyAssignedAccountManager($manager, $managerAgent, $emailTo)
    {
        $template = $this->communications_model->get_email_template('ams_email_notification_assigned_admin');
        if ($template) {
            $search_for = [
                "[RESELLER_CODE]",
                "[RESELLER_NAME]",
                "[RESELLER_FULL_NAME]",
                "[RESELLER_EMAIL]",
                "[URL]",
            ];
            $replace_with = [
                $manager['manager_code'],
                $manager['name'],
                $managerAgent['full_name'],
                $managerAgent['email'],
                $this->config->item('mhub_ams_url') . "manager/manager-view/" . $manager['id'],
            ];

            $html_template = str_replace($search_for, $replace_with, $template['html_template']);
            $text_template = str_replace($search_for, $replace_with, $template['text_template']);

            //queue email
            $email_dataset = [];
            $email_dataset['category_id'] = EMAIL_SUBSCRIPTION_REPORTS;
            $email_dataset['from'] = $this->config->item('mm8_system_noreply_email');
            $email_dataset['from_name'] = $this->config->item('mm8_system_name');
            $email_dataset['to'] = $emailTo;
            $email_dataset['subject'] = $template['subject'];
            $email_dataset['html_message'] = $this->load->view('html_email/basic_mail', ['contents' => $html_template], true);
            $email_dataset['text_message'] = $text_template;
            $this->communications_model->queue_email($email_dataset);
        }
    }

    public function user_email_blacklisted($email)
    {
        $result = is_email_blacklisted($email);
        if (isset($result[$email]) && $result[$email] === true) {
            $this->form_validation->set_message('user_email_blacklisted', ERROR_614);
            return false;
        } else {
            return true;
        }
    }

    private function grant_temporary_remote_access($internal_account_manager_id, $reseller_id)
    {
        $filter = [
            'internal_account_manager_id' => $internal_account_manager_id,
            'reseller_id' => $reseller_id,
        ];
        $order = [
            'date_added DESC',
        ];
        $limit = 1;
        $existingIsPermission = $this->account_manager_permissions_model->fetch($filter, $order, $limit);
        if (count($existingIsPermission) <= 0) {
            // possibly new IS, carry over permission

            $filter = [
                'reseller_id' => $reseller_id,
            ];
            $order = [
                'date_added DESC',
            ];
            $limit = 1;
            $previousIsPermission = $this->account_manager_permissions_model->fetch($filter, $order, $limit);
            if (count($previousIsPermission) > 0) {
                // carry over previous permission
                $remote_access_new_data['internal_account_manager_id'] = $internal_account_manager_id;
                $remote_access_new_data['reseller_id'] = $reseller_id;
                $remote_access_new_data['is_granted'] = $previousIsPermission[0]->is_granted;
                $remote_access_new_data['is_notify_every_login'] = $previousIsPermission[0]->is_notify_every_login;
                $remote_access_new_data['date_expires'] = $previousIsPermission[0]->date_expires;

                $this->account_manager_permissions_model->save($remote_access_new_data);
            } else {
                // first time assign IS
                $hours = !is_null($this->config->item('ams_default_hours_to_admin_access')) ? intval($this->config->item('ams_default_hours_to_admin_access')) : 72;
                $date_expires = date("Y-m-d H:i:s", strtotime($this->database_tz_model->now() . " +$hours hours"));

                $remote_access_new_data['internal_account_manager_id'] = $internal_account_manager_id;
                $remote_access_new_data['reseller_id'] = $reseller_id;
                $remote_access_new_data['is_granted'] = STATUS_NG;
                $remote_access_new_data['is_notify_every_login'] = STATUS_NG;
                $remote_access_new_data['date_expires'] = $date_expires;

                $this->account_manager_permissions_model->save($remote_access_new_data);
            }
        }
    }

    public function continue_manager_registration_on_behalf($token1, $token2)
    {
        $manager_id = $this->encryption->url_decrypt($token1);
        $internalSalesId = $this->encryption->url_decrypt($token2);
        if (empty($manager_id) || empty($internalSalesId)) {
            redirect(base_url() . "register");
        }

        $manager = $this->manager_model->get_manager_info($manager_id);
        if (count($manager) <= 0) {
            redirect(base_url() . "register");
        }

        $agent = $this->dashboard_user_model->get_user_profile($manager['manager_agent']);
        if (!$agent) {
            redirect(base_url() . "register");
        }

        $internalSales = $this->account_manager_model->getById($internalSalesId);
        if (!$internalSales) {
            redirect(base_url() . "register");
        }

        if (!$internalSales->is_role_internal_sales) {
            redirect(base_url() . "register");
        }

        $internalSalesAgent = $this->dashboard_user_model->get_user_profile($internalSales->account_manager_agent);
        if (!$internalSalesAgent) {
            redirect(base_url() . "register");
        }

        if ($internalSales->id != $manager['internal_account_manager_id']) {
            redirect(base_url() . "manager");
        }

        // generate password
        $agent_password = random_string('alnum', $this->config->item('mm8_system_password_length'));
        $agent_data = [];
        $agent_data['password'] = better_crypt($agent_password);
        $agent_id = $this->dashboard_user_model->set_user_profile($agent_data, $agent['id']);
        if ($agent_id === false || $agent_id === -1) {
            $this->db->trans_rollback();
            redirect(base_url() . "manager");
        }

        $token1 = $this->encryption->url_encrypt($agent['id']);
        $token2 = $this->encryption->url_encrypt($agent_password);
        $token3 = $this->encryption->url_encrypt($internalSales->id);
        $url_link = $this->config->item('mhub_hub_url') . "register/verify-account/1/" . $token1 . "/" . $token2 . "/" . $token3;
        redirect($url_link);
    }
}
