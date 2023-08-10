<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// include("cursor.php");
class activation 
{
private $db_table="activationData";
private $db_table1="activation_view";
private $db_table2="loan";
private $croneDate="";
private $activation_date="";
private $db_table3="cp_employer";
private $db_table4="settings_const";
private $db_table5="cp_employee";

private function getnoDays()
{
$db=new Cursor;

$table=$this->db_table4;

$result=$db->select($table, ["value"], ["name"=>'recday']);
if($result)
{
foreach ($result as $values) 
{
return $values['value'];
}
}

}

private function getEmployerPaydayById($employer_id)
{

$db=new Cursor;
$table=$this->db_table3;
$result=$db->select($table, ["payday"], ["Employer_ID"=>$employer_id]);
if($result)
{
foreach ($result as $values) {
return $values['payday'];
}
}
else
{
return null;
}


}




private function get_pay_date($Employer_ID)

{
$day=$this->getEmployerPaydayById($Employer_ID);
// $current_month = date('F');
$current_month = date('m');
$current_year = date('Y');

$date_created = strtotime("$current_year-$current_month-$day");
$pay_date = date("Y-m-d",$date_created);

return $pay_date;	

}


private function get_cron_date($EmployerId)
{

$cron_date = date('Y-m-d', strtotime($this->get_pay_date($EmployerId). '-'.$this->getnoDays() . 'days'));
if($cron_date <= date('Y-m-d')) 
{ 
$cron_date = date('Y-m-d', strtotime($cron_date. '+ 1 month')); 
return $cron_date;
}
else
{
return $cron_date;
}

}



//this function is not usefull right now.

private function set_next_pay_date($employer_id)
{
$next_pay_date = date('Y-m-d', strtotime($this->get_cron_date($employer_id). ' +'.$this->getnoDays() . 'days'));

return $next_pay_date;

}



private function getDatesByEmployer($EmployerId)
{
$db=new Cursor;
$table=$this->db_table1;
$result=$db->select($table, ["activationDate", "croneDate"], ["EmployerId"=>$EmployerId]);
if($result)
{
return $result;	

}
else
{
	return null;

}


}

private function getActivationDate($EmployerId)
{
	// this function returns activation date as stored in the database

	$result=$this->getDatesByEmployer($EmployerId);
	if($result)
	{
	foreach ($result as $value) {
		return $value['activationDate'];
		# code...
	}	
	}
	else
		return null;
	
}



Public function getCroneDate($EmployerId)
{
	// this function returns Crone date as stored in the database
	$result=$this->getDatesByEmployer($EmployerId);
	if($result)
	{
		foreach ($result as $value)
	 {
		return  $value['croneDate'];
		# code...
	}
	}
	else
	{
		return null;
	}
	
}


private function getEmployerIdbyEmployeeId($employeeId)
{
	$db=new Cursor;
$table=$this->db_table5;
$result=$db->select($table, ["employer_id"], ["id"=>$employeeId]);
if($result)
{
foreach ($result as $values) {
return $values['employer_id'];
}
}
else
{
return null;
}

}

public function insertActivationData($employer_id)
{

$db=new Cursor;
// $data= array('' => , );
$data['EmployerId']=$employer_id;
$data['payDay']=$this->set_next_pay_date($employer_id);
$data['croneDate']=$this->get_cron_date($employer_id);
$data['activationDate']=date('Y-m-d');
$table=$this->db_table;      
$id = $db->insert($table, $data);
//    print_r($data);
return $id;


}



public function AdvancesByEmplyerIdInAperiod($EmployerId)
{
$db=new Cursor;
$table=$this->db_table2; 
$activation=$this->getActivationDate($EmployerId);
$cronedate=$this->getCroneDate($EmployerId);    
$query=$db->query("SELECT * FROM `loan` where employer_id=$EmployerId and created_on between '$activation' and '$cronedate' and repayments>0");
//    print_r($dblata);
return $query;
}
public function AdvancesByEmplyerIdInAperiod1($EmployerId)
{
$db=new Cursor;
$table=$this->db_table2; 
$activation=$this->getActivationDate($EmployerId);
$cronedate=$this->getCroneDate($EmployerId);    
$query=$db->query("SELECT * FROM `loan` where employer_id=$EmployerId and created_on between '$activation' and '$cronedate' and repayments=0");
$query1=$db->query("select employee_id, ((amount_borrowed)/4)*repayments as amount from loan where employer_id=$EmployerId and repayments>0");
        array_push($query, $query1);
//    print_r($dblata);
return $query;
}


public function AdvanceStatement($EmployerId)
{
	$db=new Cursor;
$table=$this->db_table2; 
$activation=$this->getActivationDate($EmployerId);
//echo $activation;
        
$cronedate=$this->getCroneDate($EmployerId);
//echo $cronedate;
	$query="select employee_id, sum(amount_borrowed) as amount from loan where employer_id=$EmployerId and created_on between '$activation' and '$cronedate'and repayments=0 GROUP by employee_id";
	$result=$db->query($query);
	return $result;
}
public function AdvanceStatement1($EmployerId)
{
	$db=new Cursor;
$table=$this->db_table2; 
$activation=$this->getActivationDate($EmployerId);
//echo $activation;
        
$cronedate=$this->getCroneDate($EmployerId);
//echo $cronedate;
	$query="select employee_id, sum(amount_borrowed) as amount from loan where employer_id=$EmployerId and created_on between '$activation' and '$cronedate' and repayments=1 GROUP by employee_id";
	$result=$db->query($query);
        $query1=$db->query("select employee_id, ((amount_borrowed)/4)*repayments as amount from loan where employer_id=$EmployerId and repayments>0");
        array_push($result, $query1);
	return $result;
}

public function AdvancesByEployeeId($employeeId)
{

$employerId=$this->getEmployerIdbyEmployeeId($employeeId);
$activation=$this->getActivationDate($employerId);
$cronedate=$this->getCroneDate($employerId); 
$db=new Cursor;
$table=$this->db_table2;      
$query=$db->query("SELECT * FROM `loan` where employee_id=$employeeId and created_on between '$activation' and '$cronedate'and repayments=0");
    //print_r($dblata);
return $query;


}
public function AdvancesByEployeeId1($employeeId)
{

$employerId=$this->getEmployerIdbyEmployeeId($employeeId);
$activation=$this->getActivationDate($employerId);
$cronedate=$this->getCroneDate($employerId); 
$db=new Cursor;
$table=$this->db_table2;      
$query=$db->query("SELECT * FROM `loan` where employee_id=$employeeId and created_on between '$activation' and '$cronedate' and repayments=0 ");
$query1=$db->query("SELECT * FROM `loan` where employee_id=$employeeId and repayments>0 ");
//    print_r($dblata);
 array_push($query, $query1);
return $query;


}

public function getIdByMobile($mobile)
{

$db=new Cursor;
$table=$this->db_table5;
$result=$db->select($table, ["id"], ["mobile"=>$mobile]);
if($result)
{
foreach ($result as $values) {
return $values['id'];
}
}
else
{
return null;
}

}

public function getrows($employeeId)
{
	$result=$this->AdvancesByEployeeId($employeeId);
	$q=count($result,0);
return $q;
}



}
//
// $activation = new activation();
// $inv=$activation->AdvancesByEployeeId(111);
// $inv=$activation->AdvanceStatement1(249);
// //echo $activation->insertActivationData(249);
// print_r($inv);


