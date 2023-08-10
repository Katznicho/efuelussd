<?php
//  defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Ussd controller
 */
class Ussd
{
    // protected $permissionCreate = 'Ussd.Ussd.Create';
    // protected $permissionDelete = 'Ussd.Ussd.Delete';
    // protected $permissionEdit   = 'Ussd.Ussd.Edit';
    // protected $permissionView   = 'Ussd.Ussd.View';
    
    /*Different stages of USSD and menu codes
     * welcome - 00
     * advance request - 00_01
     * advance request pin prompt - 00_01_00
     * account status	-	00_02
     * account status pin - 00_02_00
     * active advances - 00_03
     * active advances pin - 00_03_00
     * reset pin - 00_04
     * reset pin new pin - 00_04_00
     * 
     * activation prompt - ACT_PROMPT_01
     */
     
     private $transactionId;
     private $transactionTime;
     private $msisdn;
     private $requestString;
     private $user_session;
     private $last_usercode;
     //private 

    /**
     * Constructor
     *
     * @return void
     */
    // public function __construct()
    // {
    //     parent::__construct();
        
    //     $this->lang->load('ussd');
        
    //     $this->load->helper('form');
    //     $this->load->library('form_validation');
    //     $this->load->library('Template');
        
    //     $this->load->model('employee/Employee_model','employee');
    //     $this->load->model('employer/Employer_model','employer');
    //     $this->load->model('loans/Loan_model','loan');
    //     $this->load->model('ussd/Ussd_session_model','ussd_session');
        
    //     $this->load->model('manage/Platform_settings_model','platform_settings');
        
    //     $this->load->library('users/auth');
    //     $this->load->model('users/User_model','user');

    //     Assets::add_module_js('ussd', 'ussd.js');
    // }

    /**
     * Display a list of ussd data.
     *
     * @return void
     */
    // public function index()
    // {
       
    //     $this->process();
    // }
    
    public function process()
    {
		$proceed = $this->validate_request();
		
		if(!$proceed)
			return;
		
		
		if(isset($_GET['response']) && $_GET['response'] == 'false') {
			if($this->checkIfActivated())
				$this->welcome();
			else
			{
				if($this->checkIfSuspended())
					$this->writeResponse("Welcome to CreditPlus\r\nYour account has been suspended by your employer",true);
				else
					$this->promptActivateAccount();
			}
		}
		else
		{
			
			//Lets get the session and see what the last response was
			
			$this->user_session = $this->ussd_session->getByTransactionId($this->transactionId);
			if($this->user_session == null){
				$this->sessionError();
				return;
			}
			
			$code = $this->user_session->last_usercode;
			$this->last_usercode = $code;
			//Lets validate if we have a correct request string
			
			$this->requestString = $_GET['ussdRequestString'];
			
			if($this->requestString == "*")
			{
				$this->goBack();
			}
			else
			{
				switch($code){
					case '00':
						//lets analyse the current urequest string
						if($this->requestString == '1'){
							$this->getAdvance();
						}else if($this->requestString == '2'){
							$this->getAccountStatus();
						}else if($this->requestString == '3'){
							$this->getActiveAdvances();
						}else if($this->requestString == '4'){
							$this->resetPin();
						}
						else
						{
							$this->welcome_general(true);
						}
						break;
					case '00_01':
						$this->getAdvancePinRequest();
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
					case 'ACT_PROMPT_01_02':
						$this->promptActivateAccount(true);
						break;
				}
			}
		}
    }
    
    private function validate_request()
    {
		//Lets check for the correct parameters
		
		
		//transaction id
		if (!isset($_GET['transactionId'])) {
			
			$this->writeResponse( 'Transactionid not found',true);
			return false;
		}
		
		$this->transactionId = $_GET['transactionId'];
		
		//Transaction time
		if (!isset($_GET['transactionTime'])) {
			$this->writeResponse('Transactiontime not found',true);
			return false;
		}
		
		$this->transactionTime = $_GET['transactionTime'];
		
		//msisdn
		if (!isset($_GET['msisdn'])) {
			
			$this->writeResponse('msisdn not found',true);
			
			return false;
		}else{
			//check if the number is registered with creditplus
			$msisdn = $_GET['msisdn'];
			//we need to strip off the 256 and append a 0
			$mobile = $this->formatMobile($msisdn);
			
			$employee = $this->employee->getByMobile($mobile);
			if($employee == null){
				
				$this->writeResponse("Sorry, your number is not registered on the CreditPlus\r\nPlease visit www.creditplus.ug for help",true);
				return false;
			}
			
			$this->msisdn = $mobile;
		}
		
		return true;
		
	}
    
