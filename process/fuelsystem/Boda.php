<?php

/**
 * Description of Boda
 *
 * @author MAT
 */
include_once("cursorb.php");
include_once("stage.php");
include_once("ussd_sessionb.php");
include_once("pinib.php");
include_once("smsb.php");
include_once('secret.php');
include_once("loanb.php");
include_once("MakePayment.php");

class Boda
{

    private $transactionId;
    private $transactionTime;
    private $msisdn;
    private $requestString;
    private $user_session;
    private $table_boda;
    private $sms;
    // private $last_usercode;
    private $response;
    private $ussd_session;
    private $pini;
    private $db;
    private $mobile;
    private $stage;
    private $secret;
    private $stageid;
    private $loan;
    private $makePayment;
    private $bodaName;

    //private $pini;


    public function __construct()
    {

        //  var_dump($_POST);
        $this->transactionId = $_POST['sessionId'];
        $this->transactionTime = $_POST['transactionTime'];
        $this->msisdn = $_POST['phoneNumber'];
        $this->response = $_POST['response'];
        $this->requestString = $this->extractInputAfterAsterisk($_POST['text']);
        $this->table_boda = "bodauser";
        $this->pini = new pinb();
        $this->db = new Cursorb();
        $this->sms = new smsb();
        $this->stage = new stage();
        $this->mobile = $this->formatMobile($_POST['phoneNumber']);
        $this->secret = new secret();
        $this->stageid = $this->getstageidbymobile($this->mobile);
        $this->loan = new loanb();
        // die("into boda constructor");
        $this->makePayment = new MakePayments($this->msisdn, $this->mobile);

        $this->bodaName = $this->bodabymobile()["bodaUserName"];

        $this->ussd_session = new ussd_sessionb();
        $this->user_session = $this->ussd_session->getByTransactionId($this->transactionId);
    }

    private function extractInputAfterAsterisk($input)
    {
        $asteriskPosition = strpos($input, "*");

        if ($asteriskPosition !== false) {
            return substr($input, $asteriskPosition + 1);
        } else {
            return $input; // Return the original input if no asterisk found
        }
    }

    public function process()
    {
        $proceed = $this->validate_request(); //found


        if (!$proceed)
            return;


        if ($this->requestString == '') {

            $this->welcome();
        } else {

            //Lets get the session and see what the last response was
            if ($this->user_session == null) {
                $this->sessionError();
                return;
            }


            switch ($this->user_session) {
                case '00':
                    if ($this->requestString == '1') {
                        //Capture phone of Boda Boda Guys
                        $this->selectfuelpackage();
                    } else if ($this->requestString == '2') {
                        $this->checkaccountstatusrequestpin();
                        //check Account Status
                    } else if ($this->requestString == '3') {
                        // payback
                        //$this->db->
                        $this->checkLoanAmount();
                        //retrieve latest loan
                        //

                    } else if ($this->requestString == '4') {
                        //ask for old pin
                        $this->resetPin();
                        // Change PiN
                    } else {
                        $this->welcome_general(true);
                    }
                    break;
                case 'packages':
                    $this->resolvepackage();
                    //$this->storephone();
                    break;
                case 'pinadvance':
                    $this->initiatefuel();

                    break;
                case 'loanamount':
                    //verify pin and pay
                    $this->verifyPin();
                    break;
                case 'AccountPin':
                    $this->checkaccountststus();

                    break;
                case 'OldPin':
                    //validate old pin
                    $this->resetPinValidate();
                    break;
                case 'validateoldpin':
                    $this->resetPinProcess();
                    //process change to new new pin
                    break;

                default:
            }
        }
    }

    private function resetPin()
    {
        //$ussd_session = new ussd_session();
        $data['last_usercode'] = 'OldPin';
        //Lets write a response
        $menu_text = "Please Enter Your OldPIN To Proceed";
        $this->writeResponse($menu_text);
        $this->ussd_session->update($data, $this->transactionId);
    }

