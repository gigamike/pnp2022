<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Credentials\Credentials;
use Aws\Kms\KmsClient;
use Aws\Exception\AwsException;
use Aws\Kms\Exception\KmsException;

class Aws_kms_library
{
    protected $CI;
    private $credentials;
    private $kms_client;
    private $limit;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('email_library');

        $this->credentials = new Credentials($this->CI->config->item('mm8_aws_access_key_id'), $this->CI->config->item('mm8_aws_secret_access_key'));

        $this->kms_client = new KmsClient([
            'version' => 'latest',
            'region'  => $this->CI->config->item('mm8_aws_kms_region'),
            'credentials' => $this->credentials
        ]);

        $this->limit = 10;
    }

    public function encrypt($key_id, $orig, $encryptionContext)
    {
        try {
            $result = $this->kms_client->encrypt([
                'KeyId' => $key_id,
                'Plaintext' => $orig,
                'EncryptionContext' => $encryptionContext
            ]);
            return $result->get('CiphertextBlob');
        } catch (KmsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            echo $error_message .= "Error: " . $e->getMessage();
            //$this->CI->email_library->notify_system_failure($error_message);
        } catch (AwsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            echo $error_message .= "Error: " . $e->getAwsErrorType();
            //$this->CI->email_library->notify_system_failure($error_message);
        }
        return false;
    }

    public function decrypt($ciphertext, $encryptionContext)
    {
        try {
            $result = $this->kms_client->decrypt([
                'CiphertextBlob' => $ciphertext,
                'EncryptionContext' => $encryptionContext
            ]);
            return $result->get('Plaintext');
        } catch (KmsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function listAliases()
    {
        try {
            $result = $this->kms_client->listAliases([
                'Limit' => $this->limit,
            ]);
            return $result;
        } catch (KmsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function listKeys()
    {
        try {
            $result = $this->kms_client->listKeys([
                'Limit' => $this->limit,
            ]);
            return $result;
        } catch (KmsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function generateDataKey($keyId, $keySpec)
    {
        $result = $this->kms_client->generateDataKey([
            'KeyId' => $keyId,
            'KeySpec' => $keySpec,
        ]);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    public function createKey($desc)
    {
        $result = $this->kms_client->createKey([
            'Description' => $desc,
        ]);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    public function getRandomKeyFromAliases($keys)
    {
        $filtered_keys = array_filter($keys, function ($item) {
            if (stripos($item['AliasName'], "US-payment") !== false) {
                return true;
            } else {
                return false;
            }
        });
        if (count($filtered_keys) < 1) {
            return false;
        } else {
            $index = rand(0, count($filtered_keys) - 1);
            return $filtered_keys[$index]['TargetKeyId'];
        }
    }

    public function getRandomToken()
    {
        $tokens = $this->CI->config->item('mm8_security_tokens');
        if (count($tokens) < 1) {
            return false;
        } else {
            $index = rand(0, count($tokens) - 1);
            return $tokens[$index];
        }
    }
}
