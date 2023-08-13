<?php
error_reporting(0);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Agent
 *
 * @author MAT
 */
include_once("cursorb.php");
include_once("ussd_sessionb.php");
include_once("smsb.php");
//include_once("smsb.php");
include_once('secret.php');
include_once('pinib.php');
include_once('loanb.php');
include_once ('FloatDeposits.php');
class Agent
{
    
    private $transactionId;
    private $transactionTime;
    private $msisdn;
    private $requestString;
    private $user_session;
    private $table_agent;
    private $mobile;
    // private $last_usercode;
    private $response;

    private $ussd_session;
    private $pini;
    private $db;
    private $secret;
    private $table_boda;
    private $loanb;
    private $sms;
    private $deposits;
    private $stationId;
    private $table_station;
    // private $session_data=[];
    //= $this->ussd_session->databysessionid($this->transactionId);


    //private $pini;


    public function __construct()
    {
        $this->transactionId = $_GET['transactionId'];
        $this->transactionTime = $_GET['transactionTime'];
        $this->msisdn = $_GET['msisdn'];
        $this->mobile = $this->formatMobile($_GET['msisdn']);
        $this->response = $_GET['response'];
        $this->requestString = $_GET['ussdRequestString'];
        $this->table_agent = "fuelagent";
        $this->table_station="fuelstation";
        //$this->session_data= $this->databysessionid($this->transactionId);
        $this->pini = new pinb();
        $this->db = new Cursorb();
        $this->secret = new secret();
        $this->sms = new smsb();
        $this->loanb = new loanb();
        $this->table_boda = "bodauser";
        $this->ussd_session = new ussd_sessionb();
        $this->deposits= new FloatDeposits();
        $this->stationId=$this->Agentfuelstationidbymobile();
        $this->user_session = $this->ussd_session->getByTransactionId($this->transactionId);
    }