    function extractDigitsAfterAsterisk($input)
    {
        if (preg_match('/\*(\d+)/', $input, $matches)) {
            return $matches[1];
        } else {
            return null; // Return null if no match is found
        }
    }

    private function resetPinProcess()
    {
        //Lets reset the pin
        $pin1 = $this->requestString;
        $pin1 =  $this->extractDigitsAfterAsterisk($pin1);


        if (strlen($pin1) != 5) {
            $this->writeResponse("Pin must have 5 digits");
        }
        if ($this->user_session == null) {
            $this->sessionError();
            return;
        }
        //check if old pin matches and then do a reset


        $res = $this->pini->updatePin($this->mobile, $pin1, "boda");
        if ($res == 1) {

            $this->writeResponse("PIN has been changed successfully\n", true);
            return;
        } else {
            $this->writeResponse("Failed to update user PIN, please contact E-Fuel for help", true);
        }

        //we need to delete the session
        $data['last_usercode'] = 'successresetpin';

        $data['deleted'] = 1;


        $this->ussd_session->update($data, $this->transactionId);
    }

    private function resetPinValidate()
    {

        //Lets validate the PIN

        $oldpin = $this->requestString;

        if (!$this->pini->validatePin($this->mobile, $this->requestString, "boda")) {
            $this->sessionErrorIncorrectPin();
            return;

            //return;
        }
        //  $ussd_session = new ussd_session();
        $data['last_usercode'] = 'validateoldpin';
        //Lets write a response
        $menu_text = "Please enter your new PIN";
        $this->writeResponse($menu_text);
        $this->ussd_session->update($data, $this->transactionId);
        return;
    }

    private function checkaccountststus()
    {
        //echo "nothing works";
        $data['last_usercode'] = 'Accountststus';
        if (!$this->pini->validatePin($this->mobile, $this->requestString, "boda")) {
            //echo "failed";
            $this->sessionErrorIncorrectPin();
            //  echo "failed";
            return;

            //return;
        }
        //echo "passed";
        $user = $this->BodaStatus($this->mobile);
        if ($user == '1') {
            $menu_text = "Dear " . $this->bodaName . " Your Account is Active";
        } else {
            $menu_text = "Dear " . $this->bodaName . "  Your Account is Not Active";
        }
        $this->writeResponse($menu_text, true);
        $this->ussd_session->update($data, $this->transactionId);
        return;
    }

    private function checkaccountstatusrequestpin()
    {
        $data['last_usercode'] = 'AccountPin';

        $menu_text = "Enter your PIN";
        $this->writeResponse($menu_text);
        $this->ussd_session->update($data, $this->transactionId);
    }

    //verify pin 
    private function verifyPin()
    {
        $data['last_usercode'] = 'initiatedPayment';
        if (!$this->requestString == '1' || !$this->requestString == '2') {
            $this->sessionError();
            return;

            //return;
        }
        //show  different payment options
        //make payment
        $loan = $this->loan->getLatestUnpaidLoan($this->mobile);
        if ($loan != NULL) {
            $amount = $loan["loanAmount"];
            $interest = $loan["LoanInterest"];
            $loan_penalty = $loan['loan_penalty'];
            $total = $amount + $interest + $loan_penalty;
            $this->makePayment->initPayment($total, "Pay E-Fuel loan");

            //response
            //$menu_text = "Dear ".$this->bodaName."  a payment of shs " . $total . " Has been initiated";

            $menu_text = "Dear " . $this->bodaName . "  a payment of shs " . $total . " Has been initiated";
            $this->writeResponse($menu_text, true);
        }
        $this->ussd_session->update($data, $this->transactionId);
    }

