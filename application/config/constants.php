<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') or define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       http://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
  |--------------------------------------------------------------------------
  | Movinghub System Constants
  |--------------------------------------------------------------------------
 */

//package types
define("PACKAGE_CONNECTIONS_PLUS", 1);
define("PACKAGE_CONNECTIONS", 2);

//roles
define("MHUB_AGENT", "1"); // movologist
define("MHUB_TEAMLEADER", "2"); // teamleader: previously MHUB_ADMIN
define("MHUB_MANAGER", "3"); // manager: previously MHUB_SUPER
define("MHUB_ADMIN", "4"); // admin
//scope
define("MHUB_INTERNAL", "1");
define("MHUB_EXTERNAL", "2");

//login method
define("USER_LOGIN_BASICAUTH", 1);
define("USER_LOGIN_GOOGLEAUTH", 2);

//mhub user activity
define("ACTIVITY_APPLICATION_ADDED", 1);
define("ACTIVITY_APPLICATION_MODIFIED", 2);
define("ACTIVITY_APPLICATION_SOLD", 3);
define("ACTIVITY_APPLICATION_CLOSED", 4);
define("ACTIVITY_APPLICATION_SHARED", 5);
define("ACTIVITY_APPLICATION_ASSIGNED", 6);
define("ACTIVITY_APPLICATION_RESUBMITTED", 7);
define("ACTIVITY_APPLICATION_VERIFY_SAVED", 8);
define("ACTIVITY_APPLICATION_VERIFY_REJECTED", 9);
define("ACTIVITY_APPLICATION_VERIFY_ACCEPTED", 10);

//user roles (outside mhub)
define("USER_ADMIN", "1");
define("USER_AGENT", "2");

//provider roles
define("PROVIDER_USER_AGENT", "1");
define("PROVIDER_USER_ADMIN", "2");
define("PROVIDER_USER_SUPER", "3");

// developer roles
define("USER_SUPER_DEV", "1");
define("USER_ADMIN_DEV", "2");
define("USER_DEV", "3");

//application, connection, provider, payment status
define("NOSTATUS", "0");
define("OPEN", "1");
define("QUICK", "2");
define("QUEUED", "3");
define("PENDING", "4");
define("PROCESSING", "5");
define("ACTIONED", "6");
define("RECONCILED", "7");
define("CANCELLED", "8");
define("FAILED", "9");
define("COMPLETED", "10");
define("ARCHIVED", "11");   //now called NOT INTERESTED
//additional for connection
define("CANCELLING", "12");
//additional for payment
define("INVOICED", "13");
define("REDEEMABLE", "14");
define("PAID", "15");
define("DECLINED", "16");   //now called NO SALE
define("BOUNCE", "17");
define("NOTCONTACTABLE", "18");
define("SALE", "19");
define("UNVERIFIED", "20");
//additional for provider status
define("ACCEPTED", "21");
define("REJECTED", "22");
define("INCOMPLETE", "23"); // QA reviewed and needs to be fixed by agent - NEW
define("REVIEWING", "24"); // leads that needs to be reviewied by QA
//application, connection, provider status tags
define("CALL_ATTEMPTS_1", "1");
define("CALL_ATTEMPTS_2", "2");
define("CALL_ATTEMPTS_3", "3");
define("CALL_ATTEMPTS_4", "4");
define("CALL_SCHEDULED", "5");
define("CALL_UNSCHEDULED", "6");
define("PENDING_CLIENT_ACTION", "7");
define("PARTNER_UPDATED", "8");
define("INCORRECT_PHONE", "9");
define("INCORRECT_SERVICE", "10");
define("UNRESPONSIVE", "11");
define("SYTEM_ERROR", "12");
define("NOT_INTERESTED", "13");
define("ALREADY_ORGANISED", "14");
define("PROVIDER_PANEL", "15");
define("UNSOLICITED_CALL", "16");
define("PARTNER_NOT_UPDATED", "17");
define("CONSENT_NOT_OBTAINED", "18");
define("PENDING_ARCHIVE", "19");
define("PARTIAL_ONLINE_MOVE", "20");
define("PARTIAL_SHARED_MOVE", "21");
define("CALL_ATTEMPTS_5", "22");
define("SELF_ORGANISING", "23");
define("PENDING_AGENT_ACTION", "24");
define("NOT_SERVICEABLE", "25");
define("C2_CALL_ATTEMPTS_1", "26");
define("C2_CALL_ATTEMPTS_2", "27");
define("C2_CALL_ATTEMPTS_3", "28");
define("C2_CALL_ATTEMPTS_4", "29");
define("C2_CALL_ATTEMPTS_5", "30");
define("PENDING_QA", "31");
define("INVESTOR", "32");
define("LAND_RENOVATIONS_BUILDING", "33");
define("PARTIAL_SHARED_MOVE_2", "34");
define("PARTIAL_CUSTOMER_SAVED", "35");
define("CALL_SCHEDULED_1", "36");
define("CALL_SCHEDULED_2", "37");
define("CALL_SCHEDULED_3", "38");
define("INCORRECT_INFO", "39");
define("MORE_INFO_REQUIRED", "40");
define("CONFIRM_GAS_AVAILABLE", "41");
define("DIRECT_TO_VENDOR", "42");
define("CUSTOMER_REQUEST", "43");
define("CREDIT_CHECK_FAIL", "44");
define("EMBEDDED_NETWORK", "45");
define("GREENFIELD_SITE", "46");
define("UNABLE_TO_CONTACT", "47");
define("BULK_HOT_WATER", "48");
define("TO_BE_CANCELLED", "49");
define("TEST_APPLICATION", "50");
define("DUPLICATE", "51");
define("UNREACHABLE", "52");
define("NOT_OK_WITH_PRICE", "53");
define("NOT_OK_WITH_DATA", "54");
define("DOES_NOT_WANT_BROADBAND", "55");
define("OTHERS_TAG", "56");     //REUSE IF NECESSARY
define("NMI_MIRN_MISMATCH", "57");
define("EIC_COMPLIANCE_BREACH", "58");
define("INVALID_IDENTIFICATION", "59");
define("NURTURE_1", "60");
define("NURTURE_2", "61");
define("NURTURE_3", "62");
define("NURTURE_4", "63");
define("UNSELLABLE_TARIFF", "64");
define("CUSTOMER_SCHEDULED", "65");
define("INACCURATE_QUOTE", "66");
define("FAILED_DISCLOSURE", "67");
define("FAILED_CREDIT_CHECK", "68");
define("INSTALL_DATE_UNAVAILABLE", "69");
define("INACCURATE_PAYMENT_INFO", "70");
define("PROVIDER_ISSUE", "71");
define("PARTIAL_SOLAR_CALLBACK", "72");
define("QUOTE_ONLY", "73");
define("INCORRECT_REFERRAL", "74");
define("INCORRECT_CALLBACK_TIMES", "75");
define("TO_BE_DELETED", "76");
define("BOUNCE_SAVED", "77");
define("BOUNCE_SUBMITTED", "78");
define("ACTION_CALL_CUSTOMER", "79");
define("ACTION_CREDIT_DECLINED", "80");
define("ACTION_FINANCE", "81");
define("LEAD_NOT_SENT", "82");
define("DUPLICATE_SALE", "83");
define("CANCELLED_SALE", "84");
define("CANCELLED_PRICING", "85");
define("CANCELLED_PROVIDER_PANEL", "86");
define("CANCELLED_UNKNOWN", "87");
define("UTILITIES_INCLUDED_IN_RENT", "88");
define("DID_NOT_GO_AHEAD_WITH_PROPERTY", "89");
define("TRANSFER_INTERNAL", "90");
define("SALE_MEETS_ELIGIBILITY", "91");
define("CREDIT_RISK_EMAIL_SENT", "92");
define("FINANCIAL_CAPACITY_EMAIL_SENT", "93");
define("SALE_SUBMITTED", "94");
define("ELIGIBILITY_NOT_MET", "95");
define("SHARED_HUB", "96");
define("NO_LONGER_MOVING", "97");
define("NO_SAVINGS", "98");
define("PARTNER_REQUEST", "99");
# added during QA module
define("NO_VALID_CONCESSION", "100");
define("PRODUCT_NOT_AVAILABLE", "101");
define("INCORRECT_CUSTOMER_DETAILS", "102");
define('RESEARCHING', 103);
define('UNABLE_TO_CONNECT', 104);
define('SAVED_ONLINE_1', 105);
define('SAVED_ONLINE_2', 106);
define('SAVED_ONLINE_3', 107);
define('SAVED_ONLINE_4', 108);
define('SAVED_ONLINE_5', 109);
define('SAVED_ONLINE_6', 110);

