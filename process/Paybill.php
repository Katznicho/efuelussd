<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paybill uf
 * this class is to handle all functionality related to paying bills 
 * for clients including utilities and airtime
 * its inspired by interswitch APi
 * 
 * @author anishinani@gmail.com
 */

include_once("ussd_session.php");
include_once("Interswitch.php");
include_once("pini.php");
include_once("PaybillCommision.php");

class Paybill {

    private $ussd_session;
    private $requestString;
    private $transactionId;
    private $interswitch;
    private $msisdn;
    private $pini;
    private $employeei;
    private $infobip;
    private $table_paybill="paybill";
    private $db;
    private $commision;

    public function __construct() {
        $this->ussd_session = new ussd_session();
        $this->interswitch = new Interswitch();
        $this->pini = new pin();
        $this->employeei = new Employee();
        $this->infobip = new infobip();
        $this->requestString = $_GET['ussdRequestString'];
        $this->transactionId = $_GET['transactionId'];
        $this->msisdn = $_GET['msisdn'];
        $this->db=new Cursor();
        $this->commision=new PaybillCommision();
    }

  
    public function displaybills() {
        $menu_id = "list_bills";

        $menu_text = "Payments \r\n1) Utilities \r\n2) Pay Tv \r\n3) Airtime";
        $this->ussd_session->writeResponse($menu_text);
        $data['last_usercode'] = $menu_id;
        $this->ussd_session->update($data, $this->transactionId);
    }

    public function paybillgateway() {
        $menu_from = 'list_bills';
        //$menu_id='paybillgateway';
        if ($this->requestString == '1') {
            //display menue for utilities
            $menu_id = 'utility_menu';
            $menu_text = "Utilities \r\n1) UMEME touch Pay \r\n2) NWSC";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
        } elseif ($this->requestString == '2') {
            //display menue for pay tv starttimes and multichoice
            $menu_id1 = 'TV_menu';
            $menu_text = "Pay TV \r\n1) Dstv & GOtv \r\n2) StartTimes TV";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id1;
            $this->ussd_session->update($data, $this->transactionId);
        } elseif ($this->requestString == '3') {
            /// prompt for phonenumber 
            $menu_id2 = 'Airtime_menu';
            $menu_text = "Enter Phone Number";
            $data['last_usercode'] = $menu_id2;
            $this->ussd_session->update($data, $this->transactionId);
            $this->ussd_session->writeResponse($menu_text);
        }
    }
    
    public function EnterDstvcardnumber() {
        $menu_from = 'Tvmenu';
        
        $data['paymentcode'] =$this->dsTvPackages($this->requestString);
        $data['amount']= $this->dsTvPackagesAmount($this->requestString);
        $menu_id = 'cardnumber';
        $data['last_usercode'] = $menu_id;
        $this->ussd_session->update($data, $this->transactionId);
        $menu_text = "Enter cardnumber";
        $this->ussd_session->writeResponse($menu_text);
    }
    public function EnterGotvcardnumber() {
        $menu_from = 'Tvmenu';
        
        $data['paymentcode'] =$this->goTvPackages($this->requestString);
        $data['amount']= $this->goTvPackagesAmount($this->requestString);
        $menu_id = 'cardnumber';
        $data['last_usercode'] = $menu_id;
        $this->ussd_session->update($data, $this->transactionId);
        $menu_text = "Enter cardnumber";
        $this->ussd_session->writeResponse($menu_text);
    }
    
    public function EnterAirtimeAmount() {
            $menu_from = 'Airtime_menu';
            $data['customerid'] = $this->formatMobile($this->requestString);
            $menu_id = 'Airtime_amount';
            $data['last_usercode'] = $menu_id;
            $data['paymentcode'] = $this->checkNetworktocode($this->requestString);
            $this->ussd_session->update($data, $this->transactionId);
            $menu_text = "Enter Airtime Amount";
            $this->ussd_session->writeResponse($menu_text);
        }
        