    //check loan amount
    private function checkLoanAmount()
    {
        $data['last_usercode'] = 'loanamount';
        $loan = $this->loan->getLatestUnpaidLoan($this->mobile);
        if ($loan != NULL) {
            $amount = $loan["loanAmount"];
            $interest = $loan["LoanInterest"];
            $loan_penalty = $loan['loan_penalty'];
            $total = $amount + $interest + $loan_penalty;
            $response = "Dear " . $this->bodaName . "  you have a loan of shs " . $total . "";
            $response .= "\nSelect Payment Option\n";
            $response .= "1. Mobile Money\n";
            $response .= "2. Mojaloop\n";

            $this->writeResponse($response);
        } else {
            $menu_text = "You have no loan";
            $this->writeResponse($menu_text, true);
        }
        $this->ussd_session->update($data, $this->transactionId);
    }

    //check loan amount

    private function selectfuelpackage()
    {
        $menu_text = "Select Package\r\n1. Akeendo(5,000)";

        $data['last_usercode'] = 'packages';
        $this->ussd_session->update($data, $this->transactionId);
        $this->writeResponse($menu_text);
    }

    private function initiatefuel()
    {

        if (!$this->pini->validatePin($this->mobile, $this->extractDigitsAfterAsterisk($this->requestString), "boda")) {
            $this->sessionErrorIncorrectPin();
            return;

            //return;
        }
        if (!$this->checkIfActivated()) {
            $this->sessionErrornotactivated();
            return;
        }
        if (!$this->stage->StageStatus($this->stageid)) {
            $this->sessionErrornotactivatedstage();
            return;
        }
        $secret = ($this->secret->createsecret($this->mobile, 5000));
        if (!isset($secret)) {
            //echo $secret;
            $this->Errorsecretnotcreated();
            //echo "we are here";
            return;
        }
        $data['last_usercode'] = 'generatesecrete';
        $this->ussd_session->update($data, $this->transactionId);
        $menu_text = "Dear " . $this->bodaName . "  your secret code is: " . $secret . " for Fuel of UGX:5,000";
        $message =  "Your secret Code is" . $secret . " for Fuel of UGX:5,000";
        $this->writeResponse($menu_text, true);
        $this->sms->sms_faster($message, $this->sms->formatMobileInternational($this->msisdn), 1);
    }

    private function storephone()
    {

        $data['last_usercode'] = 'requestSecret';
        $data['phone'] = $this->requestString;
        $menu_text = "Please Enter Boda SecretCode";
        $this->ussd_session->update($data, $this->transactionId);
        $this->writeResponse($menu_text);
        // $this->advancemonthroute();
    }

    private function resolvepackage()
    {
        $amount = 0;
        $package = NULL;
        if ($this->requestString == 1) {
            $amount = 13000;
            $package = "basic";
        } elseif ($this->requestString == 1) {
            $amount = 23000;
            $package = "premium";
        } else {
            $amount = 33000;
            $package = "Master";
        }
        $data['last_usercode'] = 'pinadvance';
        $data['amount'] = $amount;
        $data['charge'] = $package;
        $menu_text = "Please Enter your Pin";
        $this->ussd_session->update($data, $this->transactionId);
        $this->writeResponse($menu_text);
    }

    private function validate_request()
    {
        //Lets check for the correct parameters
        //transaction id
        if (!isset($this->transactionId)) {

            $this->writeResponse('Transactionid not found', true);
            return false;
        }

        // //Transaction time
        // if (!isset($this->transactionTime)) {
        //     $this->writeResponse('Transactiontime not found', true);
        //     return false;
        // }


        // //msisdn
        if (!isset($this->msisdn)) {

            $this->writeResponse('msisdn not found', true);

            return false;
        }

        return true;
    }

    // //method to check if the number is activated
    private function checkIfActivated()
    {

        //lets get the user and check if activated
        $user = $this->BodaStatus($this->mobile);
        // $user=null;

        if ($user == null) {

            return false;
        }
        // user active now

        if ($user == '1') {
            return true;
        }
    }

