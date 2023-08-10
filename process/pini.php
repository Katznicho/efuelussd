<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// include("cursor.php");
class pin
{
public $userId;
public $newPin;
public $oldPin;
public $newHash;
public $currentpin;
private $db_table="cp_employee";


public function resetPin()
{
echo $this->newPin=$this->randomkey();
////convert pin to a hash
echo $this->newHash=$this->hashPass($this->newPin);
if(!$this->newHash==null)
{
    return true;
}
die("could not reset pin something went wrong");


}

// this method is called if u want to change th current pin to an a new one 
//params USerId, and current pin and new pin
public function updatePin($userId,$newPin)
{
// if($this->validatePin($userId, $oldPin)==0)
// {
//     // echo "coud not valo=idate";
// return false;


// }
$hash=$this->hashPass($newPin);
//the following code is to talk to the db 

// update($table, $data, $where = null)
$data['pin']=$hash;

$db=new Cursor;
$q=$db->update($this->db_table,$data,["mobile"=>$userId]);

if($q > 0)
{
    return $q;
    // print_r($q);
}
else
{
    return 0;

}

// echo $q;


}

public function validatePin($userId,$pin)
{
// include("cursor.php");
$hash=$this->hashPass($pin);
$db=new Cursor;

        $q = $db->getRows($this->db_table,["mobile"],["mobile"=>$userId,"pin"=>$hash]);
 

		if($q > 0)
        {
            return $q;
            // print_r($q);
        }
        else
        {
            return 0;

        }



}
public function hashPass($password)
{
    if(!isset($password))
    {

        return null;

    }
    else{
        include("config.php");
        
          $password = hash("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($auth_conf['salt_1'] . $password . $auth_conf['salt_2'])))));
          return $password;
    }
 
}


private function randomkey($length = 5)
{
    //generate random key
  $chars = "1234567890";
  $key = "";
  
  for($i = 0; $i < $length; $i++)
  {
    $key .= $chars[rand(0, strlen($chars) - 1)];
  }
  
  return $key;
}












}





// $pini=new pin();
// // // // $pini->resetPin();
// // // $mob="0776389101";
// // // // $piny="82269";
// $pin2="12345";
// // $mob="0772093837";

// // // // echo $pini->validatePin($mob, $pin2);
// echo $pini->hashPass($pin2);












?>