define("ACCOUNT_PENDING", "111");
define('UNSUBSCRIBED', "112");

// send to provider status
define("CONNECT_EMAIL", "1");
define("CONNECT_API", "2");
define("CONNECT_ATTACHMENT", "3");
define("CONNECT_FTP", "4");
define("CONNECT_SFTP", "5");
define("CONNECT_NONE", "6");
define("CONNECT_RESERVED_1", "7");
define("CONNECT_RESERVED_2", "8");
define("CONNECT_RESERVED_3", "9");
define("CONNECT_RESERVED_4", "10");
define("CONNECT_SECURED_CSV", "11");

// plan cluster
define("CLUSTER_NONE", "0");
define("CLUSTER_POSTCODE_WHITELIST", "1");
define("CLUSTER_POSTCODE_BLACKLIST", "2");
define("CLUSTER_DPID_WHITELIST", "3");
define("CLUSTER_DPID_BLACKLIST", "4");
define("CLUSTER_API_CHECK", "5");
define("CLUSTER_SUBURB_WHITELIST", "6");
define("CLUSTER_POSTCODE_TEMPLATE", "7");

// plan classification
define("CLASSIFY_NONE", "1");
define("CLASSIFY_EXISTING_CUSTOMER", "2");
define("CLASSIFY_NEW_CUSTOMER", "3");

// dashboard
define("DASHBOARD_DATA_THISWEEK", "1");
define("DASHBOARD_DATA_THISMONTH", "2");
define("DASHBOARD_DATA_THISQUARTER", "3");
define("DASHBOARD_DATA_THISYEAR", "4");
define("DASHBOARD_DATA_LAST7DAYS", "5");
define("DASHBOARD_DATA_LAST30DAYS", "6");
define("DASHBOARD_DATA_LAST12MONTHS", "7");
define("DASHBOARD_DATA_ALLTIME", "10");
define("DASHBOARD_DATA_TODAY", "11");
define("DASHBOARD_DATA_YESTERDAY", "12");
define("DASHBOARD_DATA_LASTWEEK", "13");
define("DASHBOARD_DATA_LASTMONTH", "14");
define("DASHBOARD_DATA_LASTQUARTER", "15");
define("DASHBOARD_DATA_LASTYEAR", "16");
define("DASHBOARD_DATA_THISHOUR", "17");
define("DASHBOARD_DATA_LASTHOUR", "18");
define("DASHBOARD_DATA_NEXTHOUR", "19");

define("DASHBOARD_WIDGET_SUMMARY", "1");
define("DASHBOARD_WIDGET_TABLE", "2");
define("DASHBOARD_WIDGET_BARGRAPH", "3");
define("DASHBOARD_WIDGET_LINEGRAPH", "4");

//moving in/out plans
define("DIRECTION_IN", 1);
define("DIRECTION_OUT", 2);
define("DIRECTION_BOTH", 3);

//activity type
define("ACTIVITY_MHUB", 1);
define("ACTIVITY_CRON", 2);
define("ACTIVITY_USER", 3);
define("ACTIVITY_LOCK", 4);
define("ACTIVITY_FRONT", 5);
define("ACTIVITY_COMMS", 6);
define("ACTIVITY_RATING", 7);
define("ACTIVITY_CUSTOMER", 8);
define("ACTIVITY_API", 9);
define("ACTIVITY_AMS", 10);

define("STATUS_OK", 1);
define("STATUS_NG", 0);

define("TICKET_OPEN", 1);
define("TICKET_IN_PROGRESS", 2);
define("TICKET_RESOLVED", 3);
define("TICKET_CLOSED", 4);

define("PAYMENT_OPEN", 1);
define("PAYMENT_CLOSED", 2);

define("RECEIPT_ISSUED", 1);
define("RECEIPT_OPEN", 2);

define("PAY_BY_SKIP", "0");
define("PAY_BY_DEBIT_MASTERCARD", "1");
define("PAY_BY_BANK_TRANSFER", "2");
define("PAY_BY_PAYPAL", "3");
define("PAY_BY_DEBIT_VISA", "4");
define("PAY_BY_THIRD_PARTY_INVOICE", "5");

