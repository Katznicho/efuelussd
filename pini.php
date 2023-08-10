<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
class pin
{
public $userId;
public $newPin;
public $oldPin;
public $newHash;


public function resetPin()
{
$this->newPin=$this->randomkey();
////convert pin to a hash
$this->newHash=$this->hashPass($this->newPin);
if(!$this->newHash==null)
{
    return true;
}
die("could not reset pin something went wrong");


}

// this method is called if u want to change th current pin to an a new one 
//params USerId, and current pin and new pin
public function updatePin($userId, $oldPin, $newPin)
{
if($this->validatePin($userId, $oldPin, $newPin)==false)
{
return false;


}
$hash=$this->hashPass($newPin);
//the following code is to talk to the db 







}

// public function validatePin()
// {



// }
private function hashPass($password)
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
    $key .= $chars{rand(0, strlen($chars) - 1)};
  }
  
  return $key;
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












}


















?>