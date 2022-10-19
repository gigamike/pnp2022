<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*
* GLOBAL
*
 */
if (! function_exists('is_connect_sd_enable_helper')) {
    function is_connect_sd_enable_helper()
    {
        $ci = &get_instance();
        $ci->load->model('connect_sd_settings_model');
        $setting = $ci->connect_sd_settings_model->getByName('is_connect_sd_enable');
        if ($setting) {
            if ($setting->value) {
                return true;
            }
        }

        return false;
    }
}


if (!function_exists('connect_sd_get_tickets')) {
    function connect_sd_get_tickets($user_type, $app_user_id, $limit = null)
    {
        $CI = & get_instance();

        $filter = [
            'user_type' => $user_type,
            'app_user_id' => $app_user_id,
            'date_deleted_is_null' => true, // not deleted
        ];
        $order = [
            'tbl_connect_sd_tickets.date_added DESC',
        ];

        $CI->load->model('connect_sd_tickets_model');
        $tickets = $CI->connect_sd_tickets_model->fetch($filter, $order, $limit);
        
        return $tickets;
    }
}

if (! function_exists('connect_sd_get_ticket_priority_helper')) {
    /*
    *
    * https://tili-group.monday.com/boards/1445061997/pulses/2628073849
    *
     */
    function connect_sd_get_ticket_priority_helper($urgency, $impact)
    {
        $ci = &get_instance();

        if ($urgency == CONNECT_SD_TICKET_URGENCY_HIGH
            && $impact == CONNECT_SD_TICKET_IMPACT_HIGH) {
            return CONNECT_SD_TICKET_PRIORITY_CRITICAL;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_HIGH
            && $impact == CONNECT_SD_TICKET_IMPACT_MEDIUM) {
            return CONNECT_SD_TICKET_PRIORITY_HIGH;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_HIGH
            && $impact == CONNECT_SD_TICKET_IMPACT_LOW) {
            return CONNECT_SD_TICKET_PRIORITY_MEDIUM;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_MEDIUM
            && $impact == CONNECT_SD_TICKET_IMPACT_HIGH) {
            return CONNECT_SD_TICKET_PRIORITY_HIGH;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_MEDIUM
            && $impact == CONNECT_SD_TICKET_IMPACT_MEDIUM) {
            return CONNECT_SD_TICKET_PRIORITY_MEDIUM;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_MEDIUM
            && $impact == CONNECT_SD_TICKET_IMPACT_LOW) {
            return CONNECT_SD_TICKET_PRIORITY_LOW;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_LOW
            && $impact == CONNECT_SD_TICKET_IMPACT_HIGH) {
            return CONNECT_SD_TICKET_PRIORITY_MEDIUM;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_LOW
            && $impact == CONNECT_SD_TICKET_IMPACT_MEDIUM) {
            return CONNECT_SD_TICKET_PRIORITY_LOW;
        }

        if ($urgency == CONNECT_SD_TICKET_URGENCY_LOW
            && $impact == CONNECT_SD_TICKET_IMPACT_LOW) {
            return CONNECT_SD_TICKET_PRIORITY_LOW;
        }
    }
}

/*
*
* This should always return only 1 active/inactive channel
* If it doesnt have any channel, then default chatbot
*
 */