define("TRANSACTION_PARTNER", 1);
define("TRANSACTION_AGENT", 2);

define("SOURCE_MHUB", 1);
define("SOURCE_API", 2);
define("SOURCE_WIZARD", 3);
define("SOURCE_WIDGET_QUICK", 4);
define("SOURCE_WIDGET_THREESTEP", 5);
define("SOURCE_AGENT_WALLET", 6);
define("SOURCE_OTHERS", 7);
define("SOURCE_WIDGET_QM2", 8);
define("SOURCE_AGENT_MOVE", 9);
define("SOURCE_WIDGET_AFFILIATE_QUICK", 10);
define("SOURCE_API_VAULT", 11);
define("SOURCE_PARTNER_MOVE", 12);
define("SOURCE_WIDGET_NOTIFY", 13);
define("SOURCE_CSV_IMPORT", 14);
define("SOURCE_WIDGET_MANAGER_QUICK", 15);
define("SOURCE_SNAPAPP", 20);
define("SOURCE_WIZARD_CUSTOM", 21);
define("SOURCE_UTILISAVER_CONTACT", 22);
define("SOURCE_POCKET_CRM", 23);
define("SOURCE_CUSTOM_WIDGET", 24);
define("SOURCE_CUSTOM_MICROSITE", 25);
define("SOURCE_HUB", 26);
define("SOURCE_ALEXA", 27);
define("SOURCE_GOOGLE_ASSISTANT", 28);
define("SOURCE_SIRI", 29);
define("SOURCE_CORTANA", 30);
define("SOURCE_THIRDPARTY_INTEGRATION", 31);
define("SOURCE_CUSTOMER_PORTAL_V2", 32);
define("SOURCE_API_INTEGRATION_FUB", 33);
define("SOURCE_API_INTEGRATION_REX", 34);
define("SOURCE_API_INTEGRATION_VAULTRE", 35);
define("SOURCE_API_INTEGRATION_SKYSLOPE", 36);
define("SOURCE_API_INTEGRATION_REA", 37);
define("SOURCE_API_INTEGRATION_AGENTBOX", 38);
define("SOURCE_API_INTEGRATION_PROPERTYME", 39);
define("SOURCE_API_INTEGRATION_SME", 40);
define("SOURCE_API_INTEGRATION_CONSOLECLOUD", 41);
define("SOURCE_API_INTEGRATION_MRI_UK", 42);
define("SOURCE_API_INTEGRATION_MRI_US", 43);
define("SOURCE_CRM_WORKFLOW_BUILDER_CLONE", 44);
define("SOURCE_CHATBOT", 45);

//widget instance type
define("WIDGET_PARTNER", 1);
define("WIDGET_MANAGER", 2);
define("WIDGET_AFFILIATE", 3);
define("WIDGET_GENERIC", 4);
define("WIDGET_QUICK_VERTICAL", 5);

//widget step options (kind of content that a step can have)
define("WIDGET_STEP_OPTION_QUESTION_GROUP", 1);
define("WIDGET_STEP_OPTION_SERVICE_PRODUCT_SELECTION", 2);
define("WIDGET_STEP_OPTION_PLAN_PROV_PART_QUES", 3);
define("WIDGET_STEP_OPTION_CONFIRMATION_EIC", 4);

//widget pre defined questions
define("WIDGET_QUESTION_TO_ADDRESS", 1);
define("WIDGET_QUESTION_APPLICATION_TYPE", 2);
define("WIDGET_QUESTION_APPLICATION_OFFER_TYPE", 3);
define("WIDGET_QUESTION_FROM_ADDRESS", 4);
define("WIDGET_QUESTION_MOVE_IN_DATE", 5);
define("WIDGET_QUESTION_CUSTOMER_TITLE", 6);
define("WIDGET_QUESTION_CUSTOMER_FIRST_NAME", 7);
define("WIDGET_QUESTION_CUSTOMER_LAST_NAME", 8);
define("WIDGET_QUESTION_CUSTOMER_EMAIL", 9);
define("WIDGET_QUESTION_CUSTOMER_PRIMARY_PHONE", 10);
define("WIDGET_QUESTION_CUSTOMER_SECONDARY_PHONE", 11);
define("WIDGET_QUESTION_CUSTOMER_DOB", 12);
define("WIDGET_QUESTION_TO_POSTCODE", 53);
define("WIDGET_QUESTION_CURRENT_ELEC_RETAILER", 54);
define("WIDGET_QUESTION_DROPZONE", 55);
define("WIDGET_QUESTION_COMMENTS", 56);

define("WIDGET_QUESTION_BUSINESS_ABN_AU", 23);
define("WIDGET_QUESTION_BUSINESS_TYPE_AU", 24);
define("WIDGET_QUESTION_BUSINESS_COMPANY_NAME_AU", 25);
define("WIDGET_QUESTION_BUSINESS_TRADING_AS_AU", 26);
define("WIDGET_QUESTION_BUSINESS_CRN_UK", 59);

define("WIDGET_QUESTION_CUSTOMER_ID_TYPE", 27);
define("WIDGET_QUESTION_CUSTOMER_ID_NUMBER", 28);
define("WIDGET_QUESTION_CUSTOMER_ID_ORIGIN_AU", 29);
define("WIDGET_QUESTION_CUSTOMER_ID_EXPIRY", 30);
define("WIDGET_QUESTION_CUSTOMER_ID_ORIGIN_NZ", 31);
define("WIDGET_QUESTION_CUSTOMER_ID_ORIGIN_US", 32);
define("WIDGET_QUESTION_CUSTOMER_ID_EXPIRY_US", 33);

