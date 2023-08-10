<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("cursorb.php");
// include("cursor.php");
class pinb
{
    public $userId;
    public $newPin;
    public $oldPin;
    public $newHash;
    public $currentpin;
    private $db_agent = "fuelagent";
    private $db_boda = "bodauser";
    private $db;
    public function __construct()
    {
        $this->db = new Cursorb();
    }

    public function createsecret()
    {
        $secretkey = $this->randomkey(8);
        return $secretkey;
    }

    public function resetPin()
    {
        echo $this->newPin = $this->randomkey();
        ////convert pin to a hash
        echo $this->newHash = $this->hashPass($this->newPin);
        if (!$this->newHash == null) {
            return true;
        }
        die("could not reset pin something went wrong");
    }

    // this method is called if u want to change th current pin to an a new one 
    //params USerId, and current pin and new pin
    public function updatePin($userId, $newPin, $type)
    {

         
        $hash = $this->hashPass($newPin);
        //the following code is to talk to the db 

        // update($table, $data, $where = null)
        $data['pin'] = $hash;
        if ($type == "boda") {
             
            $q = $this->db->update($this->db_boda, $data, ["bodaUserPhoneNumber" => $userId]);
        } else {
             //die("going to update agent pin");
            $q = $this->db->update($this->db_agent, $data, ["fuelAgentPhoneNumber" => $userId]);
        }
        if ($q > 0) {
            return $q;
            // print_r($q);
        } else {
            return 0;
        }

        // echo $q;


    }

    public function validatePin($userId, $pin, $type)
    {
        $hash = $this->hashPass($pin);
        //$db=new Cursor;
        $q = NULL;
        if ($type == "boda") {
            $q = $this->db->getRows($this->db_boda, ["bodaUserPhoneNumber"], ["bodaUserPhoneNumber" => $userId, "pin" => $hash]);


            //echo "boda".$q;
        } else {
            $q = $this->db->getRows($this->db_agent, ["fuelAgentPhoneNumber"], ["fuelAgentPhoneNumber" => $userId, "pin" => $hash]);
        }


        if ($q > 0) {
            return TRUE;
            // print_r($q);
        } else {
            return FALSE;
        }
    }

    public function hashPass($password)
    {
        if (!isset($password)) {

            return null;
        } else {
            include("config.php");

            $password = hash("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($auth_conf['salt_1'] . $password . $auth_conf['salt_2'])))));
            return $password;
        }
    }


    public function randomkey($length = 5)
    {
        //generate random key
        $chars = "1234567890";
        $key = "";

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $key;
    }
}