// $result=$activation->getIdByMobile('0772093837');
// print_r($result);
// // $Date = "2010-09-17";
// 	$pay_date = "2018-01-25";
// 	$current_date = "2018-03-18";
// 	$activation_date = "2018-01-15";

// $data['EmployerId'] = 120;
// $data['payDay'] =$pay_date;
// $data['activationDate'] = $activation_date;
// $data['croneDate'] = $current_date;

// $EmployerId=161;
// $result=$activation->insertActivationData($EmployerId);
// print_r($result);

// $result=$activation->AdvancesByEployeeId(5,'2017-7-18', '2017-08-21');
// print_r($result);

// $days_b4_paydate = 2;
// $activation_date = "2018-01-15";
// $pay_date = $activation->get_pay_date(12);
// $cron_date= $activation->get_cron_date($pay_date,$days_b4_paydate);
// $next_pay_date = $activation->set_next_pay_date($cron_date,$activation_date,$pay_date);
// print('Pay Date:'.$pay_date.'</br>cron Date:'.$cron_date.'</br>Next Pay Date:'.$next_pay_date);


// $result=$activation->AdvancesByEployeeId(5);
// $results=$activation->AdvancesByEmplyerIdInAperiod(161);
// print_r($result);
// echo "pleaase this is another data";

// print_r($results);
















?>