define("WIDGET_QUESTION_CUSTOMER_CONCESSION_TYPE", 35);
define("WIDGET_QUESTION_CUSTOMER_CONCESSION_NUMBER", 36);
define("WIDGET_QUESTION_CUSTOMER_CONCESSION_START", 57);
define("WIDGET_QUESTION_CUSTOMER_CONCESSION_EXPIRY", 37);
define("WIDGET_QUESTION_CUSTOMER_2_TITLE", 38);
define("WIDGET_QUESTION_CUSTOMER_2_FIRST_NAME", 39);
define("WIDGET_QUESTION_CUSTOMER_2_LAST_NAME", 40);
define("WIDGET_QUESTION_CUSTOMER_2_EMAIL", 41);
define("WIDGET_QUESTION_CUSTOMER_2_DOB", 42);
define("WIDGET_QUESTION_CUSTOMER_2_PRIMARY_PHONE", 58);
define("WIDGET_QUESTION_BILLING_ADDRESS_TYPE", 43);
define("WIDGET_QUESTION_BILLING_ADDRESS_STANDARD", 44);
define("WIDGET_QUESTION_BILLING_ADDRESS_POBOX", 45);
define("WIDGET_QUESTION_PROPERTY_TYPE", 46);
define("WIDGET_QUESTION_PROPERTY_CLASSIFICATION", 47);
define("WIDGET_QUESTION_PROPERTY_OWNERSHIP", 48);
define("WIDGET_QUESTION_PROPERTY_RENTAL_PERIOD", 49);
define("WIDGET_QUESTION_PROPERTY_SOLAR_PANELS", 50);
define("WIDGET_QUESTION_PROPERTY_OCCUPANTS", 51);
define("WIDGET_QUESTION_CUSTOMER_CONCESSION_NAME", 52);

//widget question group
define("WIDGET_QUESTION_GROUP_APPLICATION_DETAILS", 1);
define("WIDGET_QUESTION_GROUP_BUSINESS_DETAILS", 2);
define("WIDGET_QUESTION_GROUP_CONTACT_DETAILS", 3);
define("WIDGET_QUESTION_GROUP_IDENTIFICATION_DETAILS", 4);

//referral type
define("REFERRAL_DIRECT", 1);
define("REFERRAL_AFFILIATE", 2);

//affiliate type
define("AFFILIATE_PREMIUM", 1);
define("AFFILIATE_FREE", 2);

//consent source
define("CONSENT_PHONE", 1);
define("CONSENT_ONLINE", 2);
define("CONSENT_ONLINE_DIRECT", 3);

//offer type
define("OFFER_TYPE_DEFAULT", 1);
define("OFFER_TYPE_MOVEIN", 2);
define("OFFER_TYPE_BETTERDEAL", 3);
define("OFFER_TYPE_RETENTION", 4);
define("OFFER_TYPE_MOVEOUT", 5);
define("OFFER_TYPE_QUOTE", 6);
define("OFFER_TYPE_HOMEOWNER", 7);
define("OFFER_TYPE_RENTER", 8);

//application type
define("APPLICATION_TYPE_RESIDENTIAL", 1);
define("APPLICATION_TYPE_BUSINESS", 2);

//crm_offer
define("CRM_OFFERS_ALLOW_ALL", 0);
define("CRM_OFFERS_CRM_ONLY", 1);

//pbx record
define("PBX_CALL_INBOUND", 1);
define("PBX_CALL_OUTBOUND", 2);

//Amazon Connect record
define("AMAZON_CONNECT_CALL_INBOUND", 1);
define("AMAZON_CONNECT_CALL_OUTBOUND", 2);

//errors
define("ERROR_400", "Database Error. Contact your site administrator.");
define("ERROR_401", "Database Error. This application could not be actioned at this time. Contact your site administrator.");
define("ERROR_402", "This application cannot be updated because it is not locked to you.");
define("ERROR_403", "This application cannot be updated because it has already been processed or is currently being processed.");
define("ERROR_404", "No data was retrieved for this application. Contact your site administrator.");
define("ERROR_405", "This application cannot be updated through this Workspace. This application must be updated at the same Workspace where it was created.");
define("ERROR_406", "Internal Error. Could not send email to your email address. Contact your site administrator.");
define("ERROR_407", "There were changes made on an existing customer information and this cannot be updated while there are similar applications waiting to be completed. Wait for a few minutes before actioning again.");
define("ERROR_408", "Your action cannot be completed because you do not have the permission to do so. Contact your site administrator.");
define("ERROR_409", "Account not found. Your account does not exist or it has been deactivated. Contact your site administrator.");
define("ERROR_410", "This link has already expired. The application linked to this may have been recently updated or have already been processed.");
define("ERROR_411", "This application contains prohibited items in the cart. Remove items to continue.");
define("ERROR_412", "Due to no activity, please refresh the page to start again.");
define("ERROR_413", "You already have pending reports. Please try again after a while.");
define("ERROR_414", "Report cannot be retrieved. Contact your site administrator.");
define("ERROR_415", "This application cannot be actioned. Contact your site administrator.");
define("ERROR_416", "You do not have permission to view payment details.");
define("ERROR_417", "This application cannot be actioned because of some missing information. Please check that you have provided all the necessary information.");
define("ERROR_418", "The values you submitted have disallowed characters.");
define("ERROR_419", "Currently SMS feature is disabled. Invalid SMS Provider. Contact your site administrator.");

define("ERROR_501", 'System Error. This application could not be sent at this time. Try again later or contact us.');
define("ERROR_502", 'Internal Error. Contact your site administrator.');
define("ERROR_503", "System Error. Could not mark as read at this time. Contact your site administrator.");
define("ERROR_504", "Internal Error. This application could not be unlocked at this time.");
define("ERROR_505", "'Workspace Data' could not be added on the Report Columns because there is more than one Workspace in your filter.");
define("ERROR_506", "'Provider Data' could not be added on the Report Columns because there is more than one Provider in your filter.");
define("ERROR_507", "Payment status cannot be updated because it is not allowed. Make sure you update it to the correct status.");
define("ERROR_508", "Payment status cannot be updated because the previous status did not match. Hit refresh and update again.");
define("ERROR_509", "Base commission is not set. Contact your site administrator");
define("ERROR_510", "Commission computation error. Contact your site administrator");
define("ERROR_511", "This application cannot be updated because its not yet finalised.");
define("ERROR_512", "System Error. Your request could not be processed at this time, try again later or contact us.");
//for PBX/VOIP configuration in crm
define("ERROR_513", "User device is not properly configured.");
define("ERROR_514", "User device extension is invalid.");
define("ERROR_515", "Commission configuration error. Contact your site administrator");
define("ERROR_516", "This application could not be actioned due to some error. Contact your site administrator (ERROR_516)");
define("ERROR_517", "Failed to process request because Provider Payments has not been finalised.");
define("ERROR_518", "Failed processing your request because application is locked at this time.");
//HUB
define("ERROR_600", "This email address is unavailable, please try again.");
define("ERROR_601", "We're sorry. Something went wrong with the registration process. Please try again or contact us.");
define("ERROR_602", "We're sorry. Something went wrong with the invitation process. Please try again or contact us.");
define("ERROR_603", "These settings cannot be submitted/saved. Contact your site administrator.");
define("ERROR_604", "Payment error. Contact your site administrator.");
define("ERROR_605", "Subscription Error. Please try again or contact us.");
define("ERROR_606", "Billing Error. Please try again or contact us.");
define("ERROR_607", "Action could not be completed because you have reached the limits of your plan. Contact your Admin.");
define("ERROR_608", "Action could not be completed because you have pending issues with your subscription. Contact your Admin.");

