<?php
// include("../../PHPAuth/auth.class.php");
function randomkey($length = 5)
{
  $chars = "1234567890";
  $key = "";
  
  for($i = 0; $i < $length; $i++)
  {
    $key .= $chars{rand(0, strlen($chars) - 1)};
  }
  
  return $key;
}

function month($date)
{
  $mydate=getdate($date);
  return $mydate[mon];
}

function hashpass($password)
{
  include("config.php");

  $password = hash("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($auth_conf['salt_1'] . $password . $auth_conf['salt_2'])))));
  return $password;
}
//step one check if the user has ever borrowed 
//step two sum up all the amount that the user has ever borrowed in the wit in the same month and year
//the function should tell us if the user has ever boowerd 
// go to the db selcet sum up amount borrowed in that same month and year then return 

function advanceAmount($userid)
{
 


}

function everborrowed()
{



}

function availamount()
{

    // $var=2
    
}

echo randomkey();
echo "------";
echo hashpass(randomkey());
echo "------";




?>