    public function utilitygateway() {
        $menu_from = 'utility_menu';
        if ($this->requestString == '1') {
            //menu for umeme
            $menu_id = 'UMEME_menu';
            $menu_text = "UMEME Touch Pay \r\n1) Pay Bill \r\n2) Yaka";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
        }
        if ($this->requestString == '2') {
            //menu for umeme
            $menu_id = 'NWSC_menu';
            $menu_text = "Pay for NWSC \r\n1) NWSC Entebbe \r\n2) NWSC Iganga \r\n3) NWSC Kajjansi \r\n4) NWSC Jinja \r\n5) NWSC Kampala \r\n6) NWSC Kawuku \r\n7) NWSC Mukono \r\n8) Other NWSC Areas";
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
            $this->ussd_session->writeResponse($menu_text);
        }
    }
    public function EnterNWSCMeter() {
            $menu_from = 'NWSC_menu';
           // $data['customerid'] = $this->formatMobile($this->requestString);
            $menu_id = 'NWSC_Meter';
            $data['last_usercode'] = $menu_id;
            $data['paymentcode'] = $this->NWSCPackages($this->requestString);
            $this->ussd_session->update($data, $this->transactionId);
            $menu_text = "Enter Customer No";
            $this->ussd_session->writeResponse($menu_text);
        }
public function EnterNWSCAmount() {
            $menu_from = 'NWSC_Meter';
            $data['customerid'] = $this->requestString;
            $menu_id = 'NWSC_AMOUNT';
            $data['last_usercode'] = $menu_id;
           // $data['paymentcode'] = $this->checkNetworktocode($this->requestString);
            $this->ussd_session->update($data, $this->transactionId);
            $menu_text = "Enter  Amount";
            $this->ussd_session->writeResponse($menu_text);
        }
    public function UMEME_gateway() {
        $menu_from = 'UMEME_menu';
        //Test if its bill payment or yaka payemnt
        if ($this->requestString == '1') {
            $menu_id = 'Umeme_meter_menu';
            $menu_text = "Enter Account Number";
            $data['last_usercode'] = $menu_id;
            $data['paymentcode'] = "4372346";
            $this->ussd_session->update($data, $this->transactionId);
            $this->ussd_session->writeResponse($menu_text);
        } elseif ($this->requestString == '2') {
            $menu_id = 'Umeme_meter_menu';
            $menu_text = "Enter Account Number";
            $data['last_usercode'] = $menu_id;
            $data['paymentcode'] = "4372347";
            $this->ussd_session->update($data, $this->transactionId);
            $this->ussd_session->writeResponse($menu_text);
        }
    }