define("ERROR_609", "Unable to save the changes due to existing bookings.");
define("ERROR_610", "Error. Select a future date and time.");
define("ERROR_611", "Booking failed. Maximum attendees reached.");
define("ERROR_612", "Status cannot be updated because it is not allowed. Make sure you update it to the correct status.");
define("ERROR_613", "Adding of viewing time not allowed becuase of current inspection status.");
define("ERROR_614", "This email address cannot be used.");

define("ERROR_615", "Action could not be completed because the subscription related to this account has been canceled.");

//api specific
define("ERROR_999", "Data was not processed due to some internal error.");
define("ERROR_998", "Invalid Request Header.");
define("ERROR_1000", "Invalid request.");
define("ERROR_900", "Invalid dataset. Make sure required fields are set.");
define("ERROR_901", "Invalid date. Make sure date fields are properly formatted.");
define("ERROR_902", "Invalid email. Make sure email fields are properly formatted.");
define("ERROR_903", "Invalid plan selection. Make sure selected plans are available.");
define("ERROR_904", "Duplicate record not allowed.");
define("ERROR_905", "No service has been selected. Check previous selections and Select at least one to continue");
define("ERROR_906", "Invalid dataset. PartnerCode is not recognized, incorrect, or inactive.");
define("ERROR_907", "Invalid dataset. WidgetType is not recognized");
define("ERROR_908", "Invalid dataset. Metadata is defined but not properly formatted");
define("ERROR_909", "The current application status does not allow for any updates");
define("ERROR_910", "Invalid dataset. Required fields can not be set to null.");
define("ERROR_911", "Bounce Failed because there's nothing to update.");
define("ERROR_912", "This email is unavailable, please try again.");
define("ERROR_913", "Configuration error. At least one workspace category has to be configured.");
define("ERROR_914", "Access to this Workspace is restricted.");
define("ERROR_915", "Access to this Agent is restricted.");
define("ERROR_916", "Invalid dataset. Agent is invalid.");
define("ERROR_917", "This lead has expired. It might have been processed already or it was removed from the system.");
define("ERROR_918", "Invalid dataset. AffiliateCode is not recognized, incorrect, or inactive.");
define("ERROR_919", "Invalid dataset. Record Identifier is not recognized");
define("ERROR_920", "Duplicate record.");
define("ERROR_921", "You cannot schedule a callback for this application because it has already been processed or is currently being processed.");
define("ERROR_922", "Invalid dataset. WidgetInstance is not recognized");
define("ERROR_923", "No steps found for the given WidgetInstance");
define("ERROR_924", "Invalid dataset. ManagerCode is not recognized, incorrect, or inactive.");
define("ERROR_925", "Duplicate record. PartnerCode is unavailable, please try again.");
define("ERROR_926", "Data update not allowed. You may have provided an invalid dataset.");
define("ERROR_1005", "Missing email and/or password.");
define("ERROR_1036", "Multiple workspaces available. Choose one.");
define("ERROR_1037", "Missing email and/or password and/or workspace.");
define("ERROR_1038", "Invalid email and/or password and/or workspace.");
define("ERROR_1039", "Missing email and/or password.");
define("ERROR_1040", "Invalid email and/or password.");
define("ERROR_1041", "Category Not available for this Workspace.");
define("ERROR_1042", "User is not active.");
define("ERROR_1043", "Error finding active agent data.");
define("ERROR_1044", "Password Reset failed. Try again.");
define("ERROR_1045", "Wrong current password.");
define("ERROR_1046", "Error saving payment details.");
define("ERROR_1047", "Error uploading image for the application");
define("ERROR_1048", "Application does not exist.");
define("ERROR_1049", "Please fill atleast one field.");
define("ERROR_1050", "Invalid resource.");
define("ERROR_1051", "Application Status and Status Tag do not match.");
define("ERROR_1052", "Application Status not allowed.");

define("SUCCESS_1018", "Profile saved successfully.");
define("SUCCESS_1019", "Password updated successfully.");
define("SUCCESS_1020", "Payment details saved successfully.");
define("SUCCESS_1021", "Attachment added successfully.");
define("SUCCESS_1022", "Recommendation sent successfully.");
define("SUCCESS_1023", "Invitation sent successfully.");
define("SUCCESS_1024", "Feedback submitted successfully.");
define("SUCCESS_1025", "Question submitted successfully.");
define("SUCCESS_1026", "Callback booked successfully.");
define("SUCCESS_1027", "Resource request sent successfully.");

//api vault
define("ERROR_950", "No data was retrieved for this application.");

// nps rating
define("NPS_QNS_GRP", 1);
define("SURVEY_QNS_GRP", 2);

// plan pricing
define("STANDARD_METER_TYPE_ELEC", 1);
define("STANDARD_METER_TYPE_GAS", 1);

define("GST_ON_PLAN", 0.10);

//myMarketplace: interaction types
define("MYMP_SEND_MY_DETAILS", 1);
define("MYMP_SEND_ME_DETAILS", 2);

//myMarketplace: listing types
define("MYMP_LISTING_PUBLIC", 1);
define("MYMP_LISTING_PARTNER", 2);
define("MYMP_LISTING_AGENT", 3);

//Notification Module
//Notification type
define("GENERAL_MHUB", 1);  // only for mhub crm notifications
// for app
define("GENERAL_PARTNER", 2);
define("PUSH_PARTNER", 3);
define("ALERT_PARTNER", 4);

// Notification Source Type
define("CRM_NOTIFICATION", 1);
define("PARTNER_NOTIFICATION", 2);

// Nurture / Marketing Campaign Module
define("CAMPAIGN_TYPE_EMAIL", 1);
define("CAMPAIGN_TYPE_SMS", 2);

define("CAMPAIGN_CATEGORY_NURTURE", 1);
define("CAMPAIGN_CATEGORY_MARKETING", 2);

// QA Module
define("QA_PENDING", "0");
define("QA_COMPLETE", "1");

