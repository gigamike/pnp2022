<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_short_url_link')) {

    /**
     * get_short_url_link
     *
     * defualt tiny url. this points to retrieve application
     *
     * @param	none
     * @return	string
     */
    function get_short_url_link($partner_reference, $app_ref_code, $application_id, $override_application_tiny_url = true)
    {
        $CI = & get_instance();
        $CI->load->model('short_url_model');
        $CI->load->model('application_model');

        $tmp_apps_url = !empty($CI->config->item('mhub_apps_alternative_url')) ? $CI->config->item('mhub_apps_alternative_url') : $CI->config->item('mhub_apps_url');
        $long_url = $tmp_apps_url . 'retrieve-application/start/' . $partner_reference . '/' . $CI->encryption->url_encrypt($app_ref_code) . '/';
        $tiny_code = $CI->short_url_model->set_url($long_url);
        if ($tiny_code === false) {
            return "";
        }

        if ($override_application_tiny_url) {
            if (!$CI->application_model->set_application_tiny_url_code($application_id, $tiny_code)) {
                return "";
            }
        }

        return $CI->config->item('mhub_tiny_url') . $tiny_code;
    }
}

if (!function_exists('get_short_url_link_customer_portal')) {

    /**
     * get_short_url_link_customer_portal
     *
     * tiny url that points to customer portal
     *
     * @param	none
     * @return	string
     */
    function get_short_url_link_customer_portal($partner_reference, $app_ref_code, $application_id, $override_application_tiny_url = true)
    {
        $CI = & get_instance();
        $CI->load->model('short_url_model');
        $CI->load->model('application_model');

        $tmp_apps_url = !empty($CI->config->item('mhub_apps_alternative_url')) ? $CI->config->item('mhub_apps_alternative_url') : $CI->config->item('mhub_apps_url');
        $long_url = $tmp_apps_url . 'customer-portal/start/' . $partner_reference . '/' . $CI->encryption->url_encrypt($app_ref_code) . '/';
        $tiny_code = $CI->short_url_model->set_url($long_url);
        if ($tiny_code === false) {
            return "";
        }

        if ($override_application_tiny_url) {
            if (!$CI->application_model->set_application_tiny_url_code($application_id, $tiny_code)) {
                return "";
            }
        }

        return $CI->config->item('mhub_tiny_url') . $tiny_code;
    }
}

if (!function_exists('get_short_url_link_customer_portal_v2_property_checklist')) {

    /**
     * get_short_url_link_customer_portal_v2_property_checklist
     *
     * tiny url that points to customer portal v2
     *
     * @param	none
     * @return	string
     */
    function get_short_url_link_customer_portal_v2_property_checklist($partner_id, $application_id, $cust_email, $cust_address_id)
    {
        $CI = & get_instance();
        $CI->load->model('short_url_model');
        $CI->load->model('application_model');
        $CI->load->model('partner_customer_portals_v2_model');
        $CI->load->model('partner_microsite_model');
        $CI->load->model('address_model');

        // get microsite domain where customer portal is hosted
        $customerPortal = $CI->partner_customer_portals_v2_model->getByPartnerId($partner_id);
        if ($customerPortal) {
            $microsite = $CI->partner_microsite_model->getById($customerPortal->partner_microsite_id);
            if ($microsite) {
                $preview_url = $CI->partner_microsite_model->get_site_fqdn($microsite->id);
                if ($preview_url) {
                    $address = $CI->address_model->getById($cust_address_id);
                    if ($address) {
                        $md5_address = $address->md5_address;

                        $long_url = $preview_url . "/customer-portal/shared/customer/" . $CI->encryption->url_encrypt($cust_email) . "/" . $CI->encryption->url_encrypt($md5_address);
                        $tiny_code = $CI->short_url_model->set_url($long_url);
                        if ($tiny_code === false || !$CI->application_model->set_application_tiny_url_code_customer_portal_v2_application($application_id, $tiny_code)) {
                            return '';
                        }

                        return $CI->config->item('mhub_tiny_url') . $tiny_code;
                    }
                }
            }
        }

        return '';
    }
}

if (!function_exists('get_short_url_link_customer_portal_v2_application')) {

    /**
     * get_short_url_link_customer_portal_v2_application
     *
     * tiny url that points to customer portal v2
     *
     * @param	none
     * @return	string
     */
    function get_short_url_link_customer_portal_v2_application($partner_id, $reference_code, $application_id)
    {
        $CI = & get_instance();
        $CI->load->model('short_url_model');
        $CI->load->model('application_model');
        $CI->load->model('partner_customer_portals_v2_model');
        $CI->load->model('partner_microsite_model');

        // get microsite domain where customer portal is hosted
        $customerPortal = $CI->partner_customer_portals_v2_model->getByPartnerId($partner_id);
        if ($customerPortal) {
            $microsite = $CI->partner_microsite_model->getById($customerPortal->partner_microsite_id);
            if ($microsite) {
                $preview_url = $CI->partner_microsite_model->get_site_fqdn($microsite->id);
                if ($preview_url) {
                    $long_url = $preview_url . "/customer-portal/shared/application/" . $CI->encryption->url_encrypt($reference_code);
                    $tiny_code = $CI->short_url_model->set_url($long_url);
                    if ($tiny_code === false || !$CI->application_model->set_application_tiny_url_code_customer_portal_v2_property_checklist($application_id, $tiny_code)) {
                        return '';
                    }

                    return $CI->config->item('mhub_tiny_url') . $tiny_code;
                }
            }
        }


        return '';
    }
}

