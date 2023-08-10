<?php 
//  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
    * Name:  Msente Payments
    *
    * Author: Arnold Mwumva Ford
    *         ford@meridiansoftech.net / fordarnold@gmail.com
    *         @fordarnold
    *
    * Location: Kampala, Uganda
    *
    * Created:  24/07/2012
    *
    * Description:  Library to work with Yo! Payments API.
    *
    *
    */


	class msente
	{
		protected $_ci;
		protected $mode;
		protected $api_username;
		protected $api_password;
        protected $sandbox_url;
        
        protected $status_message;
        protected $transaction_id;
        protected $token;

		function __construct()
		{
			// get settings from config
			//$this->mode        = 'live';
            $this->mode        ='sandbox';
            $this->api_username = 'utl_test'; //live uname
            $this->token="M5ZVVo3tsexqzlF3UcyvMtnbFdreQfiB1aodghtl9TU";
            	// $this->api_username ='90008431667';  //sand box uname
                  	// $this->api_username ='anishinani';  //sand box uname
			$this->api_password  = 'test1234'; //live password
            // $this->api_password  = '4biB-9qYR-bjHF-8W0e-KPvW-26qi-Yb0O-qmZR'; // sandbox password
//   $this->api_password  = 'log10tan10'; // sandbox password
            // Get appropriate API endpoint
            if ($this->mode == 'sandbox')
                $this->api_endpoint = "52.47.44.70:2222/proxy/utl.php";
            else
                $this->api_endpoint = "52.47.44.70:2222/proxy/utl.php";
		}

        /**
         * ksksksk
         * Deposit funds into Yo! Payments account from a phone's Mob ile Money account
         * 
         * 
         * @param float $amount The amount of money to deposit
         * @param string $phone Phone number to pull Mobile Money from <br> [Format]: 256772123456
         * @param string $narrative A description of the transaction 
         * @param string $ref_text The text to be returned to the user's phone after the transaction is complete
         * 
         * @return xml The XML Request String to be sent to the Yo! Payments Server
         *
         * ------------------------------------------------------------------------------------------------------------------
         *
         */
        public function transfer($amount, $phone, $narrative="null", $ref_text="")
        {
            
            $xml_request='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:int="http://interfac.msente.tlc.ph.com/">';
            //$xml_request='xmlns:int="http://interfac.msente.tlc.ph.com/">';
            $xml_request=$xml_request.'<soapenv:Header/>';
            $xml_request=$xml_request.'<soapenv:Body>';
            $xml_request=$xml_request.'<int:doTransferMoney>';
            $xml_request=$xml_request.'<MsenteTransaction>';
            $xml_request=$xml_request.'<token>'.$this->token.'</token>';
            $xml_request=$xml_request.'<customerData>'.$phone.'</customerData>';
           // $xml_request=$xml_request.'<NonBlocking>FALSE</NonBlocking>';
            $xml_request=$xml_request.'<amount>'.$amount.'</amount>';
           // $xml_request=$xml_request.'<Account>'.$phone.'</Account>';
            $xml_request=$xml_request.'<operationId>'.$narrative.'</operationId>';
            $xml_request=$xml_request.'<operationDesc>'.$ref_text.'</operationDesc>';
            $xml_request=$xml_request.'</MsenteTransaction>';
            $xml_request=$xml_request.'</int:doTransferMoney>';
            $xml_request=$xml_request.'</soapenv:Body>';
            $xml_request=$xml_request.'</soapenv:Envelope>';
 
            return $this->send_xml_request($xml_request);
        }

        /**
         * Withdraw funds from Yo! Payments account and add to a phone's Mobile Money account
         * 
         * 
         * @param float $amount The amount of money to withdraw
         * @param string $phone Phone number to add Mobile Money to <br> [Format]: 256772123456
         * @param string $narrative A description of the transaction 
         * @param string $ref_text The text to be returned to the user's phone after the transaction is complete
         * 
         * @return xml The XML Request String to be sent to the Yo! Payments Server
         *
         * ------------------------------------------------------------------------------------------------------------------
         *
         */
       

        /**
         * Implement cURL request to Yo! Servers
         *
         * ------------------------------------------------------------------------------------------------------------------
         */

        public function send_xml_request($xml_request) 
        {
			try
			{
				$ch = curl_init(); //initiate the curl session 

				curl_setopt($ch, CURLOPT_URL, $this->api_endpoint); //set to API endpoint
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // tell curl to return data in a variable 
                                curl_setopt($ch, CURLOPT_HEADER, false); 
                              
                                             
                                 curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml","Username: creditp_live","Password: V3wZwSFw7C"));		
                                curl_setopt($ch, CURLOPT_POST, true); 
                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request); // post the xml 
				curl_setopt($ch, CURLOPT_TIMEOUT, (int)30); // set timeout in seconds V3wZwSFw7C

				$xml_response = curl_exec($ch);

				if($xml_response === FALSE)
					$this->status_message = 'The transaction failed. Please try again.';//show_error('There was an error connecting to Yo! Payments. Please try again.');

				curl_close ($ch); 
				
				// Get Status
				// $status_code = $this->get_status($xml_response);
				
				return $xml_response;
			}catch(Exception $e) {
                            return 404;				//$this->status_message = $e->getMessage();
			}
        }

        /**
         * Get the Status Message for the Request
         *
         * ------------------------------------------------------------------------------------------------------------------
         */

        public function get_status($xml_response)
        {
            $xml = simplexml_load_string($xml_response);
            if(!$xml)
            {
                return 'false';
            }
            else
            { 
		$this->status_message = $xml->Response->result;
                return $xml->Response->result;
            }
        }          
        public function getStatusMessage()
        {
			return $this->status_message;
		}

        /**
         * Log any Errors encountered
         *
         * ------------------------------------------------------------------------------------------------------------------
         */
//this is not yet implements
// but its a good method_exists
        public function log_errors($xml_response)
        {
            // TODO: log errors to file / database
        }


        public function getYoRef($xml_response)
        {
            if($xml_response==NULL)
            {
                return 404;
            }

if(stristr($xml_response, "result"))
{
    $array=explode("<result>", $xml_response);
    $code=explode("<",$array[1])[0];
    $code= intval($code);
    return $code;
}



        }

	}
        //
 //withdraw($amount, $phone, $narrative="", $ref_text="")
//$msente = new msente;
//////////$xml_response=$yo->deposit(1700, 256775394598, $narrative="helo this  \n this is just a test", $ref_text="");
//$xml_response=$msente->transfer(500, 256716136125, $narrative="testing msente", $ref_text="123546");
//echo $xml_response;
//if($xml_response==NULL)
//{
//    echo "yes";
//}

//echo $msente->getYoRef($xml_response);
//echo $msente->get_status($xml_response);



/* End of file Yo.php */
 