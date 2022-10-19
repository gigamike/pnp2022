<?php

defined('BASEPATH') or exit('No direct script access allowed');

/* * *
 *
 * References:
 *  https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/welcome.html
 *  https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/ses-verify.html
 *  https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-email-2010-12-01.html#setidentitynotificationtopic
 *
 */


use Aws\Credentials\Credentials;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Aws\Ses\Exception\SesException;
use Aws\Result;
use Aws\ResultInterface;

class Aws_ses_library
{
    protected $CI;
    protected $credentials;
    protected $ses_client;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('email_library');


        $this->credentials = new Credentials($this->CI->config->item('mm8_aws_access_key_id'), $this->CI->config->item('mm8_aws_secret_access_key'));

        $this->ses_client = new Aws\Ses\SesClient([
            'version' => 'latest',
            'region' => $this->CI->config->item('mm8_aws_ses_region'),
            'credentials' => $this->credentials
        ]);
    }

    public function verify_email_identity($email)
    {
        try {
            $result = $this->ses_client->verifyEmailIdentity([
                'EmailAddress' => $email
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function delete_email_identity($email)
    {
        try {
            $result = $this->ses_client->deleteIdentity([
                'Identity' => $email
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function send_custom_verification_email($email, $template)
    {
        try {
            $result = $this->ses_client->sendCustomVerificationEmail([
                'TemplateName' => $template,
                'EmailAddress' => $email
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function get_email_identity_status($email)
    {
        //expectd results:
        //'VerificationStatus' => 'Pending|Success|Failed|TemporaryFailure|NotStarted',
        try {
            $result = $this->ses_client->getIdentityVerificationAttributes([
                'Identities' => [$email]
            ]);

            $tmp_ = $result->toArray();
            if (isset($tmp_['VerificationAttributes'][$email])) {
                return ['successful' => true, 'dataset' => $tmp_['VerificationAttributes'][$email]];
            } else {
                return ['successful' => false, 'error_code' => 0, 'error_message' => "Internal error"];
            }
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function create_custom_verification_template($dataset)
    {
        try {
            $result = $this->ses_client->CreateCustomVerificationEmailTemplate([
                'TemplateName' => $dataset['TemplateName'],
                'FromEmailAddress' => $dataset['FromEmailAddress'],
                'TemplateSubject' => $dataset['TemplateSubject'],
                'TemplateContent' => $dataset['TemplateContent'],
                'SuccessRedirectionURL' => $dataset['SuccessRedirectionURL'],
                'FailureRedirectionURL' => $dataset['FailureRedirectionURL']
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function update_custom_verification_template($dataset)
    {
        try {
            $result = $this->ses_client->updateCustomVerificationEmailTemplate([
                'TemplateName' => $dataset['TemplateName'],
                'FromEmailAddress' => $dataset['FromEmailAddress'],
                'TemplateSubject' => $dataset['TemplateSubject'],
                'TemplateContent' => $dataset['TemplateContent'],
                'SuccessRedirectionURL' => $dataset['SuccessRedirectionURL'],
                'FailureRedirectionURL' => $dataset['FailureRedirectionURL']
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function delete_custom_verification_template($template_name)
    {
        try {
            $result = $this->ses_client->deleteCustomVerificationEmailTemplate([
                'TemplateName' => $template_name
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function list_custom_verification_template()
    {
        try {
            $result = $this->ses_client->listCustomVerificationEmailTemplates([]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function get_dkim_attributes($email)
    {
        try {
            $result = $this->ses_client->getIdentityDkimAttributes([
                'Identities' => [$email]
            ]);


            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    public function set_dkim_identity($email)
    {
        try {
            $result = $this->ses_client->setIdentityDkimEnabled([
                'Identity' => $email
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }

    //SendCustomVerificationEmail

    /**
     * 
     *  in order to know the bounced emails sent via individual email addresses
     *  we need to have those verified emails post their bounce notifications 
     *  to our webhook. 
     * 
     *  @param string $email - the email address of the identity (agent, partner, manager etc)
     *  @param string $topic - the ARN of the SNS topic we used to listen for bounce emails (get it by "aws sns list-topics")
     */
    public function set_bounce_email_notification_listener($email, $topic, $notification_type = 'Bounce')
    {
        try {
            $result = $this->ses_client->SetIdentityNotificationTopic([
                'Identity' => $email,
                'NotificationType' => $notification_type,
                'SnsTopic' => $topic
            ]);

            return ['successful' => true, 'dataset' => $result->toArray()];
        } catch (SesException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        } catch (AwsException $e) {
            $error_message = "Aws_ses_library() ERROR:<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
            return ['successful' => false, 'error_code' => $e->getAwsErrorCode(), 'error_message' => $e->getMessage()];
        }
    }
}
