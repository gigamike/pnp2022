<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*
* https://sendy.co/api?app_path=http://emails.movinghub.io
*
* http://emails.movinghub.io
* Current account: devops@tili.io
*
* Custom Fields
* Campaign Admin
*     Campaign_Reference_Code
*     Campaign_Name
*     First_Name
*     Last_Name
*     Position
*     Mobile_Phone
* Admin
*     Manager_Code
*     Admin_Name
*     First_Name
*     Last_Name
*     Position
*     Mobile_Phone
* Referring Agent
*     Campaign_Reference_Code
*     Campaign_Name
*     First_Name
*     Last_Name
*     Position
*     Mobile_Phone
*
*/
class Sendy_api_library
{
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

    public function subscribe($installationUrl, $apiKey, $name, $email, $list, $country = null, $ipaddress = null, $referrer = null, $gdpr = null, $silent = null, $hp = null, $boolean = null, $customFields = [])
    {
        $parameters = [
            'api_key' => $apiKey,
            'name' => $name,
            'email' => $email,
            'list' => $list,
        ];

        if (!is_null($country)) {
            $parameters['country'] = $country;
        }
        if (!is_null($ipaddress)) {
            $parameters['ipaddress'] = $ipaddress;
        }
        if (!is_null($referrer)) {
            $parameters['referrer'] = $referrer;
        }
        if (!is_null($gdpr)) {
            $parameters['gdpr'] = $gdpr;
        }
        if (!is_null($silent)) {
            $parameters['silent'] = $silent;
        }
        if (!is_null($hp)) {
            $parameters['hp'] = $hp;
        }
        if (!is_null($boolean)) {
            $parameters['boolean'] = $boolean;
        }
        if (count($customFields) > 0) {
            foreach ($customFields as $k => $v) {
                $parameters[$k] = $v;
            }
        }

        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/subscribe", false, $context);
        return $result;
    }

    public function unsubscribe($installationUrl, $apiKey, $email, $list, $boolean)
    {
        $parameters = [
            'api_key' => $apiKey,
            'email' => $email,
            'list' => $list,
        ];

        if (!is_null($boolean)) {
            $parameters['boolean'] = $boolean;
        }

        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/unsubscribe", false, $context);

        return $result;
    }

    public function deleteSubscriber($installationUrl, $apiKey, $email, $listId)
    {
        $parameters = [
            'api_key' => $apiKey,
            'email' => $email,
            'list_id' => $listId,
        ];
        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/api/subscribers/delete.php", false, $context);

        return $result;
    }

    public function subscriptionStatus($installationUrl, $apiKey, $email, $listId)
    {
        $parameters = [
            'api_key' => $apiKey,
            'email' => $email,
            'list_id' => $listId,
        ];

        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/api/subscribers/subscription-status.php", false, $context);

        return $result;
    }

    public function activeSubscriberCount($installationUrl, $apiKey, $listId)
    {
        $parameters = [
            'api_key' => $apiKey,
            'list_id' => $listId,
        ];

        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/api/subscribers/active-subscriber-count.php", false, $context);

        return $result;
    }

    public function createAndSendCampaign($installationUrl, $apiKey, $fromName, $fromEmail, $replyTo, $title, $subject, $plainText = null, $htmlText, $listIds = null, $segmentIds = null, $excludeListIds = null, $excludeSegmentsIds = null, $brandId = null, $queryString = null, $trackOpens = null, $trackClicks = null, $sendCampaign = null)
    {
        $parameters = [
            'api_key' => $apiKey,
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'reply_to' => $replyTo,
            'title' => $title,
            'subject' => $subject,
            'html_text' => $htmlText,
        ];

        if (!is_null($plainText)) {
            $parameters['plain_text'] = $plainText;
        }

        if ($listIds && $sendCampaign && !$segmentIds) {
            $parameters['list_ids'] = $listIds;
            $parameters['send_campaign'] = 1;
        }

        if ($segmentIds && $sendCampaign && !$listIds) {
            $parameters['segment_ids'] = $segmentIds;
            $parameters['send_campaign'] = 1;
        }

        if (!is_null($excludeSegmentsIds)) {
            $parameters['exclude_list_ids'] = $excludeSegmentsIds;
        }

        if (!is_null($brandId)) {
            $parameters['brand_id'] = $brandId;
        }

        if (!is_null($queryString)) {
            $parameters['query_string'] = $queryString;
        }

        if (!is_null($trackOpens)) {
            $parameters['track_opens'] = $trackOpens;
        }

        if (!is_null($trackClicks)) {
            $parameters['track_clicks'] = $trackClicks;
        }

        if (!is_null($sendCampaign)) {
            $parameters['send_campaign'] = $sendCampaign;
        }

        $postdata = http_build_query($parameters);
        $opts = ['http' => ['method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata]];
        $context  = stream_context_create($opts);
        $result = file_get_contents($installationUrl . "/api/campaigns/create.php", false, $context);

        return $result;
    }
}
