<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('status_task_due_helper')) {
    /*
     *
     * Display notification for partner and agent but not in admin
     *
     */

    function status_task_due_helper()
    {
        $html = "";

        $ci = &get_instance();
        $ci->load->model('account_manager_log_tasks_model');

        $filter = [
            'status' => AMS_TASK_STATUS_OPEN,
            'account_manager_id' => $ci->session->utilihub_ams_account_manager_id,
            'date_due_less_than_or_equal_minute' => 0,
        ];
        $countTaskDue = $ci->account_manager_log_tasks_model->getCountTaskDue($filter);
        if ($countTaskDue > 0) {
            $html = " <span class=\"badge badge-danger\">" . $countTaskDue . "</span>";
        }

        return $html;
    }
}
