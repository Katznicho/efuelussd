<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Interswitch
 *
 * @author MAT
 */
include_once 'InterswitchAuth.php';

class Interswitch {

private $auth;
public $ref;
public $paymentcode;
public $customerid;
public $customermobile;
//public $terminalid="3CRP0001";
public $terminalid="3CRP0001";
public $bankCbnCode=100;
//public $bankCbnCode=044;
public $amount;
public $surcharge;
public $transactionRef;
public $customerEmail;
private $refrencepref="CRP";
private $clientId="IKIA1BC2721666C70F3CF8A55BF1DB8157365A4F282C";
//private $clientId="IKIA794905AF56402FB3948B99E0F770AE8B8BFD284E";
private $clientSecret="FhLZHXe0U3c/vC9ioXZL9sGjfVI38gdc3zKB7yrQ4ugfZ86Ays45iOfulMvBYLmf";
//private $clientSecret="ovbg/L/i8+eMrY41x0oz2O9XXpve1zWuzRoCV27jsIwaX+br9BPoMxzvDLV1E9Au";
    public function __construct() {
        $this->auth = new InterswitchAuth();
        $this->ref=$this->refrencepref.rand(1000000, 99999999).chr(rand(97,122));
    }

    private function authenticate($httpmethode,$url,$additionalparams=null) {
        $result = $this->auth->generateInterswitchAuth($httpmethode, $url, $this->clientId, $this->clientSecret,$additionalparams, "sha256");
        return $result;
    }
    private function authenticate2($additionalparams=null) {
        $result = $this->auth->generateInterswitchAuth("POST", "https://services.interswitchug.com/api/v1A/svapayments/sendAdviceRequest", $this->clientId, $this->clientSecret,$additionalparams, "sha256");
        return $result;
    }
    private function authenticate3($additionalparams=null) {
        $result = $this->auth->generateInterswitchAuth("GET", "https://services.interswitchug.com/api/v1A/svapayments/transactions/".$this->ref, "IKIA794905AF56402FB3948B99E0F770AE8B8BFD284E", "ovbg/L/i8+eMrY41x0oz2O9XXpve1zWuzRoCV27jsIwaX+br9BPoMxzvDLV1E9Au",$additionalparams, "sha256");
        return $result;
    }

    public function validatecustomer() {
       $url="https://services.interswitchug.com/api/v1A/svapayments/validateCustomer";
       $httpmethode="POST";
       $auth1 = $this->authenticate($httpmethode,$url);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://services.interswitchug.com/api/v1A/svapayments/validateCustomer",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 50,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $auth1['authorization'],
                "cache-control: no-cache",
                "content-type: application/json",
                "nonce: " . $auth1['nonce'],
                "signature: " . $auth1['signature'],
                "signaturemethod: sha256",
                "terminalid: " . $this->terminalid,
                "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
       // print_r($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $decodedcontent = json_decode($response);
            $data = [];
            if($decodedcontent->{'responseCode'}==90000)
            {
            $data['customerName'] = $decodedcontent->{'customerName'};
            $data['responseCode'] = $decodedcontent->{'responseCode'};
            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
            $data['surcharge'] = $decodedcontent->{'surcharge'};
            $data['balance'] = $decodedcontent->{'balance'};
            $data['isAmountFixed'] = $decodedcontent->{'isAmountFixed'};
            return $data;
            }
            else
            {
            $data['responseCode'] = $decodedcontent->{'responseCode'};
            return $data;
            }
        }
    }
    public function Sendpaymentnotification() {
        $additionalparams=$this->amount.$this->terminalid.$this->ref.$this->customerid.$this->paymentcode;
        //echo $additionalparams;
        $auth1 = $this->authenticate2($additionalparams);
        //echo $auth1['signature'];
        //print_r($auth1);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://services.interswitchug.com/api/v1A/svapayments/sendAdviceRequest",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\",\r\n\"surcharge\":\"$this->surcharge\",\r\n\"transactionRef\":\"$this->transactionRef\",\r\n\"customerEmail\":\"$this->customerEmail\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $auth1['authorization'],
                "cache-control: no-cache",
                "content-type: application/json",
                "nonce: " . $auth1['nonce'],
                "signature: " . $auth1['signature'],
                "signaturemethod: sha256",
                "terminalid: " . $this->terminalid,
                "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
       // echo $response;
        //print_r($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $decodedcontent = json_decode($response);
            $data = [];
            
            $data['responseMessage'] = $decodedcontent->{'responseMessage'};
            $data['responseCode'] = $decodedcontent->{'responseCode'};
            $data['requestReference'] = $decodedcontent->{'requestReference'};
            $data['rechargePIN'] = $decodedcontent->{'rechargePIN'};
            $data['transferCode'] = $decodedcontent->{'transferCode'};
            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
            return $data;
        }
    }
