<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use infobip\api\client\SendMultipleTextualSmsAdvanced;
use infobip\api\configuration\BasicAuthConfiguration;
use infobip\api\model\Destination;
use infobip\api\model\sms\mt\send\Message;
use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;
$client = new SendSingleTextualSms(new BasicAuthConfiguration("creditplusdev", "Test1234@"));

// Creating request body
function sendsms()
{
$requestBody = new SMSTextualRequest();
$requestBody->setFrom("anish");
$requestBody->setTo(["256772093837"]);
$requestBody->setText("This is an example message.");

// Executing request
try {
    $response = $client->execute($requestBody);
    $sentMessageInfo = $response->getMessages()[0];
    echo "Message ID: " . $sentMessageInfo->getMessageId() . "\n";
    echo "Receiver: " . $sentMessageInfo->getTo() . "\n";
    echo "Message status: " . $sentMessageInfo->getStatus()->getName();
} catch (Exception $exception) {
    echo "HTTP status code: " . $exception->getCode() . "\n";
    echo "Error message: " . $exception->getMessage();
}
}










?>