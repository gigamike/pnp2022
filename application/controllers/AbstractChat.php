<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

abstract class AbstractChat extends CI_Controller
{
    protected $_theme = 'blue';

    protected $_chatbotAttributes = [
        'first_name' =>  'Tili',
        'last_name' =>  null,
        'email' => null,
        'photo' =>  'img/connect-sd/chatbot1.jpeg',
    ];
    protected $_guestAttributes = [
        'first_name' =>  'Guest',
        'last_name' =>  null,
        'email' => null,
        'photo' =>  'img/connect-sd/guest1.jpg',
    ];

    protected $_ticketRelativeDir = 'uploads/connect_sd/tickets';
    protected $_ticketAbsoluteDir = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('users_model');
        $this->load->model('typeform_sessions_model');
        $this->load->model('merchants_model');
        $this->load->model('communications_model');
        $this->load->model('tickets_model');
        $this->load->model('ticket_replies_model');
        $this->load->model('ticket_reply_attachments_model');
        $this->load->model('ticket_activities_model');

        $this->load->helper('time_elapsed');
        $this->load->helper('utility_helper');

        $this->load->library('email_library');
        $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_private_bucket')], 'aws_s3_library_private');
       
        $this->_ticketAbsoluteDir = FCPATH . $this->_ticketRelativeDir;

