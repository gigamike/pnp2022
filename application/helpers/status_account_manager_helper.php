<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('status_account_manager')) {
    /*
     *
     * Display notification for partner and agent but not in admin
     *
     */

    function status_account_manager()
    {
        $html = "";

        $ci = &get_instance();
        $ci->load->model('dashboard_user_model');

        if (isset($ci->session->utilihub_hub_account_manager_id) && isset($ci->session->utilihub_hub_account_manager_user_id)) {
            $agentIS = $ci->dashboard_user_model->get_user_profile($ci->session->utilihub_hub_account_manager_user_id);
            if ($agentIS) {
                $html .= <<<EOT
<div class="row border-bottom">
    <div class="white-bg">
        <div class="text-center p-xxs">
          <strong><i class="fa fa-eye" aria-hidden="true"></i> Remote Access:</strong> {$agentIS['full_name']} / {$agentIS['email']}
        </div>
    </div>
</div>
EOT;
            }
        }

        return $html;
    }
}