    public function EnterUmemeAmount() {
        $menu_from = 'Umeme_meter_menu';
        $data['customerid'] = $this->requestString;
        $menu_id = 'Umeme_amount';
        $data['last_usercode'] = $menu_id;
        $this->ussd_session->update($data, $this->transactionId);
        $menu_text = "Enter Amount";
        $this->ussd_session->writeResponse($menu_text);
    }
    public function validate_NWSCCustomer() {
        //$menu_from = 'Umeme_amount';
        $amount = $this->requestString;
        // $amount= 800000;
        $charge=$this->commision->nwsccharges($amount);
        $this->interswitch->amount = ($amount + $charge) * 100;
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $this->interswitch->customerid = $session_data[0]["customerid"];
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $result = $this->interswitch->validatecustomer();
        if($result["responseCode"]!=90000)
        {
           $menu_text = "Request Failed With response" . $result['responseCode'];
           $this->ussd_session->writeResponse($menu_text, true);
           return;
        }
        $responsecode = $result["responseCode"];
        $surcharge = $result["surcharge"];
        $menu_id = 'verify_customer';
        $data['last_usercode'] = $menu_id;
        $data['ref'] = $this->interswitch->ref;
       $data['amount'] = $amount + $charge;
        $data['transactionRef'] = $result["transactionRef"];
        $data['surcharge'] = $result["surcharge"];
        
        $this->ussd_session->update($data, $this->transactionId);
        
        $menu_text = "Enter Pin to confirm \r\nPayment of Bill at a charge of UGX ".$charge." For ". $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
       
        
    }
    public function validate_AirtimeCustomer() {
        //$menu_from = 'Umeme_amount';
        $amount = $this->requestString;
        // $amount= 800000;
        $this->interswitch->amount = $amount * 100;
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $this->interswitch->customerid = $session_data[0]["customerid"];
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $result = $this->interswitch->validatecustomer();
        if($result["responseCode"]!=90000)
        {
           $menu_text = "Request Failed With response" . $result['responseCode'];
           $this->ussd_session->writeResponse($menu_text, true);
           return;
        }
        $responsecode = $result["responseCode"];
        $surcharge = $result["surcharge"];
        $menu_id = 'verify_customer';
        $data['last_usercode'] = $menu_id;
        $data['ref'] = $this->interswitch->ref;
        $data['amount'] = $amount;
        $data['transactionRef'] = $result["transactionRef"];
        $data['surcharge'] = $result["surcharge"];
        
        $this->ussd_session->update($data, $this->transactionId);
        
        $menu_text = "Enter Pin to confirm \r\nPurchase of Airtime \r\n" . $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
       
        
    }
    public function validate_StarTimesCustomer() {
        //$menu_from = 'Umeme_amount';
        $amount = $this->requestString;
        // $amount= 800000;
        $this->interswitch->amount = $amount * 100;
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $this->interswitch->customerid = $session_data[0]["customerid"];
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $result = $this->interswitch->validatecustomer();
        if($result["responseCode"]!=90000)
        {
           $menu_text = "Request Failed With response" . $result['responseCode'];
           $this->ussd_session->writeResponse($menu_text, true);
           return;
        }
        $responsecode = $result["responseCode"];
        $surcharge = $result["surcharge"];
        $menu_id = 'verify_customer';
        $data['last_usercode'] = $menu_id;
        $data['ref'] = $this->interswitch->ref;
        $data['amount'] = $amount;
        $data['transactionRef'] = $result["transactionRef"];
        $data['surcharge'] = $result["surcharge"];
        
        $this->ussd_session->update($data, $this->transactionId);
        
        $menu_text = "Enter Pin to confirm \r\n Purchase of startimes \r\n" . $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
       
        
    }
    public function validate_TVCustomer() {
        //$menu_from = 'Umeme_amount';
        $customerId = $this->requestString;
        // $amount= 800000;
        
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $this->interswitch->amount = $session_data[0]["amount"] * 100;
        $this->interswitch->customerid = $customerId;
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $result = $this->interswitch->validatecustomer();
        if($result["responseCode"]!=90000)
        {
           $menu_text = "Request Failed With response" . $result['responseCode'];
           $this->ussd_session->writeResponse($menu_text, true);
           return;
        }
        $responsecode = $result["responseCode"];
        $surcharge = $result["surcharge"];
        $menu_id = 'verify_customer';
        $data['last_usercode'] = $menu_id;
        $data['ref'] = $this->interswitch->ref;
        $data['customerid'] = $customerId;
        $data['transactionRef'] = $result["transactionRef"];
        $data['surcharge'] = $result["surcharge"];
        
        $this->ussd_session->update($data, $this->transactionId);
        
        $menu_text = "Enter Pin to confirm \r\nTv bill Payment \r\n" . $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
        
    }

    public function validate_UmemeCustomer() {
        //$menu_from = 'Umeme_amount';
        $amount = $this->requestString;
        // $amount= 800000;
        $charge=$this->commision->umemecharges($amount);
        $this->interswitch->amount = ($amount + $charge) * 100;
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $this->interswitch->customerid = $session_data[0]["customerid"];
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $result = $this->interswitch->validatecustomer();
        if($result["responseCode"]!=90000)
        {
           $menu_text = "Request Failed With response" . $result['responseCode'];
           $this->ussd_session->writeResponse($menu_text, true);
           return;
        }
        $responsecode = $result["responseCode"];
        $surcharge = $result["surcharge"];
        $menu_id = 'verify_customer';
        $data['last_usercode'] = $menu_id;
        $data['ref'] = $this->interswitch->ref;
        $data['amount'] = $amount + $charge;
        $data['transactionRef'] = $result["transactionRef"];
        $data['surcharge'] = $result["surcharge"];
        
        $data['charge']= $charge;
        
        $this->ussd_session->update($data, $this->transactionId);
        if($this->interswitch->paymentcode=="4372347")
        {
        $menu_text = "Enter Pin to confirm \r\nPurchase of Yaka for a charge of UGX ".$charge."\r\n ". $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
        }
        else
        {
        $menu_text = "Enter Pin to confirm \r\nPayment of Umeme Bill for a charge of UGX ".$charge."\r\n" . $result['customerName'];
        $this->ussd_session->writeResponse($menu_text);
        }
        
    }