define("QA_PASS", 1);
define("QA_FAIL", 2);
define("QA_NA", 3);
define("QA_TEXT", 4);

define("QTYPE_PF", 1);
define("QTYPE_PFNA", 2);
define("QTYPE_TEXT", 3);

//FLOAT EPSILON
//https://www.php.net/manual/en/language.types.float.php
define("FLOAT_EPSILON", 0.00001);

//db operators
define("QUERY_FILTER_CONTAINS", 1);
define("QUERY_FILTER_NOT_CONTAINS", 2);
define("QUERY_FILTER_EQUALS", 3);
define("QUERY_FILTER_NOT_EQUALS", 4);
define("QUERY_FILTER_GREATER_THAN", 5);
define("QUERY_FILTER_GREATER_THAN_OR_EQUAL", 6);
define("QUERY_FILTER_LESS_THAN", 7);
define("QUERY_FILTER_LESS_THAN_OR_EQUAL", 8);
define("QUERY_FILTER_IS_EMPTY", 9);
define("QUERY_FILTER_IS_NOT_EMPTY", 10);
define("QUERY_FILTER_IS_LISTED", 11);
define("QUERY_FILTER_IS_NOT_LISTED", 12);
define("QUERY_FILTER_IS_BETWEEN", 13);

/*
 * mhub Integration Type
 */

define("MHUB_PROPERTYME", 1);
define("MHUB_FRESHSALES", 2);
define("MHUB_MYDESKTOP", 3);

define("MHUB_BASIC", 1);
define("MHUB_APIKEY", 2);
define("MHUB_BEARERTOKEN", 3);
define("MHUB_OAUTH2", 4);

/*
 * Provider Type
 */
define("PROVIDER_TYPE_MHUB", 1);
define("PROVIDER_TYPE_EXTERNAL", 2);

/*
 * Provider Visibility
 */
define("PROVIDER_VISIBILITY_PUBLIC", 1);
define("PROVIDER_VISIBILITY_PRIVATE", 2);

/*
 * AMS
 */

//AMS User Roles
define("AMS_ROLE_ADMIN", 1);
define("AMS_ROLE_IS", 2);
define("AMS_ROLE_ES", 3);
define("AMS_ROLE_CS", 4);

//AMS Leads Status
define("AMS_LEAD_STATUS_NEW", 1);
define("AMS_LEAD_STATUS_CONTACTED", 2);
define("AMS_LEAD_STATUS_INTERESTED", 3);
define("AMS_LEAD_STATUS_UNDER_REVIEW", 4);
define("AMS_LEAD_STATUS_DEMO", 5);
define("AMS_LEAD_STATUS_CONVERT", 6);
define("AMS_LEAD_STATUS_UNQUALIFIED", 7);

//AMS Lead Source
define("AMS_LEAD_SOURCE_AMS", 1);
define("AMS_LEAD_SOURCE_API", 2);
define("AMS_LEAD_SOURCE_WIDGET", 3);
define("AMS_LEAD_SOURCE_OTHERS", 4);
define("AMS_LEAD_SOURCE_HUB", 5);

define("ERROR_AMS_1", "Invalid dataset. Account Manager is not recognized, incorrect, or inactive.");
define("ERROR_AMS_2", "This email is unavailable, please try again.");
define("ERROR_AMS_3", "Invalid Form Code, please try again.");

//AMS Leads Reason For Contact
define("AMS_LEAD_REASON_FOR_CONTACT_SALES_INQUIRY", 1);
define("AMS_LEAD_REASON_FOR_CONTACT_PLATFORM_DEMO", 2);
define("AMS_LEAD_REASON_FOR_CONTACT_TRAINING", 3);
define("AMS_LEAD_REASON_FOR_CONTACT_OTHERS", 4);

// AMS Workflow Builder Types
define("AMS_WORKFLOW_BUILDER_LEADS_PROFILE_BASED", 1);
define("AMS_WORKFLOW_BUILDER_PARTNER_AGENT_PROFILE_BASED", 2);
define("AMS_WORKFLOW_BUILDER_ACCOUNT_MANAGER_REPORT_BASED", 3);
define("AMS_WORKFLOW_BUILDER_PARTNER_AGENT_REPORT_BASED", 4);

// CRM Workflow Builder Types
define("CRM_WORKFLOW_BUILDER_APPLICATION_BASED", 1);
define("CRM_WORKFLOW_BUILDER_REPORT_BASED", 2);

// AMS Tasks Status
define("AMS_TASK_STATUS_OPEN", 1);
define("AMS_TASK_STATUS_COMPLETED", 2);
define("AMS_TASK_STATUS_NOT_REQUIRED", 3);
define("AMS_TASK_STATUS_CANCELLED", 4);
define("AMS_TASK_STATUS_RESCHEDULED", 5);

// AMS Tasks Type
define("AMS_TASK_TYPE_CALL", 1);
define("AMS_TASK_TYPE_FOLLOW_UP", 2);
define("AMS_TASK_TYPE_EMAIL", 3);
define("AMS_TASK_TYPE_MEETING", 4);
define("AMS_TASK_TYPE_POST", 5);
define("AMS_TASK_TYPE_TRAINING", 6);

// AMS Tasks Repeat
define("AMS_TASK_REPEAT_NONE", 0);
define("AMS_TASK_REPEAT_EVERY_DAY", 1);
define("AMS_TASK_REPEAT_EVERY_WEEK", 2);
define("AMS_TASK_REPEAT_EVERY_MONTH", 3);
define("AMS_TASK_REPEAT_EVERY_YEAR", 4);
define("AMS_TASK_REPEAT_EVERY_CUSTOM", 5);

// AMS Tasks Repeat Custom
define("AMS_TASK_REPEAT_CUSTOM_DAILY", 1);
define("AMS_TASK_REPEAT_CUSTOM_WEEKLY", 2);
define("AMS_TASK_REPEAT_CUSTOM_MONTHLY", 3);
define("AMS_TASK_REPEAT_EVERY_YEARLY", 4);

// AMS Sendy
define("AMS_SENDY_ACTIVE_ADMINS", 1);
define("AMS_SENDY_INACTIVE_ADMINS", 2);
define("AMS_SENDY_ACTIVE_CAMPAIGN_ADMINS", 3);
define("AMS_SENDY_INACTIVE_CAMPAIGN_ADMINS", 4);
define("AMS_SENDY_ACTIVE_SALES_AGENTS", 5);
define("AMS_SENDY_INACTIVE_SALES_AGENTS", 6);
define("AMS_SENDY_PROSPECTING", 7);

