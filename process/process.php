
<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
include_once("cursorweb.php");
include_once 'Paybill.php';
//include database file

include_once("ussd_session.php");
include_once("Employee.php");
include_once("activation.php");
include_once("pini.php");
include_once("yo.php");
include_once("sms.php");
include_once("Settings.php");
include_once ('msente.php');
include_once ('send_money.php');

class proccess {

    //ussd variable
    private $transactionId;
    private $transactionTime;
    private $msisdn;
    private $requestString;
    private $user_session;
    private $last_usercode;
    private $msente;
    private $ussd_session;
    private $sender;
    private $pini;
    private $paybill;

    public function __construct() {
        $this->msente = new msente();
        $this->ussd_session = new ussd_session();
        $this->sender = new send_money();
        $this->pini = new pin();
        $this->paybill = new Paybill();
    }

    private function performActivateAccountProcess() {
        //Lets validate the PIN
        $oldpin = $this->requestString;

        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }
        $mobile = $this->formatMobile($this->msisdn);

        if ($this->pini->validatePin($mobile, $oldpin) == 0) {

            $this->sessionErrorIncorrectPin();
            return;
        }

        //Lets prompt for a new PIN
        $data['last_usercode'] = 'ACT_PROMPT_02';
        $this->ussd_session->update($data, $this->transactionId);