    public function getstageidbymobile()
    {
        $result = $this->db->likeSelect($this->table_boda, ["stageId"], ["bodaUserPhoneNumber" => $this->mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $boda) {
                $boda_stageid = $boda["stageId"];
            }
            return $boda_stageid;
        }
    }

    private function BodaStatus($mobile)
    {

        $result = $this->db->likeSelect($this->table_boda, ["bodaUserStatus"], ["bodaUserPhoneNumber" => $mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $boda) {
                $boda_status = $boda["bodaUserStatus"];
            }
            return $boda_status;
        }
    }

    private function formatMobile($mobile)
    {
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

    private function formatMobileInternational($mobile)
    {
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

    // private function writeResponse($msg, $isend = false) {
    //     $resp_msg = 'responseString=' . urlencode($msg);
    //     if ($isend)
    //         $resp_msg .= '&action=end';
    //     else
    //         $resp_msg .= '&action=request';
    //     echo $resp_msg;
    // }
    // private function writeResponse($msg, $isend = false) {
    //     $resp_msg = '';

    //     if ($isend) {
    //         $resp_msg .= 'END ' . urlencode($msg);
    //     } else {
    //         $resp_msg .= 'CON ' . urlencode($msg);
    //     }

    //     echo $resp_msg;
    // }

    function writeResponse($msg, $isend = false)
    {
        $resp_msg = '';

        if ($isend) {
            $resp_msg .= 'END ' . $msg;
        } else {
            $resp_msg .= 'CON ' . $msg;
        }

        echo $resp_msg;
    }


    private function welcome_general($from_activation = true)
    {

        if ($from_activation == true) {

            $data['last_usercode'] = '00';

            $this->ussd_session->update($data, $this->transactionId);
        } else {


            $data['transaction_id'] = $this->transactionId;
            $data['msisdn'] = $this->msisdn;
            $data['last_usercode'] = '00';

            $this->ussd_session->insert($data);
        }


        $response  = "Welcome to E-Fuel Services?\n";
        $response .= "1. Request Fuel \n";
        $response .= "2. Check Account Status \n";
        $response .= "3. Payback Loan \n";
        $response .= "4. Change PIN ";


        // echo $response;

        $this->writeResponse($response, false);
    }

    private function BodaIdbymobile($mobile)
    {
        $result = $this->db->select($this->table_boda, ["BodaUserId"], ["bodaUserPhoneNumber" => $mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $boda) {

                $bodaId = $boda["BodaUserId"];
            }
            return $bodaId;
        }
    }

    // private function Bodafuelstationidbymobile($mobile) {
    //     $result = $this->db->select($this->table_boda, ["fuelStationId"], ["bodaUserPhoneNumber" => $mobile]);

    //     if (empty($result)) {
    //         return null;
    //     } else {
    //         foreach ($result as $boda) {

    //             $bodastationId = $agent["fuelStationId"];
    //         }
    //         return $bodastationId;
    //     }
    // }



    private function sessionError()
    {
        $this->writeResponse('Session error, please restart process', true);
    }

    private function welcome()
    {
        $this->welcome_general(false);
    }

    private function sessionErrorIncorrectPin()
    {
        $this->writeResponse("Incorrect PIN, please re-enter your PIN");
    }

    private function sessionErrornotactivated()
    {
        $this->writeResponse("Dear Customer, Your account is not Active", TRUE);
    }

    private function sessionErrornotactivatedstage()
    {
        $this->writeResponse("Dear Customer, Your Stage account is not Active", TRUE);
    }

    private function Errorsecretnotcreated()
    {
        $this->writeResponse("Dear Customer, something wrong happend when creating your secret", TRUE);
    }
    private function bodabymobile()
    {
        $result = $this->db->select($this->table_boda, null, ["bodaUserPhoneNumber" => $this->mobile]);
        return $result[0];
    }

    //put your code here
}

$boda = new Boda();
$boda->process();
//