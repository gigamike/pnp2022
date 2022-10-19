<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email_library
{
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

    public function _process_bounce_flags(&$input, $return_result_flags, &$result_flags, $flag_name)
    {
        // get the results, we will get an array of emails with booleans as values
        $result = is_email_blacklisted($input);

        // only include emails which are NOT blacklisted
        $output = [];
        foreach ($result as $email => $is_blacklisted) {
            if ($is_blacklisted == false) {
                $output[] = $email;
            }
        }

        // reconstruct the email addresses after filtering out the blacklisted ones
        $input = implode(", ", $output);

        // if the caller wanted to receive the result_flags, write it
        if ($return_result_flags) {
            $result_flags[$flag_name] = empty($input) ? 1 : 0;
        }
    }

    /**
     * @param bool $return_result_flags - whether to add the flags in return value
     * @param $result_flags - optionally pass a variable that will be filled with result flags
     *                        in case the email sending process failed for whatever reason
     *                        (e.g bounced email)
     */
    public function send($dataset, $inline = false, $return_result_flags = false, &$result_flags = [])
    {
        $this->CI->email->clear(true);

        // if all of the the primary recipients are blacklisted. return immediately
        if (isset($dataset['to']) && !empty($dataset['to'])) {
            $this->_process_bounce_flags($dataset['to'], $return_result_flags, $result_flags, 'bounce_flagged');
            if (empty($dataset['to'])) {
                return false;
            }
        }

        // if cc is blacklisted, add flag
        if (isset($dataset['cc']) && !empty($dataset['cc'])) {
            $this->_process_bounce_flags($dataset['cc'], $return_result_flags, $result_flags, 'cc_bounce_flagged');
        }

        // if bcc is blacklisted, add flag
        if (isset($dataset['bcc']) && !empty($dataset['bcc'])) {
            $this->_process_bounce_flags($dataset['bcc'], $return_result_flags, $result_flags, 'bcc_bounce_flagged');
        }


        //from
        $from_name = isset($dataset['from_name']) ? $dataset['from_name'] : "";
        $this->CI->email->from($dataset['from'], $from_name);

        //reply_to
        if (isset($dataset['reply_to']) && !empty($dataset['reply_to'])) {
            $reply_to_name = isset($dataset['reply_to_name']) ? $dataset['reply_to_name'] : "";
            $this->CI->email->reply_to($dataset['reply_to'], $reply_to_name);
        }

        //to
        $to = ENVIRONMENT == "production" ? $dataset['to'] : $this->CI->config->item('mm8_development_email');
        //$to = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['to'];
        $this->CI->email->to($to);

        //cc
        if (isset($dataset['cc']) && !empty($dataset['cc'])) {
            $cc = ENVIRONMENT == "production" ? $dataset['cc'] : $this->CI->config->item('mm8_development_email');
            //$cc = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['cc'];
            $this->CI->email->cc($cc);
        }

        //bcc
        if (isset($dataset['bcc']) && !empty($dataset['bcc'])) {
            $bcc = ENVIRONMENT == "production" ? $dataset['bcc'] : $this->CI->config->item('mm8_development_email');
            //$bcc = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['bcc'];
            $this->CI->email->bcc($bcc);
        }

        // header
        if (isset($dataset['headers']) && is_array($dataset['headers']) && count($dataset['headers']) > 0) {
            foreach ($dataset['headers'] as $k => $v) {
                $this->CI->email->set_header($k, $v);
            }
        }

        //subject
        $this->CI->email->subject($dataset['subject']);

        //message
        $this->CI->email->message($dataset['html_message']);

        //set alt message
        if (isset($dataset['text_message']) && !empty($dataset['text_message'])) {
            $this->CI->email->set_alt_message($dataset['text_message']);
        }

        //attachment
        if (isset($dataset['attachment']) && !empty($dataset['attachment'])) {
            if (is_array($dataset['attachment'])) {
                foreach ($dataset['attachment'] as $attach) {
                    $this->CI->email->attach($attach);
                }
            } else {
                $this->CI->email->attach($dataset['attachment']);
            }
        }

        $result = $this->CI->email->send(false);
        if (!$result) {
            log_message("error", "EMAIL SENDING FAILED ===> " . $this->CI->email->print_debugger());
        }

        if ($inline === false) {
            //randomly sleep (0-0.5 sec) to avoid sending mails all at the same time
            //this is to avoid SENDING LIMIT set in SMTP server
            usleep(rand(0, 5) * 100000);
        }

        return $result;
    }

    public function send_basic_email($subject, $html_message, $to, $cc = "", $attachment = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];

        $dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;
        $dataset['attachment'] = $attachment;
        $dataset['html_message'] = $this->CI->load->view('html_email/basic_mail', ['contents' => $html_message, 'to' => $to], true);
        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    public function send_mhub_email($subject, $html_message, $text_message, $to, $cc = "", $attachment = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];
        $dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;
        $dataset['attachment'] = $attachment;

        $html_str = $this->CI->load->view('html_email/mhub_mail', ['contents' => $html_message, 'to' => $to], true);
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    // Agent Wallet enhancements
    public function send_mhub_email_agent($subject, $html_message, $text_message, $to, $cc = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];
        $dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;

        $html_str = $this->CI->load->view('html_email/mhub_mail_agent', ['contents' => $html_message, 'to' => $to], true);
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);
        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    // function to send communication emails to agents
    public function send_agent_notification_email($subject, $html_message, $text_message, $to, $social_links, $partner_email_header, $portal_name, $cc = "", $from = "", $from_name = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];
        $dataset['from'] = !empty($from) ? $from : $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = !empty($from_name) ? $from_name : $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;

        $html_str = $this->CI->load->view('html_email/agent_notification_mail', ['contents' => $html_message, 'to' => $to, 'social_links' => $social_links, 'partner_email_header' => $partner_email_header, 'portal_name' => $portal_name], true);
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);
        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    // function to send communication emails
    public function send_partner_notification_email($subject, $html_message, $text_message, $to, $social_links, $partner_email_header, $portal_name, $cc = "", $from = "", $from_name = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];
        $dataset['from'] = !empty($from) ? $from : $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = !empty($from_name) ? $from_name : $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;

        $html_str = $this->CI->load->view('html_email/agent_notification_mail', ['contents' => $html_message, 'to' => $to, 'social_links' => $social_links, 'partner_email_header' => $partner_email_header, 'portal_name' => $portal_name], true);
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);
        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    // Dashboards enhancements
    public function send_dashboard_email($subject, $html_message, $text_message, $to, $social_links, $partner_email_header, $portal_name, $cc = "", $from = "", $from_name = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];
        $dataset['from'] = !empty($from) ? $from : $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = !empty($from_name) ? $from_name : $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;

        $html_str = $this->CI->load->view('html_email/dashboard_mail', ['contents' => $html_message, 'to' => $to, 'social_links' => $social_links, 'partner_email_header' => $partner_email_header, 'portal_name' => $portal_name], true);
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    /*
     *
     * As of  Aug 17, 2020, change the format of subject
     * BEFORE
     * [mhb-8QY1] HUB Test
     * AFTER
     * HUB Test mhb-8QY1
     */

    public function send_customer_email($partner_id, $application_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment = "", $return_result_flags = false, $is_send = true)
    {
        $application_status = $partner_data = [];
        $result_flags = [];

        if (!empty($application_id)) {
            $this->CI->load->model('application_model', '', true);
            $this->CI->load->model('partner_model', '', true);

            $application_status = $this->CI->application_model->get_application_status($application_id);
            if (!$application_status) {
                return ['status' => STATUS_NG];
            }

            $partner_data = $this->CI->partner_model->get_partner_info($application_status['partner_id']);
            if (count($partner_data) <= 0) {
                return ['status' => STATUS_NG];
            }

            $application_data = $this->CI->application_model->get_application_customer_info($application_id);
            if (!$application_data || count($application_data) <= 0) {
                return ['status' => STATUS_NG];
            }

            $unsubscribe = "<br><br><center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [" . $application_data['reference_code'] . "].<br>If you no longer wish to receive these emails, <a href=\"" . (!empty($this->CI->config->item('mhub_apps_alternative_url')) ? $this->CI->config->item('mhub_apps_alternative_url') : $this->CI->config->item('mhub_apps_url')) . "unsubscribe/application/" . $this->CI->encryption->url_encrypt($application_data['reference_code']) . "\">unsubscribe</center>";

            $html_body .= $unsubscribe;
        } elseif (!empty($partner_id)) {
            $this->CI->load->model('partner_model', '', true);

            $partner_data = $this->CI->partner_model->get_partner_info($partner_id);
            if (count($partner_data) <= 0) {
                return ['status' => STATUS_NG];
            }
        } else {
            return ['status' => STATUS_NG];
        }

        $search = [
            "[PARTNERCODE]",
            "[PARTNERNAME]",
            "[PARTNERURL]",
            "[PARTNERHOTLINE]",
            "[PARTNERSUPPORT]"
        ];
        $replace = [
            $partner_data['reference_code'],
            $partner_data['portal_name'],
            $partner_data['portal_url'],
            $partner_data['hotline'],
            $partner_data['support_email']
        ];

        $html_body = str_replace($search, $replace, $html_body);

        $dataset = [];
        $dataset['application_id'] = !empty($application_id) ? $application_id : null;
        $dataset['access_code'] = !empty($application_id) ? $application_id . random_string('alnum', 15) : random_string('alnum', 25);
        $dataset['from_name'] = !empty($from_name) ? $from_name : $partner_data['portal_name'];
        $dataset['from'] = !empty($from) ? $from : $partner_data['ops_email'];
        $dataset['reply_to'] = !empty($reply_to) ? $reply_to : $partner_data['ops_email_reply_to'];
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['bcc'] = $bcc;

        if (isset($application_status['reference_code']) && !empty($application_status['reference_code'])) {
            // $tag = "[" . $application_status['reference_code'] . "]";
            // $dataset['subject'] = $tag . " " . trim(str_ireplace($tag, "", $subject));

            $tag = $application_status['reference_code'];
            $subject = trim($subject);
            if (substr($subject, (0 - strlen($tag))) == $tag) {
                $dataset['subject'] = $subject;
            } else {
                $dataset['subject'] = $subject . " " . $tag;
            }
        } else {
            $dataset['subject'] = $subject;
        }

        if ($attachment !== "") {
            $dataset['attachment'] = $attachment;
        }

        $dataset['html_message'] = $this->CI->load->view('html_email/plain', ['contents' => $html_body, 'to' => $to], true);
        $dataset['text_message'] = "";

        if ($is_send) {
            //send
            $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;
            if ($return_result_flags) {
                $dataset = array_merge($dataset, $result_flags);
            }
        }

        return $dataset;
    }

    /*
     *
     * As of  Aug 17, 2020, change the format of subject
     * BEFORE
     * [mhb-8QY1] HUB Test
     * AFTER
     * HUB Test mhb-8QY1
     */

    public function send_customer_email_styled($partner_id, $application_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment = "", $return_result_flags = false, $is_send = true)
    {
        $application_status = $partner_data = [];
        $result_flags = [];

        if (!empty($application_id)) {
            $this->CI->load->model('application_model', '', true);
            $this->CI->load->model('partner_model', '', true);

            $application_status = $this->CI->application_model->get_application_status($application_id);
            if (!$application_status) {
                return ['status' => STATUS_NG];
            }

            $partner_data = $this->CI->partner_model->get_partner_info($application_status['partner_id']);
            if (count($partner_data) <= 0) {
                return ['status' => STATUS_NG];
            }

            $application_data = $this->CI->application_model->get_application_customer_info($application_id);
            if (!$application_data || count($application_data) <= 0) {
                return ['status' => STATUS_NG];
            }

            $unsubscribe = "<br><br> <center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [" . $application_data['reference_code'] . "].<br>If you no longer wish to receive these emails, <a href=\"" . (!empty($this->CI->config->item('mhub_apps_alternative_url')) ? $this->CI->config->item('mhub_apps_alternative_url') : $this->CI->config->item('mhub_apps_url')) . "unsubscribe/application/" . $this->CI->encryption->url_encrypt($application_data['reference_code']) . "\">unsubscribe</center>";

            $html_body .= $unsubscribe;
        } elseif (!empty($partner_id)) {
            $this->CI->load->model('partner_model', '', true);

            $partner_data = $this->CI->partner_model->get_partner_info($partner_id);
            if (count($partner_data) <= 0) {
                return ['status' => STATUS_NG];
            }
        } else {
            return ['status' => STATUS_NG];
        }



        $dataset = [];
        $dataset['application_id'] = !empty($application_id) ? $application_id : null;
        $dataset['access_code'] = !empty($application_id) ? $application_id . random_string('alnum', 15) : null;
        $dataset['from_name'] = !empty($from_name) ? $from_name : $partner_data['portal_name'];
        $dataset['from'] = !empty($from) ? $from : $partner_data['ops_email'];
        $dataset['reply_to'] = !empty($reply_to) ? $reply_to : $partner_data['ops_email_reply_to'];
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['bcc'] = $bcc;

        if (isset($application_status['reference_code']) && !empty($application_status['reference_code'])) {
            // $tag = "[" . $application_status['reference_code'] . "]";
            // $dataset['subject'] = $tag . " " . trim(str_ireplace($tag, "", $subject));

            $tag = $application_status['reference_code'];
            $subject = trim($subject);
            if (substr($subject, (0 - strlen($tag))) == $tag) {
                $dataset['subject'] = $subject;
            } else {
                $dataset['subject'] = $subject . " " . $tag;
            }
        } else {
            $dataset['subject'] = $subject;
        }

        if (!empty($attachment)) {
            $dataset['attachment'] = $attachment;
        }

        //
        $view_data = [
            'contents' => $html_body,
            'banner' => $partner_data['email_banner'],
            'portal_name' => $partner_data['portal_name'],
            'partner_id' => $partner_data['id'],
            'portal_url' => $partner_data['portal_url'],
            'webview_url' => !empty($dataset['access_code']) ? $this->CI->config->item('mhub_url') . "links/html-email/" . $this->CI->encryption->url_encrypt($dataset['access_code']) : null,
            'facebook_link' => isset($partner_data['link_facebook']) && $partner_data['link_facebook'] != "" ? $partner_data['link_facebook'] : "",
            'twitter_link' => isset($partner_data['link_twitter']) && $partner_data['link_twitter'] != "" ? $partner_data['link_twitter'] : "",
            'instagram_link' => isset($partner_data['link_instagram']) && $partner_data['link_instagram'] != "" ? $partner_data['link_instagram'] : "",
            'linkedin_link' => isset($partner_data['link_linkedin']) && $partner_data['link_linkedin'] != "" ? $partner_data['link_linkedin'] : "",
            'youtube_link' => isset($partner_data['link_youtube']) && $partner_data['link_youtube'] != "" ? $partner_data['link_youtube'] : "",
            'to_email' => $to,
            'to' => $to
        ];

        // if no email design (view) defined for this theme, use default
        $view = file_exists(APPPATH . "views/html_email/themes/" . $partner_data['email_theme'] . ".php") ? $partner_data['email_theme'] : "default";
        $dataset['html_message'] = $this->CI->load->view('html_email/themes/' . $view, $view_data, true);
        $dataset['text_message'] = "";

        if ($is_send) {
            //send
            $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;
            if ($return_result_flags) {
                $dataset = array_merge($dataset, $result_flags);
            }
        }

        return $dataset;
    }

    public function get_mpa_email_styled($reseller_id, $partner_id, $agent_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment = "", $return_result_flags = false)
    {
        //initialise values
        $whitelabel_details = [];
        $result_flags = [];

        if (!empty($reseller_id)) {
            $this->CI->load->model('communications_model', '', true);
            $whitelabel_details = $this->CI->communications_model->get_mpa_whitelabeled_email_details_by_manager($reseller_id);
        } elseif (!empty($partner_id)) {
            $this->CI->load->model('communications_model', '', true);
            $whitelabel_details = $this->CI->communications_model->get_mpa_whitelabeled_email_details_by_partner($partner_id);
        } elseif (!empty($agent_id)) {
            $this->CI->load->model('communications_model', '', true);
            $whitelabel_details = $this->CI->communications_model->get_mpa_whitelabeled_email_details_by_agent($agent_id);
        }


        $dataset = [];
        $dataset['from_name'] = !empty($from_name) ? $from_name : (isset($whitelabel_details['whitelabel_system_name']) && !empty($whitelabel_details['whitelabel_system_name']) ? $whitelabel_details['whitelabel_system_name'] : $this->CI->config->item('mm8_system_name'));
        $dataset['from'] = !empty($from) ? $from : (isset($whitelabel_details['whitelabel_email_from']) && !empty($whitelabel_details['whitelabel_email_from']) ? $whitelabel_details['whitelabel_email_from'] : $this->CI->config->item('mm8_system_noreply_email'));
        $dataset['reply_to'] = !empty($reply_to) ? $reply_to : $dataset['from'];
        $dataset['to'] = $to;
        $dataset['cc'] = empty($cc) ? null : $cc;
        $dataset['bcc'] = empty($bcc) ? null : $bcc;
        $dataset['subject'] = $subject;

        if (!empty($attachment)) {
            $dataset['attachment'] = $attachment;
        }


        $view_data = [
            'contents' => $html_body,
            'banner' => isset($whitelabel_details['whitelabel_email_banner']) && !empty($whitelabel_details['whitelabel_email_banner']) ? $whitelabel_details['whitelabel_email_banner'] : $this->CI->config->item('mm8_system_default_email_banner_url'),
            'portal_name' => $dataset['from_name'],
            'portal_url' => null,
            'webview_url' => null,
            'facebook_link' => isset($whitelabel_details['whitelabel_link_facebook']) && $whitelabel_details['whitelabel_link_facebook'] != "" ? $whitelabel_details['whitelabel_link_facebook'] : "",
            'twitter_link' => isset($whitelabel_details['whitelabel_link_twitter']) && $whitelabel_details['whitelabel_link_twitter'] != "" ? $whitelabel_details['whitelabel_link_twitter'] : "",
            'instagram_link' => isset($whitelabel_details['whitelabel_link_instagram']) && $whitelabel_details['whitelabel_link_instagram'] != "" ? $whitelabel_details['whitelabel_link_instagram'] : "",
            'linkedin_link' => isset($whitelabel_details['whitelabel_link_linkedin']) && $whitelabel_details['whitelabel_link_linkedin'] != "" ? $whitelabel_details['whitelabel_link_linkedin'] : "",
            'youtube_link' => isset($whitelabel_details['whitelabel_link_youtube']) && $whitelabel_details['whitelabel_link_youtube'] != "" ? $whitelabel_details['whitelabel_link_youtube'] : "",
            'to_email' => $to];

        // if no email design (view) defined for this theme, use default
        $view = isset($whitelabel_details['whitelabel_email_theme']) && file_exists(APPPATH . "views/html_email/themes/" . $whitelabel_details['whitelabel_email_theme'] . ".php") ? $whitelabel_details['whitelabel_email_theme'] : "default";
        $dataset['html_message'] = $this->CI->load->view('html_email/themes/' . $view, $view_data, true);
        $dataset['text_message'] = null;

        return $dataset;
    }

    public function send_mpa_email_styled($reseller_id, $partner_id, $agent_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment = "", $return_result_flags = false)
    {
        $dataset = $this->get_mpa_email_styled($reseller_id, $partner_id, $agent_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment);
        $result_flags = [];
        $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;
        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }
        return $dataset;
    }

    public function notify_system_failure($message, $to = "")
    {
        if (did_single_email_opted_out($to != "" ? $to : $this->CI->config->item('mm8_development_email'), EMAIL_SUBSCRIPTION_SYSTEM_MAINTENANCE)) {
            return;
        }

        $this->CI->email->from($this->CI->config->item('mm8_system_noreply_email'), $this->CI->config->item('mm8_system_name'));
        $this->CI->email->to($to != "" ? $to : $this->CI->config->item('mm8_development_email'));
        $this->CI->email->cc('karlou@movinghub.com.au');
        $this->CI->email->subject("[" . $this->CI->config->item('mm8_system_prefix') . "] [" . ENVIRONMENT . "] BACKEND SYSTEMS FAILED");
        $this->CI->email->message($this->CI->load->view('html_email/system_mail', ['message' => $message, 'to' => $to], true));
        return $this->CI->email->send();
    }

    public function format_services_for_email($theme, $dataset)
    {
        $text_str = $html_str = $moving_in = $moving_out = '';

        foreach ($dataset as $service) {
            $text_str .= $service['provider_name'] . ' - ' . $service['plan_name'];
            $text_str .= isset($service['provider_special_text']) && $service['provider_special_text'] != "" ? ' - ' . $service['provider_special_text'] : '';
            $text_str .= isset($service['plan_special_text']) && $service['plan_special_text'] != "" ? '(' . $service['plan_special_text'] . ')\r\n' : '\r\n';

            $provider_logo = $service['plan_img_url'] != null && $service['plan_img_url'] != "" ? $service['plan_img_url'] : $service['provider_img_url'];

            $html_str = '<tr>';
            $html_str .= '<td width="20%" style="border-top: 1px solid #e7eaec; padding: 15px 0px; text-align: center;" valign="top"><img src="' . $provider_logo . '" title="' . $service['service_name'] . '" alt="' . $service['service_name'] . '"></td>';
            $html_str .= '<td width="80%" style="border-top: 1px solid #e7eaec; padding: 15px 0px;" valign="top">';

            $html_str .= '<table cellpadding="0" cellspacing="0">';
            $html_str .= '<tr><td valign="top">' . $service['provider_name'] . ' - ' . $service['plan_name'] . '</td></tr>';

            if (isset($service['provider_special_html']) && $service['provider_special_html'] != "") {
                $html_str .= '<tr><td style="padding: 15px 0px; font-size: 12px;" valign="top">' . $service['provider_special_html'] . '</td></tr>';
            }

            if (isset($service['plan_special_html']) && $service['plan_special_html'] != "") {
                $html_str .= '<tr><td style="padding: 15px 0px;" valign="top">' . $service['plan_special_html'] . '</td></tr>';
            }

            $html_str .= '</table>';

            $html_str .= '</td>';
            $html_str .= '</tr>';

            if ((int) $service['direction'] == DIRECTION_IN) {
                $moving_in .= $html_str;
            } else {
                $moving_out .= $html_str;
            }
        }

        $html_str = '';

        if ($moving_in != '') {
            $html_str .= '<table cellpadding="0" cellspacing="0" width="90%" align="center" style="padding: 0 0 15px;">';
            $html_str .= '<tr><td colspan="2" style="text-align: left; padding: 5px 0px;"><strong>Services for your new address</strong></td></tr>';
            $html_str .= $moving_in;
            $html_str .= '</table>';
        }

        if ($moving_out != '') {
            $html_str .= '<table cellpadding="0" cellspacing="0" width="90%" align="center" style="padding: 0 0 15px;">';
            $html_str .= '<tr><td colspan="2" style="text-align: left; padding: 5px 0px;"><strong>Services for your old address</strong></td></tr>';
            $html_str .= $moving_out;
            $html_str .= '</table>';
        }

        return ['html_str' => $html_str, 'text_str' => $text_str];
    }

    // Agent Wallet enhancements
    public function send_snapmobile_email($app_partner_data, $subject, $html_message, $text_message, $from, $fromname, $to, $cc = "", $attachments = null, $return_result_flags = false)
    {
        $dataset = [];
        $dataset['from'] = $from;
        $dataset['from_name'] = $fromname;
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;
        $result_flags = [];

        $dataset['attachment'] = $attachments;

        // if partner data is not set
        if ($app_partner_data != null) {
            $html_str = $this->CI->load->view('html_email/mhub_snapmobile_email_agent', ['contents' => $html_message, 'app_partner_data' => $app_partner_data], true);
        } else {
            $html_str = $html_message;
        }
        $dataset['html_message'] = $html_str;
        $dataset['text_message'] = $text_message;

        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    public function send_survey_email($partner_id, $application_id = "", $to, $cc = "", $subject, $html_body, $text_body = "", $attachment = "", $return_result_flags = false)
    {
        //get email template details for this partner
        $this->CI->load->model('partner_model', '', true);

        $partner_data = $this->CI->partner_model->get_partner_info($partner_id);
        if (count($partner_data) <= 0) {
            return ['status' => STATUS_NG];
        }

        $dataset = [];
        $result_flags = [];
        $dataset['application_id'] = $application_id;
        $dataset['access_code'] = $application_id . random_string('alnum', 15);
        $dataset['from'] = $partner_data['ops_email'];
        $dataset['from_name'] = $partner_data['portal_name'];
        $dataset['reply_to'] = !empty($partner_data['ops_email_reply_to']) ? $partner_data['ops_email_reply_to'] : null;
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;

        if ($attachment !== "") {
            $dataset['attachment'] = $attachment;
        }


        $view_data = [
            'contents' => $html_body,
            'banner' => $partner_data['email_banner'],
            'portal_name' => $dataset['from_name'],
            'portal_url' => $partner_data['portal_url'],
            'webview_url' => $this->CI->config->item('mhub_url') . "links/html-email/" . $this->CI->encryption->url_encrypt($dataset['access_code']),
            'facebook_link' => isset($partner_data['link_facebook']) && $partner_data['link_facebook'] != "" ? $partner_data['link_facebook'] : "",
            'twitter_link' => isset($partner_data['link_twitter']) && $partner_data['link_twitter'] != "" ? $partner_data['link_twitter'] : "",
            'instagram_link' => isset($partner_data['link_instagram']) && $partner_data['link_instagram'] != "" ? $partner_data['link_instagram'] : "",
            'linkedin_link' => isset($partner_data['link_linkedin']) && $partner_data['link_linkedin'] != "" ? $partner_data['link_linkedin'] : "",
            'youtube_link' => isset($partner_data['link_youtube']) && $partner_data['link_youtube'] != "" ? $partner_data['link_youtube'] : "",
            'to_email' => $to];

        // if no email design (view) defined for this theme, use default
        $view = file_exists(APPPATH . "views/html_email/themes/" . $partner_data['email_theme'] . ".php") ? $partner_data['email_theme'] : "default";
        $final_html_str = $this->CI->load->view('html_email/themes/' . $view, $view_data, true);

        $dataset['html_message'] = $final_html_str;
        $dataset['text_message'] = $text_body;

        //send
        $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;
        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }
        return $dataset;
    }

    public function get_partner_agent_email_signature($partner_id, $agent_id)
    {
        $email_signature = "";

        $this->CI->load->model('dashboard_user_model', '', true);
        $this->CI->load->model('partner_model', '', true);

        if ($agent_id) {
            $agent_data = $this->CI->dashboard_user_model->get_user_profile($agent_id);
            if (count($agent_data) > 0) {
                $email_signature = trim($agent_data['email_signature']);

                if ($email_signature != '') {
                    $agentFullName = trim($agent_data['full_name']);
                    $agentFirstName = trim($agent_data['first_name']);
                    $agentEmail = trim($agent_data['email']);
                    $agentMobilePhone = trim($agent_data['mobile_phone']);

                    $partnerName = "";
                    $partnerAddress = "";
                    $partnerWebsiteUrl = "";
                    $partnerPortalName = "";
                    $partnerHotline = "";
                    $partnerPhone = "";

                    $partner_data = $this->CI->partner_model->get_partner_info($partner_id);
                    if (count($partner_data) > 0) {
                        $partnerName = trim($partner_data['name']);
                        $partnerAddress = trim($partner_data['address']);
                        $partnerWebsiteUrl = trim($partner_data['website_url']);
                        $partnerPortalName = trim($partner_data['portal_name']);
                        $partnerHotline = trim($partner_data['hotline']);
                        $partnerPhone = trim($partner_data['contact_phone']);
                    }

                    $email_signature = str_replace("[AGENTFULLNAME]", $agentFullName, $email_signature);
                    $email_signature = str_replace("[AGENTFIRSTNAME]", $agentFirstName, $email_signature);
                    $email_signature = str_replace("[AGENTEMAIL]", $agentEmail, $email_signature);
                    $email_signature = str_replace("[AGENTMOBILEPHONE]", $agentMobilePhone, $email_signature);

                    $email_signature = str_replace("[PARTNERNAME]", $partnerName, $email_signature);
                    $email_signature = str_replace("[PARTNERADDRESS]", $partnerAddress, $email_signature);
                    $email_signature = str_replace("[PARTNERWEBSITE]", $partnerWebsiteUrl, $email_signature);
                    $email_signature = str_replace("[PORTALNAME]", $partnerPortalName, $email_signature);
                    $email_signature = str_replace("[PARTNERHOTLINE]", $partnerHotline, $email_signature);
                    $email_signature = str_replace("[PARTNERPHONE]", $partnerPhone, $email_signature);
                }
            }
        }

        if ($partner_id && empty($email_signature)) {
            $partner_data = $this->CI->partner_model->get_partner_info($partner_id);
            if (count($partner_data) > 0) {
                $email_signature = trim($partner_data['email_signature']);

                if ($email_signature != '') {
                    $partnerName = trim($partner_data['name']);
                    $partnerAddress = trim($partner_data['address']);
                    $partnerWebsiteUrl = trim($partner_data['website_url']);
                    $partnerPortalName = trim($partner_data['portal_name']);
                    $partnerHotline = trim($partner_data['hotline']);
                    $partnerPhone = trim($partner_data['contact_phone']);

                    $agentFullName = "";
                    $agentFirstName = "";
                    $agentEmail = "";
                    $agentMobilePhone = "";

                    $agent_data = $this->CI->dashboard_user_model->get_user_profile($agent_id);
                    if (count($agent_data) > 0) {
                        $agentFullName = trim($agent_data['full_name']);
                        $agentFirstName = trim($agent_data['first_name']);
                        $agentEmail = trim($agent_data['email']);
                        $agentMobilePhone = trim($agent_data['mobile_phone']);
                    }

                    $email_signature = str_replace("[AGENTFULLNAME]", $agentFullName, $email_signature);
                    $email_signature = str_replace("[AGENTFIRSTNAME]", $agentFirstName, $email_signature);
                    $email_signature = str_replace("[AGENTEMAIL]", $agentEmail, $email_signature);
                    $email_signature = str_replace("[AGENTMOBILEPHONE]", $agentMobilePhone, $email_signature);

                    $email_signature = str_replace("[PARTNERNAME]", $partnerName, $email_signature);
                    $email_signature = str_replace("[PARTNERADDRESS]", $partnerAddress, $email_signature);
                    $email_signature = str_replace("[PARTNERWEBSITE]", $partnerWebsiteUrl, $email_signature);
                    $email_signature = str_replace("[PORTALNAME]", $partnerPortalName, $email_signature);
                    $email_signature = str_replace("[PARTNERHOTLINE]", $partnerHotline, $email_signature);
                    $email_signature = str_replace("[PARTNERPHONE]", $partnerPhone, $email_signature);
                }
            }
        }

        if ($email_signature != '') {
            $email_signature = "<br>\n<br>\n" . $email_signature;
        }

        return $email_signature;
    }

    public function get_user_email_signature($user_id)
    {
        $email_signature = "";

        $this->CI->load->model('crm_user_model', '', true);

        if ($user_id) {
            $user_data = $this->CI->crm_user_model->get_user_profile($user_id);
            if (count($user_data) > 0) {
                $email_signature = trim($user_data['email_signature']);
                if ($email_signature != '') {
                    $email = trim($user_data['email']);
                    $fullName = trim($user_data['full_name']);
                    $firstName = trim($user_data['first_name']);
                    $mobilePhone = trim($user_data['mobile_phone']);

                    $email_signature = str_replace("[FULLNAME]", $fullName, $email_signature);
                    $email_signature = str_replace("[FIRSTNAME]", $firstName, $email_signature);
                    $email_signature = str_replace("[EMAIL]", $email, $email_signature);
                    $email_signature = str_replace("[MOBILEPHONE]", $mobilePhone, $email_signature);
                }
            }
        }

        if ($email_signature != '') {
            $email_signature = "<br>\n<br>\n" . $email_signature;
        }

        return $email_signature;
    }

    /*
     *
     * As of  Aug 17, 2020, change the format of subject
     * BEFORE
     * [Lead-DCDVH1BIHJ] AMS Leads
     * [Cust-NLXER9B2B4] AMS Admin
     * [Cust-1VJH3LP55K] AMS Campaign Admin
     * AFTER
     * AMS Leads New Version Lead-DCDVH1BIHJ
     * AMS Admin New Version Cust-NLXER9B2B4
     * AMS Campaign Admin New Version Cust-1VJH3LP55K
     *
     */

    public function send_ams_email($email_id = null, $lead_id = 0, $reseller_id = 0, $partner_id = 0, $account_manager_id = 0, $from_name = "", $from = "", $reply_to = "", $to = "", $cc = "", $bcc = "", $subject = "", $html_body = "", $attachment = "", $with_system_template = true, $return_result_flags = false, $is_resend = false)
    {
        if (empty($lead_id) && empty($reseller_id) && empty($partner_id)) {
            return ['status' => STATUS_NG];
        }

        $agent_id = "";

        $dataset = [];
        $dataset['lead_id'] = !empty($lead_id) ? $lead_id : null;
        $dataset['reseller_id'] = !empty($reseller_id) ? $reseller_id : null;
        $dataset['partner_id'] = !empty($partner_id) ? $partner_id : null;
        $dataset['account_manager_id'] = !empty($account_manager_id) ? $account_manager_id : null;
        $dataset['from_name'] = $from_name;
        $dataset['from'] = $from;
        $dataset['reply_to'] = $reply_to;
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['bcc'] = $bcc;
        $dataset['html_message'] = $html_body;
        $dataset['attachment'] = $attachment;
        $dataset['subject'] = $subject;

        $this->CI->load->model('account_manager_leads_model', '', true);
        $this->CI->load->model('manager_model', '', true);
        $this->CI->load->model('dashboard_user_model', '', true);
        $this->CI->load->model('partner_model', '', true);

        // 1708592442 - Email enhancements
        // Remove the specific text in the Email subject.
        // Lead: Lead-XXXX
        // Admin: Cust-XXXX
        // Campaign Admin: Cust-XXXX
        // Need to store this elsewhere (hidden in the email body) in order to pull back emails into lead, admin or campaign admin.

        $unsubscribe = "";
        if ($lead_id) {
            $lead = $this->CI->account_manager_leads_model->getById($lead_id);
            if (!$lead) {
                return ['status' => STATUS_NG];
            }
            if (isset($lead->u_code) && !empty($lead->u_code)) {
                // $tag = "[Lead-" . $lead->u_code . "]";
                // $dataset['subject'] = $tag . " " . trim(str_ireplace($tag, "", $subject));
                // $tag = "Lead-" . $lead->u_code;
                // $subject = trim($subject);
                // if (substr($subject, (0 - strlen($tag))) == $tag) {
                //     $dataset['subject'] = $subject;
                // } else {
                //     $dataset['subject'] = $subject . " " . $tag;
                // }

                $tmp_apps_url = !empty($this->CI->config->item('mhub_apps_alternative_url')) ? $this->CI->config->item('mhub_apps_alternative_url') : $this->CI->config->item('mhub_apps_url');

                if (!$is_resend) {
                    $unsubscribe .= "<br><br> <center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [Lead-" . $lead->u_code . "].<br>If you no longer wish to receive these emails, <a href=\"" . $tmp_apps_url . "ams-unsubscribe/leads/" . $this->CI->encryption->url_encrypt($lead->u_code) . "\">unsubscribe</a></center>";
                }
            }
        } elseif ($reseller_id) {
            $manager = $this->CI->manager_model->get_manager_info($reseller_id);
            if (count($manager) <= 0) {
                return ['status' => STATUS_NG];
            }

            $agent = $this->CI->dashboard_user_model->get_user_profile($manager['manager_agent']);
            if (!$agent) {
                return ['status' => STATUS_NG];
            }

            $agent_id = $agent['id'];

            if (isset($agent['u_code']) && !empty($agent['u_code'])) {
                // $tag = "[Cust-" . $agent['u_code'] . "]";
                // $dataset['subject'] = $tag . " " . trim(str_ireplace($tag, "", $subject));
                // $tag = "Cust-" . $agent['u_code'];
                // $subject = trim($subject);
                // if (substr($subject, (0 - strlen($tag))) == $tag) {
                //     $dataset['subject'] = $subject;
                // } else {
                //     $dataset['subject'] = $subject . " " . $tag;
                // }

                $tmp_apps_url = !empty($this->CI->config->item('mhub_apps_alternative_url')) ? $this->CI->config->item('mhub_apps_alternative_url') : $this->CI->config->item('mhub_apps_url');

                if (!$is_resend) {
                    $unsubscribe .= "<br><br> <center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [Cust-" . $agent['u_code'] . "].<br>If you no longer wish to receive these emails, <a href=\"" . $tmp_apps_url . "ams-unsubscribe/manager/" . $this->CI->encryption->url_encrypt($agent['u_code']) . "\">unsubscribe</center>";
                }
            }
        } elseif ($partner_id) {
            $partner = $this->CI->partner_model->get_partner_info($partner_id);
            if (count($partner) <= 0) {
                return ['status' => STATUS_NG];
            }

            $agent = $this->CI->dashboard_user_model->get_user_profile($partner['super_agent']);
            if (!$agent) {
                return ['status' => STATUS_NG];
            }

            $agent_id = $agent['id'];

            if (isset($agent['u_code']) && !empty($agent['u_code'])) {
                // $tag = "[Cust-" . $agent['u_code'] . "]";
                // $dataset['subject'] = $tag . " " . trim(str_ireplace($tag, "", $subject));
                // $tag = "Cust-" . $agent['u_code'];
                // $subject = trim($subject);
                // if (substr($subject, (0 - strlen($tag))) == $tag) {
                //     $dataset['subject'] = $subject;
                // } else {
                //     $dataset['subject'] = $subject . " " . $tag;
                // }

                $tmp_apps_url = !empty($this->CI->config->item('mhub_apps_alternative_url')) ? $this->CI->config->item('mhub_apps_alternative_url') : $this->CI->config->item('mhub_apps_url');

                if (!$is_resend) {
                    $unsubscribe .= "<br><br> <center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [Cust-" . $agent['u_code'] . "].<br>If you no longer wish to receive these emails, <a href=\"" . $tmp_apps_url . "ams-unsubscribe/campaign-admin/" . $this->CI->encryption->url_encrypt($agent['u_code']) . "\">unsubscribe</center>";
                }
            }
        }

        $dataset['html_message'] .= $unsubscribe;

        if ($with_system_template) {
            $dataset = $this->get_mpa_email_styled($reseller_id, $partner_id, $agent_id, $from_name, $from, $reply_to, $to, $cc, $bcc, $dataset['subject'], $dataset['html_message'], $attachment);
        }

        $dataset['id'] = !empty($email_id) ? $email_id : null;
        $dataset['lead_id'] = !empty($lead_id) ? $lead_id : null;
        $dataset['reseller_id'] = !empty($reseller_id) ? $reseller_id : null;
        $dataset['partner_id'] = !empty($partner_id) ? $partner_id : null;
        $dataset['account_manager_id'] = !empty($account_manager_id) ? $account_manager_id : null;

        // email tracker
        $emailHtmlWithOutTracker = $dataset['html_message'];

        $emailTrackingImage = "<img width=\"0\" height=\"0\" src=\"" . $this->CI->config->item('mhub_apps_url') . "email-tracker/ams?id=" . $this->CI->encryption->url_encrypt($email_id) . "\">";
        $pos = strrpos($dataset['html_message'], "</body>");
        if ($pos !== false) {
            $dataset['html_message'] = substr_replace($dataset['html_message'], $emailTrackingImage, $pos, strlen("</body>"));
        } else {
            $dataset['html_message'] .= $emailTrackingImage;
        }

        $result_flags = [];

        //send
        $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        $dataset['html_message'] = $emailHtmlWithOutTracker;

        return $dataset;
    }

    /*
     *
     * Ticket is created on CONNECT and APPS (Widget)
     *
     */

    public function connectTicketSubject($ticket, $subject)
    {
        $tag = $ticket->reference_code;

        $subject = trim($subject);

        if (substr($subject, (0 - strlen($tag))) == $tag) {
            return $subject;
        }

        $subject = $subject . " " . $tag;

        return $subject;
    }

    /*
     *
     * Too many recipients, rather than direct email send, queue it
     *
     */

    public function connectEmailSend($recipients, $subject, $html_template, $text_template)
    {
        if (count($recipients) > 0) {
            $this->CI->load->model('connect_users_model');
            $this->CI->load->model('connect_model');
            $this->CI->load->model('communications_model');

            foreach ($recipients as $recipient) {
                $connectUser = $this->CI->connect_users_model->getById($recipient);
                if ($connectUser) {
                    $user = $this->CI->connect_model->getUser($connectUser->user_table, $connectUser->user_id);
                    if ($user) {
                        if (did_single_email_opted_out($user['email'], EMAIL_SUBSCRIPTION_REPORTS)) {
                            continue;
                        }

                        //QUEUE EMAIL
                        $email_dataset = [];
                        $email_dataset['category_id'] = EMAIL_SUBSCRIPTION_REPORTS;
                        $email_dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
                        $email_dataset['from_name'] = $this->CI->config->item('mm8_system_name');
                        $email_dataset['to'] = $user['email'];
                        $email_dataset['reply_to'] = $connectUser->email_reply_to;
                        $email_dataset['subject'] = $subject;
                        $email_dataset['html_message'] = $this->CI->load->view('html_email/basic_mail', ['contents' => $html_template], true);
                        $email_dataset['text_message'] = $text_template;

                        switch ($connectUser->user_table) {
                            case CONNECT_USER_TABLE_TBL_PARTNER_AGENTS:
                                // with Whitelabel
                                $email_dataset = $this->get_mpa_email_styled("", "", $connectUser->user_id, $email_dataset['from_name'], $email_dataset['from'], $email_dataset['reply_to'], $email_dataset['to'], "", "", $email_dataset['subject'], $html_template, "");
                                break;
                            case CONNECT_USER_TABLE_TBL_USER:
                                break;
                            case CONNECT_USER_TABLE_TBL_CUSTOMER:
                                break;
                            case CONNECT_USER_TABLE_TBL_USER_MARKETPLACE:
                                break;
                            default:
                        }

                        if ($this->CI->communications_model->queue_email($email_dataset) === false) {
                            $this->CI->db->trans_rollback();
                            echo json_encode(['successful' => false, 'error' => ERROR_406]);
                            return;
                        }
                    }
                }
            }
        }
    }

    public function connectTicketWatcherRecipients($recipients, $ticket)
    {
        $this->CI->load->model('connect_ticket_watchers_model');

        $filter = [
            'ticket_id' => $ticket->id,
        ];
        $ticketWatchers = $this->CI->connect_ticket_watchers_model->fetch($filter);
        if (count($ticketWatchers) > 0) {
            foreach ($ticketWatchers as $key => $ticketWatcher) {
                $recipients[$ticketWatcher->connect_user_id] = $ticketWatcher->connect_user_id;
            }
        }

        return $recipients;
    }
}
