<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('str_to_array')) {
    function str_to_array($email)
    {
        if (! is_array($email)) {
            return (strpos($email, ',') !== false)
                ? preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY)
                : (array) trim($email);
        }

        return $email;
    };
}

if (!function_exists('clean_email')) {
    function clean_email($email)
    {
        if (! is_array($email)) {
            return preg_match('/\<(.*)\>/', $email, $match) ? $match[1] : $email;
        }

        $clean_email = array();

        foreach ($email as $addy) {
            $clean_email[] = preg_match('/\<(.*)\>/', $addy, $match) ? $match[1] : $addy;
        }

        return $clean_email;
    };
}


if (!function_exists('is_email_blacklisted')) {

    /**
    *
    *  determine whether an email is present in the tbl_email_blacklist
    *
    * @param $input string - a valid email address, could be multiple seperated by comma
    * @return array - array of values that indicate whether a certain email is blacklisted or not
    *
    */
    function is_email_blacklisted($input)
    {
        $output = [];

        if (is_null($input) || empty($input)) {
            return $output;
        }

        // seperate comma delimited multiple emails
        $input = str_to_array($input);
        
        // reduce "Name <name@domain.com>" to "name@domain.com"
        $input = clean_email($input);

        $CI = & get_instance();
        $CI->load->model('communications_model');

        foreach ($input as $email_item) {
            if (filter_var($email_item, FILTER_VALIDATE_EMAIL)) {
                $output[$email_item] = $CI->communications_model->email_exists_in_blacklist_db($email_item);
            }
        }

        return $output;
    }
}

if (!function_exists('blacklist_email')) {
    function blacklist_email($input, $blacklist_type = 1, $bounce_type = 1, $added_by = 'unknown')
    {
        $output = true;
        if (is_null($input) || empty($input)) {
            return false;
        }

        // seperate comma delimited multiple emails
        $input = str_to_array($input);
        
        // reduce "Name <name@domain.com>" to "name@domain.com"
        $input = clean_email($input);


        $CI = & get_instance();
        $CI->load->model('communications_model');
        foreach ($input as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $output = $CI->communications_model->blacklist_email($email, $blacklist_type, $bounce_type, $added_by);
            }
        }
        return $output;
    }
}


if (!function_exists('remove_email_blacklist')) {
    function remove_email_blacklist($input)
    {
        $output = true;
        if (is_null($input) || empty($input)) {
            return false;
        }

        // seperate comma delimited multiple emails
        $input = str_to_array($input);
        
        // reduce "Name <name@domain.com>" to "name@domain.com"
        $input = clean_email($input);

        $CI = & get_instance();
        $CI->load->model('communications_model');
        foreach ($input as $email) {
            $output = $CI->communications_model->remove_email_blacklist($email);
        }
        return $output;
    }
}


if (!function_exists('is_customer_email_blacklisted')) {
    function is_customer_email_blacklisted($customer_id)
    {
        $CI = & get_instance();
        $CI->load->model('communications_model');
        return $CI->communications_model->is_customer_email_blacklisted($customer_id);
    }
}

if (!function_exists('did_single_email_opted_out')) {
    /**
     *  Determines if the email provided is present in tbl_subscriptions_categories_setting.email
     *  @param $email - a simple email, like darwin@utilihub.io, dont pass comma-seperated email in here
     *  @param $category_id - any id from tbl_subscriptions_categories.id
     * 
     *  @return Boolean whether the email opted-out for that category
     */
    function did_single_email_opted_out($email, $category_id)
    {
        $output = false;

        $email = clean_email($email);

        $CI = & get_instance();
        $CI->load->model('subscription_model');
        return $CI->subscription_model->did_opted_out($email,$category_id);
    }
}

if (!function_exists('remove_opted_out_email')) {
    /**
     *  Removes an email from a comma-seperated list if it that email opted-out for that category
     *  @param $input - comma-separated email e.g. email1@utilihub.io, anotheremail@movinghub.io
     *  @param $category_id - any id from tbl_subscriptions_categories.id
     * 
     *  @return String returns the emails, with the opted-out email removed (if any)
     */
    function remove_opted_out_email($input, $category_id)
    {
        $output = $input;

        // seperate comma delimited multiple emails
        $input = str_to_array($input);

        // reduce "Name <name@domain.com>" to "name@domain.com"
        $input = clean_email($input);

        $CI = & get_instance();
        $CI->load->model('subscription_model');
        $tmp = [];

        foreach ($input as $email) {
            if(!$CI->subscription_model->did_opted_out($email, $category_id) && filter_var($email, FILTER_VALIDATE_EMAIL))
                $tmp[] = $email;
        }
        
        $output = implode(",",$tmp);

        return $output;
    }
}

if (!function_exists('is_multi_email')) {
    /**
     *  Determines if the provided email is a simple email, or comma-separeted email
     *  @param $email - comma-separated email e.g. email1@utilihub.io, anotheremail@movinghub.io or simple email
     * 
     *  @return Boolean returnes true if comma-seperated emails
     */
    function is_multi_email($email)
    {
        $input = str_to_array($email);
        return count($input) > 1;
    }
}

if (!function_exists('did_email_opted_out')) {
    /**
     *  Determines if the list of emails provided is present in tbl_subscriptions_categories_setting.email
     *  @param $email - a single email or comma-seperated email
     *  @param $category_id - any id from tbl_subscriptions_categories.id
     * 
     *  @return mixed if a list of email is provided, it will return the list with the unsubscribed email removed. 
     *                If all the emails is unsubscribed, then it will return true.
     *                if a single email email is provided, it will return that email if the email is subscribed
     *                else it will return true
     */
    function did_email_opted_out($email, $category_id)
    {
        $output = false;
        $tmp_email = '';

        if(is_multi_email($email))
            $tmp_email = remove_opted_out_email($email, $category_id);
        else if(did_single_email_opted_out($email, $category_id))
            $tmp_email = '';
        else
            $tmp_email = $email;


        // if blank all of the email haved opted out 
        if ($tmp_email == "")
            return true;
        else
            return $tmp_email;
    }
}

if (!function_exists('did_phone_opted_out')) {
    /**
     *  Determines if the phone provided is present in tbl_subscriptions_categories_setting.phone_number
     *  @return Boolean whether the phone opted-out for that category
     */
    function did_phone_opted_out($phone, $category_id)
    {
        $output = false;

        $CI = & get_instance();
        $CI->load->model('subscription_model');
        return $CI->subscription_model->did_phone_opted_out($phone,$category_id);
    }
}
