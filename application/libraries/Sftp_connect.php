<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sftp_connect
{
    public function __construct()
    {
    }

    public function send_file($dataset, $remote_file, $local_file)
    {
        if (isset($dataset['key_file_private']) && isset($dataset['key_file_public']) && !empty($dataset['key_file_private']) && !empty($dataset['key_file_public']) && file_exists($dataset['key_file_private']) && file_exists($dataset['key_file_public'])) {
            //step 1: connect
            $connection = @ssh2_connect($dataset['host'], $dataset['port'], ['hostkey'=>'ssh-rsa']);

            if (!@ssh2_auth_pubkey_file($connection, $dataset['username'], $dataset['key_file_public'], $dataset['key_file_private'])) {
                return(["status" => STATUS_NG, "error" => "Could not authenticate with username " . $dataset['username'] . " and keys"]);
            }
        } else {
            //step 1: connect
            $connection = @ssh2_connect($dataset['host'], $dataset['port']);
            if (!$connection) {
                return(["status" => STATUS_NG, "error" => "Could not connect to " . $dataset['host'] . " on port " . $dataset['port']]);
            }

            //step 2: auth
            if (!@ssh2_auth_password($connection, $dataset['username'], $dataset['password'])) {
                return(["status" => STATUS_NG, "error" => "Could not authenticate with username " . $dataset['username'] . " and password " . $dataset['password']]);
            }
        }

        //step3: init sftp
        $sftp = @ssh2_sftp($connection);
        if (!$sftp) {
            return(["status" => STATUS_NG, "error" => "Could not initialise SFTP subsystem"]);
        }


        //step4: push file
        //BUG FIX: it seems since this PHP update, you have to surround your host part (result of ssh2_sftp()) with intval():
        $stream = @fopen("ssh2.sftp://" . intval($sftp) . $remote_file, 'w');

        if (!$stream) {
            return(["status" => STATUS_NG, "error" => "Could not open file: " . $remote_file]);
        }

        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send == false) {
            return(["status" => STATUS_NG, "error" => "Could not open local file: " . $local_file]);
        }

        if (@fwrite($stream, $data_to_send) == false) {
            return(["status" => STATUS_NG, "error" => "Could not send data from file: " . $local_file]);
        }


        @fclose($stream);
        return(["status" => STATUS_OK]);
    }
}
