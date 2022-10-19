<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('plate_number_logs_model');
        $this->load->model('plate_numbers_model');
        $this->load->model('pi_devices_model');
        $this->load->model('users_model');
        $this->load->model('sms_logs_model');
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            return false;
        }

        return true;
    }

    public function index()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "dashboard";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/bootstrap-datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/chartJs/3.8.0/Chart.min.js',
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/1.7.0/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/dashboard/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        $additional_data = [];

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $additional_data['reference_date_start'] = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $additional_data['reference_date_end'] = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('dashboard/index', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_plate_numbers()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dt_search_columns = [
            "plate_number" => "tbl_plate_numbers.plate_number",
            "pi_device_u_code" => "tbl_pi_devices.u_code",
            "location" => "tbl_pi_devices.location",
            "comments" => "tbl_plate_numbers.comments",
        ];

        $dt_params = $this->input->get();

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        $condition = "";

        $conditions_arr = [];

        if (isset($dt_params['filterDateAddedOperator']) && !empty($dt_params['filterDateAddedOperator'])) {
            if ($dt_params['filterDateAddedOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateAddedFrom']) && !empty($dt_params['filterDateAddedFrom']) && isset($dt_params['filterDateAddedTo']) && !empty($dt_params['filterDateAddedTo'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_plate_number_logs.date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAddedFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateAddedTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
            } elseif (isset($dt_params['filterDateAdded']) && !empty($dt_params['filterDateAdded'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_plate_number_logs.date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAdded'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
            }
        }

        if (isset($dt_params['searchText']) && $dt_params['searchText'] != "") {
            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $dt_search_columns[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                //remove last ' OR '
                $str_search = substr($str_search, 0, strlen($str_search) - 4);
                $condition .= $condition == "" ? "WHERE (" . $str_search . ")" : " AND (" . $str_search . ")";
            }
        }

        $dataset = $this->plate_number_logs_model->dt_get_logs($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->plate_number_logs_model->dt_get_logs_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;

            $dt_subset['img_url'] = "<img class=\"img-thumbnail\" src=\"" . $subset['img_url'] . "\">";

            $dt_subset['actions'] = $this->load->view('dashboard/section_dt_actions', $subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_metrics()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        $filterStart = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterStart'])->format("Y-m-d");
        $filterEnd = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterEnd'])->format("Y-m-d");

        $filter = [
            'tracking_type' => 'hotlist',
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        $countPlateNumberHotlistDetected = $this->plate_number_logs_model->getCount($filter);

        $filter = [
            'tracking_type' => 'whitelist',
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        $countPlateNumberWhitelistDetected = $this->plate_number_logs_model->getCount($filter);
       
        $filter = [
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        $countPlateNumbers = $this->plate_numbers_model->getCount($filter);

        /*
        $filter = [
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        $countUsers = $this->users_model->getCount($filter);
        */
       
        $filter = [
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        $countPIDevices = $this->pi_devices_model->getCount($filter);

        echo json_encode([
            'successful' => true,
            'countPlateNumberHotlistDetected' => $countPlateNumberHotlistDetected,
            'countPlateNumberWhitelistDetected' => $countPlateNumberWhitelistDetected,
            'countPlateNumbers' => $countPlateNumbers,
            // 'countUsers' => $countUsers,
            'countPIDevices' => $countPIDevices,
        ]);
    }

    public function ajax_pie_chart()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        $filterStart = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterStart'])->format("Y-m-d");
        $filterEnd = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterEnd'])->format("Y-m-d");

        
        $data = [
            intval(1),
            intval(2),
            intval(3),
            intval(4),
        ];

        echo json_encode([
            'successful' => true,
            'data' => $data,
        ]);
    }

    public function ajax_bar_chart()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        echo json_encode([
            'successful' => true,
            'labels' => [1,2,3],
            'dataOpenTickets' => [1,2,3],
            'dataResolvedTickets' => [1,2,3],
            'dataReopenTickets' => [1,2,3],
            'dataClosedTickets' => [1,2,3],
        ]);
    }

    public function view($plate_number_log_id = null)
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }
        
        $plate_number_log = $this->plate_number_logs_model->getById($plate_number_log_id);
        if (!$plate_number_log) {
            redirect(base_url() . "dashboard", 'refresh');
        }

        $plate_number = $this->plate_numbers_model->getById($plate_number_log->plate_number_id);
        if (!$plate_number) {
            redirect(base_url() . "dashboard", 'refresh');
        }

        $pi_device = $this->pi_devices_model->getById($plate_number_log->pi_device_id);
        if (!$pi_device) {
            redirect(base_url() . "dashboard", 'refresh');
        }

        

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "dashboard";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            
            asset_url() . 'js/dashboard/view.js',
            "https://maps.googleapis.com/maps/api/js?key=" . $this->config->item('google_map_api_key') . "&callback=initializeMap",
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'dashboard';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['plate_number_log'] = $plate_number_log;
        $view_data['plate_number'] = $plate_number;
        $view_data['pi_device'] = $pi_device;

        $additional_data = [];

        $filter = [
            'plate_number_id' => $plate_number_log->plate_number_id,
        ];
        $order = [
            'tbl_plate_number_logs.date_added DESC',
        ];
        $additional_data['tracks'] = $this->plate_number_logs_model->trackingHistory($filter, $order);

        $filter = [
            'plate_number_log_id' => $plate_number_log->id,
        ];
        $order = [
            'tbl_sms_logs.date_added DESC',
        ];
        $additional_data['sms'] = $this->sms_logs_model->smsHistory($filter, $order);

        $additional_data['isGoogleMap'] = true;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('dashboard/view', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_delete()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $log_id = isset($dataset['log_id']) ? $dataset['log_id'] : null;

        if (!$log_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid Plate Number Log ID"]);
            return;
        }

        $log = $this->plate_number_logs_model->getById($log_id);
        if (!$log) {
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        //START
        $this->db->trans_begin();

        if (!$this->plate_number_logs_model->delete($log->id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true, 'message' => "Plate number log deleted."]);
    }
}
