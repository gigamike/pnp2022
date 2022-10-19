<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Plate_numbers_model extends CI_Model
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

        $this->db->from('tbl_plate_numbers');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['plate_number']) && !empty($filter['plate_number'])) {
                $this->db->where('plate_number', $filter['plate_number']);
            }

            if (isset($filter['tracking_type']) && !empty($filter['tracking_type'])) {
                $this->db->where('tracking_type', $filter['tracking_type']);
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

        //print_r($this->db->last_query());

        return $query->result();
    }

    public function getCount($filter = [])
    {
        $this->db->select('COUNT(*) AS count_id');
        $this->db->from('tbl_plate_numbers');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['plate_number']) && !empty($filter['plate_number'])) {
                $this->db->where('plate_number', $filter['plate_number']);
            }

            if (isset($filter['tracking_type']) && !empty($filter['tracking_type'])) {
                $this->db->where('tracking_type', $filter['tracking_type']);
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
        $query = $this->db->get_where('tbl_plate_numbers', ['id' => $id]);
        return $query->row();
    }

    public function getByPlateNumber($plate_number)
    {
        $query = $this->db->get_where('tbl_plate_numbers', ['plate_number' => $plate_number]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_plate_numbers', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_plate_numbers');

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
        return $this->db->delete('tbl_plate_numbers');
    }

    public function dt_get_plate_numbers($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "plate_number" => "plate_number",
            "class" => "class",
            "region_name" => "region_name",
            "last_registration_date" => "last_registration_date",
            "comments" => "comments",
            "date_added" => "date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                    tbl_plate_numbers.id AS plate_number_id
                    , tbl_plate_numbers.plate_number
                    , tbl_plate_numbers.tracking_type
                    , tbl_plate_numbers.class
                    , tbl_plate_numbers.last_registration_date
                    , tbl_plate_numbers.comments
                    , tbl_plate_number_regions.region_name
                    , DATE_FORMAT(tbl_plate_numbers.date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_plate_numbers
            LEFT JOIN tbl_plate_number_regions ON tbl_plate_number_regions.id = tbl_plate_numbers.region_id
        " . $condition . $order_by . "
        LIMIT " . $start . "," . $length;
        //echo $qstr;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_plate_numbers_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(tbl_plate_numbers.id) AS cnt
        FROM tbl_plate_numbers
            LEFT JOIN tbl_plate_number_regions ON tbl_plate_number_regions.id = tbl_plate_numbers.region_id
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }
}