public function TransactionInquiry() {
        //$additionalparams=$this->amount.$this->terminalid.$this->ref.$this->customerid.$this->paymentcode;
        //echo $additionalparams;
        $auth1 = $this->authenticate3(null);
        //echo $auth1['signature'];
        //print_r($auth1);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://services.interswitchug.com/api/v1A/svapayments/transactions/".$this->ref,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\",\r\n\"surcharge\":\"$this->surcharge\",\r\n\"transactionRef\":\"$this->transactionRef\",\r\n\"customerEmail\":\"$this->customerEmail\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $auth1['authorization'],
                "cache-control: no-cache",
                "content-type: application/json",
                "nonce: " . $auth1['nonce'],
                "signature: " . $auth1['signature'],
                "signaturemethod: sha256",
                "terminalid: " . $this->terminalid,
                "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
       // echo $response;
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $decodedcontent = json_decode($response);
            $data = [];
            
//            $data['responseMessage'] = $decodedcontent->{'responseMessage'};
//            $data['responseCode'] = $decodedcontent->{'responseCode'};
//            $data['requestReference'] = $decodedcontent->{'requestReference'};
//            $data['rechargePIN'] = $decodedcontent->{'rechargePIN'};
//            $data['transferCode'] = $decodedcontent->{'transferCode'};
//            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
            return $decodedcontent;
        }
    }
    //https://services.interswitchug.com/api/v1/quickteller/
    public function getbillers() {
       $url="https://services.interswitchug.com/api/v1/quickteller/billers";
       $httpmethode="GET";
       $auth1 = $this->authenticate($httpmethode,$url);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST =>$httpmethode,
           // CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\"\r\n}",
            CURLOPT_HTTPHEADER => array(
            "authorization: " . $auth1['authorization'],
            "cache-control: no-cache",
            "content-type: application/json",
            "nonce: " . $auth1['nonce'],
            "signature: " . $auth1['signature'],
            "signaturemethod: sha256",
            "terminalid: " . $this->terminalid,
            "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
        print_r($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
//            $decodedcontent = json_decode($response);
//            $data = [];
//            $data['customerName'] = $decodedcontent->{'customerName'};
//            $data['responseCode'] = $decodedcontent->{'responseCode'};
//            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
//            $data['surcharge'] = $decodedcontent->{'surcharge'};
//            $data['balance'] = $decodedcontent->{'balance'};
//            $data['isAmountFixed'] = $decodedcontent->{'isAmountFixed'};
//            return $data;
        }
    }
     public function getbalance() {
       $url="https://services.interswitchug.com/api/v1A/svapayments/terminal/balance/1/3CRP0001";
       $httpmethode="GET";
       $auth1 = $this->authenticate($httpmethode,$url);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST =>$httpmethode,
           // CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\"\r\n}",
            CURLOPT_HTTPHEADER => array(
            "authorization: " . $auth1['authorization'],
            "cache-control: no-cache",
            "content-type: application/json",
            "nonce: " . $auth1['nonce'],
            "signature: " . $auth1['signature'],
            "signaturemethod: sha256",
            "terminalid: " . $this->terminalid,
            "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
        print_r($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
//            $decodedcontent = json_decode($response);
//            $data = [];
//            $data['customerName'] = $decodedcontent->{'customerName'};
//            $data['responseCode'] = $decodedcontent->{'responseCode'};
//            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
//            $data['surcharge'] = $decodedcontent->{'surcharge'};
//            $data['balance'] = $decodedcontent->{'balance'};
//            $data['isAmountFixed'] = $decodedcontent->{'isAmountFixed'};
//            return $data;
        }
    }
    public function getbillerItems() {
       $url="https://services.interswitchug.com/api/v1/quickteller/billers/249/paymentitems";
       $httpmethode="GET";
       $auth1 = $this->authenticate($httpmethode,$url);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_CUSTOMREQUEST =>$httpmethode,
            //CURLOPT_POSTFIELDS => "\r\n{\r\n\"requestReference\":\"$this->ref\",\r\n\"paymentCode\":\"$this->paymentcode\",\r\n\"customerId\":\"$this->customerid\",\r\n\"customerMobile\":\"$this->customermobile\",\r\n\"terminalId\":\"$this->terminalid\",\r\n\"bankCbnCode\":\"$this->bankCbnCode\",\r\n\"amount\":\"$this->amount\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $auth1['authorization'],
                "cache-control: no-cache",
                "content-type: application/json",
                "nonce: " . $auth1['nonce'],
                "signature: " . $auth1['signature'],
                "signaturemethod: sha256",
                "terminalid: " . $this->terminalid,
                "timestamp: " . $auth1['timestamp']
            ),
        ));
        $response = curl_exec($curl);
        print_r($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
//            $decodedcontent = json_decode($response);
//            $data = [];
//            $data['customerName'] = $decodedcontent->{'customerName'};
//            $data['responseCode'] = $decodedcontent->{'responseCode'};
//            $data['transactionRef'] = $decodedcontent->{'transactionRef'};
//            $data['surcharge'] = $decodedcontent->{'surcharge'};
//            $data['balance'] = $decodedcontent->{'balance'};
//            $data['isAmountFixed'] = $decodedcontent->{'isAmountFixed'};
//            return $data;
        }
    }
    


}

//$interswitch = new Interswitch();
//$result=$interswitch->getbillers();
//////////
//$result=$interswitch->getbillerItems();
//print_r($result);
//
//$interswitch->amount=450000;
////$interswitch->bankCbnCode="044";
//$interswitch->customerid="04258349663";
//$interswitch->customermobile="0772093837";
//$interswitch->paymentcode="4372347";
//$interswitch->ref=$interswitch->transactionRef."00010980";
//$interswitch->customerEmail="anishinani@gmail.com";
//$result=$interswitch->validatecustomer();
////print_r($result);
////$interswitch->terminalid="3MCS0001";
////$result = $interswitch->validatecustomer();
//$interswitch->transactionRef=$result["transactionRef"];
//$interswitch->surcharge=$result["surcharge"];
//////
//$result1=$interswitch->Sendpaymentnotification();
////$result2=$interswitch->TransactionInquiry();
//
//
////echo $result['customerName'];
//print_r($result1);