    private function welcome()
    {
		$this->welcome_general(false);
    }
    
    private function welcome_general($from_activation = true)
    {
		//We need to create the session
		
		if($from_activation == true)
		{
			$data['last_usercode'] = '00';
		
			$this->ussd_session->update($this->user_session->id,$data);
		}
		else
		{
			$data['transaction_id'] = $this->transactionId;
			$data['msisdn'] = $this->msisdn;
			$data['last_usercode'] = '00';
			
			$this->ussd_session->insert($data);
		}
		
		
        $menu_text = "Welcome to CreditPlus\r\n1. Request Advance\r\n2. Account Status\r\n3. Advance Statement\r\n4. Change PIN";
        $this->writeResponse($menu_text);
	}
    
    
    private function checkIfActivated()
    {
		$mobile = $this->formatMobile($this->msisdn);
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			$this->writeResponse("Employee not found, please contact CreditPlus for help",true);
			return;
		}
		
		//lets get the user and check if activated
		$user = $this->user->find($employee->user_id);
		if($user == null){
			$this->writeResponse("Failed to find user record based on mobile",true);
			return ;
		}
		
		if($user->active == 1)
			return true;
		else
			return false;
	}
	
	private function checkIfSuspended()
    {
		$mobile = $this->formatMobile($this->msisdn);
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			$this->writeResponse("Employee not found, please contact CreditPlus for help",true);
			return;
		}
		
				
		if($employee->status == 1)
			return true;
		else
			return false;
	}
    
    private function promptActivateAccount($is_error = false)
    {
		//We need to create the session
		if($is_error == false)
		{
			
			$data['transaction_id'] = $this->transactionId;
			$data['msisdn'] = $this->msisdn;
			$data['last_usercode'] = 'ACT_PROMPT_01';
			
			$this->ussd_session->insert($data);
			$menu_text = "Welcome to CreditPlus\r\nPlease enter your activation code";
		}
		else
		{
			$data['last_usercode'] = 'ACT_PROMPT_01';
			$this->ussd_session->update($this->user_session->id,$data);
			
			$menu_text = "Activation code is incorrect, please enter the correct code to activate";
		}
		
		
		$this->writeResponse($menu_text);
	}
	
	private function performActivateAccountProcess()
	{
		//Lets validate the PIN
		$pin = $this->requestString;
		
		if($this->user_session == null){
			$this->sessionError();
			return;
		}
		//Lets vaidate the PIN
		if(!$this->validatePin($pin)){
			$this->promptActivateAccount(true);
			return;
		}
		
		//Lets prompt for a new PIN
		$data['last_usercode'] = 'ACT_PROMPT_02';
		$this->ussd_session->update($this->user_session->id,$data);
		
		$menu_text = "Please enter a new PIN";
		$this->writeResponse($menu_text);
	}
	
	// all the go back land to the welcome general
	
	private function goBack()
	{
		switch($this->last_usercode){
			case '00_01':
			case '00_02':
			case '00_03':
			case 'ACT_PROMPT_02':
				$this->welcome_general(true);
				break;
			
		}
		
	}
    
    private function getAdvance(){
		//we need to show how much the employee can borrow
		$mobile = $this->formatMobile($this->msisdn);
		$employee = $this->employee->getByMobile($mobile); //employee method
		if($employee == null)
		{
			$this->writeResponse("Employee not found, please contact CreditPlus for help",true);
			return;
		}
		
		$employer = $this->employer->getById($employee->employer_id);
		if($employer == null){
			$this->writeResponse("Employer not found, please contact CreditPlus for help",true);
			return;
		}
		
		$loans = $this->loan->getActiveByEmployeeId($employee->id);
		$current_bal = 0;
	
		if(!empty($loans)){
			foreach($loans as $loan)
				$current_bal += $loan->amount_borrowed;
		}
		
				
		//we need to check if the amount requested is greater than the advance limit
		$advance_limit_perc = $employer->advance_limit;
		$advance_limit = ($employee->salary * $advance_limit_perc)/100;
		
		//Lets update the session data
			
		$data['last_usercode'] = '00_01';
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		$available_amount = $advance_limit - $current_bal ;
		
		if($available_amount > 0)
		{
			$menu_text = "Available amount is UGX".number_format($available_amount)."\r\n";
			$menu_text .= "Please specify the amount";
		}
		else
		{
			$menu_text = "Advance limit has been reached\r\n*. Back";
		}
		
		$this->writeResponse($menu_text);
		
	}
	
	private function getAdvancePinRequest(){
		//Lets update the session data
		$amount = $this->requestString;
		if(!is_numeric($amount))
		{
			$this->writeResponse("Invalid amount specified\r\nPlease specify a valid amount");
			return;
		}
			
		
		//Lets update the session information
		$data['last_usercode'] = '00_01_00';
		$data['data1'] = $amount;
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		//Lets add commas into the amount
		$english_amount = number_format($amount);
		
		//Lets write a response
		$menu_text = "Please enter your PIN to get advance of UGX".$english_amount;
		$this->writeResponse($menu_text);
	}
	
	private function getAdvanceProcess(){
		//Lets validate the PIN
		$pin = $this->requestString;
		
		if($this->user_session == null){
			$this->sessionError();
			return;
		}
		//Lets vaidate the PIN
		if(!$this->validatePin($pin)){
			$this->sessionErrorIncorrectPin();
			return;
		}
		
		$amount = $this->user_session->data1;
		//Lets process the payment
		$this->processPayment($amount);
				
			
	}
	
	private function processPayment($amount)
	{
			$mobile = $this->formatMobile($this->msisdn);
            $employee = $this->employee->getByMobile($mobile);
            if($employee == null){
				$this->writeResponse("Employee not found, please contact CreditPlus for help",true);
				return;
			}
            
            $employer = $this->employer->getById($employee->employer_id);
            if($employer == null){
				$this->writeResponse("Employer not found, please contact CreditPlus for help",true);
				return;
			}
            //Lets check the status of the employer before proceeding
            /**
             * Employer Status
             *  0	-	Normal
             *  1	-	Processing payments
             *  2	-	Suspended
             * 
             * 
             */
            switch($employer->status){
                case 1:
                    $this->writeResponse("Employer is still processing pending payements",true);
                    return;
                case 2:
                    $this->writeResponse("Employer is suspended from Credit Plus platform",true);
					return;
            }
            
            //Lets get the platform settings
            $cp_settings = $this->platform_settings->get_settings();
            
            $service_fee = $cp_settings->service_fee;
            
             //Lets validate before we save
             $payday = $employer->payday;
			//loan amount must not be greater than salary
			
			if($amount >=  $employee->salary){
				$this->writeResponse("Cannot process payment because amount requested is greater than salary",true);
				return;
			}
			
            //Check the date of request compared to the payday
            $processing_period = $cp_settings->reconciliation_period;
            /*$today = date('j');
            if($today > $payday ){
                //we need to check if employer account is allowed to make further transactions
                $this->form_validation->set_message('check_amount', 'Cannot process payment because of the request date');
                return FALSE;
            }*/
            //Check if he has previous loans
            $loans = $this->loan->getActiveByEmployeeId($employee->id);
			$current_bal = 0;
        
	        if(!empty($loans)){
	            foreach($loans as $loan)
	                $current_bal += $loan->amount_borrowed;
	        }
			
			if(($amount + $current_bal) >= $employee->salary){
				$this->writeResponse("Cannot process payment because current balance would be greater than salary",true);
				return;
			}
			
			//we need to check if the amount requested is greater than the advance limit
			$advance_limit_perc = $employer->advance_limit;
			$advance_limit = ($employee->salary * $advance_limit_perc)/100;
			
			if($amount > $advance_limit){
				$this->writeResponse("Requested advance is greater than advance limit of UGX".number_format($advance_limit),true);
				return;
			}
			
			if(($amount + $current_bal) > $advance_limit){
				$rec_amount = $advance_limit - $current_bal;
				$this->writeResponse("Requested advance is greater than advance limit, recommended amount is UGX".number_format($rec_amount),true);
				return;
			}
            
            
            $service_amount = ($amount * $service_fee)/100;
            
            
            $network_fee = 0;
            $network = $this->checkNetwork($mobile);
            if($network == "airtel")
				$network_fee = $cp_settings->airtel_fee;
			else if($network == "mtn")
				$network_fee = $cp_settings->mtn_fee;
			else if($network == "africell")
				$network_fee = $cp_settings->africell_fee;
			else if($network == "utl")
				$network_fee = $cp_settings->utl_fee;
			
			$yo_amount = $amount + $network_fee;	
			
            $data['amount_borrowed'] = $amount;
            $data['employee_id'] = $employee->id;
            $data['employer_id'] = $employee->employer_id;
            $data['send_charge'] = $network_fee;
            $data['withdraw_charge'] = 0;
            $data['service_fee'] = $service_amount;
            $data['total_amount_due'] = $amount + $service_amount;
                        
            
            //Lets process the payment
            // $this->load->library('payments/Yo');// time to call payment class

            $to = $this->formatMobileInternational256($this->msisdn);
            $code = $this->yo->withdraw($yo_amount, $to, "CreditPlus Advance","Advance from CREDITPLUS UGANDA LIMITED");
            //$code =0;
            //we need to analyse this code incase it shows an error
            
			$proceed = false;
			if($code == 0 || $code == 1 || $code == 4 || $code == 6)
				$proceed = true;
			else
			{
				$this->writeResponse("Payment gateway cannot process request (error: $code)",true);
				return;
			}
            
            
            
            //Lets save the record
            $advance_id = $this->loan->skip_validation(true)->insert($data);
            
            
            //Lets send an sms
            $this->load->library('sms/Infobip');
			$to = $this->formatMobileInternational($this->msisdn);
			$msg = "Your advance of UGX ".number_format($amount)." has been processed with id ".$advance_id;
			$this->infobip->sendSms($to,$msg);
            
            //Lets write the end response            
            $this->writeResponse("Advance has been processed successfully",true);
            
            
            //we need to delete the session
			$this->ussd_session->delete($this->user_session->id);
    
	}
	
	
	private function getAccountStatus(){
		//Lets update the session data
		$data['last_usercode'] = '00_02';
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		//Lets write a response
		$menu_text = "Please enter your PIN";
		$this->writeResponse($menu_text);
		
	}
	
	private function getAccountStatusProcess(){
		//Lets validate the PIN
		$pin = $this->requestString;
		
		
		if($this->user_session == null){
			$this->sessionError();
			return;
		}
		//Lets vaidate the PIN
		if(!$this->validatePin($pin)){
			$this->sessionErrorIncorrectPin();
			return;
		}
		
		$mobile = $this->formatMobile($this->msisdn);
		
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			$this->writeResponse("Failed to find employee record based on mobile",true);
			return;
		}
		
		$user = $this->user->find($employee->user_id);
		if($user == null){
			$this->writeResponse("Failed to find user record based on mobile",true);
			return ;
		}
		
		$loans = $this->loan->getActiveByEmployeeId($employee->id);
		$current_bal = 0;
	
		if(!empty($loans)){
			foreach($loans as $loan)
				$current_bal += $loan->amount_borrowed;
		}
		
		$status ="unknown";
		$employer = $this->employer->find($employee->employer_id);
		if($employer->status == 0)
		{
			switch($user->active){
				case 0:
					$status = "Inactive";
					break;
				case 1;
					$status = "Active";
					break;
				
			}
		}
		else
		{
			$status = "Suspended";
		}
		
		//we need to check if the amount requested is greater than the advance limit
		$advance_limit_perc = $employer->advance_limit;
		$advance_limit = ($employee->salary * $advance_limit_perc)/100;
		
		$summary = "Status: $status\r\n";
		
		$summary .= "Account Name: ".$employee->first_name." ".$employee->last_name."\r\n";
		$summary .= "Advance Limit: UGX".number_format($advance_limit)."\r\n";
		$summary .= "Advance Total: UGX".number_format($current_bal);
		$summary .= "\r\n*. Back";
			
		$this->writeResponse($summary);
		
		//we need to delete the session
		//$this->ussd_session->delete($this->user_session->id);
		
		
	}
	
		
	private function resetPin(){
		$data['last_usercode'] = '00_04';
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		//Lets write a response
		$menu_text = "Please enter your PIN to proceed";
		$this->writeResponse($menu_text);
	}
	
	private function resetPinValidate(){
		
		//Lets validate the PIN
		$pin = $this->requestString;
		if(!$this->validatePin($pin)){
			$this->sessionErrorIncorrectPin();
			return;
		}
		
		$data['last_usercode'] = '00_04_00';
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		//Lets write a response
		$menu_text = "Please enter your new PIN";
		$this->writeResponse($menu_text);
	}
	
	private function resetPinProcess(){
		//Lets reset the pin
		$pin = $this->requestString;
		
		if($this->user_session == null){
			$this->sessionError();
			return;
		}
		//do pin reset magic
		
		$mobile = $this->formatMobile($this->msisdn);
		
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			$this->writeResponse("Failed to find employee record based on mobile",true);
			return;
		}
		
		$user = $this->user->find($employee->user_id);
		if($user == null){
			$this->writeResponse("Failed to find user record based on mobile",true);
			return ;
		}
		
		$data["password"] = $pin;
		$data["active"] = 1;
		if($this->user->update($user->id,$data))
		{
			$this->writeResponse("PIN has been changed successfully\r\n*. Back");
			return;
		}
		else
			$this->writeResponse("Failed to update user PIN, please contact CreditPlus for help",true);
		
		
		//we need to delete the session
		$this->ussd_session->delete($this->user_session->id);
		
		
	}
	
	
	private function getActiveAdvances()
	{
		$data['last_usercode'] = '00_03';
		
		$this->ussd_session->update($this->user_session->id,$data);
		
		//Lets write a response
		$menu_text = "Please enter your PIN to proceed";
		$this->writeResponse($menu_text);
	}
	
	
	private function getActiveAdvancesProcess()
	{
		//Lets validate the PIN
		$pin = $this->requestString;
		
		
		if($this->user_session == null){
			$this->sessionError();
			return;
		}
		//Lets vaidate the PIN
		if(!$this->validatePin($pin)){
			$this->sessionErrorIncorrectPin();
			return;
		}
		
		$mobile = $this->formatMobile($this->msisdn);
		
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			$this->writeResponse("Failed to find employee record based on mobile",true);
			return;
		}
		
		$user = $this->user->find($employee->user_id);
		if($user == null){
			$this->writeResponse("Failed to find user record based on mobile",true);
			return ;
		}
		
		$loans = $this->loan->getActiveByEmployeeId($employee->id);
		$current_bal = 0;
	
		$summary = "Account Name: ".$employee->first_name." ".$employee->last_name."\r\n";
		$summary .= "Active Advances:\r\n";
		if(!empty($loans)){
			foreach($loans as $loan){
				$current_bal += $loan->amount_borrowed;
				$summary .= "Id: ".$loan->id." Amount: ".number_format($loan->amount_borrowed)." on ".date('j-m-Y H:i:s',$loan->created_on)."\r\n";
			}
		}else{
			$summary .= "No advances found!\r\n";
		}
		$summary .= "--------------\r\n";
		$summary .= "Advance Total: UGX".number_format($current_bal);
		$summary .= "\r\n*. Back";
		
		$this->writeResponse($summary);
			
		//we need to delete the session
		//$this->ussd_session->delete($this->user_session->id);
		
		
	}
	
	private function validatePin($pin)
	{
		$correct = false;
		
		//we need to strip off the 256 and append a 0
		$mobile = $this->formatMobile($this->msisdn);
		
		$employee = $this->employee->getByMobile($mobile);
		if($employee == null){
			return false;
		}
		
		$user = $this->user->find($employee->user_id);
		
		if($user == null){
			return false;
			
		}
		
		$correct = $this->auth->check_password($pin, $user->password_hash);
		
		
		return $correct;
	}
		
	
	private function sessionError(){
		$this->writeResponse('Session error, please restart process',true);
				
	}
	
	private function sessionErrorIncorrectPin(){
		$this->writeResponse("Incorrect PIN, please re-enter your PIN");
				
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
	
	private function formatMobileInternational($mobile){
		$length = strlen($mobile);
		$m = '+256';
		//format 1: +256752665888
		if($length == 13)
			return $mobile;
		elseif($length == 12) //format 2: 256752665888
			return "+".$mobile;
		elseif($length == 10) //format 3: 0752665888
			return $m .= substr($mobile, 1);
		elseif($length == 9) //format 4: 752665888
			return $m .= $mobile;
			
		return $mobile;
	}
	
	private function formatMobileInternational256($mobile){
		$length = strlen($mobile);
		$m = '256';
		//format 1: +256752665888
		if($length == 13)
			return $mobile;
		elseif($length == 12) //format 2: 256752665888
			return "+".$mobile;
		elseif($length == 10) //format 3: 0752665888
			return $m .= substr($mobile, 1);
		elseif($length == 9) //format 4: 752665888
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
    
    private function writeResponse($msg,$isend = false){
		$resp_msg = 'responseString='.urlencode($msg);
		if($isend)
			$resp_msg .= '&action=end';
		else
			$resp_msg .= '&action=request';
        echo $resp_msg;
	}
    
}

// echo "hello";