        $menu_text = "Please enter a new PIN";
        $this->writeResponse($menu_text);
    }

    private function promptActivateAccount($is_error = false) {
        //We need to create the session
        if ($is_error == false) {
            $ussd_session = new ussd_session();
            $data['transaction_id'] = $this->transactionId;
            $data['msisdn'] = $this->msisdn;
            $data['last_usercode'] = 'ACT_PROMPT_01';

            $ussd_session->insert($data);
            $menu_text = "Welcome to CreditPlus\r\nPlease enter your activation code";
        } else {
            $data['last_usercode'] = 'ACT_PROMPT_01';
            // $this->ussd_session->update($this->user_session->id,$data);

            $menu_text = "Activation code is incorrect, please enter the correct code to activate";
        }
        $this->writeResponse($menu_text);
    }

    public function process() {
        $proceed = $this->validate_request(); //found
        

        if (!$proceed)
            return;



        if (isset($_GET['response']) && $_GET['response'] == 'false') {
            if ($this->checkIfActivated()) {
                if ($this->checkIfSuspended() == 2) {
                    $this->writeResponse("Welcome to CreditPlus \r \n Your account has been suspended by your employer", true);
                } else {
                    $this->welcome();
                }
            } else {

                $this->promptActivateAccount();
            }
        } else {

            //Lets get the session and see what the last response was

            $this->user_session = $this->ussd_session->getByTransactionId($this->transactionId);
            // $this->writeResponse($this->user_session,true);
            if ($this->user_session == null) {
                $this->sessionError();
                return;
            }

            $code = $this->user_session;
            $this->last_usercode = $code;
            //Lets validate if we have a correct request string

            $this->requestString = $_GET['ussdRequestString'];

            if ($this->requestString == "*") {
                $this->goBack();
            } else {
                switch ($code) {
                    case '00':
                        //lets analyse the current urequest string
                        if ($this->requestString == '1') {
                            $this->advancepurpose();
                        } else if ($this->requestString == '2') {
                            $this->getAccountStatus();
                            // $this->writeResponse('trying to get account status',true);
                        } else if ($this->requestString == '3') {
                            // $this->writeResponse('trying get ative advances',true);
                            $this->getActiveAdvances();
                        } else if ($this->requestString == '4') {
                            // $this->writeResponse('trying to reset pin',true);
                            $this->resetPin();
                        } else {
                            $this->welcome_general(true);
                        }
                        break;
                    case '00_00':
                        $this->getAdvance();
                        //$this->advancepurpose();
                        break;
                    case '00_01':
                        $this->getAdvancePinRequest();
                        break;
                    case 'handlephone':
                        $this->storephone();
                        break;
                    case 'sendrouter':
                        $this->sendrouter();
                        break;
                    case '00_01_00':

                        $this->getAdvanceProcess();
                        break;
                    case '00_02':
                        $this->getAccountStatusProcess();
                        break;
                    case '00_03':
                        $this->getActiveAdvancesProcess();
                        break;
                    case '00_04':
                        $this->resetPinValidate();
                        break;
                    case '00_04_00':
                        $this->resetPinProcess();
                        break;
                    case 'ACT_PROMPT_01':
                        $this->performActivateAccountProcess();
                        break;
                    case 'ACT_PROMPT_02':
                        $this->resetPinProcess();
                        break;
                    case 'list_bills':
                        $this->paybill->paybillgateway();
                        break;
                    case 'Airtime_menu':
                        $this->paybill->EnterAirtimeAmount();
                        break;
                    case 'Airtime_amount':
                        $this->paybill->validate_AirtimeCustomer();
                    case 'utility_menu':
                        $this->paybill->utilitygateway();
                        break;
                    case 'UMEME_menu':
                        $this->paybill->UMEME_gateway();
                        break;
                    case 'Umeme_meter_menu':
                        $this->paybill->EnterUmemeAmount();
                        break;
                    case 'Umeme_amount':
                        $this->paybill->validate_UmemeCustomer();
                        break;
                    case 'NWSC_menu':
                        $this->paybill->EnterNWSCMeter();
                        break;
                    case 'NWSC_Meter':
                        $this->paybill->EnterNWSCAmount();
                        break;
                    case 'NWSC_AMOUNT':
                        $this->paybill->validate_NWSCCustomer();
                        break;
                    case 'verify_customer':
                        $this->paybill->getAdvanceProcess();
                        break;
                    case 'TV_menu':
                        $this->paybill->TV_gateway();
                        break;
                    case 'DStv_GOtv_menu':
                        $this->paybill->dstv_gotv_gateway();
                        break;
                    case 'DStv_menu':
                        $this->paybill->EnterDstvcardnumber();
                        break;
                    case 'GOtv_menu':
                        $this->paybill->EnterGotvcardnumber();
                        break;
                    case 'cardnumber':
                        $this->paybill->validate_TVCustomer();
                        break;
                    case 'StarTimes_meter':
                        $this->paybill->EnterStarTimesAmount();
                        break;
                    case 'StarTimes_amount':
                        $this->paybill->validate_StarTimesCustomer();
                }
            }
        }
    }

    private function validate_request() {
        
        //Lets check for the correct parameters
        //transaction id
        if (!isset($_GET['transactionId'])) {

            $this->writeResponse('Transactionid not found', true);
            return false;
        }

        $this->transactionId = $_GET['transactionId']; //available
        // //Transaction time
        if (!isset($_GET['transactionTime'])) {
            $this->writeResponse('Transactiontime not found', true);
            return false;
        }

        $this->transactionTime = $_GET['transactionTime'];

        // //msisdn
        if (!isset($_GET['msisdn'])) {

            $this->writeResponse('msisdn not found', true);

            return false;
        } else {
            //check if the number is registered with creditplus
            $msisdn = $_GET['msisdn'];
            //we need to strip off the 256 and append a 0
            $mobile = $this->formatMobile($msisdn);
            // $mobile="0772093837";
            $employeei = new Employee();
            $employee = $employeei->getByMobile($mobile);
            
            //  $employee=null;
            if ($employee == null) {
                if ($this->checkNetwork($mobile) == "utl") {

                    $this->writeResponse("Sorry, your number is not registered on the CreditPlus \r \n Please call customer care 100 for help", true);
                    return false;
                } else {
                    $this->writeResponse("Sorry, your number is not registered on the CreditPlus \r \n visit www.creditplus.ug", true);
                    return false;
                }
            }
            // $this->writeResponse("retuned true",true);
            $this->msisdn = $mobile;
        }

        return true;
    }

