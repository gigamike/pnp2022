<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Connect_sd_library
{
    protected $CI;

    private $_userType = null;
    private $_appUserId = null;
    private $_applicationId = null;
    
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->CI->load->model('connect_sd_users_model');
        $this->CI->load->model('connect_sd_chat_channels_model');
        $this->CI->load->model('connect_sd_chat_messages_model');
        $this->CI->load->model('connect_sd_chat_message_attachments_model');
        $this->CI->load->model('communications_model');
        $this->CI->load->model('database_tz_model');
    }

    public function getSessionChatChannel($app)
    {
        switch ($app) {
            case CONNECT_SD_APP_DASHBOARD:
                switch ($this->CI->session->utilihub_dashboard_user_role) {
                    case USER_MANAGER:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_MANAGER;
                        $this->_appUserId = $this->CI->session->utilihub_dashboard_user_id;

                        $this->CI->session->utilihub_dashboard_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case USER_SUPER_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_SUPER_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_dashboard_user_id;

                        $this->CI->session->utilihub_dashboard_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case USER_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_dashboard_user_id;

                        $this->CI->session->utilihub_dashboard_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case USER_SALES:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_SALES;
                        $this->_appUserId = $this->CI->session->utilihub_dashboard_user_id;

                        $this->CI->session->utilihub_dashboard_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case USER_AFFILIATE_MANAGER:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_AFFILIATE_MANAGER;
                        $this->_appUserId = $this->CI->session->utilihub_dashboard_user_id;

                        $this->CI->session->utilihub_dashboard_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    default:
                }

                break;
            case CONNECT_SD_APP_HUB:
                switch ($this->CI->session->utilihub_hub_target_role) {
                    case USER_MANAGER:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_MANAGER;
                        $this->_appUserId = $this->CI->session->utilihub_hub_user_id;

                        $this->CI->session->utilihub_hub_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );
                        break;
                    case USER_SUPER_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_SUPER_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_hub_user_id;

                        $this->CI->session->utilihub_hub_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );
                        break;
                    case USER_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_hub_user_id;

                        $this->CI->session->utilihub_hub_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );
                        break;
                    case USER_CUSTOMER_SERVICE_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_USER_CUSTOMER_SERVICE_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_hub_user_id;

                        $this->CI->session->utilihub_hub_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );
                        break;
                    default:
                }
                break;
            case CONNECT_SD_APP_PROVIDER:
                switch ($this->CI->session->utilihub_provider_user_role) {
                    case PROVIDER_USER_AGENT:
                        $this->_userType = CONNECT_SD_USER_TYPE_PROVIDER_USER_AGENT;
                        $this->_appUserId = $this->CI->session->utilihub_provider_user_id;

                        $this->CI->session->utilihub_provider_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case PROVIDER_USER_ADMIN:
                        $this->_userType = CONNECT_SD_USER_TYPE_PROVIDER_USER_ADMIN;
                        $this->_appUserId = $this->CI->session->utilihub_provider_user_id;

                        $this->CI->session->utilihub_provider_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );

                        break;
                    case PROVIDER_USER_SUPER:
                        $this->_userType = CONNECT_SD_USER_TYPE_PROVIDER_USER_SUPER;
                        $this->_appUserId = $this->CI->session->utilihub_provider_user_id;

                        $this->CI->session->utilihub_provider_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                            $this->_userType,
                            $this->_appUserId,
                            $this->_applicationId
                        );
                        
                        break;
                    default:
                }
                break;
            case CONNECT_SD_APP_CUSTOMER_PORTAL:
                $this->_userType = CONNECT_SD_USER_TYPE_CUSTOMER;
                $this->_applicationId = $this->CI->session->customer_portal_app_id;

                $this->CI->session->customer_portal_connect_sd_chat_channel = connect_sd_get_chat_channel(
                    $this->_userType,
                    $this->_appUserId,
                    $this->_applicationId
                );

                break;
            case CONNECT_SD_APP_CUSTOMER_PORTAL_V2:
                $this->_userType = CONNECT_SD_USER_TYPE_CUSTOMER;
                $this->_appUserId = $this->CI->session->customer_portal_app_id;

                $this->CI->session->utilihub_customer_portal_v2_user_connect_sd_chat_channel = connect_sd_get_chat_channel(
                    $this->_userType,
                    $this->_appUserId,
                    $this->_applicationId
                );

                break;
            case CONNECT_SD_APP_PUBLIC_WEBSITE:
                break;
            default:
        }
    }

    public function chatTimeOut($chatChannelId)
    {
        $chatChannel = $this->CI->connect_sd_chat_channels_model->getById($chatChannelId);
        if ($chatChannel) {
            if ($chatChannel->status == CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_CUSTOMER) {
                if ($chatChannel->customer_date_online) {
                    // wait until customer is online
                    $interval = strtotime($this->CI->database_tz_model->now()) - strtotime($chatChannel->customer_date_online);
                    if ($interval >= $this->CI->config->item('mm8_connect_sd_chat_idle_threshold_in_seconds')) {
                        $filter = [
                            'chat_channel_id' => $chatChannel->id,
                            'connect_sd_user_id_null' => true,
                            'date_added_is_less_than_seconds' => $this->CI->config->item('mm8_connect_sd_chat_idle_threshold_in_seconds'),
                        ];
                        $countMessage = $this->CI->connect_sd_chat_messages_model->getCount($filter);
                        if ($countMessage <= 0) {
                            $agent = $this->CI->connect_sd_users_model->getById($chatChannel->assignee_connect_sd_user_id);
                            if ($agent) {
                                $message = "Sorry [CUSTOMER_FIRST_NAME], session timeout. Email conversation will be sent. Thank you!";
                                $search = [
                                    '[CUSTOMER_FIRST_NAME]',
                                    '[CUSTOMER_LAST_NAME]',
                                ];
                                $replace = [
                                    $chatChannel->first_name,
                                    $chatChannel->last_name,
                                ];
                                $message = str_replace($search, $replace, $message);

                                $data = [
                                    'chat_channel_id' => $chatChannel->id,
                                    'connect_sd_user_id' => $chatChannel->assignee_connect_sd_user_id,
                                    'first_name' => $agent->first_name,
                                    'last_name' => $agent->last_name,
                                    'email' => $agent->email,
                                    'profile_photo' => $agent->profile_photo,
                                    'message' => $message,
                                ];
                                $chat_message_id = $this->CI->connect_sd_chat_messages_model->save($data);
                                if (!$chat_message_id) {
                                    return [
                                        'successful' => false,
                                        'error' => "Failed to update chat channel message. Chat Channel ID: " . $chatChannel->id,
                                    ];
                                }

                                $options = [
                                    'cluster' => $this->CI->config->item('mm8_connect_sd_pusher_cluster'),
                                    'useTLS' => true
                                ];
                                $pusher = new Pusher\Pusher(
                                    $this->CI->config->item('mm8_connect_sd_pusher_key'),
                                    $this->CI->config->item('mm8_connect_sd_pusher_secret'),
                                    $this->CI->config->item('mm8_connect_sd_pusher_app_id'),
                                    $options
                                );

                                $data['message'] = $message;
                                $data=[]; // do not submit any data as per compliance
                                $pusher->trigger($this->CI->config->item('mm8_country_iso_code') . "-" . $chatChannel->reference_code, 'chat-event', $data);
                            }

                            $data = [
                                'id' => $chatChannel->id,
                                'status' => CONNECT_SD_CHAT_CHANNEL_STATUS_INACTIVE,
                                'date_inactive' => $this->CI->database_tz_model->now(),
                            ];
                            $chat_channel_id = $this->CI->connect_sd_chat_channels_model->save($data);
                            if (!$chat_channel_id) {
                                return [
                                    'successful' => false,
                                    'error' => "Failed to update chat channel. Chat Channel ID: " . $chatChannel->id,
                                ];
                            }

                            $view_data = [];

                            $chatMessageAttachments = [];
                            $filter = [
                                'chat_channel_id' => $chatChannel->id,
                            ];
                            $order = [
                                'date_added',
                            ];
                            $chatMessages = $this->CI->connect_sd_chat_messages_model->fetch($filter, $order);
                            if (count($chatMessages) > 0) {
                                foreach ($chatMessages as $chatMessage) {
                                    $filter = [
                                        'chat_message_id' => $chatMessage->id,
                                    ];
                                    $order = [
                                        'date_added',
                                    ];
                                    $attachments = $this->CI->connect_sd_chat_message_attachments_model->fetch($filter, $order);
                                    $chatMessageAttachments[$chatMessage->id] = $attachments;
                                }
                            }
                            $view_data['chatMessages'] = $chatMessages;
                            $view_data['chatMessageAttachments'] = $chatMessageAttachments;

                            $chatConversation = "";
                            $chatConversation .= $this->CI->load->view('html_email/connect_sd_chat_messages', $view_data, true);

                            // send email conversation
                            //generate message
                            $template = $this->CI->communications_model->get_email_template('connect_sd_chat_conversation');
                            if (!$template) {
                                return [
                                    'successful' => false,
                                    'error' => "Missing email template connect_sd_chat_conversation email. Chat Channel ID: " . $chatChannel->id,
                                ];
                            }

                            $search_for = [
                                "[CHAT_CHANNEL_REFERENCE_CODE]",
                                "[GUEST_FIRST_NAME]",
                                "[GUEST_LAST_NAME]",
                                "[GUEST_EMAIL]",
                                "[CHAT_CONVERSATION]",
                                "[SYSTEMHOTLINE]",
                                "[SYSTEMNAME]",
                            ];
                            $replace_with = [
                                $chatChannel->reference_code,
                                $chatChannel->first_name,
                                $chatChannel->last_name,
                                $chatChannel->email,
                                $chatConversation,
                                $this->CI->config->item('mm8_product_name'),
                                $this->CI->config->item('mm8_system_hotline'),
                                $this->CI->config->item('mm8_system_name')
                            ];

                            $subject = str_replace($search_for, $replace_with, $template['subject']);
                            $html_template = str_replace($search_for, $replace_with, $template['html_template']);

                            $email_dataset = [];
                            $email_dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
                            $email_dataset['from_name'] = $this->CI->config->item('mm8_system_name');
                            $email_dataset['to'] = $chatChannel->email;
                            $email_dataset['reply_to'] = $this->CI->config->item('mm8_system_noreply_email');
                            $email_dataset['subject'] = $subject;
                            $email_dataset['html_message'] = $this->CI->load->view('html_email/basic_mail', ['contents' => $html_template], true);
                            $this->CI->communications_model->queue_email($email_dataset);

                            return [
                                'successful' => true,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'successful' => false,
        ];
    }
}
