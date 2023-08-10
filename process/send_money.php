<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of send_money
 *
 * @author MAT
 */

include_once("cursorweb.php");
class send_money {
    
    private $table="send_transactions";
    private $db;
    public function __construct() {
        $this->db=new Cursor();
    }

        public function save_send_transactions($sender, $receiver, $amount, $charge, $loanid,$sender_empId)
    {
        $data=[];
        $data["sender_phone"]=$sender;
        $data["sender_empId"]=$sender_empId;
        $data["receiver_phone"]=$receiver;
        $data["amount"]=$amount;
        $data["charge"]=$charge;	
        $data["loanid"]=$loanid;
       // print_r($data);
        $result= $this->db->insert($this->table, $data);
        return $result;
        
    }
    
    
    
       public function send_xml_request($xml_request)
        {
			try
			{
				$ch = curl_init(); //initiate the curl session 

				curl_setopt($ch, CURLOPT_URL, "https://paymentsdev1.yo.co.ug/yopaytest/task.php"); //set to API endpoint
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // tell curl to return data in a variable 
				curl_setopt($ch, CURLOPT_HEADER, false); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: ".strlen($xml_request))); 
				curl_setopt($ch, CURLOPT_POST, true); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request); // post the xml 
				curl_setopt($ch, CURLOPT_TIMEOUT, (int)30); // set timeout in seconds 

				$xml_response = curl_exec($ch);

				if($xml_response === FALSE)
					$this->status_message = 'There was an error connecting to Yo! Payments. Please try again.';//show_error('There was an error connecting to Yo! Payments. Please try again.');

				curl_close ($ch); 
				
				// Get Status
				// $status_code = $this->get_status($xml_response);
				
				return $xml_response;
			}catch(Exception $e) {
				$this->status_message = $e->getMessage();
			}
        }
         public function nameverification($phone)
        {
            $xml_request='<?xml version="1.0" encoding="UTF-8"?>';
            $xml_request=$xml_request.'<AutoCreate>';
            $xml_request=$xml_request.'<Request>';
            $xml_request=$xml_request.'<APIUsername>1152817</APIUsername>';
            $xml_request=$xml_request.'<APIPassword>RtLL-9M6W-5S2v-jBgw-hLgz-en95-3B8g-sdVU</APIPassword>';
            $xml_request=$xml_request.'<Method>acgetmsisdnkycinfo</Method>';
       
            $xml_request=$xml_request.'<Msisdn>'.$phone.'</Msisdn>';
         
            $xml_request=$xml_request.'</Request>';
            $xml_request=$xml_request.'</AutoCreate>';
            
           $xml_response=$this->send_xml_request($xml_request);
           
           $simpleXMLObject =  new SimpleXMLElement($xml_response);
        $response = $simpleXMLObject->Response;
        $result = array();
        $result['Status'] = (string) $response->Status;
        $result['StatusCode'] = (string) $response->StatusCode;
        if (!empty($response->StatusMessage)) {
            $result['StatusMessage'] = (string) $response->StatusMessage;
        }
        if (!empty($response->AccountInformation->PersonalInformation->Names->FirstName)) {
            $result['FirstName'] = (string) $response->AccountInformation->PersonalInformation->Names->FirstName;
        }
        if (!empty($response->AccountInformation->PersonalInformation->Names->MiddleName)) {
            $result['MiddleName'] = (string) $response->AccountInformation->PersonalInformation->Names->MiddleName;
        }
        if (!empty($response->AccountInformation->PersonalInformation->Names->Surname)) {
            $result['Surname'] = (string) $response->AccountInformation->PersonalInformation->Names->Surname;
        }
        
        $name=$result["Surname"]." ".$result["FirstName"];
       
        return $name;

        }
    private function formatMobile($mobile){
		$length = strlen($mobile);
		$m = '0';
		//format 1: +256752665888
		if($length == 13)
			return $m .= substr($mobile, 4);
		elseif($length == 12)
			return $m .= substr($mobile, 3);
		elseif($length == 9)
			return $m .= $mobile;
			
		return $mobile;
	}
    private function checkNetwork($mobile)
	{
		$network = "none";
		$m = $this->formatMobile($mobile);
		$prefix = substr($m,0,3);
		
		if($prefix == "075" || $prefix == "070") //check for airtel
			$network = "airtel";
		else if($prefix == "077" || $prefix == "078") //check for mtn
			$network = "mtn";
		else if($prefix == "079") //check for africell
			$network = "africell";
		else if($prefix == "071")//check for utl
			$network = "utl";
		
		return $network;
	}
     public function router($sender,$receiver,$amount)
    {
        $sender=$this->checkNetwork($sender);
        $receiver=$this->checkNetwork($receiver);
        if($sender=="mtn")
        {
            if($receiver=="mtn")
            {
               $result=$this->mtn_mtn_charges($amount);
               return $result;
            }
            else
            {
                $result=$this->mtn_other_charges($amount);
                 return $result;
            }
            
        }
        else if ($sender=="airtel")
        {
            if($receiver=="airtel")
            {
               $result=$this->airtel_airtel_charges($amount); 
                return $result;
            }
            else
            {
                $result=$this->airtel_other_charges($amount);
                 return $result;
            }
        }
       
    }
    public function mtn_mtn_charges($amount)
    {
        //echo $amount;
        if($amount<=2500)
        {
            return 30;
        }
        else if($amount<=5000)
        {
            return 90;
        }
        else if($amount<=15000)
        {
            return 300;
        }
        else if($amount<=30000)
        {
            return 360;
        }
        else if($amount<=45000)
        {
            return 420;
        }
        else if($amount<=60000)
        {
            return 510;
        }
        else if($amount<=125000)
        {
            return 600;
        }
        else if($amount<=250000)
        {
            
            return 690;
        }
        else if($amount<=500000)
        {
            return 780;
        }
        else if($amount<=1000000)
        {
            return 900;
        }
         else if($amount<=2000000)
        {
            return 900;
        }
         else if($amount<=4000000)
        {
            return 900;
        }
         else if($amount<=7000000)
        {
            return 900;
        }
        else
        {
            
            return NULL;
        }
        //return true;
    }
    public function mtn_other_charges($amount)
    {
        if($amount<=2500)
        {
            return 180;
        }
        else if($amount<=5000)
        {
            return 360;
        }
        else if($amount<=15000)
        {
            return 720;
        }
        else if($amount<=30000)
        {
            return 900;
        }
        else if($amount<=45000)
        {
            return 900;
        }
        else if($amount<=60000)
        {
            return 900;
        }
        else if($amount<=125000)
        {
            return 1500;
        }
        else if($amount<=250000)
        {
            
            return 2100;
        }
        else if($amount<=500000)
        {
            return 4200;
        }
        else if($amount<=1000000)
        {
            return 6000;
        }
         else if($amount<=2000000)
        {
            return 10200;
        }
         else if($amount<=4000000)
        {
            return 16200;
        }
         else if($amount<=7000000)
        {
            return 24000;
        }
        else
        {
            
            return NULL;
        }
        //return true;
    }
    public function airtel_airtel_charges($amount)
    {
       if($amount<=2500)
        {
            return 60;
        }
        else if($amount<=5000)
        {
            return 60;
        }
        else if($amount<=15000)
        {
            return 300;
        }
        else if($amount<=30000)
        {
            return 300;
        }
        else if($amount<=45000)
        {
            return 300;
        }
        else if($amount<=60000)
        {
            return 300;
        }
        else if($amount<=125000)
        {
            return 600;
        }
        else if($amount<=250000)
        {
            
            return 600;
        }
        else if($amount<=500000)
        {
            return 600;
        }
        else if($amount<=1000000)
        {
            return 600;
        }
         else if($amount<=2000000)
        {
            return 600;
        }
         else if($amount<=4000000)
        {
            return 600;
        }
         else if($amount<=7000000)
        {
            return 600;
        }
        else
        {
            
            return NULL;
        }
    }
    public function airtel_other_charges($amount)
    {
       if($amount<=2500)
        {
            return 588;
        }
        else if($amount<=5000)
        {
            return 588;
        }
        else if($amount<=15000)
        {
            return 1185;
        }
        else if($amount<=30000)
        {
            return 1299;
        }
        else if($amount<=45000)
        {
            return 1557;
        }
        else if($amount<=60000)
        {
            return 1557;
        }
        else if($amount<=125000)
        {
            return 2259;
        }
        else if($amount<=250000)
        {
            
            return 3297;
        }
        else if($amount<=500000)
        {
            return 4815;
        }
        else if($amount<=1000000)
        {
            return 9090;
        }
         else if($amount<=2000000)
        {
            return 15900;
        }
         else if($amount<=4000000)
        {
            return 27240;
        }
         else if($amount<=7000000)
        {
            return 36420;
        }
        else
        {
            
            return NULL;
        }
    }
    //put your code here
}

//$sender = new send_money();
//echo $sender->save_send_transactions("0772093938", "0702813986", 100000, 2000.00, 10, 12);
//echo $response=$sender->nameverification(256772093837);

//$sender->router("0772093837", "0782604047", 400000);