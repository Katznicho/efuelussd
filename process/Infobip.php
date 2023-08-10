<?php
    // if (!defined('BASEPATH')) exit('No direct script access allowed');  
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include (CP_DIR . '/infobip/JsonMapper/JsonMapper.php'); 
    // require_once APPPATH.'../infobip/JsonMapper/JsonMapper.php';
    require_once APPPATH.'infobip/api/AbstractApiClient.php';
    require_once APPPATH.'infobip/api/configuration/Configuration.php';
    require_once APPPATH.'infobip/api/client/SendSingleTextualSms.php';
    require_once APPPATH.'infobip/api/configuration/BasicAuthConfiguration.php';
    require_once APPPATH.'infobip/api/model/sms/mt/send/textual/SMSTextualRequest.php';
    require_once APPPATH.'infobip/api/model/sms/mt/send/SMSResponse.php';
    require_once APPPATH.'infobip/api/model/sms/mt/send/SMSResponseDetails.php';
    require_once APPPATH.'infobip/api/model/Status.php';
    
    
    use infobip\api\client\SendSingleTextualSms;
    use infobip\api\configuration\BasicAuthConfiguration;
    use infobip\api\model\sms\mt\send\textual\SMSTextualRequest;

    /**
     * Web: https://portal.infobip.com/
     *API: http://dev.infobip.com/
     */

    class Infobip {
        
        protected $_ci;
        protected $messageId;
        protected $msgStatus;
        protected $receiver;
        protected $errorMsg;
        
        public function __construct() {
            
        }
        
        
        public function sendSms($to,$msg){
            // Initializing SendSingleTextualSms client with appropriate configuration
            $client = new SendSingleTextualSms(new BasicAuthConfiguration('creditplusdev', 'Test1234@'));
            
            // Creating request body
            $requestBody = new SMSTextualRequest();
            $requestBody->setFrom('CreditPlus');
            $requestBody->setTo($to);
            $requestBody->setText($msg);
            
            // Executing request
            try {
                $response = $client->execute($requestBody);
                //$sentMessageInfo = $response->getMessages()[0];
                /*echo "Message ID: " . $sentMessageInfo->getMessageId() . "\n";
                echo "Receiver: " . $sentMessageInfo->getTo() . "\n";
                echo "Message status: " . $sentMessageInfo->getStatus()->getName();*/
            } catch (Exception $exception) {
                /*echo "HTTP status code: " . $exception->getCode() . "<br/>";
                echo "Error message: " . $exception->getMessage();*/
            }
        }
}


?>