    public function process()
    {
        $proceed = $this->validate_request(); //found

        if (!$proceed)
            return;


        if (isset($this->response) && $this->response == 'false') {

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
                        //Activate Fuel using Secret Code
                        $this->capturebodaphone();
                    } else if ($this->requestString == '2') {
                        //Check float Balance 
                        $this->displayfloatbalance();

                    } else if ($this->requestString == '3') {
                       // $this->resetPin();
                      $this->displaystationstatus();
                        // $this->writeResponse('change Pin',true);

                    }
                    else if ($this->requestString == '4') {
                        $this->resetPin();
                        // $this->writeResponse('change Pin',true);

                    }else {
                        $this->welcome_general(true);
                    }
                    break;
                case 'handlephone':
                    $this->storephone();
                    break;
                case 'requestSecret':
                    $this->ActivateFuel();
                    break;
                case 'verifysecret':
                    $this->processfuel();
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

    private function capturebodaphone()
    {
        $menu_text = "Please Enter Boda Phone Number";

        $data['last_usercode'] = 'handlephone';
        $this->ussd_session->update($data, $this->transactionId);
        $this->writeResponse($menu_text);
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
    private function bodabymobile($mobile)
    {
        $result = $this->db->select($this->table_boda, null, ["bodaUserPhoneNumber" => $mobile]);
        return $result;
    }
    private function ActivateFuel()
    {
        $data1 = array();
        $boda = [];

        $session_data = $this->ussd_session->databysessionid($this->transactionId);
        $bodanumber = $session_data[0]["phone"];
        $secret = $this->requestString;
        $boda = $this->bodabymobile($bodanumber);
        // print_r($boda);
        $data['last_usercode'] = 'verifysecret';
        $session_data = [];
        $morto = $boda[0]["bodaUserBodaNumber"];
        $name = $boda[0]["bodaUserName"];
        //data2 is boda name
        $data["data2"] = $name;
        $data["data1"] = $morto;

        //$fuelcode= md5($secret);
        //verify pin
        if (!$this->secret->verifysecret($bodanumber, $secret)) {
            //echo "match";
            $this->secretError();
            return FALSE;
        }
        //check if secret is expired
        //echo "We are here". $this->secret->currentsecretstate($secret);
        if (!$this->secret->currentsecretstate($secret)) {
            // echo "expired";
            $this->secretexpired();
            return FALSE;
        }
        $this->secret->updatesecret($secret, $this->mobile);
        $menu_text = "Fuel of UGX: 15,000 to " . $name . "  " . $morto . " " . "Enter PIN to Comfirm";
        $this->ussd_session->update($data, $this->transactionId);
        $this->writeResponse($menu_text);
    }

    public function updateStationAmoun($amount, $stationId)
    {
         //first get the current amount
        $currentAmount = $this->db->select($this->table_station, ["currentAmount"], ["fuelStationId" => $stationId])[0]["currentAmount"]; 
        $newAmount =  intval($currentAmount)- intval($amount);
        $this->db->update($this->table_station, ["currentAmount" => $newAmount], ["fuelStationId" => $stationId]);
        return true;
    }
    private function processfuel()
    {


        if (!$this->pini->validatePin($this->mobile, $this->requestString, "Agent")) {
            $this->sessionErrorIncorrectPin();
            return;

            //return;
        }
        
        $session_data = $this->ussd_session->databysessionid($this->transactionId);
        $bodanumber = $session_data[0]["phone"];
        $boda = $this->bodabymobile($bodanumber);
        $interest=1000;
        if($boda[0]["bodaUserRole"]=="BODA USER")
        {
            $interest=1000;
        }
        else
        {
            $interest=1000;
        }
        $loan = array();
        $loan["LoanInterest"]=$interest;
        $loan["loanAmount"] = 15000;
        $loan["boadUserId"] = $bodanumber;
        $loan["fuelSationId"] = $boda[0]["fuelStationId"];
        $loan["agentId"] = $this->mobile;
        $loan["stageId"] = $boda[0]["stageId"];
        // $loan["loanRef"] = $this->pini->hashPass($this->pini->randomkey(10)); 
        $loan["loanRef"] =  time().rand(1000,9999); 
        $loanid = $this->loanb->createloan($loan);
        if ($loanid > 0) {
            $menu_text = "Fuel of UGX: 15,000 to " . $boda[0]["bodaUserName"] . "  " . $boda[0]["bodaUserBodaNumber"] . " " . "has been activated";
            $this->sms->sendsms("E-Fuel ", $this->msisdn, "You Have aproved fuel of UGX: 15,000/= for Boda user " . $boda[0]["bodaUserName"] . " with loanId Cb" . $loanid);
            $this->sms->sendsms("E-Fuel ", $this->formatMobileInternational($bodanumber), "Dear customer " . $boda[0]["bodaUserName"] . " we have aprroved your fuel loan of UGX: 15,000 with loanId Cb" . $loanid . "payment of UGX: 16,000 is expected before Midnight Thank you");
            $data['last_usercode'] = 'Fuel';
            $this->ussd_session->update($data, $this->transactionId);
            $result=$this->loanb->updatebodastatus($bodanumber);
              //update fuel station current balance
            $this->updateStationAmoun(15000,$boda[0]["fuelStationId"]);
            $this->writeResponse($menu_text, true);
            return;
        } else {
            $this->sms->sendsms("E-Fuel", $this->msisdn, "Sorry something went wrong and loan was not aproved");
            $menu_text = "Something went wrong and loan was not aprooved";
            $this->writeResponse($menu_text, true);
            return;
        }
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
        if (!isset($this->transactionTime)) {
            $this->writeResponse('Transactiontime not found', true);
            return false;
        }


        // //msisdn
        if (!isset($this->msisdn)) {

            $this->writeResponse('msisdn not found', true);

            return false;
        }

        return true;
    }
    private function resetPin() {
        //$ussd_session = new ussd_session();
        $data['last_usercode'] = 'OldPin';
        //Lets write a response
        $menu_text = "Please Enter Your OldPIN To Proceed";
        $this->writeResponse($menu_text);
        $this->ussd_session->update($data, $this->transactionId);
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
        //update old pin with new Pin
        $res = $this->pini->updatePin($this->mobile, $pin1, "agent");
        if ($res == 1) {

            $this->writeResponse("PIN has been changed successfully\r\n*. Back", true);
            return;
        } else {
            $this->writeResponse("Failed to update user PIN, please contact E-Fuel for help", true);
        }

        //we need to delete the session
        $data['last_usercode'] = 'successresetpin';

        $data['deleted'] = 1;


        $this->ussd_session->update($data, $this->transactionId);
    }

    private function resetPinValidate() {

        //Lets validate the PIN

        $oldpin = $this->requestString;
        if (!$this->pini->validatePin($this->mobile, $this->requestString, "agent")) {
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

    // //method to check if the number is activated
//  
    public function AgentStatus($mobile)
    {
        // $employerId = $this->getEmployerIdByMobile($mobile);

        $db = new Cursor;
        $table = $this->db_table;
        $result = $db->likeSelect($table, ["status"], ["fuelAgentPhoneNumber" => $mobile]);
               if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["status"];
            }
            return $user_session;
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
    private function writeResponse($msg, $isend = false)
    {
        $resp_msg = 'responseString=' . urlencode($msg);
        if ($isend)
            $resp_msg .= '&action=end';
        else
            $resp_msg .= '&action=request';
        echo $resp_msg;
    }

    private function welcome_general($from_activation = true)
    {

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


        $menu_text = "Welcome to E-Fuel\r\n1. Activate Fuel\r\n2. Check Account Balance\r\n3. Check Account Status\r\n4. Change PIN";
        $this->writeResponse($menu_text);
    }

    private function AgentIdbymobile($mobile)
    {
        $result = $this->db->select($this->table_agent, ["fuelAgentId"], ["fuelAgentPhoneNumber" => $mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $agent) {

                $agentId = $agent["fuelAgentId"];
            }
            return $agentId;
        }
    }
    private function Agentfuelstationidbymobile()
    {

        $result = $this->db->select($this->table_agent, ["stationId"], ["fuelAgentPhoneNumber" => $this->mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $agent) {

                $agentstationId = $agent["stationId"];
            }
            return $agentstationId;
        }
    }
    function displaystationstatus()
    {
        $status=$this->getstationstatus();
        $statusmessage=null;
        if($status==null)
        {
            $this->writeResponse("Status unknown -error 000",true);
            return null;
        }
        else
        {
            if($status==0)
            {
                $statusmessage="inactive";
             
            }
            if($status==1)
            {
                $statusmessage="Active";
            }
             if($status==2)
            {
                $statusmessage="switched Off";
            }
              if($status==3)
            {
                $statusmessage="suspended";
            }
            
                    
        }
        $this->writeResponse("Your account is :".$statusmessage, true);
        $data['last_usercode'] = 'displaystationstatus';
            //$data['last_usercode']="send_menu";

      $this->ussd_session->update($data, $this->transactionId);
      return;
    }

    private function getstationstatus()
    {
        $result= $this->db->select($this->table_station,["fuelStationStatus"], ["fuelStationId"=>$this->stationId])[0];
         if (count($result)) 
                {
            return $result["fuelStationStatus"];
                } 
                else {
                    return null;
                }
        
    }

    private function floatbalance()
    {
     
        $totaldeposits=$this->deposits->getAllTimeTotalDepossitsOfstation($this->stationId);
        $totalloans=$this->loanb->getAmountsumofallloansoffuelstaion($this->stationId);
        $balance=$totaldeposits-$totalloans;
        if($balance)
        {
            
            return $balance;
        }
        else
        {
            return 0;
        }
        
    }
private function displayfloatbalance()
{
    
    $this->writeResponse("Account balance is UGX: ".$this->floatbalance(), true);
    
      $data['last_usercode'] = 'displaybalance';
            //$data['last_usercode']="send_menu";

      $this->ussd_session->update($data, $this->transactionId);
      return;
}

    private function sessionErrorIncorrectPin()
    {
        $this->writeResponse("Incorrect PIN, please re-enter your PIN");
    }
    private function sessionError()
    {
        $this->writeResponse('Session error, please restart process', true);
    }
    private function secretError()
    {
        $this->writeResponse('The secretPin you entered does not match', true);
    }
    private function secretexpired()
    {
        $this->writeResponse('The secretPin you enterd is expired', true);
    }
    private function welcome()
    {
        $this->welcome_general(false);
    }
    //put your code here
}

$agent = new Agent();
$agent->process();
//$agent->Agentfuelstationidbymobile("0772093837");
//echo $agent->AgentIdbymobile("0772093837");
//$agent->process();