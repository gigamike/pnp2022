<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sms_logs_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null, $fields = [])
    {
        if (count($fields) > 0) {
            $this->db->select(implode(',', $fields));
        }

        $this->db->from('tbl_sms_logs');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }

            if (isset($filter['mobile_phone']) && !empty($filter['mobile_phone'])) {
                $this->db->where('mobile_phone', $filter['mobile_phone']);
            }
        }

        if (!is_null($order) && is_array($order) && count($order) > 0) {
            $this->db->order_by(implode(',', $order));
        }

        if (!is_null($limit) && !is_null($start)) {
            $this->db->limit($limit, $start);
        } elseif (!empty($limit)) {
            $this->db->limit($limit);
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        return $query->result();
    }

    public function getCount($filter = [])
    {
        $this->db->select('COUNT(*) AS count_id');
        $this->db->from('tbl_sms_logs');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }

            if (isset($filter['mobile_phone']) && !empty($filter['mobile_phone'])) {
                $this->db->where('mobile_phone', $filter['mobile_phone']);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $this->db->select("
          *
          , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_added_formatted
        ");
        $query = $this->db->get_where('tbl_sms_logs', ['id' => $id]);
        return $query->row();
    }

    public function getByUCode($u_code)
    {
        $query = $this->db->get_where('tbl_sms_logs', ['u_code' => $u_code]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_sms_logs', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_sms_logs');

        return $data['id'];
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tbl_sms_logs');
    }

    public function smsHistory($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
            tbl_sms_logs.date_added AS date_added
            , tbl_plate_number_logs.tracking_type
            , tbl_sms_logs.mobile_phone
            , tbl_sms_logs.message
            , tbl_users.first_name
            , tbl_users.last_name
        ");
        $this->db->from('tbl_sms_logs');
        $this->db->join('tbl_plate_number_logs', 'tbl_plate_number_logs.id = tbl_sms_logs.plate_number_log_id');
        $this->db->join('tbl_users', 'tbl_sms_logs.user_id = tbl_users.id');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['plate_number_log_id']) && !empty($filter['plate_number_log_id'])) {
                $this->db->where('plate_number_log_id', $filter['plate_number_log_id']);
            }
        }

        if (!is_null($order) && is_array($order) && count($order) > 0) {
            $this->db->order_by(implode(',', $order));
        }

        if (!is_null($limit) && !is_null($start)) {
            $this->db->limit($limit, $start);
        } elseif (!empty($limit)) {
            $this->db->limit($limit);
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        return $query->result();
    }
}
