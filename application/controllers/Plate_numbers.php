<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Plate_numbers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('plate_numbers_model');
        $this->load->model('plate_number_regions_model');
        $this->load->model('user_pi_device_notification_model');
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
        $view_data['user_menu'] = "plate-numbers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */

        $view_data['styles'] = [
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plate-numbers/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'merchants';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        // Overview defaults
        $view_data['saved_filter'] = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('plate_numbers/index', $view_data);
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
            "class" => "tbl_plate_numbers.class",
            "region_name" => "tbl_plate_number_regions.region_name",
            "comments" => "tbl_plate_numbers.comments",
        ];

        $dt_params = $this->input->get();

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        $condition = "";

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

        $dataset = $this->plate_numbers_model->dt_get_plate_numbers($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->plate_numbers_model->dt_get_plate_numbers_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;

            $dt_subset['actions'] = $this->load->view('plate_numbers/section_dt_actions', $subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function add()
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
        $view_data['user_menu'] = "plate-numbers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plate-numbers/plate-number.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'plate_number_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $filter = [];
        $order = [
            'region',
            'region_name',
        ];
        $additional_data['regions'] = $this->plate_number_regions_model->fetch($filter, $order);
        
        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('plate_numbers/add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function update($plate_number_id = null)
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        $plate_number = $this->plate_numbers_model->getById($plate_number_id);
        if (!$plate_number) {
            redirect(base_url() . "plate-numbers", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "plate-numbers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plate-numbers/plate-number.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'plate_number_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['plate_number'] = $plate_number;

        $additional_data = [];

        $filter = [];
        $order = [
            'region',
            'region_name',
        ];
        $additional_data['regions'] = $this->plate_number_regions_model->fetch($filter, $order);

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('plate_numbers/update', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $plate_number = isset($dataset['plate_number']) ? trim($dataset['plate_number']) : '';
        $tracking_type = isset($dataset['tracking_type']) ? trim($dataset['tracking_type']) : '';
        $class = isset($dataset['class']) ? trim($dataset['class']) : '';
        $region_id = isset($dataset['region_id']) ? trim($dataset['region_id']) : '';
        $first_name = isset($dataset['first_name']) ? trim($dataset['first_name']) : '';
        $last_name = isset($dataset['last_name']) ? trim($dataset['last_name']) : '';
        $address = isset($dataset['address']) ? trim($dataset['address']) : '';
        $last_registration_date = isset($dataset['last_registration_date']) ? trim($dataset['last_registration_date']) : '';
        $cr_no = isset($dataset['cr_no']) ? trim($dataset['cr_no']) : '';
        $comments = isset($dataset['comments']) ? trim($dataset['comments']) : '';

        if ($plate_number == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Plate Number."]);
            return;
        }

        if ($tracking_type == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Tracking Type."]);
            return;
        }

        switch ($tracking_type) {
            case 'hotlist':
                break;
            case 'whitelist':
                break;
            default:
                echo json_encode(['successful' => false, 'error' => "Invalid Tracking Type."]);
            return;
        }

        if ($class == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Class."]);
            return;
        }

        switch ($class) {
            case 'private':
                break;
            case 'public':
                break;
            case 'government':
                break;
            case 'diplomat':
                break;
            case 'other':
                break;
            default:
                echo json_encode(['successful' => false, 'error' => "Invalid Class."]);
                return;
        }

        if (!$region_id) {
            echo json_encode(['successful' => false, 'error' => "Required field Region."]);
            return;
        }

        $region = $this->plate_number_regions_model->getById($region_id);
        if (!$region) {
            echo json_encode(['successful' => false, 'error' => "Invalid Region."]);
            return;
        }

        if (isset($dataset['plate_number_id']) && !empty($dataset['plate_number_id'])) {
            //UPDATE
            //no need to check if there are seats left
            $exitingPlateNumber = $this->plate_numbers_model->getById($dataset['plate_number_id']);
            if (!$exitingPlateNumber) {
                echo json_encode(['successful' => false, 'error' => "Plate number does not exists."]);
                return;
            }

            $filter = [
                'plate_number' => $plate_number,
                'id_not' => $exitingPlateNumber->id,
            ];
            $duplicatePlateNumber = $this->plate_numbers_model->fetch($filter);
            if ($duplicatePlateNumber) {
                echo json_encode(['successful' => false, 'error' => 'Plate number already exist.']);
                return;
            }

            //START
            $this->db->trans_begin();

            $data = [];
            $data['id'] = $exitingPlateNumber->id;
            $data['tracking_type'] = $tracking_type;
            $data['class'] = $class;
            $data['region_id'] = $region->id;
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['address'] = $address;
            $data['last_registration_date'] = empty($last_registration_date) ? null : $last_registration_date;
            $data['cr_no'] = $cr_no;
            $data['comments'] = $comments;

            $plate_number_id = $this->plate_numbers_model->save($data);
            if (!$plate_number_id) {
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

            echo json_encode(['successful' => true]);
        } else {
            //ADD NEW
            
            $exitingPlateNumber = $this->plate_numbers_model->getByPlateNumber($plate_number);
            if ($exitingPlateNumber) {
                echo json_encode(['successful' => false, 'error' => 'Plate number already exist.']);
                return;
            }
    
            //START
            $this->db->trans_begin();

            $data = [];
            $data['plate_number'] = $plate_number;
            $data['tracking_type'] = $tracking_type;
            $data['class'] = $class;
            $data['region_id'] = $region->id;
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['address'] = $address;
            $data['last_registration_date'] = empty($last_registration_date) ? null : $last_registration_date;
            $data['cr_no'] = $cr_no;
            $data['comments'] = $comments;

            $plate_number_id = $this->plate_numbers_model->save($data);
            if (!$plate_number_id) {
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

            echo json_encode(['successful' => true]);
        }
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
        $plate_number_id = isset($dataset['plate_number_id']) ? $dataset['plate_number_id'] : null;

        if (!$plate_number_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid Plate Number"]);
            return;
        }

        $plate_number = $this->plate_numbers_model->getById($plate_number_id);
        if (!$plate_number) {
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        //START
        $this->db->trans_begin();

        if (!$this->plate_numbers_model->delete($plate_number->id)) {
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

        echo json_encode(['successful' => true, 'message' => "Plate number deleted."]);
    }

    public function add_import()
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
        $view_data['user_menu'] = "plate-numbers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plate-numbers/plate-number-import.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'plate_number_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $view_data['text_placeholder'] = "\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"\n\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"\n\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"\n";
        $view_data['text_format'] = "next line separated \"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"";
        $view_data['csv_format'] = "\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"<br>\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"<br>\"Plate Number\",\"Tracking Type\",\"Class\",\"Region\",\"First Name\",\"Last Name\",\"Address\",\"Last Registration Date\",\"CR Number\",\"Incident\"<br>";
        
        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('plate_numbers/add_import', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_bulk_import_save()
    {
        //update time outs since uploads may take time to complete
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //lets check if uploaded file is csv
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $file_mime_type = mime_content_type($_FILES['csv']['tmp_name']);

            $allowedFileTypes = ['application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv'];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                echo json_encode(['successful' => false, 'error' => "Invalid file/mime type. Make sure your file is a valid CSV file"]);
                return;
            }
        }

        //form data
        $dataset = $this->input->post();
        $text = isset($dataset['text']) ? trim($dataset['text']) : '';
        
        $inserted = 0;

        if ($text != '') {
            $csvRows = [];
            $string = explode("\n", $text);
            if (count($string) > 0) {
                foreach ($string as $stringRow) {
                    $csv = str_getcsv($stringRow);
                    if (count($csv) > 0) {
                        $csvRows[] = $csv;
                    }
                }
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importPLateNumbers($csvRows);
            }
        }
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $csvRows = [];
            if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row = [];
                    $num = count($data);
                    for ($c = 0; $c < $num; $c++) {
                        $row[] = $data[$c];
                    }
                    $csvRows[] = $row;
                    unset($row);
                }
                fclose($handle);
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importPLateNumbers($csvRows);
            }
        }

        echo json_encode(['successful' => true, 'message' => "Bulk import successful. Total Prospect inserted " . $inserted]);
    }

    private function _importPLateNumbers($plateNunmbers)
    {
        $inserted = 0;

        $this->db->trans_begin();

        foreach ($plateNunmbers as $plateNunmber) {
            $plate_number = isset($plateNunmber[0]) ? trim($plateNunmber[0]) : null;
            $tracking_type = isset($plateNunmber[1]) ? trim($plateNunmber[1]) : null;
            $class = isset($plateNunmber[2]) ? trim($plateNunmber[2]) : null;
            $region = isset($plateNunmber[3]) ? trim($plateNunmber[3]) : null;
            $first_name = isset($plateNunmber[4]) ? trim($plateNunmber[4]) : null;
            $last_name = isset($plateNunmber[5]) ? trim($plateNunmber[5]) : null;
            $address = isset($plateNunmber[6]) ? trim($plateNunmber[6]) : null;
            $last_registration_date = isset($plateNunmber[7]) ? trim($plateNunmber[7]) : null;
            $cr_no = isset($plateNunmber[8]) ? trim($plateNunmber[8]) : null;
            $comments = isset($plateNunmber[9]) ? trim($plateNunmber[9]) : null;

            if ($plate_number == '') {
                continue;
            }
            if ($tracking_type == '') {
                continue;
            }
            if ($class == '') {
                continue;
            }
            if ($region == '') {
                continue;
            }
            if ($comments == '') {
                continue;
            }

            switch ($tracking_type) {
                case 'hotlist':
                    break;
                case 'whitelist':
                    break;
                default:
                    continue 2;
            }

            switch ($class) {
                case 'private':
                    break;
                case 'public':
                    break;
                case 'government':
                    break;
                case 'diplomat':
                    break;
                case 'other':
                    break;
                default:
                    continue 2;
            }

            $region = $this->plate_number_regions_model->getByRegion($region);
            if (!$region) {
                continue;
            }

            $data = [];
            $data['plate_number'] = $plate_number;
            $data['tracking_type'] = $tracking_type;
            $data['class'] = $class;
            $data['region_id'] = $region->id;
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['address'] = $address;
            $data['last_registration_date'] = empty($last_registration_date) ? null : $last_registration_date;
            $data['cr_no'] = $cr_no;
            $data['comments'] = $comments;

            $exitingPlateNumber = $this->plate_numbers_model->getByPlateNumber($plate_number);
            if ($exitingPlateNumber) {
                $data['id'] = $exitingPlateNumber->id;
            }

            $plate_number_id = $this->plate_numbers_model->save($data);
            if (!$plate_number_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $inserted++;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        $this->db->trans_commit();

        return $inserted;
    }
}
