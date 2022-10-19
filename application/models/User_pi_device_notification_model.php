<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_pi_device_notification_model extends CI_Model
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

        $this->db->from('tbl_user_pi_device_notification');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }

            if (isset($filter['pi_device_id']) && !empty($filter['pi_device_id'])) {
                $this->db->where('pi_device_id', $filter['pi_device_id']);
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

    public function getCountLeads($filter = [])
    {
        $this->db->select('COUNT(*) AS count_id');
        $this->db->from('tbl_user_pi_device_notification');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }

            if (isset($filter['pi_device_id']) && !empty($filter['pi_device_id'])) {
                $this->db->where('pi_device_id', $filter['pi_device_id']);
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
        $query = $this->db->get_where('tbl_user_pi_device_notification', ['id' => $id]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_user_pi_device_notification', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_user_pi_device_notification');

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
        return $this->db->delete('tbl_user_pi_device_notification');
    }

    public function deleteByUserId($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->delete('tbl_user_pi_device_notification');
    }

    public function deleteByUserIdPIDeviceId($user_id, $pi_device_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('pi_device_i', $pi_device_i);
        return $this->db->delete('tbl_user_pi_device_notification');
    }
}