    public function TV_gateway() {
        $menu_from = 'TV_menu';
        //Test if its bill payment or yaka payemnt
        if ($this->requestString == '1') {
            $menu_id = 'DStv_GOtv_menu';
            $menu_text = "DStv & Gotv \r\n1) Pay DStv \r\n2) Pay GOtv";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
        } elseif ($this->requestString == '2') {
            $menu_id = 'StarTimes_meter';
            $menu_text = "Enter Account Number";
            $data['paymentcode'] = "21340";
            $data['last_usercode'] = $menu_id;
             $this->ussd_session->update($data, $this->transactionId);
            $this->ussd_session->writeResponse($menu_text);
        }
    }
public function EnterStarTimesAmount() {
        $menu_from = 'StarTimes_meter';
        $data['customerid'] = $this->requestString;
        $menu_id = 'StarTimes_amount';
        $data['last_usercode'] = $menu_id;
        $this->ussd_session->update($data, $this->transactionId);
        $menu_text = "Enter Amount";
        $this->ussd_session->writeResponse($menu_text);
    }
    public function dstv_gotv_gateway() {
        $menu_from = 'DStv_GOtv_menu';
        if ($this->requestString == '1') {
            $menu_id = 'DStv_menu';
            $menu_text = "Pay DStv \r\n1) DStv Access-33,000/= \r\n2) DStv Family-49,000/= \r\n3) DStv Compact-79,000/= \r\n4) DStv Premium-219,000/=";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
        } elseif ($this->requestString == '2') {
            $menu_id = 'GOtv_menu';
            $menu_text = "Pay GOtv \r\n1) GOTV Lite-11000 \r\n2) GOtv Value-16000 \r\n3) Gotv Max Bouquet-39000 \r\n4) GOtv Plus-26000";
            $this->ussd_session->writeResponse($menu_text);
            $data['last_usercode'] = $menu_id;
            $this->ussd_session->update($data, $this->transactionId);
        }
    }

    public function getAdvanceProcess() {
        //Lets validate the PIN
        $pin3 = $this->requestString;
        $mobile = $this->formatMobile($this->msisdn);
        if ($this->pini->validatePin($mobile, $pin3) == 0) {
            // $this->writeResponse("The Pin You entered is wrong",true);
            $this->sessionErrorIncorrectPin();
            return;
        }

        //Lets process the payment
        $result = $this->ussd_session->getalldata($this->transactionId);
        $amount=$result[0]["amount"];
//         $this->ussd_session->writeResponse("ur successfull");
        $this->processPayment($amount);
      
        
    }

//    public function paybill($data = []) {
//      
//    }

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
private function sessionErrorIncorrectPin() {
         $this->ussd_session->writeResponse("Incorrect PIN, please re-enter your PIN");
    }
    private function toPay($amount){
        return $amount*100;
    }
    
