<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class Plate_number extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('pi_devices_model');
        $this->load->model('plate_numbers_model');
        $this->load->model('plate_number_regions_model');
        $this->load->model('plate_number_logs_model');
        $this->load->model('user_pi_device_notification_model');
        $this->load->model('users_model');
        $this->load->model('sms_logs_model');
        $this->load->model('email_logs_model');
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function index_get($id = 0)
    {
        if (!empty($id)) {
            // $data = $this->db->get_where("items", ['id' => $id])->row_array();
        } else {
            // $data = $this->db->get("items")->result();
        }
     
        $this->response($data, REST_Controller::HTTP_OK);
    }
      
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_post()
    {
        $dataset = $this->post();

        $piId = isset($dataset['piId']) ? $dataset['piId'] : null;
        $plateNumber = isset($dataset['plateNumber']) ? $dataset['plateNumber'] : null;
        $s3Bucket = isset($dataset['s3Bucket']) ? $dataset['s3Bucket'] : null;
        $s3Key = isset($dataset['s3Key']) ? $dataset['s3Key'] : null;

        if ($piId == '') {
            $this->response(['success' => false, 'error' => 'Invalid PI Device'], REST_Controller::HTTP_OK);
        }

        $pi_device = $this->pi_devices_model->getByUCode($piId);
        if (!$pi_device) {
            return $this->response(['success' => false, 'error' => 'Invalid PI Device'], self::HTTP_OK);
        }

        if ($plateNumber == '') {
            return $this->response(['success' => false, 'error' => 'Invalid Plate Number'], REST_Controller::HTTP_OK);
        }

        

        if ($s3Bucket == '') {
            return $this->response(['success' => false, 'error' => 'Invalid S3 Bucket'], REST_Controller::HTTP_OK);
        }

        if ($s3Key == '') {
            return $this->response(['success' => false, 'error' => 'Invalid S3 Object'], REST_Controller::HTTP_OK);
        }

        switch ($pi_device->tracking_type) {
            case 'hotlist':
                $filter = [
                    'plate_number' => $plateNumber,
                    'tracking_type' => 'hotlist',
                ];
                $plate_number = $this->plate_numbers_model->fetch($filter, [], 1);
                if (count($plate_number) <= 0) {
                    return $this->response(['success' => false, 'error' => 'Not in hotlist'], REST_Controller::HTTP_OK);
                }

                $this->db->trans_begin();

                $data = [];
                $data['pi_device_id'] = $pi_device->id;
                $data['tracking_type'] = 'hotlist';
                $data['plate_number_id'] = $plate_number[0]->id;
                $data['img_url'] = "https://" . $s3Bucket . ".s3.amazonaws.com/" . $s3Key;
                $plate_number_log_id = $this->plate_number_logs_model->save($data);
                if (!$plate_number_log_id) {
                    $this->db->trans_rollback();
                    return $this->response(['success' => false, 'error' => 'Error saving plate_number_logs'], REST_Controller::HTTP_OK);
                }

                $filter = [
                    'pi_device_id' => $pi_device->id,
                ];
                $order = [
                ];
                $assign_devices = $this->user_pi_device_notification_model->fetch($filter, $order);
                if (count($assign_devices) > 0) {
                    $credentials = new Credentials($this->config->item('mm8_aws_access_key_id'), $this->config->item('mm8_aws_secret_access_key'));

                    $snSclient = new SnsClient([
                        'region' => $this->config->item('mm8_aws_region'),
                        'version' => '2010-03-31',
                        'credentials' => $credentials,
                    ]);

                    foreach ($assign_devices as $assign_device) {
                        $user = $this->users_model->getById($assign_device->user_id);
                        if ($user && !empty($user->mobile_phone)) {
                            $message = "Plate number " . $plateNumber ." hotlist detected at " . $pi_device->location. " - ITMS KaagaPI. Pls. do not reply.";
                       
                            try {
                                $result = $snSclient->publish([
                                    'Message' => $message,
                                    'PhoneNumber' => $user->mobile_phone,
                                ]);
                                // var_dump($result);

                                $data = [];
                                $data['plate_number_log_id'] = $plate_number_log_id;
                                $data['user_id'] = $user->id;
                                $data['mobile_phone'] = $user->mobile_phone;
                                $data['message'] = $message;
                                $sms_id = $this->sms_logs_model->save($data);
                                if (!$sms_id) {
                                    $this->db->trans_rollback();
                                    return $this->response(['success' => false, 'error' => 'Error saving sms_logs'], REST_Controller::HTTP_OK);
                                }
                            } catch (AwsException $e) {
                                // output error message if fails
                                error_log($e->getMessage());

                                return $this->response(['success' => false, 'error' => 'Error AWS SNS'], REST_Controller::HTTP_OK);
                            }
                        }
                    }
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return $this->response(['success' => false, 'error' => 'Error saving plate_number_logs'], REST_Controller::HTTP_OK);
                }

                $this->db->trans_commit();

                break;
            case 'whitelist':
                $filter = [
                    'plate_number' => $plateNumber,
                    'tracking_type' => 'whitelist',
                ];
                $plate_number = $this->plate_numbers_model->fetch($filter, [], 1);
                if (count($plate_number) > 0) {
                    return $this->response(['success' => false, 'error' => 'In whitelist'], REST_Controller::HTTP_OK);
                }

                $this->db->trans_begin();

                $plate_number = $this->plate_numbers_model->getByPlateNumber($plateNumber);
                if ($plate_number) {
                    $plate_number_id = $plate_number->id;
                } else {
                    $data = [];
                    $data['plate_number'] = $plateNumber;
                    $data['comments'] = "whitelist detected/Unknown Plate Number";

                    $plate_number_id = $this->plate_numbers_model->save($data);
                    if (!$plate_number_id) {
                        $this->db->trans_rollback();
                        return $this->response(['success' => false, 'error' => 'Error saving plate_number'], REST_Controller::HTTP_OK);
                    }
                }

                $data = [];
                $data['pi_device_id'] = $pi_device->id;
                $data['tracking_type'] = 'whitelist';
                $data['plate_number_id'] = $plate_number_id;
                $data['img_url'] = "https://" . $s3Bucket . ".s3.amazonaws.com/" . $s3Key;
                $plate_number_log_id = $this->plate_number_logs_model->save($data);
                if (!$plate_number_log_id) {
                    $this->db->trans_rollback();
                    return $this->response(['success' => false, 'error' => 'Error saving plate_number_logs'], REST_Controller::HTTP_OK);
                }

                $filter = [
                    'pi_device_id' => $pi_device->id,
                ];
                $order = [
                ];
                $assign_devices = $this->user_pi_device_notification_model->fetch($filter, $order);
                if (count($assign_devices) > 0) {
                    $credentials = new Credentials($this->config->item('mm8_aws_access_key_id'), $this->config->item('mm8_aws_secret_access_key'));

                    $snSclient = new SnsClient([
                        'region' => $this->config->item('mm8_aws_region'),
                        'version' => '2010-03-31',
                        'credentials' => $credentials,
                    ]);

                    foreach ($assign_devices as $assign_device) {
                        $user = $this->users_model->getById($assign_device->user_id);
                        if ($user && !empty($user->mobile_phone)) {
                            $message = "Plate number " . $plateNumber ." not in whitelist detected at " . $pi_device->location. " - ITMS KaagaPI. Pls. do not reply.";
                       
                            try {
                                $result = $snSclient->publish([
                                    'Message' => $message,
                                    'PhoneNumber' => $user->mobile_phone,
                                ]);
                                // var_dump($result);

                                $data = [];
                                $data['plate_number_log_id'] = $plate_number_log_id;
                                $data['user_id'] = $user->id;
                                $data['mobile_phone'] = $user->mobile_phone;
                                $data['message'] = $message;
                                $sms_id = $this->sms_logs_model->save($data);
                                if (!$sms_id) {
                                    $this->db->trans_rollback();
                                    return $this->response(['success' => false, 'error' => 'Error saving sms_logs'], REST_Controller::HTTP_OK);
                                }
                            } catch (AwsException $e) {
                                // output error message if fails
                                error_log($e->getMessage());

                                return $this->response(['success' => false, 'error' => 'Error AWS SNS'], REST_Controller::HTTP_OK);
                            }
                        }
                    }
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return $this->response(['success' => false, 'error' => 'Error saving plate_number_logs'], REST_Controller::HTTP_OK);
                }

                $this->db->trans_commit();

                break;
            default:
                return $this->response(['success' => false, 'error' => 'Invalid PI Device'], self::HTTP_OK);
        }
    
        
        return $this->response(['success' => true], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_put($id)
    {
        $input = $this->put();
     
        $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
        $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);
    }
}
