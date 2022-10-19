<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*
* php index.php commands/gmail getinbox
*
* https://developers.google.com/gmail/api/quickstart/php
* http://sahmwebdesign.com/gmail-api-via-service-accounts/
* Need to create credentials, go to google developer console https://console.developers.google.com/
* Create Credentials >> OAuth 2.0 Client IDs, need to specify URI to get Auth Code
*
*/
class Intergration_gmail
{
    protected $CI;

    private $_appName = null;
    private $_scopes = Google_Service_Gmail::GMAIL_READONLY;
    private $_clientId = null;
    private $_clientSecret = null;
    private $_redirectUri = null;
    private $_user = 'me';
    private $_accessToken = null;
    private $_callbackCode = null;
    private $_googleAuthenticateId = null;

    private $_client = null;
    private $_service = null;

    public function __construct($params)
    {
        $this->CI = & get_instance();
        $this->CI->load->model('backend_google_authenticate_model');

        if (isset($params['appName'])) {
            $this->_appName = $params['appName'];
        }
        if (isset($params['clientId'])) {
            $this->_clientId = $params['clientId'];
        }
        if (isset($params['clientSecret'])) {
            $this->_clientSecret = $params['clientSecret'];
        }
        if (isset($params['redirectUri'])) {
            $this->_redirectUri = $params['redirectUri'];
        }
        if (isset($params['redirectUri'])) {
            $this->_redirectUri = $params['redirectUri'];
        }
        if (isset($params['accessToken'])) {
            $this->_accessToken = $params['accessToken'];
        }
        if (isset($params['callbackCode'])) {
            $this->_callbackCode = $params['callbackCode'];
        }
        if (isset($params['googleAuthenticateId'])) {
            $this->_googleAuthenticateId = $params['googleAuthenticateId'];
        }
    }

