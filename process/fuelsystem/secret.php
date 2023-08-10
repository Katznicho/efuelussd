<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of secret
 *
 * @author MAT
 */
class secret
{
    private $table;

    private $db;
    public function __construct()
    {
        $this->db = new Cursorb();
        $this->table = "fuelcode";
    }

    public function createsecret($bodaphone, $amount)
    {
        $bodaUsermobile = $bodaphone;
        $secret = $this->randomkey(6);
        $fuelCode = md5($secret);
        $FuelAmount = $amount;
        $data = array();
        $data["bodaUsermobile"] = $bodaUsermobile;
        $data["fuelCode"] = $fuelCode;
        $data["FuelAmount"] = $FuelAmount;
        $result = $this->db->insert($this->table, $data);
        // echo $result;
        if ($result > 0) {
            return $secret;
        } else {
            return FALSE;
        }
    }
    public function verifysecret($bodanumber, $secret)
    {
        $fuelCode = md5($secret);
        //echo $ownerid;
        // $query="SELECT fuelCode from $this->table WHERE fuelCode=$fuelCode";
        $q = $this->db->getRows($this->table, ["fuelCode"], ["bodaUsermobile" => $bodanumber, "fuelCode" => $fuelCode, "status" => 1]);
        if ($q > 0) {
            return TRUE;
            // print_r($q);
        } else {
            return FALSE;
        }
    }

    //the following code is to talk to the db 

    // update($table, $data, $where = null)

    public function updatesecret($secret, $agent)
    {
        $hash = md5($secret);
        //the following code is to talk to the db 

        // update($table, $data, $where = null)
        $data['status'] = 4;
        $data['fuelAgentid'] = $agent;


        $q = $this->db->update($this->table, $data, ["fuelCode" => $hash]);


        if ($q > 0) {
            return $q;
            // print_r($q);
        } else {
            return 0;
        }
    }
    private function randomkey($length = 5)
    {
        //generate random key
        $chars = "1234567890";
        $key = "";

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $key;
    }

    public function currentsecretstate($secret)
    {
        $fuelCode = md5($secret);
        //echo $ownerid;
        $query = "SELECT TIMESTAMPDIFF(MINUTE,created_at, CURRENT_TIMESTAMP) AS 'minutes' from $this->table WHERE fuelCode='$fuelCode'";
        $result = $this->db->query($query);
        // print_r($result);
        //print_r($result);
        //       

        // echo $value["minutes"];
        //echo $result[0]["minutes"];
        if ($result[0]["minutes"] < 60) {
            //echo "here";
            return TRUE;
        } else {
            // echo "there";
            return FALSE;
        }
    }


    //put your code here
}