/*
 * CONNECT
 */

define("CONNECT_USER_TABLE_TBL_PARTNER_AGENTS", 1);
define("CONNECT_USER_TABLE_TBL_USER", 2);
define("CONNECT_USER_TABLE_TBL_CUSTOMER", 3);
define("CONNECT_USER_TABLE_TBL_USER_MARKETPLACE", 4);
define("CONNECT_USER_TABLE_TBL_ACCOUNT_MANAGER_LEADS", 5);
define("CONNECT_USER_TABLE_TBL_CONNECT_CHAT_GUESTS", 6);
define("CONNECT_USER_TABLE_TBL_CUSTOMER_PROFILE", 7);

define("CONNECT_USER_GROUP_ADMIN", 1);
define("CONNECT_USER_GROUP_CAMPAIGN_ADMIN_CONNECTIONS_PLUS", 2);
define("CONNECT_USER_GROUP_CAMPAIGN_ADMIN_CONNECTIONS", 3);
define("CONNECT_USER_GROUP_SALES_AGENT_CONNECTIONS_PLUS", 4);
define("CONNECT_USER_GROUP_SALES_AGENT_CONNECTIONS", 5);
define("CONNECT_USER_GROUP_CUSTOMER_SERVICE_AGENT_CONNECTIONS", 6);
define("CONNECT_USER_GROUP_CRM_USER", 7);
define("CONNECT_USER_GROUP_CRM_TEAMLEADER", 8);
define("CONNECT_USER_GROUP_CRM_MANAGER", 9);
define("CONNECT_USER_GROUP_CUSTOMER", 10);
define("CONNECT_USER_GROUP_MARKETPLACE_MANAGER", 11);
define("CONNECT_USER_GROUP_MARKETPLACE_ADMIN", 12);
define("CONNECT_USER_GROUP_MARKETPLACE_AGENT", 13);
define("CONNECT_USER_GROUP_AMS_ADMIN", 14);
define("CONNECT_USER_GROUP_AMS_INTERNAL_SALES", 15);
define("CONNECT_USER_GROUP_AMS_EXTERNAL_SALES", 16);
define("CONNECT_USER_GROUP_CUSTOMER_PROFILE", 17);
define("CONNECT_USER_GROUP_AMS_CLIENT_SUCCESS", 18);

define("CONNECT_TICKET_STATUS_OPEN", 1);
define("CONNECT_TICKET_STATUS_PENDING", 2);
define("CONNECT_TICKET_STATUS_RESOLVED", 3);
define("CONNECT_TICKET_STATUS_CLOSED", 4);

define("CONNECT_TICKET_URGENCY_LOW", 1);
define("CONNECT_TICKET_URGENCY_MEDIUM", 2);
define("CONNECT_TICKET_URGENCY_HIGH", 3);

define("CONNECT_TICKET_IMPACT_LOW", 1);
define("CONNECT_TICKET_IMPACT_MEDIUM", 2);
define("CONNECT_TICKET_IMPACT_HIGH", 3);

define("CONNECT_TICKET_PRIORITY_LOW", 1);
define("CONNECT_TICKET_PRIORITY_MEDIUM", 2);
define("CONNECT_TICKET_PRIORITY_HIGH", 3);
define("CONNECT_TICKET_PRIORITY_URGENT", 4);

//CONNECT TICKET Source
define("CONNECT_TICKET_SOURCE_CONNECT", 1);
define("CONNECT_TICKET_SOURCE_API", 2);
define("CONNECT_TICKET_SOURCE_WIDGET", 3);
define("CONNECT_TICKET_SOURCE_OTHERS", 4);
define("CONNECT_TICKET_SOURCE_EMAIL", 5);
define("CONNECT_TICKET_SOURCE_AMAZON_CONNECT", 6);

define("ERROR_CONNECT_1", "Invalid dataset. Ticket category is not recognized, incorrect, or inactive.");
define("ERROR_CONNECT_2", "Invalid dataset. User ID is not recognized, incorrect, or inactive.");
define("ERROR_CONNECT_3", "Invalid Form Code, please try again.");
define("ERROR_CONNECT_4", "Invalid dataset. Subject is a required field.");
define("ERROR_CONNECT_5", "Invalid dataset. Description is a required field.");
define("ERROR_CONNECT_6", "Invalid dataset. Status is not recognized or incorrect.");
define("ERROR_CONNECT_7", "Invalid dataset. Urgency is not recognized or incorrect.");
define("ERROR_CONNECT_8", "Invalid dataset. Impact is not recognized or incorrect.");
define("ERROR_CONNECT_9", "Invalid dataset. Priority is not recognized or incorrect.");
define("ERROR_CONNECT_10", "Invalid dataset. Workspace is not recognized or incorrect.");
define("ERROR_CONNECT_11", "Invalid dataset. User is not recognized or incorrect.");

define("CONNECT_APP_GROUP_HUB", 1);
define("CONNECT_APP_GROUP_CRM", 2);
define("CONNECT_APP_GROUP_DASHBOARD", 3);
define("CONNECT_APP_GROUP_MARKETPLACE", 4);
define("CONNECT_APP_GROUP_CUSTOMER", 5);
define("CONNECT_APP_GROUP_AMS", 6);
define("CONNECT_APP_GROUP_AMS_PUBLIC", 7);
define("CONNECT_APP_GROUP_CAMPAIGN_PUBLIC", 8);
define("CONNECT_APP_GROUP_CUSTOMER_PORTAL", 9);

define("CONNECT_CHANNEL_TYPE_AMS_DEFAULT", 1);
define("CONNECT_CHANNEL_TYPE_CRM_PARTNER", 2);
define("CONNECT_CHANNEL_TYPE_HUB_DEFAULT", 3);
define("CONNECT_CHANNEL_TYPE_CAMPAIGN_PRIVATE", 4);
define("CONNECT_CHANNEL_TYPE_APPLICATION", 5);
define("CONNECT_CHANNEL_TYPE_AMS_LEADS", 6);
define("CONNECT_CHANNEL_TYPE_AMS_PUBLIC", 7);
define("CONNECT_CHANNEL_TYPE_CAMPAIGN_PUBLIC", 8);
define("CONNECT_CHANNEL_TYPE_CUSTOMER_PORTAL", 9);
define("CONNECT_CHANNEL_TYPE_AMS_DEFAULT_CS", 10);

