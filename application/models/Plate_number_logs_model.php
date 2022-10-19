<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Plate_number_logs_model extends CI_Model
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

        $this->db->from('tbl_plate_number_logs');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['tracking_type']) && !empty($filter['tracking_type'])) {
                $this->db->where('tracking_type', $filter['tracking_type']);
            }

            if (isset($filter['pi_device_id']) && !empty($filter['pi_device_id'])) {
                $this->db->where('pi_device_id', $filter['pi_device_id']);
            }

            if (isset($filter['plate_number_id']) && !empty($filter['plate_number_id'])) {
                $this->db->where('plate_number_id', $filter['plate_number_id']);
            }

            if (isset($filter['date_added_between'])) {
                $this->db->where("DATE(date_added) BETWEEN '" . $filter['date_added_between']['start_date'] . "' AND '" . $filter['date_added_between']['end_date'] . "'");
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
        $this->db->from('tbl_plate_number_logs');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['tracking_type']) && !empty($filter['tracking_type'])) {
                $this->db->where('tracking_type', $filter['tracking_type']);
            }

            if (isset($filter['pi_device_id']) && !empty($filter['pi_device_id'])) {
                $this->db->where('pi_device_id', $filter['pi_device_id']);
            }

            if (isset($filter['plate_number_id']) && !empty($filter['plate_number_id'])) {
                $this->db->where('plate_number_id', $filter['plate_number_id']);
            }

            if (isset($filter['date_added_between'])) {
                $this->db->where("DATE(date_added) BETWEEN '" . $filter['date_added_between']['start_date'] . "' AND '" . $filter['date_added_between']['end_date'] . "'");
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
        $query = $this->db->get_where('tbl_plate_number_logs', ['id' => $id]);
        return $query->row();
    }

    public function getByUCode($u_code)
    {
        $this->db->select("
          *
          , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_added_formatted
        ");
        $query = $this->db->get_where('tbl_plate_number_logs', ['u_code' => $u_code]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_plate_number_logs', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_plate_number_logs');

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
        return $this->db->delete('tbl_plate_number_logs');
    }

    public function dt_get_logs($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "plate_number" => "plate_number",
            "tracking_type" => "tracking_type",
            "comments" => "comments",
            "pi_device_u_code" => "pi_device_u_code",
            "location" => "location",
            "sms_notified" => "sms_notified",
            "date_added" => "date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                     tbl_plate_number_logs.id AS log_id
                    , tbl_plate_number_logs.img_url AS img_url
                    , tbl_plate_numbers.plate_number AS plate_number
                    , tbl_plate_numbers.comments AS comments
                    , tbl_pi_devices.u_code AS pi_device_u_code
                    , tbl_plate_number_logs.tracking_type AS tracking_type
                    , tbl_pi_devices.location AS location
                    , (SELECT COUNT(tbl_sms_logs.id) 
                        FROM tbl_sms_logs 
                        WHERE tbl_sms_logs.plate_number_log_id = tbl_plate_number_logs.id) AS sms_notified
                    , DATE_FORMAT(tbl_plate_number_logs.date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_plate_number_logs
            INNER JOIN tbl_plate_numbers ON tbl_plate_numbers.id = tbl_plate_number_logs.plate_number_id
            INNER JOIN tbl_pi_devices ON tbl_pi_devices.id = tbl_plate_number_logs.pi_device_id
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

    public function dt_get_logs_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(tbl_plate_number_logs.id) AS cnt
        FROM tbl_plate_number_logs
            INNER JOIN tbl_plate_numbers ON tbl_plate_numbers.id = tbl_plate_number_logs.plate_number_id
            INNER JOIN tbl_pi_devices ON tbl_pi_devices.id = tbl_plate_number_logs.pi_device_id
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function trackingHistory($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
            tbl_plate_number_logs.id AS track_id
            , tbl_pi_devices.tracking_type
            , tbl_pi_devices.u_code
            , tbl_pi_devices.location
            , tbl_plate_number_logs.date_added
        ");
        $this->db->from('tbl_plate_number_logs');
        $this->db->join('tbl_pi_devices', 'tbl_plate_number_logs.pi_device_id = tbl_pi_devices.id');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['pi_device_id']) && !empty($filter['pi_device_id'])) {
                $this->db->where('tbl_plate_number_logs.pi_device_id', $filter['pi_device_id']);
            }

            if (isset($filter['plate_number_id']) && !empty($filter['plate_number_id'])) {
                $this->db->where('tbl_plate_number_logs.plate_number_id', $filter['plate_number_id']);
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