if (!function_exists('get_system_featured_plan_highlight_flag')) {

    /**
     * get_system_featured_plan_highlight_flag
     *
     * get config setting for the system to display highlighted featured plan
     *
     * @param	none
     * @return	string
     */
    function get_system_featured_plan_highlight_flag()
    {
        $CI = & get_instance();
        return $CI->config->item('mm8_system_feature_highlight_featured_plan');
    }
}

if (!function_exists('get_partner_service_offer_type_featured_plan_display_flag')) {

    /**
     * get_partner_featured_plan_display_flag
     *
     * get partner settings for the system to display highlighted featured plan
     *
     * @param	partner_id
     * @return	string
     */
    function get_partner_service_offer_type_featured_plan_display_flag($partner_id, $service_id, $offer_type)
    {
        $CI = & get_instance();
        $service_offer_type_featured_on = 0;
        $partner_featured_on = 0;
        $CI->load->model('services_model');
        //check for partner settings featured_plan_display_on
        $partner_row = $CI->services_model->get_featured_on_value_partner($partner_id);

        if ($partner_row->featured_plan_display_on == STATUS_OK) {
            $partner_featured_on = STATUS_OK;
            //check for service offer type settings offer_type_movein_featured_on,  offer_type_moveout_featured_on
            $service_row = $CI->services_model->get_featured_on_value_service_offer_types($service_id);

            //check if it is on for $offer_type in service-partner switchcase
            if ($offer_type == OFFER_TYPE_DEFAULT) {
                $service_offer_type_featured_on = $service_row->offer_type_default_featured_on;
            }
            if ($offer_type == OFFER_TYPE_MOVEIN) {
                $service_offer_type_featured_on = $service_row->offer_type_movein_featured_on;
            }
            if ($offer_type == OFFER_TYPE_BETTERDEAL) {
                $service_offer_type_featured_on = $service_row->offer_type_betterdeal_featured_on;
            }
            if ($offer_type == OFFER_TYPE_RETENTION) {
                $service_offer_type_featured_on = $service_row->offer_type_retention_featured_on;
            }
            if ($offer_type == OFFER_TYPE_MOVEOUT) {
                $service_offer_type_featured_on = $service_row->offer_type_moveout_featured_on;
            }
            if ($offer_type == OFFER_TYPE_QUOTE) {
                $service_offer_type_featured_on = $service_row->offer_type_quote_featured_on;
            }
            if ($offer_type == OFFER_TYPE_HOMEOWNER) {
                $service_offer_type_featured_on = $service_row->offer_type_homeowner_featured_on;
            }
            if ($offer_type == OFFER_TYPE_RENTER) {
                $service_offer_type_featured_on = $service_row->offer_type_renter_featured_on;
            }
        }

        return $service_offer_type_featured_on;
    }
}

if (!function_exists('serialize_scorecard_details_to_json')) {
    function serialize_scorecard_details_to_json($scorecard_details, $plan_id)
    {
        $output_scorecard_id = null;
        $output_questions = [];
        foreach ($scorecard_details as $scorecard_id => $scorecard_questions) {
            $output_scorecard_id = $scorecard_id;
            foreach ($scorecard_questions as $scorecard_question) {
                $output_questions[] = [
                    'question_id' => $scorecard_question['question_id'],
                    'answer' => $scorecard_question['answer'],
                    'score' => $scorecard_question['score'],
                    'total_score' => $scorecard_question['total_score'],
                    'comment' => $scorecard_question['comment']
                ];
            }
        }

        return [
            'scorecard_id' => $output_scorecard_id,
            'plan_id' => $plan_id,
            'qna' => $output_questions
        ];
    }
}

if (!function_exists('redact_short_url_link')) {

    /**
     * redact_short_url_link
     *
     * replace short url links to {LINK REMOVED}
     *
     * @param	none
     * @return	string
     */
    function redact_short_url_link($str)
    {
        $CI = & get_instance();
        return preg_replace('/' . str_replace([':', '/', '.'], ['\:', '\/', '\.'], $CI->config->item('mhub_tiny_url')) . '([a-zA-Z0-9_\-\/]+)/', '{LINK REMOVED}', $str);
    }
}

if (!function_exists('redact_href_tags')) {

    /**
     * redact_href_tags
     *
     * replace href links to #
     *
     * @param	none
     * @return	string
     */
    function redact_href_tags($str)
    {
        $CI = & get_instance();
        return preg_replace('/' . 'href(\s*)=(\s*)\"(\S+)\"/', 'href="#"', $str);
    }
}