    /**
    * Returns an authorized API client.
    * @return Google_Client the authorized client object
    */
    public function getClient()
    {
        $results = [
            'status' => 0, //
        ];

        $client = new Google_Client();
        $client->setApplicationName($this->_appName);
        $client->setScopes($this->_scopes);
        $client->setClientId($this->_clientId);
        $client->setClientSecret($this->_clientSecret);
        $client->setRedirectUri($this->_redirectUri);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // echo $client::LIBVER;

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        if (!empty($this->_accessToken)) {
            $accessToken = json_decode($this->_accessToken, true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $results['is_authenticated'] = true;
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();

                if (empty($this->_callbackCode)) {
                    $results['is_authenticated'] = false;
                    $results['auth_url'] = $authUrl;
                } else {
                    $authCode = trim($this->_callbackCode);

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
            }

            // Save the token to a file.
            // if (!file_exists(dirname($this->_tokenPath))) {
            //     mkdir(dirname($this->_tokenPath), 0700, true);
            // }
            // file_put_contents($this->_tokenPath, json_encode($client->getAccessToken()));

            if (!empty($this->_googleAuthenticateId)) {
                $data = [];
                $data['id'] = $this->_googleAuthenticateId;
                $data['access_token'] = json_encode($client->getAccessToken());
                $this->CI->backend_google_authenticate_model->save($data);

                $results['is_authenticated'] = true;
            }
        } else {
            $results['is_authenticated'] = true;
        }

        $this->_client = $client;
        $this->_getService();

        return $results;
    }

    private function _getService()
    {
        $this->_service = new Google_Service_Gmail($this->_client);
    }

    /*
    *
    * https://developers.google.com/gmail/api/v1/reference/users/messages/list
    * https://developers.google.com/gmail/api/v1/reference/users/messages
    *
    */
    public function getListMessages($opt_param = [])
    {
        $data = [];

        $pageToken = null;
        $messages = [];
        $success = true;
        // $opt_param['q']="is:unread";  //this will get us only unread messages
        // $opt_param['maxResults']=2; //this limits the amount of unread messages returned

        try {
            $messagesResponse = $this->_service->users_messages->listUsersMessages($this->_user, $opt_param);
            // print_r($messagesResponse);

            $data['nextPageToken'] = $messagesResponse['nextPageToken'];
            $data['resultSizeEstimate'] = $messagesResponse['resultSizeEstimate'];

            if ($messagesResponse->getMessages()) {
                $messages = array_merge($messages, $messagesResponse->getMessages());
            }
        } catch (Exception $e) {
            echo "\nyou don't have email access";
            echo "\n";
            echo $e;
            $success=false;
        }

        $resultMessages = [];
        if ($success) {
            foreach ($messages as $message) {
                // for debugging
                
                /*
                if ($message->getId()!='1798e0619e7a0e8a') {
                    continue;
                }
                */
               
                $resultMessages[] = $this->_getMessage($message->getId());
            }
            // echo"\n\nNumber of Unread Messages is". $messagesResponse['resultSizeEstimate'] ."";
        }
        $data['messages'] = $resultMessages;

        // print_r($resultMessages); exit();

        return $data;
    }

    private function _getMessage($messageId)
    {
        try {
            $resultMessage = [];

            $message = $this->_service->users_messages->get($this->_user, $messageId);
      
            $resultMessage['gmail_message_id'] = $message->getId();
            $resultMessage['threadId'] = $message->getThreadId();
            $resultMessage['labelIds'] = $message->getLabelIds();

            $headers = $message->getPayload()->getHeaders();
       
            // $resultMessage['Header_Message_ID'] = $this->_getHeader($headers, 'Message-ID');
            $resultMessage['To_Email'] = $this->_getHeader($headers, 'Delivered-To');
            $resultMessage['Return_Path'] = $this->_getHeader($headers, 'Return-Path');

            $resultMessage['Date'] = $this->_getHeader($headers, 'Date');
            $resultMessage['Epoch_Time'] = strtotime($resultMessage['Date']);
            $resultMessage['From'] = $this->_getHeader($headers, 'From'); // Mik Galon <michaelgalon@utilihub.io>

            // echo $resultMessage['From'] . "|" . $this->_extractNameFromString($resultMessage['From']) . "\n";

            $resultMessage['From_Name'] = $this->_extractNameFromString($resultMessage['From']); // Mik Galon
            $resultMessage['From_Email'] = $this->_extractEmailFromString($resultMessage['From']); // michaelgalon@utilihub.io
            $resultMessage['To'] = $this->_extractEmailFromString($this->_getHeader($headers, 'To')); // Reply To <reply@movinghub.com.au>
            if (empty($resultMessage['To'])) {
                $resultMessage['To'] = $this->_extractEmailFromString($this->_getHeader($headers, 'Delivered-To')); // Delivered-To reply+lwrm@movinghub.co.uk
            }

            // possible muliple CC reply+mhb@movinghub.com.au, utilihub@gmail.com
            $resultMessage['Cc'] = [];
            $cc = $this->_getHeader($headers, 'Cc');
            $ccs = explode(',', $cc);
            if (count($ccs) > 0) {
                foreach ($ccs as $cc) {
                    $resultMessage['Cc'][] = trim($cc);
                }
            }

            // possible muliple BCC reply+mhb@movinghub.com.au, utilihub@gmail.com
            $resultMessage['Bcc'] = [];
            $bcc = $this->_getHeader($headers, 'Bcc');
            $bccs = explode(',', $bcc);
            if (count($bccs) > 0) {
                foreach ($bccs as $bcc) {
                    $resultMessage['Bcc'][] = trim($bcc);
                }
            }

            $resultMessage['Subject'] = $this->_getHeader($headers, 'Subject');

            $files = [];
            $messageDetails = $message->getPayload();
            foreach ($messageDetails['parts'] as $key => $value) {
                if (isset($value['mimeType']) && $value['mimeType'] == 'text/plain') {
                    $resultMessage['Plain_Text_Message'] = $this->_decodeData($value['body']['data']);
                }
                if (isset($value['mimeType']) && $value['mimeType'] == 'text/html') {
                    $resultMessage['Html_Message'] = $this->_decodeData($value['body']['data']);
                }

                if (isset($value['parts'])) {
                    foreach ($value['parts'] as $partsKey => $partsValue) {
                        if (isset($partsValue['mimeType']) && $partsValue['mimeType'] == 'text/plain') {
                            if (isset($partsValue['body']['data'])) {
                                if (!isset($resultMessage['Plain_Text_Message']) || trim($resultMessage['Plain_Text_Message'])=='') {
                                    $resultMessage['Plain_Text_Message'] = $this->_decodeData($partsValue['body']['data']);
                                }
                            }
                        }

                        if (isset($partsValue['mimeType']) && $partsValue['mimeType'] == 'text/html') {
                            if (isset($partsValue['body']['data'])) {
                                if (!isset($resultMessage['Html_Message']) || trim($resultMessage['Html_Message'])=='') {
                                    $resultMessage['Html_Message'] = $this->_decodeData($partsValue['body']['data']);
                                }
                            }
                        }

                        if (isset($partsValue['filename']) && !empty($partsValue['filename'])
                            && isset($partsValue['mimeType']) && !empty($partsValue['mimeType'])
                            && isset($partsValue['partId']) && !empty($partsValue['partId'])) {
                            $files[] = [
                                'filename' => $partsValue['filename'],
                                'mimeType' => $partsValue['mimeType'],
                                'partId'   => $partsValue['partId'],
                            ];
                        }
                    }
                }

                if (isset($value['filename']) && !empty($value['filename'])
                    && isset($value['mimeType']) && !empty($value['mimeType'])
                    && isset($value['partId']) && !empty($value['partId'])) {
                    $files[] = [
                        'filename' => $value['filename'],
                        'mimeType' => $value['mimeType'],
                        'partId'   => $value['partId'],
                    ];
                }
            }
            if (count($files) > 0) {
                $resultMessage['Attachments'] = $files;
            }

            if (!isset($resultMessage['Plain_Text_Message']) || trim($resultMessage['Plain_Text_Message'])=='') {
                $resultMessage['Plain_Text_Message'] = $message['snippet'];
            }

            if (!isset($resultMessage['Html_Message']) || trim($resultMessage['Html_Message'])=='') {
                $resultMessage['Html_Message'] = base64_decode(strtr($message->getPayload()->getBody()->data, '-_', '+/'));
            }

            return $resultMessage;
        } catch (Exception $e) {
            print "\nAn error occurred: " . $e->getMessage();
        }
    }

    public function getAttachment($messageId, $partId)
    {
        try {
            $message = $this->_service->users_messages->get($this->_user, $messageId);

            $file = null;
            $messageDetails = $message->getPayload();
            // print_r($messageDetails);
            foreach ($messageDetails['parts'] as $key => $value) {
                if (isset($value['filename']) && !empty($value['filename'])) {
                    if ($value['partId'] == $partId) {
                        $headers = [];
                        foreach ($value['headers'] as $header) {
                            $headers[$header['name']] =  $header['value'];
                        }

                        $file = [
                            'filename' => $value['filename'],
                            'mimeType' => $value['mimeType'],
                            'partId'   => $value['partId'],
                            'attachmentId'   => $value['body']['attachmentId'],
                            'headers' => $headers,
                        ];
                    }
                }

                if ($value['parts']) {
                    foreach ($value['parts'] as $partsKey => $partsValue) {
                        if ($partsValue['partId'] == $partId) {
                            if (isset($partsValue['filename']) && !empty($partsValue['filename'])
                            && isset($partsValue['mimeType']) && !empty($partsValue['mimeType'])
                            && isset($partsValue['partId']) && !empty($partsValue['partId'])) {
                                $headers = [];
                                foreach ($partsValue['headers'] as $header) {
                                    $headers[$header['name']] =  $header['value'];
                                }

                                $file = [
                                    'filename' => $partsValue['filename'],
                                    'mimeType' => $partsValue['mimeType'],
                                    'partId'   => $partsValue['partId'],
                                    'attachmentId'   => $partsValue['body']['attachmentId'],
                                    'headers' => $headers,
                                ];
                            }
                        }
                    }
                }
            }

            // print_r($file); exit();

            if (count($file) > 0) {
                if (isset($file['attachmentId']) && !empty($file['attachmentId'])) {
                    $attachment = $this->_service->users_messages_attachments->get($this->_user, $messageId, $file['attachmentId']);
                    $file['data_encoded'] = $attachment->getData();
                    $file['data'] = $this->_decodeData($attachment->getData());

                    return ['status' => true, 'data' => $file];
                } else {
                    return ['status' => false, 'is_error' => true, 'message' => 'Attachment doesnt exists'];
                }
            }
        } catch (\Google_Service_Exception $e) {
            return ['status' => false, 'is_error' => true, 'message' => $e->getMessage()];
        }
    }

    private function _getHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header['name'] == $name) {
                return $header['value'];
            }
        }
    }

    private function _extractEmailFromString($text)
    {
        $str = '/([a-z0-9_\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
        preg_match_all($str, $text, $out);
        return isset($out[0][0]) ? $out[0][0] : [];
    }

    private function _extractNameFromString($text)
    {
        list($name) = explode(' <', $text);
        return trim($name);
    }

    public function extractTopicFromString($text)
    {
        if (preg_match("/\[[^\]]*\]/", $text, $matches)) {
            $replace = ['[', ']'];
            $text = str_replace($replace, '', $matches[0]);
            return $text;
        } else {
            return false;
        }
    }

    private function _decodeData($data)
    {
        return base64_decode(strtr($data, ['-' => '+', '_' => '/']));
    }
}