if (!function_exists('connect_sd_get_chat_channel')) {
    /*
    *
    * Reference for app_user_id
    * ams = tbl_partner_agents.id
    * crm = tbl_user.id
    * dashboard = tbl_partner_agents.id
    * hub = tbl_partner_agents.id
    * provider = tbl_user_marketplace.id
    * customer_portal = tbl_customer.id
    * customer_portal_v2 = tbl_customer_profile.id
    *
     */
    function connect_sd_get_chat_channel(
        $userType, // 1 USER_SUPER_AGENT, 2 USER_AGENT, 3 USER_SALES, 4 USER_MANAGER, 5 USER_AFFILIATE_MANAGER, 6 USER_CUSTOMER_SERVICE_AGENT, 7 PROVIDER_USER_AGENT, 8 PROVIDER_USER_ADMIN, 9 PROVIDER_USER_SUPER, 10 CUSTOMER, 11 GUEST
        $app_user_id, //  USER_SUPER_AGENT, USER_AGENT, USER_SALES, USER_MANAGER, USER_AFFILIATE_MANAGER, USER_CUSTOMER_SERVICE_AGENT - tbl_partner_agents.id, PROVIDER_USER_AGENT, PROVIDER_USER_ADMIN, PROVIDER_USER_SUPER - tbl_user_marketplace.id, CUSTOMER - tbl_customer.id, tbl_customer_profile.id, GUEST - null
        $application_id = null // customer_portal
    ) {
        $CI = & get_instance();
        $CI->load->model('connect_sd_chat_channels_model');

        $filter = [
            'user_type' => $userType,
            'app_user_id' => $app_user_id,
            'statuses' => [
                CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_CUSTOMER,
                CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT,
                CONNECT_SD_CHAT_CHANNEL_STATUS_INACTIVE,
            ]
        ];
        if ($application_id) {
            $filter['application_id'] = $application_id;
        }
        $order = [
            'date_added DESC'
        ];
        $chatChannel = $CI->connect_sd_chat_channels_model->fetch($filter, $order, 1);
        if (count($chatChannel) > 0) {
            return $chatChannel[0];
        } else {
            return false;
        }
    }
}

/*
*
* CRM
*
 */
if (!function_exists('connect_sd_crm_menu')) {
    function connect_sd_crm_menu($user_id)
    {
        $CI = & get_instance();

        $html = "";

        $CI->load->model('crm_user_model');
        $user_profile = $CI->crm_user_model->get_user_profile($user_id);
        if ($user_profile) {
            $CI->load->model('connect_sd_users_model');

            $connect_sd_user = $CI->connect_sd_users_model->getByEmail($user_profile['email']);
            if ($connect_sd_user) {
                $token = $CI->encryption->url_encrypt(serialize([
                    'email' => $user_profile['email'],
                    'password' => $connect_sd_user->password,
                    'seed' => $CI->config->item('mm8_connect_sd_seed'),
                    'time' => time(),
                ]));

                $html = "<li><a target=\"_blank\" href=\"" . $CI->config->item('mhub_connect_sd_url') . "login/sso/" . $token . "\"><div><i class=\"fa fa-life-ring\" aria-hidden=\"true\"></i> Connect Service Desk</div></a></li>";
            }
        }

        return $html;
    }
}

/*
*
* AMS
*
 */
if (!function_exists('connect_sd_ams_menu')) {
    function connect_sd_ams_menu($user_id)
    {
        $CI = & get_instance();

        $html = "";

        $CI->load->model('dashboard_user_model');
        $user_profile = $CI->dashboard_user_model->get_user_profile($user_id);
        if ($user_profile) {
            $CI->load->model('connect_sd_users_model');
                
            $connect_sd_user = $CI->connect_sd_users_model->getByEmail($user_profile['email']);
            if ($connect_sd_user) {
                $token = $CI->encryption->url_encrypt(serialize([
                    'email' => $user_profile['email'],
                    'password' => $connect_sd_user->password,
                    'seed' => $CI->config->item('mm8_connect_sd_seed'),
                    'time' => time(),
                ]));
                    
                $html = "<li><a target=\"_blank\" href=\"" . $CI->config->item('mhub_connect_sd_url') . "login/sso/" . $token . "\"><div><i class=\"fa fa-life-ring\" aria-hidden=\"true\"></i> Connect Service Desk</div></a></li>";
            }
        }

        return $html;
    }
}

/*
*
* HUB
*
 */
if (!function_exists('connect_sd_hub_get_ticket_category_sla')) {
    function connect_sd_hub_get_ticket_category_sla($ticket_category_id, $role, $reseller_id = null, $partner_id = null)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_ticket_category_sla_model');

        switch ($role) {
            case USER_MANAGER:
                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_SUPER_AGENT:
                if ($partner_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'partner_id' => $partner_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }

                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_AGENT:
                if ($partner_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'partner_id' => $partner_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }

                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_CUSTOMER_SERVICE_AGENT:
                break;
            default:
        }

        $filter = [
            'ticket_category_id' => $ticket_category_id,
        ];
        $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
        if (count($ticket_category_sla) > 0) {
            return $ticket_category_sla[0];
        }

        return [];
    }
}