    private function processPayment($amount) {
        
        $mobile = $this->formatMobile($this->msisdn);

       
        $employer = $this->employeei->getEmployerIdByMobile($mobile);
        //$this->writeResponse($employer,true);
        if ($employer == null) {
            $this->ussd_session->writeResponse("Employer not found, please contact CreditPlus for help", true);
            return;
        }
        //Lets check the status of the employer before proceeding
        /**
         * Employer Status
         * 0	-	Normal
         * 1	-	Processing payments
         * 2	-	Suspended
         * 3    -       Long Term Loan  
         */
        switch ($this->employeei->employerStatus($mobile)) {
            case 1:
                $this->ussd_session->writeResponse("Employer is still processing pending payements", true);
                return;
            case 2:
                $this->ussd_session->writeResponse("Employer is suspended from Credit Plus platform", true);
                return;

            case 4:
                $this->ussd_session->writeResponse("Employer is suspended from Credit Plus platform", true);
                return FALSE;
        }

        //Lets get the platform settings
        $salary = $this->employeei->getSalaryByMobile($mobile);
        $current_bal = 0;
        $current_bal = $this->employeei->getTotalBorrowed($mobile);
        if (($amount + $current_bal) >= $salary) {
            $this->ussd_session->writeResponse("Cannot process payment because current balance would be greater than salary".$amount, true);
            return;
        }

        $advance_limit;

        $advance_limit = $this->employeei->advanceLimitAmount($mobile);
        $data = [];
        $data2 = [];

        if ($amount > $advance_limit) {
            $this->ussd_session->writeResponse("Requested advance is greater than advance limit of UGX" . number_format($advance_limit), true);
            return;
        }

        if (($amount + $current_bal) > $advance_limit) {
            $rec_amount = $advance_limit - $current_bal;
            $this->ussd_session->writeResponse("Requested advance is greater than advance limit, recommended amount is UGX" . number_format($rec_amount), true);
            return;
        }
        //$service_amount = ($amount + $service_fee);

        $network_fee = 0;
        $session_data = $this->ussd_session->getalldata($this->transactionId);
        $data['amount_borrowed'] = $amount;
        $data['employee_id'] = $this->employeei->getIdByMobile($mobile);
        $data['employer_id'] = $this->employeei->getEmployerIdByMobile($mobile);
        $data['send_charge'] = $network_fee;
        $data['withdraw_charge'] = 0;
        $data['service_fee'] = 0;
        $data['commission'] = $this->commision->getBillerCommission($session_data[0]["paymentcode"], $session_data[0]["amount"]);

        //Lets process the payment
        $to = $this->formatMobileInternational256($this->msisdn);
        
        $this->interswitch->amount=$session_data[0]["amount"]*100;
        $this->interswitch->customerid = $session_data[0]["customerid"];
        $this->interswitch->customerEmail = "anishinani@gmail.com";
        $this->interswitch->paymentcode =$session_data[0]["paymentcode"];
        //$this->interswitch->customermobile= $this->formatMobile($this->msisdn);
        $this->interswitch->customermobile = $session_data[0]["msisdn"];
        $this->interswitch->transactionRef=$session_data[0]["transactionRef"];
        $this->interswitch->ref=$session_data[0]["ref"];
        $this->interswitch->surcharge=$session_data[0]["surcharge"];
        $this->interswitch->customermobile=$this->formatMobile($this->msisdn);
       
        $code=$this->interswitch->Sendpaymentnotification();
        
        //$code = $yo->withdraw($yo_amount, $to, $narrative = "ADVANCE FROM CREDITPLUS", "ADVANCE FROM CREDITPLUS");
         $paybill_bill=[];
         $paybill_bill["paymentCode"]=$this->interswitch->paymentcode;
         $paybill_bill["amount"]=$session_data[0]["amount"];
         $paybill_bill["transactionRef"]=$session_data[0]["transactionRef"];
         $paybill_bill["clientMobile"]=$session_data[0]["msisdn"];
         $paybill_bill["Empid"]=$this->employeei->getIdByMobile($mobile);
         $paybill_bill["EmployerId"]=$this->employeei->getEmployerIdByMobile($mobile);
         $paybill_bill["Transactionstataus"]=$code["responseCode"];
         $paybill_bill["clientId"]=$this->interswitch->customerid;
         $paybill_bill["responseMessage"]=$code["responseMessage"];
         
         $cod = $code["responseCode"];
        //$cod=0;
        $data['transaction_id'] = $code["transferCode"];
        //$data['responseMessage']=$code["responseMessage"];
        //$data['transaction_id'] = $cod;
      
        //we need to analyse this code incase it shows an error

        $data['transaction_code'] = $cod;
        $proceed = false;
        if ($cod == 9000)
            $proceed = true;
        else {
            
                $this->ussd_session->writeResponse("Payment gateway cannot process request (error: $cod)", true);
                return;
            }
      
        //Lets save the record

        $advance_id = $this->employeei->insert($data);
        $paybill_bill["loanId"]=$advance_id;
        $bill=$this->db->insert($this->table_paybill, $paybill_bill);
        $update = $this->employeei->update($data2, $mobile);

        $to = $this->formatMobileInternational($this->msisdn);
        $msg = "Dear " . $this->employeei->getEmployeeName($mobile) . ", your advance of UGX " . number_format($amount) . " has been processed with id " . $advance_id;
        $from ="CreditPlus";
        $message = $this->infobip->sendsms($from, $to, $msg);

        //Lets write the end response            
        $this->ussd_session->writeResponse("Advance has been processed successfully", true);

        //we need to delete the session
        $this->ussd_session->delete($this->transactionId);
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

    
     private function checkNetworktocode($mobile) {
        $network = "none";
        $m = $this->formatMobile($mobile);
        $prefix = substr($m, 0, 3);

        if ($prefix == "075" || $prefix == "070") //check for airtel
            $network = "238181";
        else if ($prefix == "077" || $prefix == "078") //check for mtn
            $network = "238180";
        else if ($prefix == "079") //check for africell
            $network = "238226";
        else if ($prefix == "071")//check for utl
            $network = "238182";

        return $network;
    }
    private function dsTvPackages($prefix) {
        $network = "none";
//        $m = $this->formatMobile($mobile);
//        $prefix = substr($m, 0, 3);

        if ($prefix == "1") //check for airtel
            $network = "21552";
        else if ($prefix == "2") //check for mtn
            $network = "21551";
        else if ($prefix == "3") //check for africell
            $network = "21549";
        else if ($prefix == "4")//check for utl
            $network = "21548";

        return $network;
    }
    private function NWSCPackages($prefix) {
        $network = "none";
//        $m = $this->formatMobile($mobile);
//        $prefix = substr($m, 0, 3);

        if ($prefix == "1") //check for airtel
            $network = "249375";
        else if ($prefix == "2") //check for mtn
            $network = "249372";
        else if ($prefix == "3") //check for africell
            $network = "249374";
        else if ($prefix == "4")//check for utl
            $network = "249373";
        else if ($prefix == "5")//check for utl
            $network = "249371";
        else if ($prefix == "6")//check for utl
            $network = "249376";
        else if ($prefix == "7")//check for utl
            $network = "249378";
        else if ($prefix == "8")//check for utl
            $network = "249379";

        return $network;
    }
    private function dsTvPackagesAmount($prefix) {
        $network = "none";
//        $m = $this->formatMobile($mobile);
//        $prefix = substr($m, 0, 3);

        if ($prefix == "1") //check for airtel
            $network = 33000;
        else if ($prefix == "2") //check for mtn
            $network = 49000;
        else if ($prefix == "3") //check for africell
            $network = 79000;
        else if ($prefix == "4")//check for utl
            $network = 219000;

        return $network;
    }
    private function goTvPackages($prefix) {
        $network = "none";
//        $m = $this->formatMobile($mobile);
//        $prefix = substr($m, 0, 3);

        if ($prefix == "1") //check for airtel
            $network = "21546";
        else if ($prefix == "2") //check for mtn
            $network = "21554";
        else if ($prefix == "3") //check for africell
            $network = "215612";
        else if ($prefix == "4")//check for utl
            $network = "21555";

        return $network;
    }
    private function goTvPackagesAmount($prefix) {
        $network = "none";
//        $m = $this->formatMobile($mobile);
//        $prefix = substr($m, 0, 3);

        if ($prefix == "1") //check for airtel
            $network = 11000;
        else if ($prefix == "2") //check for mtn
            $network = 16000;
        else if ($prefix == "3") //check for africell
            $network = 39000;
        else if ($prefix == "4")//check for utl
            $network = 26000;

        return $network;
    }
    
}

//$paybill= new Paybill();
//$result =$paybill->validate_yaka_meter();
