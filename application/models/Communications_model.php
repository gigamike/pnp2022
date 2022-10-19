<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Communications_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_sns_notification($message_type, $topic, $data)
    {
        $str_sql = $this->db->insert_string('tbl_sns_notifications', [
            'topic' => $topic,
            'message_type' => $message_type,
            'data' => is_array($data) ? json_encode($data) : $data
        ]);

        $this->db->query($str_sql);
        return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }

    public function is_customer_email_blacklisted($customer_id)
    {
        $output = false;

        $qstr = "SELECT email FROM tbl_customer WHERE id = " . $this->db->escape($customer_id) . " LIMIT 1";

        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            $customer = $query->row_array();
            if ($this->email_exists_in_blacklist_db($customer['email'])) {
                $output = true;
            }
        }

        return $output;
    }

    public function email_exists_in_blacklist_db($email)
    {
        $qstr = "SELECT COUNT(id) as cnt
            FROM tbl_email_blacklist WHERE email = " . $this->db->escape($email);

        $query = $this->db->query($qstr);
        $result = $query && $query->num_rows() > 0 ? (int) $query->row_array()['cnt'] : 0;

        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function blacklist_email($email, $blacklist_type = 1, $bounce_type = 1, $added_by = 'unknown')
    {
        if (!$this->email_exists_in_blacklist_db($email)) {
            $data = [
                'email' => $email,
                'blacklist_type' => $blacklist_type,
                'bounce_type' => $bounce_type,
                'added_by' => $added_by
            ];

            $str_sql = $this->db->insert_string('tbl_email_blacklist', $data);
            $query = $this->db->query($str_sql);
            if (!$query) {
                $error = $this->db->error(); // Has keys 'code' and 'message'
                return (int) $error['code'] === 1062 || (int) $error['code'] === 1586 ? -1 : false;
            } else {
                return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
            }
        } // if doesnt exists
    }

    // black list email

    public function remove_email_blacklist($email)
    {
        $this->db->where('email', $email);
        $this->db->delete('tbl_email_blacklist');
        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function dt_get_blacklist_summary_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(id) AS cnt
        FROM
        tbl_email_blacklist
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function dt_get_blacklist_summary($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "email" => "email",
            "blacklist_type" => "blacklist_type",
            "bounce_type" => "bounce_type",
            "date_added" => "date_added",
            "date_bounced" => "date_last_sent",
            "added_by" => "added_by"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                *
            FROM
            tbl_email_blacklist
        " . $condition . $order_by . "
        LIMIT " . $start . "," . $length;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    //by default use movinghub (1)
    public function get_sms_template($action)
    {
        //try given provider first
        $qstr = "SELECT template FROM tbl_template_sms WHERE action = " . $this->db->escape($action) . " LIMIT 1";

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                return $query->row_array()['template'];
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                //retry for default partner movinghub
                $qstr = "SELECT template FROM tbl_template_sms WHERE action = " . $this->db->escape($action) . " AND partner_id = " . $this->config->item('mm8_system_default_partner') . " LIMIT 1";

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    return ($query->num_rows() > 0) ? $query->row_array()['template'] : false;
                }
            }

            return false;
        }
    }

    //by default use movinghub (1)
    public function get_email_template($action)
    {
        //try given provider first
        $qstr = "SELECT * FROM tbl_template_email WHERE action = " . $this->db->escape($action) . " LIMIT 1";

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                return $query->row_array();
            }

            return false;
        }
    }

    public function add_job($dataset)
    {
        //insert
        $str_sql = $this->db->insert_string('tbl_daemon_backlogs', $dataset);
        $this->db->query($str_sql);
        return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }

    public function get_jobs($method)
    {
        $ret_data = [];

        $qstr = "SELECT
        id,
        application_id,
        template,
                recipient
        FROM tbl_daemon_backlogs
        WHERE method = " . $this->db->escape($method) . "
        ORDER BY date_added ASC";

        $query = $this->db->query($qstr);
        if (!$query) {
            return [];
        } else {
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $ret_data[$row['id']] = $row;
                }
            }

            if (count($ret_data) > 0) {
                //DELETE
                $qstr = "DELETE FROM tbl_daemon_backlogs WHERE id IN (" . implode(",", array_keys($ret_data)) . ")";
                if (!$this->db->simple_query($qstr)) {
                    $ret_data = [];
                }
            }

            return $ret_data;
        }
    }

    public function retrieve_sms($id, $fields = [])
    {
        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_log_sms WHERE id = " . $this->db->escape($id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        if (!$query) {
            return [];
        } else {
            $ret_data = [];

            if ($query->num_rows() > 0) {
                $ret_data = $query->row_array();
            }

            return $ret_data;
        }
    }

    public function retrieve_email($id, $fields = [])
    {
        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_log_email WHERE id = " . $this->db->escape($id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        if (!$query) {
            return [];
        } else {
            $ret_data = [];

            if ($query->num_rows() > 0) {
                $ret_data = $query->row_array();
            }

            return $ret_data;
        }
    }

    /**
     *  NEW SMS AND EMAIL GATEWAY
     *  this will be used by other emails that's not related to the moving service (mhub)
     */
    public function queue_email($dataset)
    {
        if (isset($dataset['attachment']) && is_array($dataset['attachment'])) {
            $dataset['attachment'] = implode("::", $dataset['attachment']);
        }

        $str_sql = $this->db->insert_string('tbl_gateway_email', $dataset);
        return $this->db->query($str_sql);
    }

    public function check_queue_email()
    {
        $qstr = "SELECT * FROM tbl_gateway_email WHERE processed = '0' AND date_processed = '0000-00-00 00:00:00'";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row;
            }
        }

        //update processed to 1 so it wont be picked up in case theres a cron overlap
        if (count($ret_data) > 0) {
            $this->db->set('processed', 1);
            $this->db->where_in('id', array_keys($ret_data));
            if ($this->db->update('tbl_gateway_email') === false) {
                return false;
            }
        }

        return $ret_data;
    }

    public function update_queue_email($jobid, $dataset)
    {
        //update
        $str_sql = $this->db->update_string('tbl_gateway_email', $dataset, "id = " . $jobid);
        return $this->db->simple_query($str_sql) ? true : false;
    }

    public function queue_sms($dataset)
    {
        $str_sql = $this->db->insert_string('tbl_gateway_sms', $dataset);
        return $this->db->query($str_sql);
    }

    public function check_queue_sms()
    {
        $qstr = "SELECT * FROM tbl_gateway_sms WHERE processed = '0' AND date_processed = '0000-00-00 00:00:00'";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row;
            }
        }

        //update processed to 1 so it wont be picked up in case theres a cron overlap
        if (count($ret_data) > 0) {
            $this->db->set('processed', 1);
            $this->db->where_in('id', array_keys($ret_data));
            if ($this->db->update('tbl_gateway_sms') === false) {
                return false;
            }
        }

        return $ret_data;
    }

    public function update_queue_sms($jobid, $dataset)
    {
        //update
        $str_sql = $this->db->update_string('tbl_gateway_sms', $dataset, "id = " . $jobid);
        return $this->db->simple_query($str_sql) ? true : false;
    }

    //function to return email template actin names array for the category and/or partner
    public function get_email_templates_list($category = null, $partner_id = null)
    {
        if (empty($partner_id)) {
            $partner_id = $this->config->item('mm8_system_default_partner');
        }

        //try given provider first
        $qstr = "SELECT
                action,
                subject,
                html_template,
                text_template
                FROM tbl_template_email
                WHERE partner_id = " . $this->db->escape($partner_id) . "
                AND category = " . $this->db->escape($category);

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $ret_data[$row['action']] = $row['action'];
                }
                return $ret_data;
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                //retry for default partner movinghub
                $qstr = "SELECT
                        action,
                        subject,
                        html_template,
                        text_template
                        FROM tbl_template_email
                        WHERE partner_id = " . $this->config->item('mm8_system_default_partner') . "
                        AND category = " . $this->db->escape($category);

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    if ($query->num_rows() > 0) {
                        foreach ($query->result_array() as $row) {
                            $ret_data[$row['action']] = $row['action'];
                        }
                        return $ret_data;
                    }
                }
            }

            return false;
        }
    }

    //function to return email template actin names array for the category and/or partner
    public function get_sms_templates_list($category = null, $partner_id = null)
    {
        if (empty($partner_id)) {
            $partner_id = $this->config->item('mm8_system_default_partner');
        }

        //try given provider first
        $qstr = "SELECT
                action,
                template
                FROM tbl_template_sms
                WHERE partner_id = " . $this->db->escape($partner_id) . "
                AND category = " . $this->db->escape($category);

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $ret_data[$row['action']] = $row['action'];
                }
                return $ret_data;
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                //retry for default partner movinghub
                $qstr = "SELECT
                        action,
                        template
                        FROM tbl_template_sms
                        WHERE partner_id = " . $this->config->item('mm8_system_default_partner') . "
                        AND category = " . $this->db->escape($category);

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    if ($query->num_rows() > 0) {
                        foreach ($query->result_array() as $row) {
                            $ret_data[$row['action']] = $row['action'];
                        }
                        return $ret_data;
                    }
                }
            }

            return false;
        }
    }

    public function get_all_email_templates_list()
    {
        $ret_data = [];

        //retry for default partner movinghub
        $qstr = "SELECT DISTINCT(action) AS action FROM tbl_template_email ORDER BY 1";

        $query = $this->db->query($qstr);
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row['action']);
            }
        }

        return $ret_data;
    }

    public function get_all_sms_templates_list()
    {
        $ret_data = [];

        //retry for default partner movinghub
        $qstr = "SELECT DISTINCT(action) AS action FROM tbl_template_sms ORDER BY 1";

        $query = $this->db->query($qstr);
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row['action']);
            }
        }

        return $ret_data;
    }

    public function get_commission_templates($start, $length, $order_col, $order_dir, $return_count = false)
    {
        if ($return_count !== true) {//return actual data
            $qstr = "SELECT *
                FROM (SELECT id, name, description, active, 'Affiliate' template_type from tbl_plan_commission_template_affiliate_list
                union
                SELECT id, name, description, active, 'Partner' template_type from tbl_plan_commission_template_mpa_list) AS t" .
                    " ORDER BY " . $order_col . " " . strtoupper($order_dir) . //.
                    " LIMIT " . $start . "," . $length;
        } else {//return count
            $qstr = "SELECT count(name) as total
                FROM (SELECT name from tbl_plan_commission_template_affiliate_list
                union
                SELECT name from tbl_plan_commission_template_mpa_list) AS t";
        }
        $query = $this->db->query($qstr);
//        return $qstr;
        if (!$query) {
            return false;
        } else {
            if ($return_count !== true) {//return actual data
                if ($query->num_rows() > 0) {
                    return $query->result_array();
                }
                return false;
            } else {
                return $query->num_rows() > 0 ? $query->row_array()['total'] : 0;
            }
        }
    }

    public function get_sla_templates($start, $length, $order_col, $order_dir, $return_count = false)
    {
        if ($return_count !== true) {//return actual data
            $qstr = "SELECT id, name
                FROM tbl_sla_template " .
                    " ORDER BY " . $order_col . " " . strtoupper($order_dir) . //.
                    " LIMIT " . $start . "," . $length;
        } else {//return count
            $qstr = "SELECT count(id) as total
                FROM tbl_sla_template";
        }
        $query = $this->db->query($qstr);
//        return $qstr;
        if (!$query) {
            return false;
        } else {
            if ($return_count !== true) {//return actual data
                if ($query->num_rows() > 0) {
                    return $query->result_array();
                }
                return false;
            } else {
                return $query->num_rows() > 0 ? $query->row_array()['total'] : 0;
            }
        }
    }

    public function get_campaign($campaign_id, $fields = [])
    {
        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_backend_campaigns WHERE id = " . $this->db->escape($campaign_id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            $ret_data = $query->row_array();
        }

        return $ret_data;
    }

    public function set_campaign($dataset, $campaign_id = "")
    {
        if ($campaign_id == "") {
            //insert
            $str_sql = $this->db->insert_string('tbl_backend_campaigns', $dataset);
            $query = $this->db->query($str_sql);
            if (!$query) {
                $error = $this->db->error(); // Has keys 'code' and 'message'
                return (int) $error['code'] === 1062 || (int) $error['code'] === 1586 ? -1 : false;
            } else {
                return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
            }
        } else {
            //update
            $str_sql = $this->db->update_string('tbl_backend_campaigns', $dataset, "id = " . $campaign_id);
            return $this->db->simple_query($str_sql) ? $campaign_id : false;
        }
    }

    public function get_campaigns_in_schedule()
    {
        $qstr = "SELECT
                id,
                weekday,
                day,
                hour,
                minute,
                DAYOFWEEK(NOW()) AS current_weekday,
                DAYOFMONTH(NOW()) AS current_day,
                HOUR(NOW()) AS current_hour,
                MINUTE(NOW()) AS current_minute,
                type,
                category
        FROM tbl_backend_campaigns
        WHERE active = 1";

        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row;
            }
        }
        return $ret_data;
    }

    public function set_processed_campaigns_in_applications($dataset)
    {
        return $this->db->insert_batch('tbl_backend_campaigns_applications', $dataset);
    }

    public function update_campaigns_run_date($id)
    {
        $condition = is_array($id) ? "id IN (" . implode(",", $id) . ")" : "id = " . $this->db->escape($id);
        $qstr = "UPDATE tbl_backend_campaigns SET date_last_run = NOW() WHERE " . $condition;

        if (!$this->db->simple_query($qstr)) {
            log_message('error', $this->db->error()['message']);
            return false;
        } else {
            return true;
        }
    }

    public function get_commission_template($type, $template_id)
    {
        $qstr = "SELECT name, description
        FROM tbl_plan_commission_template_" . $type . "_list
        WHERE id = " . $this->db->escape($template_id);
        $query = $this->db->query($qstr);
        if ($query && ($query->num_rows() > 0)) {
            return $query->row_array();
        }
        return false;
    }

    public function get_sla_template($template_id)
    {
        $qstr = "SELECT name
        FROM tbl_sla_template
        WHERE id = " . $this->db->escape($template_id);
        $query = $this->db->query($qstr);
        if ($query && ($query->num_rows() > 0)) {
            return $query->row_array();
        }
        return false;
    }

    public function get_commission_template_providers($type, $template_id)
    {
        $qstr = "SELECT distinct pp.id,pp.name FROM tbl_plan_commission_template_" . $type . "_list al
                INNER JOIN tbl_plan_commission_template_" . $type . " a on a.template_id = al.id
                INNER JOIN tbl_plan p on p.id = a.plan_id
                INNER JOIN tbl_provider pp on pp.id = p.provider_id
                WHERE al.id = " . $this->db->escape($template_id);
        $query = $this->db->query($qstr);
        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row['name'];
            }
        }
        return $ret_data;
    }

    public function get_commission_template_services($type, $template_id)
    {
        $qstr = "SELECT distinct s.id,s.name FROM tbl_plan_commission_template_" . $type . "_list al
                INNER JOIN tbl_plan_commission_template_" . $type . " a on a.template_id = al.id
                INNER JOIN tbl_plan p on p.id = a.plan_id
                INNER JOIN tbl_service s on s.id = p.service_id
                WHERE al.id = " . $this->db->escape($template_id);
        $query = $this->db->query($qstr);
        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row['name'];
            }
        }
        return $ret_data;
    }

    //get affiliate commissions mapped to the given template
    public function get_commission_template_comms_affiliate($template_id, $order_col, $order_dir, $return_count, $condition = "")
    {
        if ($condition === "") {
            $condition = " WHERE ta.template_id = " . $this->db->escape($template_id);
        } else {
            $condition .= " AND ta.template_id = " . $this->db->escape($template_id);
        }
        if ($return_count !== true) {//return actual data
            $qstr = "SELECT ta.id, pp.name provider_name, s.name service_name, p.name plan_name, p.id plan_id, ta.commission FROM tbl_plan_commission_template_affiliate ta
                INNER JOIN tbl_plan p on p.id = ta.plan_id
                INNER JOIN tbl_service s on s.id = p.service_id
                INNER JOIN tbl_provider pp on pp.id = p.provider_id
                " . $condition .
                    " ORDER BY " . $order_col . " " . strtoupper($order_dir); //.
//                " LIMIT " . $start . "," . $length;
        } else {//return count
            $qstr = "SELECT
                count(ta.id) as total
        FROM tbl_plan_commission_template_affiliate ta
                INNER JOIN tbl_plan p on p.id = ta.plan_id
                INNER JOIN tbl_service s on s.id = p.service_id
                INNER JOIN tbl_provider pp on pp.id = p.provider_id
                " . $condition;
        }
        $query = $this->db->query($qstr);
//        return $qstr;
        if (!$query) {
            return false;
        } else {
            if ($return_count !== true) {//return actual data
                if ($query->num_rows() > 0) {
                    return $query->result_array();
                }
                return false;
            } else {
                return $query->num_rows() > 0 ? $query->row_array()['total'] : 0;
            }
        }
    }

    //get mpa commissions mapped to the given template
    public function get_commission_template_comms_mpa($template_id, $order_col, $order_dir, $return_count, $condition = "")
    {
        if ($condition === "") {
            $condition = " WHERE ta.template_id = " . $this->db->escape($template_id);
        } else {
            $condition .= " AND ta.template_id = " . $this->db->escape($template_id);
        }
        if ($return_count !== true) {//return actual data
            $qstr = "SELECT ta.id, pp.name provider_name, s.name service_name, p.name plan_name, p.plan_type,
                p.offer_type, p.id plan_id, ta.direct_commission mpa, ta.affiliate_commission commission
                FROM tbl_plan_commission_template_mpa ta
                INNER JOIN tbl_plan p on p.id = ta.plan_id
                INNER JOIN tbl_service s on s.id = p.service_id
                INNER JOIN tbl_provider pp on pp.id = p.provider_id
                " . $condition .
                    " ORDER BY " . $order_col . " " . strtoupper($order_dir); //.
//                " LIMIT " . $start . "," . $length;
        } else {//return count
            $qstr = "SELECT
                count(ta.id) as total
        FROM tbl_plan_commission_template_mpa ta
                INNER JOIN tbl_plan p on p.id = ta.plan_id
                INNER JOIN tbl_service s on s.id = p.service_id
                INNER JOIN tbl_provider pp on pp.id = p.provider_id
                " . $condition;
        }
        $query = $this->db->query($qstr);
//        return $qstr;
        if (!$query) {
            return false;
        } else {
            if ($return_count !== true) {//return actual data
                if ($query->num_rows() > 0) {
                    return $query->result_array();
                }
                return false;
            } else {
                return $query->num_rows() > 0 ? $query->row_array()['total'] : 0;
            }
        }
    }

    //get mpa commissions mapped to the given template
    public function get_sla_template_contents($template_id, $order_col, $order_dir, $return_count, $condition = "")
    {
        if ($condition === "") {
            $condition = " WHERE ta.template_id = " . $this->db->escape($template_id);
        } else {
            $condition .= " AND ta.template_id = " . $this->db->escape($template_id);
        }
        if ($return_count !== true) {//return actual data
            $qstr = "SELECT id, status, status_tag, time_to_call
                FROM tbl_sla_template_contents ta
                " . $condition .
                    " ORDER BY " . $order_col . " " . strtoupper($order_dir); //.
//                " LIMIT " . $start . "," . $length;
        } else {//return count
            $qstr = "SELECT
                count(ta.id) as total
        FROM tbl_sla_template_contents ta
                " . $condition;
        }
        $query = $this->db->query($qstr);
//        return $qstr;
        if (!$query) {
            return false;
        } else {
            if ($return_count !== true) {//return actual data
                if ($query->num_rows() > 0) {
                    return $query->result_array();
                }
                return false;
            } else {
                return $query->num_rows() > 0 ? $query->row_array()['total'] : 0;
            }
        }
    }

    //update commission amount for a single product for an affiliate template
    public function add_update_commission_affiliate_product($id, $dataset)
    {
        //update
        if (!is_null($id)) {
            $str_sql = $this->db->update_string('tbl_plan_commission_template_affiliate', $dataset, "id = " . $id);
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            $str_sql = $this->db->insert_string('tbl_plan_commission_template_affiliate', $dataset);
            $this->db->query($str_sql);
            return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
        }
    }

    //update commission amount for a single product for a mpa template
    public function add_update_commission_mpa_product($id, $dataset)
    {
        //update
        if (!is_null($id)) {
            $str_sql = $this->db->update_string('tbl_plan_commission_template_mpa', $dataset, "id = " . $id);
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            $str_sql = $this->db->insert_string('tbl_plan_commission_template_mpa', $dataset);
            $this->db->query($str_sql);
            return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
        }
    }

    //update commission amount for multiple products for an affiliate template
    public function update_commission_affiliate_products($ids, $dataset)
    {
        //update
        if (!is_null($ids) && is_array($ids) && count($ids) > 0) {
            $str_sql = $this->db->update_string('tbl_plan_commission_template_affiliate', $dataset, "id in (" . implode(",", $ids) . ")");
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            return false;
        }
    }

    //update commission amount for multiple products for an mpa template
    public function update_commission_mpa_products($ids, $dataset)
    {
        //update
        if (!is_null($ids) && is_array($ids) && count($ids) > 0) {
            $str_sql = $this->db->update_string('tbl_plan_commission_template_mpa', $dataset, "id in (" . implode(",", $ids) . ")");
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            return false;
        }
    }

    public function add_update_sla_template($row_id, $dataset)
    {
        if (!is_null($row_id)) {
            //update
            $str_sql = $this->db->update_string('tbl_sla_template', $dataset, "id = " . $row_id);
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            //insert
            $str_sql = $this->db->insert_string('tbl_sla_template', $dataset);
            $this->db->query($str_sql);
            return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
        }
    }

    public function add_update_sla_template_row($row_id, $dataset)
    {
        if (!is_null($row_id)) {
            //update
            $str_sql = $this->db->update_string('tbl_sla_template_contents', $dataset, "id = " . $row_id);
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            //insert
            $str_sql = $this->db->insert_string('tbl_sla_template_contents', $dataset);
            $this->db->query($str_sql);
            return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
        }
    }

    //function to update commission template (affiliate/mpa)
    public function add_update_commission_template($type, $template_id, $dataset)
    {
        if (!is_null($template_id)) {
            //update
            $str_sql = $this->db->update_string('tbl_plan_commission_template_' . $type . '_list', $dataset, "id = " . $template_id);
            return $this->db->simple_query($str_sql) ? true : false;
        } else {
            //insert
            $str_sql = $this->db->insert_string('tbl_plan_commission_template_' . $type . '_list', $dataset);
            $this->db->query($str_sql);
            return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
        }
    }

    public function get_mpa_whitelabeled_email_details_by_manager($manager_id)
    {
        if (is_numeric($manager_id)) {
            $condition = "tbl_reseller.id = " . $this->db->escape($manager_id);
        } else {
            $condition = "tbl_reseller.manager_code = " . $this->db->escape($manager_id);
        }

        $qstr = "SELECT
                tbl_reseller.is_whitelabel,
                tbl_reseller.whitelabel_system_name,
                tbl_reseller.whitelabel_email_from,
                tbl_reseller.whitelabel_email_banner,
                tbl_reseller.whitelabel_email_theme,
                tbl_reseller.whitelabel_img_url,
                tbl_reseller.whitelabel_link_facebook,
                tbl_reseller.whitelabel_link_twitter,
                tbl_reseller.whitelabel_link_instagram,
                tbl_reseller.whitelabel_link_linkedin,
                tbl_reseller.whitelabel_link_youtube
                FROM
                tbl_reseller
        WHERE " . $condition;
        $query = $this->db->query($qstr);

        if ($query && ($query->num_rows() > 0)) {
            $dataset = $query->row_array();
            return (int) $dataset['is_whitelabel'] == 1 ? $dataset : [];
        } else {
            return [];
        }
    }

    /*
     *
     * Instead of partner whitelabel, should be campaign whitelabels
     *
     */

    public function get_mpa_whitelabeled_email_details_by_partner($partner_id)
    {
        if (is_numeric($partner_id)) {
            $condition = "tbl_partner.id = " . $this->db->escape($partner_id);
        } else {
            $condition = "tbl_partner.reference_code = " . $this->db->escape($partner_id);
        }

        /*
          $qstr = "SELECT
          tbl_reseller.is_whitelabel,
          tbl_reseller.whitelabel_system_name,
          tbl_reseller.whitelabel_email_from,
          tbl_reseller.whitelabel_email_banner,
          tbl_reseller.whitelabel_email_theme,
          tbl_reseller.whitelabel_img_url,
          tbl_reseller.whitelabel_link_facebook,
          tbl_reseller.whitelabel_link_twitter,
          tbl_reseller.whitelabel_link_instagram,
          tbl_reseller.whitelabel_link_linkedin,
          tbl_reseller.whitelabel_link_youtube
          FROM
          tbl_reseller
          JOIN tbl_partner ON tbl_partner.reseller_id = tbl_reseller.id
         */

        $qstr = "SELECT
                tbl_reseller.is_whitelabel
                , tbl_reseller.whitelabel_system_name
                , tbl_reseller.whitelabel_email_from
                , tbl_partner.email_banner AS whitelabel_email_banner
                , tbl_partner.email_theme AS whitelabel_email_theme
                , tbl_partner.img_url AS whitelabel_img_url
                , tbl_partner.link_facebook AS whitelabel_link_facebook
                , tbl_partner.link_twitter AS whitelabel_link_twitter
                , tbl_partner.link_instagram AS whitelabel_link_instagram
                , tbl_partner.link_linkedin AS whitelabel_link_linkedin
                , tbl_partner.link_youtube AS whitelabel_link_youtube
                FROM
                tbl_partner
                JOIN tbl_reseller ON tbl_reseller.id = tbl_partner.reseller_id
        WHERE " . $condition;
        $query = $this->db->query($qstr);

        if ($query && ($query->num_rows() > 0)) {
            $dataset = $query->row_array();
            return (int) $dataset['is_whitelabel'] == 1 ? $dataset : [];
        } else {
            return [];
        }
    }

    public function get_mpa_whitelabeled_email_details_by_agent($agent_id)
    {
        if (is_numeric($agent_id)) {
            $condition = "tbl_partner_agents.id = " . $this->db->escape($agent_id);
        } else {
            $condition = "tbl_partner_agents.u_code = " . $this->db->escape($agent_id);
        }

        //get role
        $qstr = "SELECT tbl_partner_agents.role FROM tbl_partner_agents WHERE " . $condition;
        $query = $this->db->query($qstr);
        if (!$query || ($query->num_rows() <= 0)) {
            return [];
        }

        $joins = "";
        switch ($query->row_array()['role']) {
            case USER_MANAGER:
                $joins = " JOIN tbl_partner_agents ON tbl_partner_agents.id = tbl_reseller.manager_agent";
                break;
            case USER_SUPER_AGENT:
            case USER_AGENT:
                $joins = " JOIN tbl_partner ON tbl_partner.reseller_id = tbl_reseller.id
                          JOIN tbl_partner_agents ON tbl_partner_agents.partner_id = tbl_partner.id";
                break;
            case USER_CUSTOMER_SERVICE_AGENT:
                $joins = " JOIN tbl_partner_agents ON tbl_partner_agents.reseller_id = tbl_reseller.id";
                break;
            default:
                return [];
        }



        $qstr = "SELECT
                tbl_reseller.is_whitelabel,
                tbl_reseller.whitelabel_system_name,
                tbl_reseller.whitelabel_email_from,
                tbl_reseller.whitelabel_email_banner,
                tbl_reseller.whitelabel_email_theme,
                tbl_reseller.whitelabel_img_url,
                tbl_reseller.whitelabel_link_facebook,
                tbl_reseller.whitelabel_link_twitter,
                tbl_reseller.whitelabel_link_instagram,
                tbl_reseller.whitelabel_link_linkedin,
                tbl_reseller.whitelabel_link_youtube
                FROM
                tbl_reseller
                " . $joins . "
        WHERE " . $condition;

        $query = $this->db->query($qstr);

        if ($query && ($query->num_rows() > 0)) {
            $dataset = $query->row_array();
            return (int) $dataset['is_whitelabel'] == 1 ? $dataset : [];
        } else {
            return [];
        }
    }

    public function add_email_template($dataset)
    {
        //insert
        $str_sql = $this->db->insert_string('tbl_template_email', $dataset);
        $this->db->query($str_sql);
        return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }

    public function add_sms_template($dataset)
    {
        //insert
        $str_sql = $this->db->insert_string('tbl_template_sms', $dataset);
        $this->db->query($str_sql);
        return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }
}
