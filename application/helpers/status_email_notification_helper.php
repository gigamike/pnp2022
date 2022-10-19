<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('status_email_notification')) {
    /*
     *
     * Display notification for partner and agent but not in admin
     *
     */

    function status_email_notification()
    {
        $ci = &get_instance();
        $ci->load->model('backend_google_authenticate_model');

        $total_count = 0;
        $html = "";

        // tbl_partner_agents.role = 5
        // do not display
        // tbl_partner_agents.role = 1
        if ($ci->session->utilihub_hub_user_role == USER_SUPER_AGENT) {
            $google_authenticate = $ci->backend_google_authenticate_model->fetch(null, null, 1);
            if (count($google_authenticate) > 0) {
                $total_count = 0;

                $ci->load->model('dashboard_user_model');
                $user_profile = $ci->dashboard_user_model->get_user_profile($ci->session->utilihub_hub_user_id);
                if ($user_profile) {
                    if (!empty($user_profile['date_last_email_read'])) {
                        $emailLastRunTime = strtotime($google_authenticate[0]->date_cron_gmail_scrap_last_run);
                        $userLastReadTime = strtotime($user_profile['date_last_email_read']);

                        if ($emailLastRunTime > $userLastReadTime) {
                            $startDatetime = date('Y-m-d H:i:s', $userLastReadTime);
                            $endDatetime = date('Y-m-d H:i:s', $emailLastRunTime);

                            $ci->load->model('partner_dashboard_model');

                            $conditions_arr = [];
                            array_push($conditions_arr, "(tbl_log_email.partner_id = " . $ci->db->escape($ci->session->utilihub_hub_target_id)
                                    . " OR tbl_application.partner_id = " . $ci->db->escape($ci->session->utilihub_hub_target_id) . ")");
                            array_push($conditions_arr, "(tbl_log_email.date_processed BETWEEN '" . $startDatetime . "' AND '" . $endDatetime . "')");
                            $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";
                            $total_count = $ci->partner_dashboard_model->dashboard_get_applications_email_count($condition);
                        }
                    }
                }
            }

            $html = "<a class=\"dropdown-toggle count-info\" href=\"" . base_url() . "partner/email\">";
            $html .= "<i class=\"text-navy fa fa-envelope\"></i>";
            if ($total_count > 0) {
                $html .= "<span class=\"label label-warning\">" . $total_count . "</span>";
            }
            $html .= "</a>";
        }

        // tbl_partner_agents.role = 3
        if ($ci->session->utilihub_hub_user_role == USER_AGENT) {
            $google_authenticate = $ci->backend_google_authenticate_model->fetch(null, null, 1);
            if (count($google_authenticate) > 0) {
                $total_count = 0;

                $ci->load->model('dashboard_user_model');
                $user_profile = $ci->dashboard_user_model->get_user_profile($ci->session->utilihub_hub_user_id);
                if ($user_profile) {
                    if (!empty($user_profile['date_last_email_read'])) {
                        $emailLastRunTime = strtotime($google_authenticate[0]->date_cron_gmail_scrap_last_run);
                        $userLastReadTime = strtotime($user_profile['date_last_email_read']);

                        if ($emailLastRunTime > $userLastReadTime) {
                            $startDatetime = date('Y-m-d H:i:s', $userLastReadTime);
                            $endDatetime = date('Y-m-d H:i:s', $emailLastRunTime);

                            $ci->load->model('agent_dashboard_model');

                            $conditions_arr = [];
                            array_push($conditions_arr, "tbl_application.agent_referred = " . $ci->db->escape($ci->session->utilihub_hub_user_id));
                            array_push($conditions_arr, "tbl_log_email.date_processed BETWEEN '" . $startDatetime . "' AND '" . $endDatetime . "'");
                            $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";
                            $total_count = $ci->agent_dashboard_model->dashboard_get_applications_email_count($condition);
                        }
                    }
                }
            }

            $html = "<a class=\"dropdown-toggle count-info\" href=\"" . base_url() . "agent/email\">";
            $html .= "<i class=\"text-navy fa fa-envelope\"></i>";
            if ($total_count > 0) {
                $html .= "<span class=\"label label-warning\">" . $total_count . "</span>";
            }
            $html .= "</a>";
        }

        return $html;
    }
}