/*
*
* DASHBOARD
*
 */
if (!function_exists('connect_sd_dashboard_get_ticket_category_sla')) {
    function connect_sd_dashboard_get_ticket_category_sla($ticket_category_id, $role, $reseller_id = null, $partner_id = null)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_ticket_category_sla_model');

        switch ($role) {
            case USER_MANAGER:
                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_SUPER_AGENT:
                if ($partner_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'partner_id' => $partner_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }

                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_AGENT:
                if ($partner_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'partner_id' => $partner_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }

                if ($reseller_id) {
                    $filter = [
                        'ticket_category_id' => $ticket_category_id,
                        'reseller_id' => $reseller_id,
                    ];
                    $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
                    if (count($ticket_category_sla) > 0) {
                        return $ticket_category_sla[0];
                    }
                }
                break;
            case USER_SALES:
                break;
            case USER_AFFILIATE_MANAGER:
                break;
            default:
        }

        $filter = [
            'ticket_category_id' => $ticket_category_id,
        ];
        $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
        if (count($ticket_category_sla) > 0) {
            return $ticket_category_sla[0];
        }

        return [];
    }
}

/*
*
* PROVIDER
*
 */
if (!function_exists('connect_sd_provider_get_ticket_category_sla')) {
    function connect_sd_provider_get_ticket_category_sla($ticket_category_id, $role = null)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_ticket_category_sla_model');

        switch ($role) {
            case PROVIDER_USER_AGENT:
                break;
            case PROVIDER_USER_ADMIN:
                break;
            case PROVIDER_USER_SUPER:
                break;
            default:
        }

        $filter = [
            'ticket_category_id' => $ticket_category_id,
        ];
        $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
        if (count($ticket_category_sla) > 0) {
            return $ticket_category_sla[0];
        }

        return [];
    }
}

/*
*
* CUSTOMER PORTAL V2
*
 */
if (!function_exists('connect_sd_customer_portal_v2_get_ticket_category_sla')) {
    function connect_sd_customer_portal_v2_get_ticket_category_sla($ticket_category_id)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_ticket_category_sla_model');

        $filter = [
            'ticket_category_id' => $ticket_category_id,
        ];
        $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
        if (count($ticket_category_sla) > 0) {
            return $ticket_category_sla[0];
        }

        return [];
    }
}

/*
*
* Public Website (movinghub.com)
*
 */
if (!function_exists('connect_sd_public_website_get_ticket_category_sla')) {
    function connect_sd_public_website_get_ticket_category_sla($ticket_category_id)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_ticket_category_sla_model');

        $filter = [
            'ticket_category_id' => $ticket_category_id,
        ];
        $ticket_category_sla = $CI->connect_sd_ticket_category_sla_model->fetch($filter, [], 1);
        if (count($ticket_category_sla) > 0) {
            return $ticket_category_sla[0];
        }

        return [];
    }
}

/*
*
* CONNECT-SD
*
 */
if (!function_exists('connect_sd_get_new_tickets')) {
    function connect_sd_get_new_tickets()
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_tickets_model');

        $filter = [
            // 'date_added' => date('Y-m-d', strtotime($CI->database_tz_model->now())),
            'date_deleted_is_null' => true,
            'date_read_is_null' => true,
        ];
        return $CI->connect_sd_tickets_model->getCount($filter);
    }
}

if (!function_exists('connect_sd_get_new_chats')) {
    function connect_sd_get_new_chats()
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_chat_messages_model');

        $filter = [
            // 'date_added' => date('Y-m-d', strtotime($CI->database_tz_model->now())),
            'connect_sd_user_id_is_null' => true,
            'date_read_is_null' => true,
        ];
        return $CI->connect_sd_chat_messages_model->getCountChatChannelWithMessages($filter);
    }
}

if (!function_exists('connect_sd_user_update_date_last_online')) {
    function connect_sd_user_update_date_last_online($connect_sd_user_id)
    {
        $CI = & get_instance();
        $CI->load->model('connect_sd_users_model');

        $data = [
            'id' => $connect_sd_user_id,
            'date_last_online' => $CI->database_tz_model->now(),
        ];
        $CI->connect_sd_users_model->save($data);
    }
}
