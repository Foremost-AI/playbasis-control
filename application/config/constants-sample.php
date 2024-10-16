<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!defined('CURL_SSLVERSION_TLSv1')) define('CURL_SSLVERSION_TLSv1', 1);

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
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('CAPTCHA_PUBLIC_KEY', '6LcPOPgSAAAAAFELsbPGvNeEByjWavQNk1f7ZLSY');
define('CAPTCHA_PRIVATE_KEY', '6LcPOPgSAAAAAIH3-5uY9DFrXpkTiBoTPsWuasGK');

define('S3_IMAGE', 'https://images.pbapp.net/');
define('DIR_IMAGE', 'C:\\Program Files (x86)\\Ampps\\www\\control\\image\\');
//define('DIR_IMAGE', './control/image/');

define('DEFAULT_PASSWORD', 'playbasis');

define('DEFAULT_PLAN', '542a69e02cfa49be0c6755e4'); // default package when a user registers without plan
define('BETA_TEST_PLAN', '52ea1eab8d8c89401c0000d8'); // beta plan
define('FREE_PLAN', '5428f2df998040b0458b45f2'); // free plan
define('DEFAULT_PLAN_PRICE', 0); // default is free
define('DEFAULT_PLAN_DISPLAY', false); // default is to not display the plan
define('DEFAULT_TRIAL_DAYS', 0); // default is having no trial period
define('MAX_ALLOWED_TRIAL_DAYS', 90); // this is limited by PayPal
define('PAYMENT_CHANNEL_PAYPAL', 'PayPal');
define('PAYMENT_CHANNEL_STRIPE', 'Stripe');
define('PAYMENT_CHANNEL_DEFAULT', PAYMENT_CHANNEL_STRIPE);
define('STRIPE_API_KEY', 'sk_test_8ChxEiUQyzeiN7OgnnFDBBYG');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_1dekH9esZmjybutm3r76RIhG');
define('PAYPAL_MERCHANT_ID', 'CEUXV2RH33E92');
define('PAYPAL_MODIFY_NEW_SUBSCRIPTION_ONLY', 0);
define('PAYPAL_MODIFY_EITHER_NEW_SUBSCRIPTION_OR_MODIFY', 1);
define('PAYPAL_MODIFY_CURRENT_SUBSCRIPTION_ONLY', 2);
define('PAYPAL_ENV', '');
define('PRODUCT_NAME', 'Playbasis API Subscription');
define('PURCHASE_SUBSCRIBE', 0);
define('PURCHASE_UPGRADE', 1);
define('PURCHASE_DOWNGRADE', 2);
define('FOREVER', 100); // number of years our system used for representing an unlimited value (for example, free package has no "date_expire")

define('EMAIL_FROM', 'no-reply@playbasis.com');

define('NUMBER_OF_ADJACENT_PAGES', 6);
define('NUMBER_OF_RECORDS_PER_PAGE', 10);

//define('API_SERVER', 'https://api.pbapp.net');
define('API_SERVER', 'http://localhost');
define('NGINX_API_SERVER', 'http://host.docker.internal');
define('NODE_SERVER', 'https://node.pbapp.net');
define('WIDGET_SERVER', 'widget.pbapp.net');

define('TEST_PLAYER_ID', 'test');
define('LIMIT_PLAYERS_QUERY', 10000);

define('ALLOW_SIGN_UP', false);

define('LIMIT_USER_LOGIN_ATTEMPT',3);

define('MEDIA_MANAGER_SMALL_THUMBNAIL_WIDTH', 80);
define('MEDIA_MANAGER_SMALL_THUMBNAIL_HEIGHT', 80);
define('MEDIA_MANAGER_LARGE_THUMBNAIL_WIDTH', 240);
define('MEDIA_MANAGER_LARGE_THUMBNAIL_HEIGHT', 240);
define('MEDIA_MANAGER_MAX_IMAGE_HEIGHT', 4000);
define('MEDIA_MANAGER_MAX_IMAGE_WIDTH', 4000);

define('EMAIL_TYPE_NOTIFY_INACTIVE_CLIENTS', 'notifyInactiveClients');
define('EMAIL_BCC_PLAYBASIS_EMAIL', 'pongsakorn.ruadsong@playbasis.com');
define('EMAIL_DEBUG_MODE', true);
define('EMAIL_TYPE_REPORT', 'report');
define('EMAIL_TYPE_REMIND_TO_SETUP_SUBSCRIPTION', 'remindClientsToSetupSubscription');
define('EMAIL_TYPE_CLIENT_REGISTRATION', 'listClientRegistration');
define('EMAIL_TYPE_USER', 'user');

define('GRACE_PERIOD_IN_DAYS', 5);

define('FULLCONTACT_API', 'https://api.fullcontact.com');
define('FULLCONTACT_API_KEY', '8f10cefa2030457a');
define('FULLCONTACT_RATE_LIMIT', 1); // per sec
define('FULLCONTACT_CALLBACK_URL', 'https://api.pbapp.net/notification/%s');
define('FULLCONTACT_REQUEST_OK', 200);
define('FULLCONTACT_REQUEST_WEBHOOK_ACCEPTED', 202);
define('FULLCONTACT_USER_AGENT', 'FullContact');

define('DEMO_SITE_ID', '52ea1eac8d8c89401c0000e5');

define('GECKO_API_KEY', '3b28853ec6792fb3cc0e94ad891d1659');
define('GECKO_URL', 'https://push.geckoboard.com/v1/send/');

define('DATE_FREE_ACCOUNT_SHOULD_SETUP_MOBILE', '2015-06-01');

define('CACHE_ADAPTER', 'file');
define('CACHE_KEY_VERSION', 'version-api');
define('CACHE_TTL_IN_SEC', 10*60);

/* End of file constants.php */
/* Location: ./application/config/constants.php */
