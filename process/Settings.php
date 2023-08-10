<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// include("cursor.php");
class settings
{
    private $db_table="mobile_operator_charges";
    private $db_table2="mobile_operator";
    private $db_table3="cp_employee";
    private $db_table4="cp_employer";

    public function getsendchargeByName($name)
    {

        if (empty($name)) {
            return null;
        }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["send_charge"], ["operator_name" =>$name]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["send_charge"];

            }
            return $user_session;
        }


    }

    public function insertcharges($data)
    {
          $db=new Cursor;
        $table=$this->db_table;      
        $id = $db->insert($table, $data);
    //    print_r($data);
        return $id;
    }
    public function insertoperator($data)
    {
          $db=new Cursor;
        $table=$this->db_table2;      
        $id = $db->insert($table, $data);
    //    print_r($data);
        return $id;
    }

    public function getcolumnscharges()
    {

        // if (empty($mobile)) {
        //     return null;
        // }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["id","operator_name","lower_limit", "upper_limit", "send_charge", "withdraw_charge"]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            return $result;
            // foreach ($result as $session) {
            //     $user_session = $session["full_name"];

            // }
            // return $user_session;
        }


    }

    public function getcolumnsoprators()
    {

        // if (empty($mobile)) {
        //     return null;
        // }
        $db = new Cursor;
        $table = $this->db_table2;

        $result = $db->likeSelect($table, ["id","name","msisdn_code"]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            return $result;
            // foreach ($result as $session) {
            //     $user_session = $session["full_name"];

            // }
            // return $user_session;
        }


    }

    

    public function update($data, $id)
    {
        
          $db=new Cursor;
            $table=$this->db_table; 
        if (empty($mobile)) {
            return null;
        }

        $result = $db->update($table, $data, ["id"=>$id]);
       
        return $result;
    }
    
public function ratiovalue($nettwork, $employerId)
{
    // network ccan be (mtn)or (airel) and 
    $db = new Cursor;
    $table = $this->db_table4;
    $result = $db->likeSelect($table, ["advance_limit"], ["employer_id" => $employerId]);
    // print_r($result);
    # this works very similar to the select except that it matches every whose name starts with pia..
    if (empty($result)) {
        return null;
    } else {
        foreach ($result as $session) {
            $user_session = $session["advance_limit"];

        }
        $ratio=($this->creditratio($nettwork, $employerId)*$user_session)/100;
    }

return $ratio;

}



    public function creditratio($nettwork, $employerId)
    {
        $user_session=0;
        $db = new Cursor;
            $table = $this->db_table3;
    
            $result = $db->likeSelect($table, ["salary","mobile"], ["employer_id" =>$employerId]);
    
            if ($nettwork=="airtel") {
                foreach ($result as $session) {
                    if($this->checkNetwork($session["mobile"])==$nettwork)
                    {
                    $user_session += $session["salary"];
                    }
                }
                return $user_session;
            } 
            elseif($nettwork="mtn") {
                foreach ($result as $session) {
                    if($this->checkNetwork($session["mobile"])==$nettwork)
                    {
                    $user_session += $session["salary"];
                    }
    
                }
                return $user_session;
            }



    
    }
           
    // public function getsendchargeByName($name)
    // {

    //     if (empty($name)) {
    //         return null;
    //     }
    //     $db = new Cursor;
    //     $table = $this->db_table;

    //     $result = $db->likeSelect($table, ["send_charge"], ["operator_name" =>$name]);

    //     if (empty($result)) {
    //         return null;
    //     } else {
    //         foreach ($result as $session) {
    //             $user_session = $session["send_charge"];

    //         }
    //         return $user_session;
    //     }


    // }


    private function checkNetwork($mobile)
	{
		$network = "none";
		$m = $this->formatMobile($mobile);
		$prefix = substr($m,0,3);
		
        if($prefix == "075" || $prefix == "070")
         //check for airtel
			$network = "airtel";
		else if($prefix == "077" || $prefix == "078") //check for mtn
			$network = "mtn";
		// else if($prefix == "079") //check for africell
		// 	$network = "africell";
		 else if($prefix == "071" || $prefix == "041" )//check for utl
		 	$network = "utl";
		
		return $network;
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
	









}


// echo mail("anishinani@gmail.com", "testing", "hello kampala");

// $settings=new settings();
// $name="mtn";
// echo $settings->getsendchargeByName($name);
// ech
// pri
// echo $settings->ratiovalue("mtn", 162);

// echo "hahah";




?>