/*
 * DOCS
 */
define("DOCS_STATUS_PUBLISHED", 1);
define("DOCS_STATUS_DRAFT", 2);

// SES
define("SES_BLACKLIST_TYPE_BOUNCE", 1);
define("SES_BLACKLIST_TYPE_COMPLAINT", 2);
define("SES_BLACKLIST_TYPE_TESTDATA", 3);
define("SES_BLACKLIST_TYPE_OTHER", 4);

define("SES_BOUNCE_PERMANENT", 1);
define("SES_BOUNCE_TRANSIENT", 2);

/*
 * CRM USER STATUS
 */
define("USER_STATUS_ACTIVE", 1);
define("USER_STATUS_ACTIVE_PLAY", 2);
define("USER_STATUS_BREAK", 3);
define("USER_STATUS_TRAINING", 4);
define("USER_STATUS_MEETING", 5);
define("USER_STATUS_IDLE", 6);
define("USER_STATUS_OFFLINE", 7); // Amazon Connect Only
define("USER_STATUS_BREAK_UNPAID", 8); // Amazon Connect Only
define("USER_STATUS_REWORK", 9); // Amazon Connect Only

/*
 * COLOR BASED ON INSPINIA
 */
define("INSPINIA_HEX_SUCCESS", "#136DBC");
define("INSPINIA_HEX_PRIMARY", "#2C83FF");
define("INSPINIA_HEX_DANGER", "#B63737");
define("INSPINIA_HEX_WARNING", "#E59A16");
define("INSPINIA_HEX_INFO", "#00EDC7");
define("INSPINIA_HEX_DEFAULT", "#545A5F");

/*
 * QA REVIEW RESULT TYPES
 */
define("QA_REVIEW_PASS", 1);
define("QA_REVIEW_FAIL", 2);

/*
 * HUBSPOT constants
 */
define('HUBSPOT_REGISTERED_CONNECTIONS', 'mh_registered_connections');
define('HUBSPOT_UNVERIFIED_CONNECTIONS', 'mh_unverified_connections');
define('HUBSPOT_VERIFIED_CONNECTIONS', 'mh_verified_connections');
define('HUBSPOT_DEREGISTERED_CONNECTIONS', 'mh_deregistered_connections');

define('HUBSPOT_REGISTERED_AFFILIATES', 'mh_registered_affiliates');
define('HUBSPOT_UNVERIFIED_AFFILIATES', 'mh_unverified_affiliates');
define('HUBSPOT_VERIFIED_AFFILIATES', 'mh_verified_affiliates');
define('HUBSPOT_DEREGISTERED_AFFILIATES', 'mh_deregistered_affiliates');

define('HUBSPOT_REGISTERED_PROVIDERS', 'mh_registered_providers');
define('HUBSPOT_UNVERIFIED_PROVIDERS', 'mh_unverified_providers');
define('HUBSPOT_VERIFIED_PROVIDERS', 'mh_verified_providers');
define('HUBSPOT_DEREGISTERED_PROVIDERS', 'mh_deregistered_providers');

/*
 * Email subscription categories
 */
define("EMAIL_SUBSCRIPTION_MARKETING", 1);
define("EMAIL_SUBSCRIPTION_REPORTS", 2);
define("EMAIL_SUBSCRIPTION_SYSTEM_MAINTENANCE", 3);
define("EMAIL_SUBSCRIPTION_MOVING_NOTIFICATIONS", 4);
define("SMS_SUBSCRIPTION_MARKETING", 1);
define("SMS_SUBSCRIPTION_REPORTS", 2);
define("SMS_SUBSCRIPTION_SYSTEM_MAINTENANCE", 3);
define("SMS_SUBSCRIPTION_MOVING_NOTIFICATIONS", 4);

/*
 * Email subscription status
 */
define("EMAIL_SUBSCRIPTION_UNKNOWN", 0);
define("EMAIL_SUBSCRIPTION_OPTEDIN", 1);
define("EMAIL_SUBSCRIPTION_OPTEDOUT", 2);


/*
 * CONNECT SD
 */
define("CONNECT_SD_USER_TYPE_USER_ADMIN", 1);
define("CONNECT_SD_USER_TYPE_USER_AGENT", 2);

define("CONNECT_SD_APP_DASHBOARD", 1);
define("CONNECT_SD_APP_HUB", 2);
define("CONNECT_SD_APP_PROVIDER", 3);
define("CONNECT_SD_APP_CUSTOMER_PORTAL", 4);
define("CONNECT_SD_APP_CUSTOMER_PORTAL_V2", 5);
define("CONNECT_SD_APP_PUBLIC_WEBSITE", 6);
define("CONNECT_SD_APP_CONNECT_SD", 7);

define("CONNECT_SD_TICKET_STATUS_OPEN", 1);
define("CONNECT_SD_TICKET_STATUS_PENDING", 2);
define("CONNECT_SD_TICKET_STATUS_RESOLVED", 3);
define("CONNECT_SD_TICKET_STATUS_CLOSED", 4);
define("CONNECT_SD_TICKET_STATUS_REOPEN", 5);

define("CONNECT_SD_TICKET_URGENCY_LOW", 1);
define("CONNECT_SD_TICKET_URGENCY_MEDIUM", 2);
define("CONNECT_SD_TICKET_URGENCY_HIGH", 3);

define("CONNECT_SD_TICKET_IMPACT_LOW", 1);
define("CONNECT_SD_TICKET_IMPACT_MEDIUM", 2);
define("CONNECT_SD_TICKET_IMPACT_HIGH", 3);

define("CONNECT_SD_TICKET_PRIORITY_LOW", 1);
define("CONNECT_SD_TICKET_PRIORITY_MEDIUM", 2);
define("CONNECT_SD_TICKET_PRIORITY_HIGH", 3);
define("CONNECT_SD_TICKET_PRIORITY_CRITICAL", 4);

define("CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_CUSTOMER", 1);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT", 2);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_INACTIVE", 3);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_ARCHIVE", 4);

define("CONNECT_SD_USER_STATUS_ONLINE", 1);
define("CONNECT_SD_USER_STATUS_OFFLINE", 2);
define("CONNECT_SD_USER_STATUS_BREAK", 3);
define("CONNECT_SD_USER_STATUS_TRAINING", 4);
define("CONNECT_SD_USER_STATUS_MEETING", 5);