// //method to check if the number is activated
    private function checkIfActivated() {

        $mobile = $this->formatMobile($this->msisdn);
        // $mobile="0772093837";
        $employeei = new Employee();
        $employee = $employeei->getByMobile($mobile);

        //lets get the user and check if activated
        $user = $employeei->isActive($mobile);
        // $user=null;

        if ($user == null) {

            return false;
        }
        // user active now

        if ($user == '1') {
            return true;
        }
    }

    private function formatMobile($mobile) {
        $length = strlen($mobile);
        $m = '0';
        //format 1: +256752665888
        if ($length == 13)
            return $m .= substr($mobile, 4);
        elseif ($length == 12)
            return $m .= substr($mobile, 3);
        elseif ($length == 9)
            return $m .= $mobile;

        return $mobile;
    }

    private function formatMobileInternational($mobile) {
        $length = strlen($mobile);
        $m = '+256';
        //format 1: +256752665888
        if ($length == 13)
            return $mobile;
        elseif ($length == 12) //format 2: 256752665888
            return "+" . $mobile;
        elseif ($length == 10) //format 3: 0752665888
            return $m .= substr($mobile, 1);
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }

    private function formatMobileInternational256($mobile) {
        $length = strlen($mobile);
        $m = '256';
        //format 1: +256752665888
        if ($length == 13)
            return $mobile;
        elseif ($length == 12) //format 2: 256752665888
            return "+" . $mobile;
        elseif ($length == 10) //format 3: 0752665888
            return $m .= substr($mobile, 1);
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }

    private function checkNetwork($mobile) {
        $network = "none";
        $m = $this->formatMobile($mobile);
        $prefix = substr($m, 0, 3);

        if ($prefix == "075" || $prefix == "070") //check for airtel
            $network = "airtel";
        else if ($prefix == "077" || $prefix == "078") //check for mtn
            $network = "mtn";
        else if ($prefix == "079") //check for africell
            $network = "africell";
        else if ($prefix == "071")//check for utl
            $network = "utl";

        return $network;
    }

    private function goBack() {
        $this->welcome_general(true);
        switch ($this->last_usercode) {
            case '00_01':
            case '00_02':
            case '00_03':
            case 'ACT_PROMPT_02':
                $this->welcome_general(true);
                break;
            case '00_04':
                $this->welcome_general(true);
                break;
        }
    }

    private function welcome_general($from_activation = true) {

        if ($from_activation == true) {

            $data['last_usercode'] = '00';
            //$data['last_usercode']="send_menu";

            $this->ussd_session->update($data, $this->transactionId);
        } else {


            $data['transaction_id'] = $this->transactionId;
            $data['msisdn'] = $this->msisdn;
            $data['last_usercode'] = '00';
            // $data['last_usercode']="send_menu";

            $this->ussd_session->insert($data);
        }


        $menu_text = "Welcome to CreditPlus\r\n1. Request Advance\r\n2. Account Status\r\n3. Advance Statement\r\n4. Change PIN";
        $this->writeResponse($menu_text);
    }

    private function writeResponse($msg, $isend = false) {
        $resp_msg = 'responseString=' . urlencode($msg);
        if ($isend)
            $resp_msg .= '&action=end';
        else
            $resp_msg .= '&action=request';
        echo $resp_msg;
    }

    private function checkIfSuspended() {
        $mobile = $this->formatMobile($this->msisdn);
        $employeei = new Employee();

        $employee = $employeei->getByMobile($mobile);
        if ($employee == null) {

            $this->writeResponse("Employee not found, please contact CreditPlus for help", true);
            return;
        }

        $status = $employeei->employeeStatus($mobile);
        if ($status == 2)
            return 2;
        else
            return false;
    }

    private function welcome() {
        $this->welcome_general(false);
    }

    private function advancemonthroute() {
        $msisdn = $_GET['msisdn'];
        //we need to strip off the 256 and append a 0
        //$this->writeResponse($msisdn);

        $mobile = $this->formatMobile($msisdn);
//       //testing which payment methode it is
        $employeei = new Employee();
        if ($employeei->getTypeByMobile($mobile) == 4) {
            if ($employeei->employeeStatus($mobile) == 5) {
                $this->getAdvance();
            } else {
                $this->monthlymenu();
            }

            $this->getAdvance();
        } else {
            $this->getAdvance1();
        }
    }

    private function sendrouter() {

        //test if the user wants to do a normal advance
        if ($this->requestString == '1') {

            $this->advancemonthroute();
        } elseif ($this->requestString == '2') {
            $data['last_usercode'] = 'askphonenumber';
            $menu_text = "Please Enter Phone Number";
            $this->writeResponse($menu_text);
            $data['last_usercode'] = 'handlephone';
            $this->ussd_session->update($data, $this->transactionId);
        } elseif ($this->requestString == '3') {
            $this->paybill->displaybills();
        }
    }

    private function storephone() {

        //$data['last_usercode'] = 'storedphone';
        $data['phone'] = $this->requestString;

        $this->ussd_session->update($data, $this->transactionId);
        $this->advancemonthroute();
    }

    private function advancepurpose() {

        //Lets update the session data
        $menu_text = "Purpose Of Advance \r\n1. For Self \r\n2. Send To Other\r\n3. Payments";

        $data['last_usercode'] = 'sendrouter';
        $data['data2'] = $this->requestString;

        $this->ussd_session->update($data, $this->transactionId);

        //$available_amount = $advance_limit - $current_bal ;

        $this->writeResponse($menu_text);
    }

    private function getAdvance() {
        //this is if the borrowe wants to withdraw the money himself
        //we need to show how much the employee can borrow
        $msisdn = $_GET['msisdn'];
        //we need to strip off the 256 and append a 0
        $mobile = $this->formatMobile($msisdn);
        // $mobile="0772093837";
        $employeei = new Employee();
        $status = $employeei->employeeStatus($mobile);
        $employee = $employeei->getByMobile($mobile);
        if ($employee == null) {
            $this->writeResponse("Employee not found, please contact CreditPlus for help", true);
            return;
        }
        if ($status == 3) {
            $this->writeResponse("You have an Out standing loan", true);
            return;
        }

        $current_bal = $employeei->getTotalBorrowed($mobile);


        $advance_limit = $employeei->advanceLimitAmount($mobile);
        if ($this->requestString == '1') {
            $advance_limit = $advance_limit / 4;
        } else {
            $advance_limit = $advance_limit;
        }
        //Lets update the session data

        $data['last_usercode'] = '00_01';
        $data['data2'] = $this->requestString;

        $this->ussd_session->update($data, $this->transactionId);

        $available_amount = $advance_limit - $current_bal;

        if ($available_amount > 0) {
            $menu_text = "Available Amount is UGX " . number_format($available_amount) . "\r\n";
            $menu_text .= "Please Specify The Amount";
        } else {
            $menu_text = "Advance limit has been reached\r\n*. Back";
            $this->writeResponse($menu_text, true);
            return;
        }

        $this->writeResponse($menu_text);
    }

    private function getAdvance1() {
        $msisdn = $_GET['msisdn'];
        //we need to strip off the 256 and append a 0
        $mobile = $this->formatMobile($msisdn);
        // $mobile="0772093837";
        $employeei = new Employee();
        $status = $employeei->employeeStatus($mobile);
        $employee = $employeei->getByMobile($mobile);
        if ($employee == null) {
            $this->writeResponse("Employee not found, please contact CreditPlus for help", true);
            return;
        }
        if ($status == 3) {
            $this->writeResponse("You have an Out standing loan", true);
            return;
        }

        $current_bal = $employeei->getTotalBorrowed($mobile);
        $advance_limit = $employeei->advanceLimitAmount($mobile);
        //Lets update the session data


        $data['last_usercode'] = '00_01';
        $data['data2'] = $this->requestString;

        $this->ussd_session->update($data, $this->transactionId);

        $available_amount = $advance_limit - $current_bal;

        if ($available_amount > 0) {
            $menu_text = "Available Amount is UGX " . number_format($available_amount) . "\r\n";
            $menu_text .= "Please specify the amount";
        } else {
            $menu_text = "Advance limit has been reached\r\n*. Back";
            $this->writeResponse($menu_text, true);
            return;
        }
        $this->writeResponse($menu_text);
    }

    private function resetPin() {
        $ussd_session = new ussd_session();
        $data['last_usercode'] = '00_04';


        $ussd_session->update($data, $this->transactionId);

        //Lets write a response
        $menu_text = "Please Enter Your OldPIN To Proceed";
        $this->writeResponse($menu_text);
    }

    private function resetPinProcess() {
        //Lets reset the pin
        $pin1 = $this->requestString;
        if (strlen($pin1) != 5) {
            $this->writeResponse("Pin must have 5 digits");
        }
        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }
        //do pin reset magic

        $mobile = $this->formatMobile($this->msisdn);
        // $mobile="0772093837";
        $employeei = new Employee();
        $employee = $employeei->getByMobile($mobile);
        if ($employee == null) {
            $this->writeResponse("Failed to find employee record based on mobile", true);
            return;
        }

        $res = $this->pini->updatePin($mobile, $pin1);
        if ($res == 1) {
            $data["Active"] = 1;
            $employeei->Activate($data, $mobile);
            $this->writeResponse("PIN has been changed successfully\r\n*. Back");
            return;
        } else {
            $this->writeResponse("Failed to update user PIN, please contact CreditPlus for help", true);
        }

        //we need to delete the session

        $data['deleted'] = 1;


        $this->ussd_session->update($data, $this->transactionId);
    }

    private function resetPinValidate() {

        //Lets validate the PIN
        $mobile = $this->formatMobile($this->msisdn);
        $oldpin = $this->requestString;
        if ($this->pini->validatePin($mobile, $oldpin) == 0) {

            $this->sessionErrorIncorrectPin();
            return;
        }
        $ussd_session = new ussd_session();
        $data['last_usercode'] = '00_04_00';

        $ussd_session->update($data, $this->transactionId);

        //Lets write a response
        $menu_text = "Please enter your new PIN";
        $this->writeResponse($menu_text);
    }

    private function monthlymenu() {
        $ussd_session = new ussd_session();
        //$data['last_usercode'] = '00_00';
        $data['last_usercode'] = 'send_menu';

        $ussd_session->update($data, $this->transactionId);
        $menu_text = "choose payment Option \n 1. Monthly Advance \n 2. 4 months Advance ";
        $this->writeResponse($menu_text);
    }

    private function getAdvancePinRequest() {
        //Lets update the session data
        $amount = $this->requestString;
        $session_data = [];
        $session_data = $this->ussd_session->databysessionid($this->transactionId);

        $receiver = $session_data[0]["phone"];
        $sender = $this->msisdn;

        if (!is_numeric($amount)) {
            $this->writeResponse("Invalid amount specified\r\nPlease specify a valid amount");
            return;
        } elseif ($amount < 10000) {
            $this->writeResponse("please enter amount not less than  10,000/=", true);
            return false;
        }
        //Lets update the session information
        $data['last_usercode'] = '00_01_00';
        $data['data1'] = $amount;

        $this->ussd_session->update($data, $this->transactionId);

        //Lets add commas into the amount
        $english_amount = number_format($amount);

        //Lets write a response
        if ($receiver == NULL) {
            $menu_text = "Please enter your PIN to get advance of UGX " . $english_amount;
        } else {
            $charge = $this->sender->router($sender, $receiver, $amount);
            $name = $this->sender->nameverification($this->formatMobileInternational256($receiver));

            $menu_text = "Send UGX " . $english_amount . " to " . $name . " on " . $receiver . " at a fee of UGX " . $charge . " Enter PIN for Comfirmation";
        }
        $this->writeResponse($menu_text);
    }

    private function getAdvanceProcess() {
        //Lets validate the PIN
        $pin3 = $this->requestString;

        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }
        // Lets vaidate the PIN

        $mobile = $this->formatMobile($this->msisdn);
        if ($this->pini->validatePin($mobile, $pin3) == 0) {
            // $this->writeResponse("The Pin You entered is wrong",true);
            $this->sessionErrorIncorrectPin();
            return;
        }

        $ussd_session = new ussd_session();
        $amount = $ussd_session->getdata1($this->transactionId);
        //Lets process the payment
        $session_data = $this->ussd_session->databysessionid($this->transactionId);
        $receiver = $session_data[0]["phone"];
        if (empty($receiver)) {
            $this->processPayment($amount);
        } else {
            $this->sendpayment();
        }
    }

    private function processPayment($amount) {

        $mobile = $this->formatMobile($this->msisdn);

        $employeei = new Employee();
        $employer = $employeei->getEmployerIdByMobile($mobile);
        //$this->writeResponse($employer,true);
        if ($employer == null) {
            $this->writeResponse("Employer not found, please contact CreditPlus for help", true);
            return;
        }
        //Lets check the status of the employer before proceeding
        /**
         * Employer Status
         * 0	-	Normal
         * 1	-	Processing payments
         * 2	-	Suspended
         * 3       -       Long Term Loan 
         * 
         * 
         */
        switch ($employeei->employerStatus($mobile)) {
            case 1:
                $this->writeResponse("Employer is still processing pending payements", true);
                return;
            case 2:
                $this->writeResponse("Employer is suspended from Credit Plus platform", true);
                return;

            case 4:
                $this->writeResponse("Employer is suspended from Credit Plus platform", true);
                return FALSE;
        }

        //Lets get the platform settings

        $service_fee = 390;
        $salary = $employeei->getSalaryByMobile($mobile);
        $current_bal = 0;


        $current_bal = $employeei->getTotalBorrowed($mobile);


        if (($amount + $current_bal) >= $salary) {
            $this->writeResponse("Cannot process payment because current balance would be greater than salary", true);
            return;
        }

        $ussd_session = new ussd_session();
        $advance_limit;
        $advance_limit = $employeei->advanceLimitAmount($mobile);
        //testing if the user wants a 4moths loan or 1 months advance
        $advancetype = $ussd_session->getdata2($this->transactionId);
        $data = [];
        $data2 = [];

        if ($employeei->getTypeByMobile($mobile) == 4) {
            if ($advancetype == 1) {
                $advance_limit = $advance_limit / 4;
                $data2["status"] = 5;
            } else {
                $advance_limit = $advance_limit;
                $data['repayments'] = 4;
                $data2["status"] = 3;
            }
        } else {
            $advance_limit = $advance_limit;
        }

        if ($amount > $advance_limit) {
            $this->writeResponse("Requested advance is greater than advance limit of UGX" . number_format($advance_limit), true);
            return;
        }

        if (($amount + $current_bal) > $advance_limit) {
            $rec_amount = $advance_limit - $current_bal;
            $this->writeResponse("Requested advance is greater than advance limit, recommended amount is UGX" . number_format($rec_amount), true);
            return;
        }
        $service_amount = ($amount + $service_fee);

        $network_fee = 0;
        $network = $this->checkNetwork($mobile);
        $cpsettings = new settings();
        $network_fee = $cpsettings->getsendchargeByName($network);
        $yo_charge = $cpsettings->getsendchargeByName("yo");
        $yo_amount = $amount + $network_fee + $yo_charge;
        $msente_amount = $amount;
        $data['amount_borrowed'] = $amount;
        $data['employee_id'] = $employeei->getIdByMobile($mobile);
        $data['employer_id'] = $employeei->getEmployerIdByMobile($mobile);
        $data['send_charge'] = $network_fee;
        $data['withdraw_charge'] = 0;
        $data['service_fee'] = $service_amount;

        //Lets process the payment
        $to = $this->formatMobileInternational256($this->msisdn);
        $yo = new Yo();
        $code;
        $cod;
        if ($this->checkNetwork($mobile) == 'utl') {
            $code = $this->msente->transfer($msente_amount, $to, $narrative = "ADVANCE FROM CREDITPLUS", "ADVANCE FROM CREDITPLUS");

            $cod = $this->msente->getYoRef($code);

            $data['transaction_id'] = $this->msente->getYoRef($code);
        } else {
            $code = $yo->withdraw($yo_amount, $to, $narrative = "ADVANCE FROM CREDITPLUS", "ADVANCE FROM CREDITPLUS");

            $cod = $yo->get_status($code);
            //$cod=0;
            $data['transaction_id'] = $yo->getYoRef($code);
            //$data['transaction_id'] = $cod;
        }
        //we need to analyse this code incase it shows an error

        $data['transaction_code'] = $cod;
        $proceed = false;
        if ($cod == 0 || $cod == 1 || $cod == 6)
            $proceed = true;
        else {
            if ($this->checkNetwork($mobile) == 'utl') {
                $this->writeResponse("Transaction failed. Please contact customer care on 100 for assistance", true);
                //$this->writeResponse("gate way error code ".$cod,true);
                return;
            } else {
                $this->writeResponse("Payment gateway cannot process request (error: $cod)", true);
                return;
            }
        }



        //Lets save the record

        $advance_id = $employeei->insert($data);

        $update = $employeei->update($data2, $mobile);

        //Lets send an sms
        // $this->load->library('sms/Infobip');
        $infobip = new infobip();


        $to = $this->formatMobileInternational($this->msisdn);
        $msg = "Dear " . $employeei->getEmployeeName($mobile) . ", your advance of UGX " . number_format($amount) . " has been processed with id " . $advance_id;
        $from = "CreditPlus";
        $message = $infobip->sendsms($from, $to, $msg);

        //Lets write the end response            
        $this->writeResponse("Advance has been processed successfully", true);

        $ussd_session = new ussd_session();
        //we need to delete the session
        $ussd_session->delete($transactionId);
    }

    public function sendpayment() {
        $mobile = $this->formatMobile($this->msisdn);
        $session_data = $this->ussd_session->databysessionid($this->transactionId);
        $receiver = $session_data[0]["phone"];
        $amount = $session_data[0]["data1"];
        $charge = $this->sender->router($mobile, $receiver, $amount);
        $employeei = new Employee();
        $employer = $employeei->getEmployerIdByMobile($mobile);
        //$this->writeResponse($employer,true);
        if ($employer == null) {
            $this->writeResponse("Employer not found, please contact CreditPlus for help", true);
            return;
        }
        //Lets check the status of the employer before proceeding
        /**
         * Employer Status
         * 0	-	Normal
         * 1	-	Processing payments
         * 2	-	Suspended
         * 3    -     Long Term Loan 
         * 
         * 
         */
        switch ($employeei->employerStatus($mobile)) {
            case 1:
                $this->writeResponse("Employer is still processing pending payements", true);
                return;
            case 2:
                $this->writeResponse("Employer is suspended from Credit Plus platform", true);
                return;

            case 4:
                $this->writeResponse("Employer is suspended from Credit Plus platform", true);
                return FALSE;
        }

        //Lets get the platform settings

        $service_fee = 390;
        $salary = $employeei->getSalaryByMobile($mobile);

        $current_bal = 0;
        $current_bal = $employeei->getTotalBorrowed($mobile);

        if (($amount + $current_bal + $charge) >= $salary) {
            $this->writeResponse("Cannot process payment because current balance would be greater than salary", true);
            return;
        }

        $ussd_session = new ussd_session();
        $advance_limit;
        $advance_limit = $employeei->advanceLimitAmount($mobile);

        //testing if the user wants a 4moths loan or 1 months advance
        $advancetype = $ussd_session->getdata2($this->transactionId);
        $data = [];
        $data2 = [];

        if ($employeei->getTypeByMobile($mobile) == 4) {
            if ($advancetype == 1) {
                $advance_limit = $advance_limit / 4;
                $data2["status"] = 5;
            } else {
                $advance_limit = $advance_limit;
                $data['repayments'] = 4;
                $data2["status"] = 3;
            }
        } else {
            $advance_limit = $advance_limit;
        }

        if ($amount + $charge > $advance_limit) {
            $this->writeResponse("Requested advance is greater than advance limit of UGX" . number_format($advance_limit), true);
            return;
        }

        if (($amount + $current_bal + $charge) > $advance_limit) {
            $rec_amount = $advance_limit - $current_bal;
            $this->writeResponse("Requested advance is greater than advance limit, recommended amount is UGX" . number_format($rec_amount), true);
            return;
        }

        $service_amount = ($amount + $service_fee);

        $network_fee = 0;
        $network = $this->checkNetwork($receiver);


        $cpsettings = new settings();

        $network_fee = $cpsettings->getsendchargeByName($network);
        $yo_charge = $cpsettings->getsendchargeByName("yo");
        $yo_amount = $amount + $network_fee + $yo_charge;
        $msente_amount = $amount;


        $data['amount_borrowed'] = $amount + $charge;
        $data['employee_id'] = $employeei->getIdByMobile($mobile);
        $data['employer_id'] = $employeei->getEmployerIdByMobile($mobile);
        $data['send_charge'] = $network_fee;
        $data['withdraw_charge'] = $charge;
        $data['service_fee'] = $service_amount;
        $data['receiver_phone'] = $receiver;

        //Lets process the payment

        $to = $this->formatMobileInternational256($receiver);
        $yo = new Yo();
        $code;
        $cod;
        if ($this->checkNetwork($mobile) == 'utl') {
            $code = $this->msente->transfer($msente_amount, $to, $narrative = "ADVANCE FROM CREDITPLUS", "ADVANCE FROM CREDITPLUS");

            $cod = $this->msente->getYoRef($code);

            $data['transaction_id'] = $this->msente->getYoRef($code);
        } else {
            $code = $yo->withdraw($yo_amount, $to, $narrative = "ADVANCE FROM CREDITPLUS", "ADVANCE FROM CREDITPLUS");

            $cod = $yo->get_status($code);
            //$cod=0;
            $data['transaction_id'] = $cod;
        }
        //we need to analyse this code incase it shows an error

        $data['transaction_code'] = $cod;
        $proceed = false;
        if ($cod == 0 || $cod == 1 || $cod == 6)
            $proceed = true;
        else {
            if ($this->checkNetwork($mobile) == 'utl') {
                $this->writeResponse("Transaction failed. Please contact customer care on 100 for assistance", true);
                //$this->writeResponse("gate way error code ".$cod,true);
                return;
            } else {
                $this->writeResponse("Payment gateway cannot process request (error: $cod)", true);
                return;
            }
        }



        //Lets save the record

        $advance_id = $employeei->insert($data);
        $sender = $this->sender->save_send_transactions($mobile, $receiver, $amount, $charge, $advance_id, $employeei->getIdByMobile($mobile));

        $update = $employeei->update($data2, $mobile);

        //Lets send an sms

        $infobip = new infobip();
        //money receiver
        $to1 = $this->formatMobileInternational($receiver);
        //money sender
        $to = $this->formatMobileInternational($this->msisdn);
        //message to sender
        $msg = "Dear " . $employeei->getEmployeeName($mobile) . ", you have sent UGX " . number_format($amount) . " to " . $this->sender->nameverification($this->formatMobileInternational256($receiver)) . " on " . $receiver . " . Fee UGX " . $charge . ". The total amount advanced to you is UGX " . number_format($amount + $charge) . ".";
        //message to receiver
        $msg1 = "You have received UGX " . number_format($amount) . " from " . $employeei->getEmployeeName($mobile) . " on " . $receiver . ". Trans ID:" . $advance_id . " Date: " . date('d-m-Y');
        $from = "CreditPlus";
        $message = $infobip->sendsms($from, $to, $msg);
        $message2 = $infobip->sendsms($from, $to1, $msg1);

        //Lets write the end response            
        $this->writeResponse("Advance has been processed successfully", true);

        $ussd_session = new ussd_session();
        //we need to delete the session
        $ussd_session->delete($transactionId);
        return;
    }

    private function getAccountStatus() {
        $ussd_session = new ussd_session();
        //Lets update the session data
        $data['last_usercode'] = '00_02';

        $ussd_session->update($data, $this->transactionId);
        //Lets write a response
        $menu_text = "Please enter your PIN";
        $this->writeResponse($menu_text);
    }

    private function getAccountStatusProcess() {
        //Lets validate the PIN
        $pin = $this->requestString;


        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }

        //Lets vaidate the PIN
        $mobile = $this->formatMobile($this->msisdn);
        // $this->writeResponse($mobile);

        if ($this->pini->validatePin($mobile, $pin) == 0) {

            $this->sessionErrorIncorrectPin();
            return;
        }

        $employeei = new Employee();
        $employee = $employeei->getByMobile($mobile);

        // $this->writeResponse($employee,true);
        if ($employee == null) {
            $this->writeResponse("Failed to find employee record based on mobile", true);
            return;
        }

        $current_bal = 0;
        $current_bal = $employeei->getTotalBorrowed($mobile);

        $status = "unknown";
        $employer = $employeei->employerStatus($mobile);


        switch ($employer) {
            case 0:
                $status = "Normal";
                break;
            case 2;
                $status = "Suspended";
                break;
            case 1;
                $status = "Processing payment";
                break;
        }


        //we need to check if the amount requested is greater than the advance limit

        $advance_limit = $employeei->advanceLimitAmount($mobile);

        $summary = "Status: $status\r\n";

        $summary .= "Account Name: " . $employeei->getEmployeeName($mobile) . "\r\n";
        $summary .= "Advance Limit: UGX" . number_format($advance_limit) . "\r\n";
        $summary .= "Advance Total: UGX" . number_format($current_bal);
        $summary .= "\r\n*. Back";

        $this->writeResponse($summary);

        //we need to delete the session
        //$this->ussd_session->delete($this->user_session->id);
    }

    private function sessionError() {
        $this->writeResponse('Session error, please restart process', true);
    }

    private function sessionErrorIncorrectPin() {
        $this->writeResponse("Incorrect PIN, please re-enter your PIN");
    }

    private function getActiveAdvances() {
        $ussd_session = new ussd_session();
        $data['last_usercode'] = '00_03';
        $ussd_session->update($data, $this->transactionId);

        $menu_text = "Please enter your PIN to proceed";
        $this->writeResponse($menu_text);
    }

    private function getActiveAdvancesProcess() {
        //Lets validate the PIN

        $pin = $this->requestString;


        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }
        $mobile = $this->formatMobile($this->msisdn);

        if ($this->pini->validatePin($mobile, $pin) == 0) {

            $this->sessionErrorIncorrectPin();
            return;
        }


        $employeei = new Employee();
        $employee = $employeei->getByMobile($mobile);

        if ($employee == null) {
            $this->writeResponse("Failed to find employee record based on mobile", true);
            return;
        }

        $current_bal = 0;

        $summary = "Account Name: " . $employeei->getEmployeeName($mobile) . "\r\n";

        $summary .= "Last 3 Transactions:\r\n";

        $loans = $employeei->getListBorrowed($mobile);
        // $this->writeResponse($loans);
        if (!empty($loans)) {
            foreach ($loans as $loan) {
                $current_bal += $loan["amount_borrowed"];

                $time = strtotime($loan["created_on"]);
                $myFormatForView = date("d/m/y g:i A", $time);

                $summary .= $myFormatForView . "       " . number_format($loan["amount_borrowed"]) . "/=\r\n";
            }
        } else {
            $summary .= "No advances found!\r\n";
        }


        $this->writeResponse($summary, true);
    }

}

$process = New proccess();
$process->process();
?>






