        $this->_chatbotAttributes = $this->config->item('mm8_connect_sd_chatbot');
    }

    protected function _createTypeformSession()
    {
        $this->db->trans_begin();

        // create typeform session
        $data = [
            'profile_photo' => asset_url() . $this->_guestAttributes['photo'],
            'state' => 1,
            'ip' => get_ip(),
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);

        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return false;
        }

        $typeformSession = $this->typeform_sessions_model->getById($typeformSessionId);

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return  $typeformSession;
    }

    public function ajax_typeform()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();

        $typeformSessionCode = isset($dataset['typeformSessionCode']) ? trim($dataset['typeformSessionCode']) : null;
        $currentState = isset($dataset['currentState']) ? trim($dataset['currentState']) : 1;
        $rowCount = isset($dataset['rowCount']) ? intval(trim($dataset['rowCount'])) : 0;

        if (!is_numeric($rowCount)) {
            $rowCount = 0;
        }
        $rowCount++;
        
        if (!is_numeric($currentState)) {
            $currentState = 1;
        }

        if (empty($typeformSessionCode)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $typeformSession = $this->typeform_sessions_model->getByUCode($typeformSessionCode);
        if (!$typeformSession) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $view_data = [];
        $view_data['typeformSession'] = $typeformSession;
        $view_data['chatbotAttributes'] = $this->_chatbotAttributes;
        $view_data['rowCount'] = $rowCount;
        $view_data['currentState'] = $currentState;

        if ($currentState == 1) {
            // KB
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 2;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 2) {
            $results = $this->_answerKB($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            $kbId = trim($dataset['kbId']);
            switch ($kbId) {
                case 1:
                    // What is Online Business Registry?
                    $view_data['title'] = 'What products are prohibited to sell online?';
                    $view_data['content'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. To learn more please visit <a href='https://www.dti.gov.ph/' target='_blank'>https://www.dti.gov.ph/</a>. Does this helps you?";

                    $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

                    $currentState = 3;

                    break;
                case 2:
                    // Try our Website reputation checker
                    $view_data['title'] = 'Website reputation checker';
                    $view_data['content'] = "Please open this page for website reputation checker <a href='" . base_url() . "website-reputation-checker' target='_blank'>" . base_url() . "website-reputation-checker</a>. Does this helps you?";

                    $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

                    $currentState = 3;

                    break;
                case 3:
                    // I want a complaint report now!
                    $view_data['currentState'] = 4;

                    $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

                    $currentState = 5;
                    break;
                default:
            }

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 3) {
            // KB Help yes or no?
           
            $results = $this->_answerKBHelp($dataset);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            $isKBHelp = trim($dataset['isKBHelp']);

            $view_data['isKBHelp'] = $isKBHelp;
            
            if ($isKBHelp == 'yes') {
                $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

                $currentState = 2;
            } else {
                $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

                $currentState = 2;
            }

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 4) {
            // please enter you name
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 5;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 5) {
            $results = $this->_answerName($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }
            
            // question email
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);
           
            $currentState = 6;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 6) {
            $results = $this->_answerEmail($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            // mobile phone
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 7;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 7) {
            $results = $this->_answerMobilePhone($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            // ticket subject
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 8;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 8) {
            $results = $this->_answerTicketSubject($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            // ticket body
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 9;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 9) {
            $results = $this->_answerTicketBody($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            // Ask attachment
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 10;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 10) {
            $dataset = [
                'typeform_session_id' => $typeformSession->id,
                'first_name' => $typeformSession->first_name,
                'last_name' => $typeformSession->last_name,
                'email' => $typeformSession->email,
                'mobile_phone' => $typeformSession->mobile_phone,
                'subject' => $typeformSession->ticket_subject,
                'body' => $typeformSession->ticket_body,
                'profile_photo' => !empty($typeformSession->profile_photo) ? $typeformSession->profile_photo :  asset_url() . $this->_guestAttributes['photo'],
            ];
            $results = $this->_createTicket($dataset);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 11;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 11) {
            $results = $this->_answerUnionbankAccount($dataset, $typeformSession);
            if (!$results['successful']) {
                echo json_encode([
                    'successful' => false,
                    'error' => $results['error'],
                ]);
                return;
            }

            $data = [
                'id' => $typeformSession->ticket_id,
                'unionbank_account' => trim($dataset['unionbank_account']),
            ];
            $merchant_id = $this->tickets_model->save($data);
            if (!$merchant_id) {
                $this->db->trans_rollback();
                return [
                    'successful' => false,
                    'error' => 'Typeform session update failed! (ERROR_502)',
                ];
            }

            // Done
            $html = $this->load->view("chatbot/chat/typeform_state", $view_data, true);

            $currentState = 12;

            echo json_encode([
                'successful' => true,
                'currentState' => $currentState,
                'rowCount' => $rowCount,
                'html' => $html,
            ]);
            return;
        } elseif ($currentState == 12) {
        }
    }

    /*
    * Validate answers
    *
     */
    protected function _answerName($dataset, $typeformSession)
    {
        $firstName = isset($dataset['firstName']) ? trim($dataset['firstName']) : '';
        $lastName = isset($dataset['lastName']) ? trim($dataset['lastName']) : '';

        if ($firstName == "") {
            return [
                'successful' => false,
                'error' => 'Required field first name.',
            ];
        }

        if ($lastName == "") {
            return [
                'successful' => false,
                'error' => 'Required field last name.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerEmail($dataset, $typeformSession)
    {
        $email = isset($dataset['email']) ? trim($dataset['email']) : '';
        if ($email=="") {
            return [
                'successful' => false,
                'error' => 'Required field email.',
            ];
        }

        $email = strtolower($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'successful' => false,
                'error' => 'Invalid email.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'email' => $email,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerKB($dataset)
    {
        $kbId = isset($dataset['kbId']) ? trim($dataset['kbId']) : '';
        if ($kbId=="") {
            return [
                'successful' => false,
                'error' => 'Invalid answer.',
            ];
        }

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerKBHelp($dataset)
    {
        $isKBHelp = isset($dataset['isKBHelp']) ? trim($dataset['isKBHelp']) : '';
        if ($isKBHelp=="") {
            return [
                'successful' => false,
                'error' => 'Invalid answer.',
            ];
        }

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerAnythingElse($dataset)
    {
        $isAnythingElse = isset($dataset['isAnythingElse']) ? trim($dataset['isAnythingElse']) : '';
        if ($isAnythingElse=="") {
            return [
                'successful' => false,
                'error' => 'Invalid answer.',
            ];
        }

        return [
            'successful' => true,
        ];
    }

    protected function _answerUnionbankAccount($dataset, $typeformSession)
    {
        $unionbank_account = isset($dataset['unionbank_account']) ? trim($dataset['unionbank_account']) : '';

        if ($unionbank_account != "") {
            $this->db->trans_begin();

            $data = [
                'id' => $typeformSession->id,
                'unionbank_account' => $unionbank_account,
            ];
            $typeformSessionId = $this->typeform_sessions_model->save($data);
            if (!$typeformSessionId) {
                $this->db->trans_rollback();
                return [
                    'successful' => false,
                    'error' => 'Typeform session update failed! (ERROR_502)',
                ];
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo [
                    'successful' => false,
                    'error' => 'Typeform session update failed! (ERROR_502)',
                ];
            }

            $this->db->trans_commit();
        }

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerTicketSubject($dataset, $typeformSession)
    {
        $ticketSubject = isset($dataset['ticketSubject']) ? trim($dataset['ticketSubject']) : '';
        if ($ticketSubject=="") {
            return [
                'successful' => false,
                'error' => 'Required field problem overview.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'ticket_subject' => $ticketSubject,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerTicketBody($dataset, $typeformSession)
    {
        $ticketBody = isset($dataset['ticketBody']) ? trim($dataset['ticketBody']) : '';
        if ($ticketBody=="") {
            return [
                'successful' => false,
                'error' => 'Required field problem description.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'ticket_body' => $ticketBody,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerMobilePhone($dataset, $typeformSession)
    {
        $mobile_phone = isset($dataset['mobile_phone']) ? trim($dataset['mobile_phone']) : '';
        if ($mobile_phone=="") {
            return [
                'successful' => false,
                'error' => 'Required field mobile number.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'mobile_phone' => $mobile_phone,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerProduct($dataset, $typeformSession)
    {
        $product = isset($dataset['product']) ? trim($dataset['product']) : '';
        if ($product=="") {
            return [
                'successful' => false,
                'error' => 'Required field product.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'product' => $product,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
        * Validate answers
        *
         */
    protected function _answerProductPhoto($dataset, $typeformSession)
    {
        if (!isset($_FILES['productAttachments'])) {
            return [
                'successful' => false,
                'error' => 'Required field product photo.',
            ];
        }

        return [
            'successful' => true,
        ];
    }
    /*
    * Validate answers
    *
     */
    protected function _answerAddress($dataset, $typeformSession)
    {
        $address = isset($dataset['address']) ? trim($dataset['address']) : '';
        if ($address=="") {
            return [
                'successful' => false,
                'error' => 'Required field address.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'address' => $address,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerCallbackDate($dataset, $typeformSession)
    {
        $callbackDate = isset($dataset['callbackDate']) ? trim($dataset['callbackDate']) : '';
        if ($callbackDate == "") {
            return [
                'successful' => false,
                'error' => 'Required field callback date.',
            ];
        }

        $callbackDate = reformat_str_date($callbackDate, $this->config->item('mm8_php_default_date_format'), 'Y-m-d');
      
        if (empty($callbackDate)) {
            return [
                'successful' => false,
                'error' => 'Required field callback date.',
            ];
        }

        list($year, $month, $day) = explode('-', $callbackDate);
        if (!checkdate($month, $day, $year)) {
            return [
                'successful' => false,
                'error' => 'Invalid callback date.',
            ];
        }

        if ($callbackDate <= date('Y-m-d', strtotime($this->database_tz_model->now()))) {
            return [
                'successful' => false,
                'error' => 'Invalid callback date. Move date should be in future.',
            ];
        }

        if ($this->_isHoliday($callbackDate . " 00:00:00")) {
            return [
                'successful' => false,
                'error' => 'Invalid callback date. Date selected is a holiday.',
            ];
        }

        if (!$this->_isBusinessDays($callbackDate)) {
            return [
                'successful' => false,
                'error' => 'Invalid callback date. Date selected is out of business hours.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'callback_schedule' => $callbackDate,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Validate answers
    *
     */
    protected function _answerCallbackTime($dataset, $typeformSession)
    {
        $callbackTime = isset($dataset['callbackTime']) ? trim($dataset['callbackTime']) : '';
        if ($callbackTime == "") {
            return [
                'successful' => false,
                'error' => 'Required field callback time.',
            ];
        }

        $this->db->trans_begin();

        $data = [
            'id' => $typeformSession->id,
            'callback_schedule' => date('Y-m-d', strtotime($typeformSession->callback_schedule)) . " " . $callbackTime,
        ];
        $typeformSessionId = $this->typeform_sessions_model->save($data);
        if (!$typeformSessionId) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo [
                'successful' => false,
                'error' => 'Typeform session update failed! (ERROR_502)',
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
        ];
    }

    /*
    * Public pages
    *
     */
    public function ajax_connect_chat_or_ticket()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $typeformSessionCode = isset($dataset['typeformSessionCode']) ? trim($dataset['typeformSessionCode']) : null;
        
        if (empty($typeformSessionCode)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $typeformSession = $this->typeform_sessions_model->getByReferenceCode($typeformSessionCode);
        if (!$typeformSession) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $filter = [
            'active' => STATUS_OK,
            'status' => CONNECT_SD_USER_STATUS_ONLINE,
            'date_last_online_is_less_than_minutes' => 5,
        ];
        $fields = [
            'id',
            'first_name',
            'last_name',
            'email',
            'profile_photo',
        ];
        $order = [
            'date_last_online DESC',
        ];
        $connectSDAgents = $this->connect_sd_users_model->fetch($filter, $order, null, null, $fields);
        if (count($connectSDAgents) > 0) {
            if ($typeformSession->user_type == CONNECT_SD_USER_TYPE_GUEST) {
                $filter = [
                    'typeform_session_id' => $typeformSession->id,
                ];
                $chatChannel = $this->connect_sd_chat_channels_model->fetch($filter, [], 1);
                if ($chatChannel) {
                    $chatChannelId = $chatChannel[0]->id;
                } else {
                    $this->db->trans_begin();

                    $data = [
                        'typeform_session_id' => $typeformSession->id,
                        'user_type' => $typeformSession->user_type,
                        'app_user_id' => $typeformSession->app_user_id,
                        'app' => $this->_app,
                        'application_id' => $typeformSession->application_id,
                        'status' => CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT,
                        'first_name' => $typeformSession->first_name,
                        'last_name' => $typeformSession->last_name,
                        'email' =>  $typeformSession->email,
                        'profile_photo' => !empty($typeformSession->profile_photo) ? $typeformSession->profile_photo :  asset_url() . $this->connect_sd_library->_guestAttributes['photo'],
                        'ip' => get_ip(),
                    ];

                    $chatChannelId = $this->connect_sd_chat_channels_model->save($data);
                    if (!$chatChannelId) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            'successful' => false,
                            'error' => "Chat channel update failed! (ERROR_502)",
                        ]);
                        return;
                    }

                    //COMMIT
                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            'successful' => false,
                            'error' => "Chat message update failed! (ERROR_502)",
                        ]);
                        return;
                    }

                    $this->db->trans_commit();
                }
            } else {
                $chatChannel = connect_sd_get_chat_channel(
                    $typeformSession->user_type,
                    $typeformSession->app_user_id,
                    null
                );
                if ($chatChannel) {
                    $chatChannelId = $chatChannel->id;
                } else {
                    $this->db->trans_begin();

                    $data = [
                        'typeform_session_id' => $typeformSession->id,
                        'user_type' => $typeformSession->user_type,
                        'app_user_id' => $typeformSession->app_user_id,
                        'app' => $this->_app,
                        'application_id' => $typeformSession->application_id,
                        'status' => CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT,
                        'first_name' => $typeformSession->first_name,
                        'last_name' => $typeformSession->last_name,
                        'email' =>  $typeformSession->email,
                        'profile_photo' => !empty($typeformSession->profile_photo) ? $typeformSession->profile_photo :  asset_url() . $this->connect_sd_library->_guestAttributes['photo'],
                        'ip' => get_ip(),
                    ];

                    $filter = [
                        'user_type' => $typeformSession->user_type,
                        'app_user_id' => $typeformSession->app_user_id,
                        'parent_id_is_null' => true,
                    ];
                    $chatChannel = $this->connect_sd_chat_channels_model->fetch($filter, [], 1);
                    if ($chatChannel) {
                        $data['parent_id'] = $chatChannel[0]->id;
                    }

                    $chatChannelId = $this->connect_sd_chat_channels_model->save($data);
                    if (!$chatChannelId) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            'successful' => false,
                            'error' => "Chat channel update failed! (ERROR_502)",
                        ]);
                        return;
                    }

                    //COMMIT
                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            'successful' => false,
                            'error' => "Chat message update failed! (ERROR_502)",
                        ]);
                        return;
                    }

                    $this->db->trans_commit();
                }
            }

            $chatChannel = $this->connect_sd_chat_channels_model->getById($chatChannelId);
            if (!$chatChannel) {
                echo json_encode([
                    'successful' => false,
                    'error' => "Invalid chat channel",
                ]);
                return;
            }

            // we dont want to keep sending push notification
            if (is_null($chatChannel->date_push_notification)) {
                $beamsClient = new \Pusher\PushNotifications\PushNotifications([
                    "instanceId" => $this->config->item('mm8_connect_sd_pusher_beam_instance_id'),
                    "secretKey" => $this->config->item('mm8_connect_sd_pusher_beam_primary_key'),
                ]);
                $publishResponse = $beamsClient->publishToInterests(
                    [
                        "connect-sd-chat"
                    ],
                    ["web" =>
                        [
                            "notification" => [
                                "title" => "Chat " . $chatChannel->reference_code,
                                "body" => "New chat from chat chanel " . $chatChannel->reference_code,
                                "deep_link" => $this->config->item('mhub_connect_sd_url') . "chat?chat_channel_id=" . $chatChannel->reference_code,
                            ]
                        ],
                    ]
                );

                $this->db->trans_begin();

                $data = [
                    'id' => $chatChannel->id,
                    'date_push_notification' => $this->database_tz_model->now(),
                ];

                $chatChannelId = $this->connect_sd_chat_channels_model->save($data);
                if (!$chatChannelId) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat channel update failed! (ERROR_502)",
                    ]);
                    return;
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat message update failed! (ERROR_502)",
                    ]);
                    return;
                }

                $this->db->trans_commit();
            }

            if (!empty($chatChannel->assignee_connect_sd_user_id)) {
                // agent assigned
                // redirect to agent
                echo json_encode([
                    'successful' => true,
                    'url' => base_url() . "chatbot/agent/" . $chatChannel->reference_code,
                ]);
                return;
            } else {
                $timeDiff = strtotime($this->database_tz_model->now()) - strtotime($chatChannel->date_added);
                if ($timeDiff >= $this->config->item('mm8_connect_sd_chat_to_ticket_threshold_in_seconds')) {
                    // nobody is anserwing
                    $filter = [
                        'chat_channel_id' => $chatChannel->id,
                        'connect_sd_user_id_null' => true,
                    ];
                    $countMessage = $this->connect_sd_chat_messages_model->getCount($filter);
                    if ($countMessage <= 0) {
                        // delete channel, nobody is answering
                        $this->connect_sd_chat_channels_model->delete($chatChannel->id);

                        // create ticket
                        switch ($chatType) {
                            case 'customer':
                                echo json_encode([
                                    'successful' => true,
                                    'is_ticket' => true,
                                    'state' => 19,
                                ]);
                                return;
                                break;
                            case 'partner':
                                echo json_encode([
                                    'successful' => true,
                                    'is_ticket' => true,
                                    'state' => 7,
                                ]);
                                return;

                                break;
                            case 'affiliate':
                                echo json_encode([
                                    'successful' => true,
                                    'is_ticket' => true,
                                    'state' => 7,
                                ]);
                                return;

                                break;
                            case 'provider':
                                echo json_encode([
                                    'successful' => true,
                                    'is_ticket' => true,
                                    'state' => 7,
                                ]);
                                return;

                                break;
                            default:
                                echo json_encode([
                                    'successful' => false,
                                    'error' => 'Invalid Typeform Session.',
                                ]);
                                return;

                                break;
                        }
                    }
                }
                echo json_encode([
                    'successful' => true,
                    'timeDiff' => $timeDiff,
                ]);
                return;
            }
        } else {
            // No agents, create ticket
            
            switch ($chatType) {
                case 'customer':
                    echo json_encode([
                        'successful' => true,
                        'is_ticket' => true,
                        'state' => 19,
                    ]);
                    return;
                    break;
                case 'partner':
                    echo json_encode([
                        'successful' => true,
                        'is_ticket' => true,
                        'state' => 7,
                    ]);
                    return;
                    break;
                case 'affiliate':
                    echo json_encode([
                        'successful' => true,
                        'is_ticket' => true,
                        'state' => 7,
                    ]);
                    return;
                    break;
                case 'provider':
                    echo json_encode([
                        'successful' => true,
                        'is_ticket' => true,
                        'state' => 7,
                    ]);
                    return;
                    break;
                default:
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Typeform Session.',
                    ]);
                    return;

                    break;
            }
        }
        
        echo json_encode([
            'successful' => true,
        ]);
        return;
    }

    /*
    *
    * Customer
    *
     */
    public function ajax_connect_chat_or_callback()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $typeformSessionCode = isset($dataset['typeformSessionCode']) ? trim($dataset['typeformSessionCode']) : null;
        
        if (empty($typeformSessionCode)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $typeformSession = $this->typeform_sessions_model->getByReferenceCode($typeformSessionCode);
        if (!$typeformSession) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Typeform Session.',
            ]);
            return;
        }

        $chatType = isset($dataset['chatType']) ? trim($dataset['chatType']) : null;

        switch ($chatType) {
            case 'customer':
                break;
            case 'partner':
                break;
            case 'affiliate':
                break;
            case 'provider':
                break;
            default:
                echo json_encode([
                    'successful' => false,
                    'error' => 'Invalid Typeform Session.',
                ]);
                return;

                break;
        }

        $filter = [
            'active' => STATUS_OK,
            'status' => CONNECT_SD_USER_STATUS_ONLINE,
            'date_last_online_is_less_than_minutes' => 5,
        ];
        $fields = [
            'id',
            'first_name',
            'last_name',
            'email',
            'profile_photo',
        ];
        $order = [
            'date_last_online DESC',
        ];
        $connectSDAgents = $this->connect_sd_users_model->fetch($filter, $order, null, null, $fields);
        if (count($connectSDAgents) > 0) {
            $filter = [
                'typeform_session_id' => $typeformSession->id,
            ];
            $chatChannel = $this->connect_sd_chat_channels_model->fetch($filter, [], 1);
            if ($chatChannel) {
                $chatChannelId = $chatChannel[0]->id;
            } else {
                $this->db->trans_begin();

                $data = [
                    'typeform_session_id' => $typeformSession->id,
                    'user_type' => $typeformSession->user_type,
                    'app_user_id' => $typeformSession->app_user_id,
                    'app' => $this->_app,
                    'user_type' => $typeformSession->user_type,
                    'application_id' => $typeformSession->application_id,
                    'status' => CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT,
                    'first_name' => $typeformSession->first_name,
                    'last_name' => $typeformSession->last_name,
                    'email' =>  $typeformSession->email,
                    'profile_photo' => !empty($typeformSession->profile_photo) ? $typeformSession->profile_photo :  asset_url() . $this->connect_sd_library->_guestAttributes['photo'],
                    'ip' => get_ip(),
                ];

                $chatChannelId = $this->connect_sd_chat_channels_model->save($data);
                if (!$chatChannelId) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat channel update failed! (ERROR_502)",
                    ]);
                    return;
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat message update failed! (ERROR_502)",
                    ]);
                    return;
                }

                $this->db->trans_commit();
            }
        
            $chatChannel = $this->connect_sd_chat_channels_model->getById($chatChannelId);
            if (!$chatChannel) {
                echo json_encode([
                    'successful' => false,
                    'error' => "Invalid chat channel",
                ]);
                return;
            }

            // we dont want to keep sending push notification
            if (is_null($chatChannel->date_push_notification)) {
                $beamsClient = new \Pusher\PushNotifications\PushNotifications([
                    "instanceId" => $this->config->item('mm8_connect_sd_pusher_beam_instance_id'),
                    "secretKey" => $this->config->item('mm8_connect_sd_pusher_beam_primary_key'),
                ]);
                $publishResponse = $beamsClient->publishToInterests(
                    [
                        "connect-sd-chat"
                    ],
                    ["web" =>
                        [
                            "notification" => [
                                "title" => "Chat " . $chatChannel->reference_code,
                                "body" => "New chat from chat chanel " . $chatChannel->reference_code,
                                "deep_link" => $this->config->item('mhub_connect_sd_url') . "chat?chat_channel_id=" . $chatChannel->reference_code,
                            ]
                        ],
                    ]
                );

                $this->db->trans_begin();

                $data = [
                    'id' => $chatChannel->id,
                    'date_push_notification' => $this->database_tz_model->now(),
                ];

                $chatChannelId = $this->connect_sd_chat_channels_model->save($data);
                if (!$chatChannelId) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat channel update failed! (ERROR_502)",
                    ]);
                    return;
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'successful' => false,
                        'error' => "Chat message update failed! (ERROR_502)",
                    ]);
                    return;
                }

                $this->db->trans_commit();
            }

            if (!empty($chatChannel->assignee_connect_sd_user_id)) {
                // agent assigned
                // redirect to agent
                echo json_encode([
                    'successful' => true,
                    'url' => base_url() . "chatbot/agent/" . $chatChannel->reference_code,
                ]);
                return;
            } else {
                $timeDiff = strtotime($this->database_tz_model->now()) - strtotime($chatChannel->date_added);
                if ($timeDiff >= $this->config->item('mm8_connect_sd_chat_to_ticket_threshold_in_seconds')) {
                    // nobody is anserwing
                    $filter = [
                        'chat_channel_id' => $chatChannel->id,
                        'connect_sd_user_id_null' => true,
                    ];
                    $countMessage = $this->connect_sd_chat_messages_model->getCount($filter);
                    if ($countMessage <= 0) {
                        // delete channel, nobody is answering
                        $this->connect_sd_chat_channels_model->delete($chatChannel->id);

                        // callback
              
                        echo json_encode([
                            'successful' => true,
                            'is_callback' => true,
                            'state' => 13, // callback
                        ]);
                        return;
                    }
                }

                echo json_encode([
                    'successful' => true,
                    'timeDiff' => $timeDiff,
                ]);
                return;
            }
        } else {
            // No agents, callback

            echo json_encode([
                'successful' => true,
                'is_callback' => true,
                'state' => 13, // callback
            ]);
            return;
        }
        
        echo json_encode([
            'successful' => true,
        ]);
        return;
    }

    public function agent($chatChannel = null)
    {
        if (empty($chatChannel)) {
            $this->load->view('connect_sd/error_page', ['error' => ERROR_408]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            $this->load->view('connect_sd/error_page', ['error' => ERROR_408]);
            return;
        }
        if ($chatChannel->status == CONNECT_SD_CHAT_CHANNEL_STATUS_ARCHIVE) {
            $this->load->view('connect_sd/error_page', ['error' => ERROR_408]);
            return;
        }

        $this->db->trans_begin();

        $data = [
            'id' => $chatChannel->id,
            'customer_date_online' => $this->database_tz_model->now(),
        ];
        $chat_channel_id = $this->connect_sd_chat_channels_model->save($data);
        if (!$chat_channel_id) {
            $this->db->trans_rollback();
            $this->load->view('connect_sd/error_page', ['error' => ERROR_408]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();


        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['theme'] = $this->_theme;
        $view_data['styles'] = [];
        $view_data['scripts'] = [];

        if ($chatChannel->status == CONNECT_SD_CHAT_CHANNEL_STATUS_ARCHIVE) {
        } else {
            $view_data['scripts'][] = asset_url() . "js/connect-sd/chat-agent.js";
        }

        $view_data['chatChannelDetails'] = $chatChannel;
        $view_data['chatChannel'] = $chatChannel->reference_code;

        $this->load->view('connect_sd/header', $view_data);
        $this->load->view('chatbot/chat_agent', $view_data);
        $this->load->view('connect_sd/footer', $view_data);
    }

    public function ajax_get_chat_agent_messages()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $chatChannel = isset($dataset['chatChannel']) ? trim($dataset['chatChannel']) : null;
      
        if (empty($chatChannel)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $view_data = [];

        $chatMessageAttachments = [];
        $chatMessagesDisplayLimit = $this->config->item('mm8_connect_sd_chat_messages_display_limit');
        
        $filter = [
            'chat_channel_id' => $chatChannel->id,
        ];
        $countChatMessages = $this->connect_sd_chat_messages_model->getCount($filter);
        $limit = null;
        $start = null;
        if ($countChatMessages >= $chatMessagesDisplayLimit) {
            // do not load all, heavy
            $limit = $chatMessagesDisplayLimit;
            $numberOfPage = ceil($countChatMessages / $limit);
            $start = ($numberOfPage * $limit) - $limit;
        }
        $order = [
            'date_added',
        ];
        $chatMessages = $this->connect_sd_chat_messages_model->fetch($filter, $order, $limit, $start);
        if (count($chatMessages) > 0) {
            foreach ($chatMessages as $chatMessage) {
                // message from internal
                if ($chatMessage->connect_sd_user_id) {
                    $filter = [
                        'chat_message_id' => $chatMessage->id,
                        'connect_sd_user_id_is_null' => true,
                    ];
                    $countMessageRead = $this->connect_sd_chat_message_read_model->getCount($filter);
                    if (!$countMessageRead) {
                        $data = [
                            'chat_message_id' => $chatMessage->id,
                            'connect_sd_user_id' => null,
                        ];
                        $this->connect_sd_chat_message_read_model->save($data);
                    }
                }

                $filter = [
                    'chat_message_id' => $chatMessage->id,
                ];
                $order = [
                    'date_added',
                ];
                $attachments = $this->connect_sd_chat_message_attachments_model->fetch($filter, $order);
                $chatMessageAttachments[$chatMessage->id] = $attachments;
            }
        }
        $view_data['chatMessages'] = $chatMessages;
        $view_data['chatMessageAttachments'] = $chatMessageAttachments;

        $html = $this->load->view('chatbot/chat_agent_messages', $view_data, true);

        echo json_encode([
            'successful' => true,
            'html' => $html,
            'status' => $chatChannel->status,
        ]);
        return;
    }

    public function ajax_download_file_exists()
    {
        header('Content-Type: application/json;');
       
        $download_uri = $this->input->post('download_uri');
        $download_uri = urldecode($download_uri);
        $download_uri = str_replace($this->config->item('mm8_aws_private_bucket') . "/", "", $download_uri);
        $exists = $this->aws_s3_library_private->does_object_exists($download_uri);
        if ($exists) {
            echo json_encode(['exists' => true]);
            return;
        } else {
            echo json_encode(['exists' => false]);
            return;
        }
    }

    public function force_download()
    {
        header('Content-Type: application/json;');
      
        $this->load->helper('download');
        $download_uri = $this->input->post('download_uri');
        $download_uri = urldecode($download_uri);
        $download_uri = str_replace($this->config->item('mm8_aws_private_bucket') . "/", "", $download_uri);
        if (ENVIRONMENT == "production") {
            $exists = $this->aws_s3_library_private->does_object_exists($download_uri);
            if ($exists) {
                //echo basename($download_uri);
                $s3_file_data = $this->aws_s3_library_private->read_s3_stream($download_uri);
                force_download(basename($download_uri), $s3_file_data);
            }
        } else {
            $file_url = FCPATH . $download_uri;
            force_download($file_url, null);
        }
    }

    public function ajax_chat_agent_message_save()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $chatChannel = isset($dataset['chatChannel']) ? trim($dataset['chatChannel']) : null;
        $message = isset($dataset['message']) ? trim($dataset['message']) : null;
    
        if (empty($chatChannel)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        if ($message=='') {
            echo json_encode(['successful' => false, 'error' => "Required Field: Message"]);
            return;
        }

        $this->db->trans_begin();

        $data = [
            'chat_channel_id' => $chatChannel->id,
            'connect_sd_user_id' => $this->session->utilihub_connect_sd_user_id,
            'first_name' => $chatChannel->first_name,
            'last_name' => $chatChannel->last_name,
            'email' => $chatChannel->email,
            'profile_photo' => !empty($chatChannel->profile_photo) ? $chatChannel->profile_photo :  asset_url() . $this->connect_sd_library->_guestAttributes['photo'],
            'message' => $message,
        ];
        $message_id = $this->connect_sd_chat_messages_model->save($data);
        if (!$message_id) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        // might needed in the future
        //process file uploads
        $attachments = [];
        if (isset($_FILES['attachments']) && count($_FILES['attachments']['name']) > 0) {
            foreach ($_FILES['attachments']['name'] as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $attachment = $this->process_multiple_input_file($chatChannel->id, $message_id, 'attachments', $k);
                if ($attachment['successful'] && isset($attachment['url']) && !empty($attachment['url'])) {
                    $data = [];
                    $data['chat_message_id'] = $message_id;
                    $data['url'] = $attachment['url'];
                    $data['file_name'] = $v;

                    $chat_message_attachment_id = $this->connect_sd_chat_message_attachments_model->save($data);
                    if (!$chat_message_attachment_id) {
                        $this->db->trans_rollback();
                        echo json_encode(['successful' => false, 'error' => ERROR_502]);
                        return false;
                    }

                    $attachments[] = [
                        'url' => $attachment['url'],
                        'file_name' => $v,
                        'tmp_name' => $_FILES['attachments']['tmp_name'][$k],
                        'uploaded_file' => $attachment['uploaded_file'],
                    ];
                } else {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => $attachment['error']]);
                    return;
                }
            }
        }

        $data = [
            'id' => $chatChannel->id,
            'status' => CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT,
            'is_timeout_warning' => STATUS_NG,
            'customer_date_online' => $this->database_tz_model->now(),
        ];
        $chat_channel_id = $this->connect_sd_chat_channels_model->save($data);
        if (!$chat_channel_id) {
            $this->db->trans_rollback();
            echo json_encode([
                'successful' => false,
                'error' => "Chat channel update failed! (ERROR_502)",
            ]);
            return;
        }

        $options = [
            'cluster' => $this->config->item('mm8_connect_sd_pusher_cluster'),
            'useTLS' => true
        ];
        $pusher = new Pusher\Pusher(
            $this->config->item('mm8_connect_sd_pusher_key'),
            $this->config->item('mm8_connect_sd_pusher_secret'),
            $this->config->item('mm8_connect_sd_pusher_app_id'),
            $options
        );

        $data['message'] = $message;
        $data=[]; // do not submit any data as per compliance
        $pusher->trigger($this->config->item('mm8_country_iso_code') . "-" . $chatChannel->reference_code, 'chat-event', $data);

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode([
            'successful' => true,
        ]);
        return;
    }

    public function ajax_chat_agent_check_idle()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $chatChannel = isset($dataset['chatChannel']) ? trim($dataset['chatChannel']) : null;
      
        if (empty($chatChannel)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        if ($chatChannel->status == CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_CUSTOMER) {
            // we need this, user refreshes and creates duplicate message
            if (!$chatChannel->is_timeout_warning) {
                $filter = [
                    'chat_channel_id' => $chatChannel->id,
                    'connect_sd_user_id_null' => true,
                    'date_added_is_less_than_seconds' => $this->config->item('mm8_connect_sd_chat_idle_threshold_in_seconds'),
                ];
                $countMessage = $this->connect_sd_chat_messages_model->getCount($filter);
                if ($countMessage <= 0) {
                    if ($chatChannel->assignee_connect_sd_user_id) {
                        $agent = $this->connect_sd_users_model->getById($chatChannel->assignee_connect_sd_user_id);
                        if ($agent) {
                            $message = "Are you still there [CUSTOMER_FIRST_NAME]?";
                            $search = [
                                '[CUSTOMER_FIRST_NAME]',
                                '[CUSTOMER_LAST_NAME]',
                            ];
                            $replace = [
                                $chatChannel->first_name,
                                $chatChannel->last_name,
                            ];
                            $message = str_replace($search, $replace, $message);

                            $this->db->trans_begin();

                            $data = [
                                'chat_channel_id' => $chatChannel->id,
                                'connect_sd_user_id' => $chatChannel->assignee_connect_sd_user_id,
                                'first_name' => $agent->first_name,
                                'last_name' => $agent->last_name,
                                'email' => $agent->email,
                                'profile_photo' => $agent->profile_photo,
                                'message' => $message,
                            ];
                            $chat_message_id = $this->connect_sd_chat_messages_model->save($data);
                            if (!$chat_message_id) {
                                $this->db->trans_rollback();
                                echo json_encode([
                                    'successful' => false,
                                    'is_idle' => true
                                ]);
                                return;
                            }

                            $data = [
                                'id' => $chatChannel->id,
                                'is_timeout_warning' => STATUS_OK,
                            ];
                            $chat_channel_id = $this->connect_sd_chat_channels_model->save($data);
                            if (!$chat_channel_id) {
                                $this->db->trans_rollback();
                                echo json_encode([
                                    'successful' => false,
                                    'error' => "Chat channel update failed! (ERROR_502)",
                                ]);
                                return;
                            }

                            $options = [
                                'cluster' => $this->config->item('mm8_connect_sd_pusher_cluster'),
                                'useTLS' => true
                            ];
                            $pusher = new Pusher\Pusher(
                                $this->config->item('mm8_connect_sd_pusher_key'),
                                $this->config->item('mm8_connect_sd_pusher_secret'),
                                $this->config->item('mm8_connect_sd_pusher_app_id'),
                                $options
                            );

                            $data['message'] = $message;
                            $data=[]; // do not submit any data as per compliance
                            $pusher->trigger($this->config->item('mm8_country_iso_code') . "-" . $chatChannel->reference_code, 'chat-event', $data);

                            //COMMIT
                            if ($this->db->trans_status() === false) {
                                $this->db->trans_rollback();
                                echo json_encode([
                                    'successful' => false,
                                    'is_idle' => true
                                ]);
                            }

                            $this->db->trans_commit();
                        }
                    }
                }
            }
        }

        echo json_encode([
            'successful' => true,
            'is_idle' => true
        ]);
        return;
    }

    public function ajax_get_chat_timeout()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $chatChannel = isset($dataset['chatChannel']) ? trim($dataset['chatChannel']) : null;
      
        if (empty($chatChannel)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $this->db->trans_begin();

        $results = $this->connect_sd_library->chatTimeOut($chatChannel->id);
        if (!$results['successful'] && isset($results['error'])) {
            $this->db->trans_rollback();

            echo json_encode([
                'successful' => false,
                'is_idle' => true
            ]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            
            echo json_encode([
                'successful' => false,
                'is_idle' => true
            ]);
            return;
        }

        $this->db->trans_commit();

        // redirect to end chat
        echo json_encode([
            'successful' => true,
        ]);
    }

    public function ajax_chat_agent_customer_online_update()
    {
        header('Content-Type: application/json;');

        $dataset = $this->input->post();
        $chatChannel = isset($dataset['chatChannel']) ? trim($dataset['chatChannel']) : null;
      
        if (empty($chatChannel)) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $chatChannel = $this->connect_sd_chat_channels_model->getByReferenceCode($chatChannel);
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Chat Channel.',
            ]);
            return;
        }

        $this->db->trans_begin();
        
        $data = [
            'id' => $chatChannel->id,
            'customer_date_online' => $this->database_tz_model->now(),
        ];
        $chat_channel_id = $this->connect_sd_chat_channels_model->save($data);
        if (!$chat_channel_id) {
            $this->db->trans_rollback();
            echo json_encode([
                'successful' => false,
                'error' => "Chat channel update failed! (ERROR_502)",
            ]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        $this->db->trans_commit();

        // redirect to end chat
        echo json_encode([
            'successful' => true,
        ]);
    }

    /*
    *
    * Common functions
    *
     */
    
    protected function _createMerchant($dataset)
    {
        //ADD NEW
        //START
        $this->db->trans_begin();

        $data = [];
        $data['typeform_session_id'] = $dataset['typeform_session_id'];
        $data['first_name'] = $dataset['first_name'];
        $data['last_name'] = $dataset['last_name'];
        $data['email'] = $dataset['email'];
        $data['mobile_phone'] = $dataset['mobile_phone'];
        $data['address'] = $dataset['address'];
        $data['product'] = $dataset['product'];
        $data['profile_photo'] = $dataset['profile_photo'];
        $merchant_id = $this->merchants_model->save($data);
        if (!$merchant_id) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        //process file uploads
        if (isset($_FILES['productAttachments']) && count($_FILES['productAttachments']['name']) > 0) {
            foreach ($_FILES['productAttachments']['name'] as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $attachment = $this->process_multiple_input_file($merchant_id, $merchant_id, 'productAttachments', $k);
                if ($attachment['successful'] && isset($attachment['url']) && !empty($attachment['url'])) {
                    $data = [];
                    $data['id'] = $merchant_id;
                    $data['product_photo'] = $attachment['url'];
                    $data['product_photo_file_name'] = $v;

                    $merchant_id = $this->merchants_model->save($data);
                    if (!$merchant_id) {
                        $this->db->trans_rollback();
                        return[
                            'successful' => false,
                            'error' => ERROR_502,
                        ];
                    }
                } else {
                    $this->db->trans_rollback();
                    return[
                        'successful' => false,
                        'error' => $attachment['error'],
                    ];
                }
            }
        }

        $merchant = $this->merchants_model->getById($merchant_id);
        if ($merchant) {
            $data = [
                'id' => $dataset['typeform_session_id'],
                'merchant_id' => $merchant_id,
            ];
            $typeformSessionId = $this->typeform_sessions_model->save($data);
            if (!$typeformSessionId) {
                $this->db->trans_rollback();
                return [
                    'successful' => false,
                    'error' => 'Typeform session update failed! (ERROR_502)',
                ];
            }

            // email notification
            //generate message
            $template = $this->communications_model->get_email_template('merchant_registration_verification');
            if (!$template) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_401]);
                return;
            }

            $search_for = [
                "[FIRSTNAME]",
                "[URL]",
            ];
            $replace_with = [
                base_url() . "verification/email/" . $merchant->u_code,
                $merchant->first_name,
            ];
            $subject = str_replace($search_for, $replace_with, $template['subject']);

            $html_template = str_replace($search_for, $replace_with, $template['html_template']);
            $text_template = str_replace($search_for, $replace_with, $template['text_template']);

            $email_dataset = [];
            $email_dataset['from'] = $this->config->item('mm8_system_noreply_email');
            $email_dataset['from_name'] = $this->config->item('mm8_system_name');
            $email_dataset['to'] = $merchant->email;
            $email_dataset['reply_to'] = $this->config->item('mm8_system_noreply_email');
            $email_dataset['subject'] = $subject;
            $email_dataset['html_message'] = $this->load->view('html_email/basic_mail', ['contents' => $html_template], true);
            $email_dataset['text_message'] = $text_template;

            if ($this->communications_model->queue_email($email_dataset) === false) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_406]);
                return;
            }

            $template = $this->communications_model->get_sms_template('merchant_registration_verification');
            if (!$template) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_401]);
                return;
            }

            $search_for = [
                "[FIRSTNAME]",
                "[URL]",
            ];
            $replace_with = [
                base_url() . "verification/sms/" . $merchant->u_code,
                $merchant->first_name,
            ];
            $template = str_replace($search_for, $replace_with, $template);

            // sms notification
            $credentials = new Credentials($this->config->item('mm8_aws_access_key_id'), $this->config->item('mm8_aws_secret_access_key'));

            $snSclient = new SnsClient([
                'region' => $this->config->item('mm8_aws_region'),
                'version' => '2010-03-31',
                'credentials' => $credentials,
            ]);

            $message = $template;
            $phone = $merchant->mobile_phone;

            /*
            try {
                $result = $snSclient->publish([
                    'Message' => $message,
                    'PhoneNumber' => $phone,
                ]);
                // var_dump($result);
            } catch (AwsException $e) {
                // output error message if fails
                error_log($e->getMessage());
            }
            */
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
            'merchant_id' => $merchant_id,
        ];
    }

    protected function _createTicket($dataset)
    {
        //ADD NEW
        //START
        $this->db->trans_begin();

        $new_ticket = [];
        $new_ticket['typeform_session_id'] = $dataset['typeform_session_id'];
        $new_ticket['first_name'] = $dataset['first_name'];
        $new_ticket['last_name'] = $dataset['last_name'];
        $new_ticket['email'] = $dataset['email'];
        $new_ticket['mobile_phone'] = $dataset['mobile_phone'];
        $new_ticket['profile_photo'] = $dataset['profile_photo'];
        $new_ticket['subject'] = $dataset['subject'];
        $new_ticket['status'] = CONNECT_SD_TICKET_STATUS_OPEN;
        $new_ticket['urgency'] = CONNECT_SD_TICKET_URGENCY_LOW;
        $new_ticket['impact'] = CONNECT_SD_TICKET_IMPACT_LOW;
        $new_ticket['priority'] = CONNECT_SD_TICKET_PRIORITY_LOW;

        $ticket_id = $this->tickets_model->save($new_ticket);
        if ($ticket_id === false || $ticket_id === -1) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        $reply = [];
        $reply['ticket_id'] = $ticket_id;
        $reply['first_name'] = $dataset['first_name'];
        $reply['last_name'] = $dataset['last_name'];
        $reply['email'] = $dataset['email'];
        $reply['profile_photo'] = $dataset['profile_photo'];
        $reply['body'] = $dataset['body'];
        $ticket_reply_id = $this->ticket_replies_model->save($reply);
        if (!$ticket_reply_id) {
            $this->db->trans_rollback();
            return[
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        //process file uploads
        if (isset($_FILES['ticketAttachments']) && count($_FILES['ticketAttachments']['name']) > 0) {
            foreach ($_FILES['ticketAttachments']['name'] as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $attachment = $this->process_multiple_input_file($ticket_id, $ticket_reply_id, 'ticketAttachments', $k);
                if ($attachment['successful'] && isset($attachment['url']) && !empty($attachment['url'])) {
                    $data = [];
                    $data['ticket_reply_id'] = $ticket_reply_id;
                    $data['url'] = $attachment['url'];
                    $data['file_name'] = $v;

                    $ticket_attachment_id = $this->ticket_reply_attachments_model->save($data);
                    if (!$ticket_attachment_id) {
                        $this->db->trans_rollback();
                        return[
                            'successful' => false,
                            'error' => ERROR_502,
                        ];
                    }
                } else {
                    $this->db->trans_rollback();
                    return[
                        'successful' => false,
                        'error' => $attachment['error'],
                    ];
                }
            }
        }

        $ticketActivity = [
            'ticket_id' => $ticket_id,
            'first_name' => $dataset['first_name'],
            'last_name' => $dataset['last_name'],
            'email' => $dataset['email'],
            'profile_photo' => $dataset['profile_photo'],
            'activity' => 'Ticket created',
        ];
        $ticket_activity_id = $this->ticket_activities_model->save($ticketActivity);
        if (!$ticket_activity_id) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        $ticket = $this->tickets_model->getById($ticket_id);
        if ($ticket) {
            $data = [
                'id' => $dataset['typeform_session_id'],
                'ticket_id' => $ticket_id,
            ];
            $typeformSessionId = $this->typeform_sessions_model->save($data);
            if (!$typeformSessionId) {
                $this->db->trans_rollback();
                return [
                    'successful' => false,
                    'error' => 'Typeform session update failed! (ERROR_502)',
                ];
            }

            /*
            // email notification
            $filter = [
                'active' => STATUS_OK,
            ];
            $recipients = $this->users_model->fetch($filter);
            if (count($recipients) > 0) {
                //generate message
                $template = $this->communications_model->get_email_template('connect_sd_new_ticket_notification');
                if (!$template) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => ERROR_401]);
                    return;
                }

                $search_for = [
                    "[TICKET URL]",
                    "[TICKET REFERENCE CODE]",
                    "[TICKET REQUESTOR FIRST NAME]",
                    "[TICKET REQUESTOR LAST NAME]",
                    "[TICKET REQUESTOR NAME]",
                    "[TICKET REQUESTOR EMAIL]",
                    "[TICKET SUBJECT]",
                    "[TICKET DESCRIPTION]",
                    "[TICKET CATEGORY]",
                    "[TICKET STATUS]",
                    "[TICKET URGENCY]",
                    "[TICKET IMPACT]",
                    "[TICKET PRIORITY]",
                ];
                $replace_with = [
                    base_url() . "tickets/view/" . $ticket->reference_code,
                    $ticket->reference_code,
                    $ticket->first_name,
                    $ticket->last_name,
                    $ticket->first_name . " " . $ticket->last_name,
                    $ticket->email,
                    $ticket->subject,
                    $dataset['body'],
                    $ticket_category[0]->name,
                    $this->config->item('mm8_connect_sd_ticket_status')[$ticket->status],
                    $this->config->item('mm8_connect_sd_ticket_urgency')[$ticket->urgency],
                    $this->config->item('mm8_connect_sd_ticket_impact')[$ticket->impact],
                    $this->config->item('mm8_connect_sd_ticket_priority')[$ticket->priority],
                ];
                $subject = str_replace($search_for, $replace_with, $template['subject']);
                $subject = $this->email_library->connectTicketSubject($ticket, $subject);

                $html_template = str_replace($search_for, $replace_with, $template['html_template']);
                $text_template = str_replace($search_for, $replace_with, $template['text_template']);

                $tempRecipients = [];
                foreach ($recipients as $recipient) {
                    $tempRecipients[$recipient->email] = $recipient->email;
                }

                $email_prefix = ENVIRONMENT == "production" ? $this->config->item('mm8_connect_default_email_prod_prefix') : $this->config->item('mm8_connect_default_email_dev_prefix');
                $replyTo = $email_prefix . "@" . $this->config->item('mm8_connect_default_email_domain');

                $email_dataset = [];
                $email_dataset['category_id'] = EMAIL_SUBSCRIPTION_REPORTS;
                $email_dataset['from'] = $this->config->item('mm8_system_noreply_email');
                $email_dataset['from_name'] = $this->config->item('mm8_system_name');
                $email_dataset['to'] = implode(',', $tempRecipients);
                $email_dataset['reply_to'] = $replyTo;
                $email_dataset['subject'] = $subject;
                $email_dataset['html_message'] = $this->load->view('html_email/basic_mail', ['contents' => $html_template], true);
                $email_dataset['text_message'] = $text_template;

                if ($this->communications_model->queue_email($email_dataset) === false) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => ERROR_406]);
                    return;
                }
            }
            */
        }
        

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'successful' => false,
                'error' => ERROR_502,
            ];
        }

        $this->db->trans_commit();

        return [
            'successful' => true,
            'ticket_id' => $ticket_id,
        ];
    }

    protected function _isHoliday($datetime)
    {
        $currentDateFormatted = reformat_str_date($datetime, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format'));
     
        $holidays = $this->lookup_model->get_holidays($currentDateFormatted, $currentDateFormatted);
        if (count($holidays) > 0) {
            return true;
        }
        
        return false;
    }

    protected function _isBusinessHours($datetime)
    {
        $day = date('w', strtotime($datetime));
        if (!in_array($day, $this->config->item('mm8_call_support_dotw_disabled'))) {
            $start = $this->config->item('mm8_call_support_times')[$day]['start'];
            $end = $this->config->item('mm8_call_support_times')[$day]['end'];

            $tempStart = date("Y-m-d " . $start, strtotime($datetime));
            $tempEnd = date("Y-m-d " . $end, strtotime($datetime));

            if ($datetime >= $tempStart && $datetime <= $tempEnd) {
                return true;
            }
        }

        return false;
    }

    protected function _isBusinessDays($datetime)
    {
        $day = date('w', strtotime($datetime));

        if (!in_array($day, $this->config->item('mm8_call_support_dotw_disabled'))) {
            return true;
        }

        return false;
    }

    protected function _callbackTimeSlots($callbackDate)
    {
        $reference_date = date_create_from_format($this->config->item('mm8_php_default_date_format'), $callbackDate);
        $reference_date_dotw = $reference_date->format("w");

        $tmp_date_now = $this->database_tz_model->now($this->config->item('mm8_php_default_date_format'));
        $adjust_today = false;
        $build_timeslots = true;

        $tmp_start = $this->config->item('mm8_call_support_times')[$reference_date_dotw]['start'];
        $tmp_end = $this->config->item('mm8_call_support_times')[$reference_date_dotw]['end'];

        if ($tmp_date_now == $reference_date->format($this->config->item('mm8_php_default_date_format'))) {
            //same day
            //whats the current time? round up to next allowed interval (mm8_call_support_interval_mins)
            $current_time = strtotime($this->database_tz_model->now('H:i:00'));
            $frac = 60 * $this->config->item('mm8_call_support_interval_mins');
            $r = $current_time % $frac;
            $tmp_now = date('H:i:s', ($current_time + ($frac - $r)));

            if (strtotime($tmp_now) >= strtotime($tmp_start) && strtotime($tmp_now) < strtotime($tmp_end)) {
                //time is middle of the timslots, well adjust later
                $tmp_start = $tmp_now;
                $adjust_today = true;
            } elseif (strtotime($tmp_now) >= strtotime($tmp_end)) {
                //its past the time, return empty
                $build_timeslots = false;
            }
        }


        $dataset = [];

        if ($build_timeslots) {
            $timeslots = ((strtotime($tmp_end) - strtotime($tmp_start)) / 60) / $this->config->item('mm8_call_support_interval_mins');
            $current_slot_start = $tmp_start;
            $current_slot_end = date('H:i:s', strtotime("+" . ($this->config->item('mm8_call_support_interval_mins') - 1) . " minutes", strtotime($current_slot_start)));

            for ($i = 0; $i < $timeslots; $i++) {
                if ($adjust_today && $i < $this->config->item('mm8_call_support_minimum_buffer_today_slots')) {
                    $current_slot_start = date('H:i:s', strtotime("+1 minutes", strtotime($current_slot_end)));
                    $current_slot_end = date('H:i:s', strtotime("+" . ($this->config->item('mm8_call_support_interval_mins') - 1) . " minutes", strtotime($current_slot_start)));
                    continue;
                }

                array_push($dataset, ['k' => $current_slot_start, 'v' => $current_slot_start . ' - ' . $current_slot_end]);

                $current_slot_start = date('H:i:s', strtotime("+1 minutes", strtotime($current_slot_end)));
                $current_slot_end = date('H:i:s', strtotime("+" . ($this->config->item('mm8_call_support_interval_mins') - 1) . " minutes", strtotime($current_slot_start)));
            }
        }

        return $dataset;
    }

    /*
    *
    * Utilities
    *
     */
    protected function process_multiple_input_file($id, $ticket_reply_id, $fileId, $fileKey)
    {
        if (isset($_FILES[$fileId]['tmp_name'][$fileKey]) && file_exists($_FILES[$fileId]['tmp_name'][$fileKey])) {
            $file_mime_type = mime_content_type($_FILES[$fileId]['tmp_name'][$fileKey]);

            $allowedFileTypes = [
                // Images
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/gif',
            ];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                return ['successful' => false, 'error' => "Invalid file type. Make sure the image is either a JPEG, PNG or GIF."];
            }

            //check file size
            if (filesize($_FILES[$fileId]['tmp_name'][$fileKey]) > 2000000) {
                return ['successful' => false, 'error' => "File too large. Make sure the image is not more than 2 MB."];
            }

            //generate random filename
            $img_file = getRandomAlphaNum() . "." . pathinfo($_FILES[$fileId]['name'][$fileKey], PATHINFO_EXTENSION); //date("dHis") . '-' . pathinfo($_FILES[$fileId]['name'][$fileKey], PATHINFO_BASENAME);
            $tmp_file = $this->_ticketAbsoluteDir . "/" . $id . "/" . $ticket_reply_id . "/" . $img_file;

            if (!file_exists($this->_ticketAbsoluteDir)) {
                $oldumask = umask(0);
                mkdir($this->_ticketAbsoluteDir, 0775, true);
                umask($oldumask);

                if (!file_exists($this->_ticketAbsoluteDir)) {
                    echo json_encode(['successful' => false, 'error' => "Internal Error. Error uploading file. Try again."]);
                    return;
                }
            }

            if (!file_exists($this->_ticketAbsoluteDir . "/" . $id)) {
                $oldumask = umask(0);
                mkdir($this->_ticketAbsoluteDir . "/" . $id, 0775, true);
                umask($oldumask);

                if (!file_exists($this->_ticketAbsoluteDir . "/" . $id)) {
                    echo json_encode(['successful' => false, 'error' => "Internal Error. Error uploading file. Try again."]);
                    return;
                }
            }

            if (!file_exists($this->_ticketAbsoluteDir . "/" . $id . "/" . $ticket_reply_id)) {
                $oldumask = umask(0);
                mkdir($this->_ticketAbsoluteDir . "/" . $id . "/" . $ticket_reply_id, 0775, true);
                umask($oldumask);

                if (!file_exists($this->_ticketAbsoluteDir . "/" . $id . "/" . $ticket_reply_id)) {
                    echo json_encode(['successful' => false, 'error' => "Internal Error. Error uploading file. Try again."]);
                    return;
                }
            }

            if (!move_uploaded_file($_FILES[$fileId]['tmp_name'][$fileKey], $tmp_file) || !file_exists($tmp_file)) {
                return ['successful' => false, 'error' => "Error uploading image file. Try again."];
            }

            //save file to s3
            if (ENVIRONMENT == "production") {
                $s3_file_url = $this->aws_s3_library_private->put_object($tmp_file, $this->_ticketRelativeDir . $img_file);
                if (file_exists($this->_ticketRelativeDir . $img_file)) {
                    unlink($this->_ticketRelativeDir . $img_file);
                }
                if ($s3_file_url === false) {
                    return ['successful' => false, 'error' => "Error uploading image file. Try again."];
                }
            } else {
                $s3_file_url = base_url() . $this->_ticketRelativeDir . "/" . $id . "/" . $ticket_reply_id . "/" . $img_file;
            }

            return ['successful' => true, 'url' => $s3_file_url, 'uploaded_file' => $tmp_file];
        } else {
            return ['successful' => true, 'url' => null];
        }
    }
}
