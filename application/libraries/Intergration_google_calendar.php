<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Intergration_google_calendar
{
    protected $CI;

    private $_appName = null;
    private $_scopes = Google_Service_Calendar::CALENDAR;
    private $_clientId = null;
    private $_clientSecret = null;
    private $_redirectUri = null;
    private $_user = 'me';
    private $_accessToken = null;
    private $_callbackCode = null;
    private $_googleAuthenticateId = null;

    private $_client = null;
    private $_service = null;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('account_manager_model');
        $this->CI->load->model('account_manager_google_calendar_model');
    }

    /**
    * Returns an authorized API client.
    * @return Google_Client the authorized client object
    */
    public function getClient($params)
    {
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
                $data['oauth_2_access_token'] = json_encode($client->getAccessToken());
                $this->CI->account_manager_model->save($data);

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
        $this->_service = new Google_Service_Calendar($this->_client);
    }

    /*
    *
    * https://developers.google.com/calendar/quickstart/php
    *
     */
    public function getEvents()
    {
        $lists = [];

        try {
            $calendarId = 'primary';
            $optParams = [
                // 'maxResults' => 10,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
            ];
            $results = $this->_service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            if (!empty($events)) {
                foreach ($events as $event) {
                    $eventAttributes['id'] = $event->id;
                    $eventAttributes['summary'] = $event->getSummary();

                    $start = $event->start->dateTime;
                    if (empty($start)) {
                        $eventAttributes['start_date'] = $event->start->date;
                        $eventAttributes['end_date'] = $event->end->date;
                        $eventAttributes['all_day'] = 1;
                    } else {
                        $eventAttributes['start_date'] = $event->start->dateTime;
                        $eventAttributes['end_date'] = $event->end->dateTime;
                        $eventAttributes['all_day'] = 0;
                    }

                    $lists[$event->id] = $eventAttributes;
                }
            }
        } catch (Exception $e) {
            // something went wrong, maybe delete tokens
            if (strpos($e->getMessage(), 'deleted_client')) {
                return 'deleted_client';
            }
        }

        return $lists;
    }

    /*
    *
    * https://developers.google.com/calendar/create-events
    *
     */
    public function addEvent($eventAttributes)
    {
        $event = [];

        try {
            if ($eventAttributes['all_day'] == 1) {
                $params = new Google_Service_Calendar_Event([
                    'summary' => $eventAttributes['activity'] . " (" . $eventAttributes['full_name'] . ")",
                    'description' => '',
                    'start' => [
                        'date' => date('Y-m-d', strtotime($eventAttributes['date_schedule_from'])),
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ],
                    'end' => [
                        'date' => date('Y-m-d', strtotime($eventAttributes['date_schedule_to'])),
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ],
                    'attendees' => [
                        ['email' => $eventAttributes['email']],
                    ],
                    'reminders' => [
                        'useDefault' => false,
                        'overrides' => [
                            ['method' => 'email', 'minutes' => 24 * 60],
                            ['method' => 'popup', 'minutes' => 10],
                        ],
                    ]
                ]);
            } else {
                $params = new Google_Service_Calendar_Event([
                    'summary' => $eventAttributes['activity'] . " (" . $eventAttributes['full_name'] . ")",
                    'description' => '',
                    'start' => [
                        'dateTime' => $eventAttributes['date_schedule_from'],
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ],
                    'end' => [
                        'dateTime' => $eventAttributes['date_schedule_to'],
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ],
                    'attendees' => [
                        ['email' => $eventAttributes['email']],
                    ],
                    'reminders' => [
                        'useDefault' => false,
                        'overrides' => [
                            ['method' => 'email', 'minutes' => 24 * 60],
                            ['method' => 'popup', 'minutes' => 10],
                        ],
                    ]
                ]);
            }
        
            $calendarId = 'primary';
            $event = $this->_service->events->insert($calendarId, $params);
            // print_r($event->id);
            // printf('Event created: %s\n', $event->htmlLink);
        } catch (Exception $e) {
            // something went wrong, maybe delete tokens
            if (strpos($e->getMessage(), 'deleted_client')) {
            }
        }
        
        return $event;
    }

    /*
    *
    * https://developers.google.com/calendar/v3/reference/events/update
    *
     */
    public function updateEvent($eventId, $eventAttributes)
    {
        try {
            $event = $this->_service->events->get('primary', $eventId);

            if (isset($eventAttributes['all_day'])) {
                if ($eventAttributes['all_day'] == 1) {
                    $start = new Google_Service_Calendar_EventDateTime([
                        'date' => date('Y-m-d', strtotime($eventAttributes['date_schedule_from'])),
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ]);

                    $end = new Google_Service_Calendar_EventDateTime([
                        'date' => date('Y-m-d', strtotime($eventAttributes['date_schedule_to'])),
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ]);

                    $event->setStart($start);
                    $event->setEnd($end);
                } else {
                    $start = new Google_Service_Calendar_EventDateTime([
                        'dateTime' => $eventAttributes['date_schedule_from'],
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ]);

                    $end = new Google_Service_Calendar_EventDateTime([
                        'dateTime' => $eventAttributes['date_schedule_to'],
                        'timeZone' => $this->CI->config->item('mm8_db_timezone'),
                    ]);

                    $event->setStart($start);
                    $event->setEnd($end);
                }
            }
        
            $updatedEvent = $this->_service->events->update('primary', $event->getId(), $event);
        } catch (Exception $e) {
            // something went wrong, maybe delete tokens
            if (strpos($e->getMessage(), 'deleted_client')) {
            }
        }
    }

    /*
    *
    * https://developers.google.com/calendar/v3/reference/events/delete
    *
     */
    public function deleteEvent($eventId)
    {
        try {
            $this->_service->events->delete('primary', $eventId);
        } catch (Exception $e) {
            // just ignore if already deleted
            
            // something went wrong, maybe delete tokens
            if (strpos($e->getMessage(), 'deleted_client')) {
            }
        }
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerIsGoogleCalendar($accountManagerID)
    {
        $accountManager = $this->CI->account_manager_model->getById($accountManagerID);
        if ($accountManager) {
            if (!empty($accountManager->gmail)
            && !empty($accountManager->oauth_2_client_id)
            && !empty($accountManager->oauth_2_client_secret)
            && !empty($accountManager->oauth_2_access_token)) {
                $params = [
                    'appName' => "AMS " . $accountManager->u_code,
                    'clientId' => $accountManager->oauth_2_client_id,
                    'clientSecret' => $accountManager->oauth_2_client_secret,
                    'redirectUri' => base_url() . "calendar/google-authenticate-callback/",
                    'accessToken' => $accountManager->oauth_2_access_token,
                ];
                $results = $this->getClient($params);
                if ($results) {
                    if ($results['is_authenticated']) {
                        // Add Event to Google Calendar
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerResetAccount($accountManagerID)
    {
        $data = [];
        $data['id'] = $accountManagerID;
        $data['gmail'] = null;
        $data['oauth_2_client_id'] = null;
        $data['oauth_2_client_secret'] = null;
        $data['oauth_2_access_token'] = null;
        $this->CI->account_manager_model->save($data);

        $this->CI->account_manager_google_calendar_model->deleteByAccountManagerId($accountManagerID);
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerGetEvents($accountManagerID)
    {
        $events = [];

        if ($this->accountManagerIsGoogleCalendar($accountManagerID)) {
            // Add Event to Google Calendar
            $results = $this->getEvents();
            if (!is_array($results)) {
                if ($results == 'deleted_client') {
                    $this->accountManagerResetAccount($accountManagerID);
                    

                    return [];
                }
            }

            $events = $results;
        }

        return $events;
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerSyncEvents($accountManagerID)
    {
        $googleCalendarEvents = $this->accountManagerGetEvents($accountManagerID);
        if (count($googleCalendarEvents) > 0) {
            // reset
            $this->CI->account_manager_google_calendar_model->deleteByAccountManagerId($accountManagerID);

            foreach ($googleCalendarEvents as $googleCalendarEvent) {
                $event = [
                    'account_manager_id' => $accountManagerID,
                    'date_schedule_from' => $googleCalendarEvent['start_date'],
                    'date_schedule_to' => $googleCalendarEvent['end_date'],
                    'all_day' => $googleCalendarEvent['all_day'],
                    'activity' => $googleCalendarEvent['summary'],
                    'google_calendar_event_id' => $googleCalendarEvent['id'],
                ];
                $this->CI->account_manager_google_calendar_model->save($event);
            }
        }
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerAddEvent($accountManagerID, $eventAttributes)
    {
        if ($this->accountManagerIsGoogleCalendar($accountManagerID)) {
            // Add Event to Google Calendar
            return $this->addEvent($eventAttributes);
        }

        return false;
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerUpdateEvent($accountManagerID, $eventAttributes)
    {
        if ($this->accountManagerIsGoogleCalendar($accountManagerID)) {
            // Add Event to Google Calendar
            return $this->updateEvent($eventAttributes['google_calendar_event_id'], $eventAttributes);
        }

        return false;
    }

    /*
    *
    * Custom for AMS
    *
     */
    public function accountManagerDeleteEvent($accountManagerID, $event)
    {
        if ($this->accountManagerIsGoogleCalendar($accountManagerID)) {
            // Add Event to Google Calendar
            return $this->deleteEvent($event->google_calendar_event_id);
        }

        return false;
    }
}
