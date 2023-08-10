<?php
include_once("cursorweb.php");
class PaybillCommision {
    private $db;
    private $table="billers";
    public function __construct() {
        $this->db=new Cursor();
        
    }
    public function getBillerCommission($code, $amount)
    {
        $result=$this->db->select($this->table,null, ["code"=>$code]);
        //print_r($result);
        return $this->route_commssion($result[0]["schedule"], $amount, $result[0]["percentage"]);
    }

    public function route_commssion($schedule, $amount, $percent = null) {
        if ($schedule == 1) {
           return $this->scheduleb1($amount);
        } elseif ($schedule == 2) {
           return  $this->schedulec2($amount);
        } elseif ($schedule == 3) {
            //echo $percent;
           return  $this->shedulepercent($amount, $percent);
        }
    }
    public function scheduleb1($amount)
    {
        if ($amount <= 2500) {
            return 200;
        } else if ($amount <= 5000) {
            return 200;
        } else if ($amount <= 15000) {
            return 200;
        } else if ($amount <= 30000) {
            return 220;
        } else if ($amount <= 45000) {
            return 500;
        } else if ($amount <= 60000) {
            return 800;
        } else if ($amount <= 125000) {
            return 900;
        } else if ($amount <= 250000) {

          return 900;
        } else if ($amount <= 500000) {
            return 1000;
        } else if ($amount <= 1000000) {
            return 1000;
        } else if ($amount <= 2000000) {
            return 3000;
        } else if ($amount <= 4000000) {
            return 4000;
        } else if ($amount <= 7000000) {
            return 12000;
        } else {

            return NULL;
        }
        //return true;
    }
    
    public function schedulec2($amount)
    {
        
         if ($amount <= 5000) {
            return 100;
        } else if ($amount <= 10000) {
            return 100;
        } else if ($amount <= 20000) {
            return 500;
        } else if ($amount <= 50000) {
            return 1000;
        } else if ($amount <= 100000) {
            return 2000;
        } else if ($amount <= 200000) {
            return 3000;
        } else if ($amount <= 500000) {
            return 4000;
        } else if ($amount <= 1000000) {
          return 10000;
        } else if ($amount <= 2000000) {
            return 10000;
        } else if ($amount <= 4000000) {
            return 10000;
        } else if ($amount >4000000) {
            return 10000;
                } else {

            return NULL;
        }
    }
    public function shedulepercent($amount, $percent)
    {
        if(isset($amount)&&isset($percent))
        {
            //echo $amount*$percent/100;
            return $amount*$percent/100;
            //return 10;
        }
        else
        {
            //echo 'not';
            return false;
        }
    }
    
    public function umemecharges($amount)
    {
        
         if ($amount <= 5000) {
           return 1150;
        } else if ($amount <= 15000) {
            return 1150;
        } else if ($amount <= 30000) {
            return 1725;
        } else if ($amount <= 45000) {
            return 2300;
        } else if ($amount <= 60000) {
            return 3795;
        } else if ($amount <= 125000) {
            return 3795;
        } else if ($amount <= 250000) {
            return 4370;
        } else if ($amount <= 500000) {
          return  6900;
        } else if ($amount <= 4000000) {
            return 13800;
        } else if ($amount <= 7000000) {
            return 23000;
        }  else {

            return NULL;
        }
    }
    public function nwsccharges($amount)
    {
        
         if ($amount <= 5000) {
           return 1150;
        } else if ($amount <= 15000) {
            return 1150;
        } else if ($amount <= 30000) {
            return 1725;
        } else if ($amount <= 45000) {
            return 2300;
        } else if ($amount <= 60000) {
            return 3795;
        } else if ($amount <= 125000) {
            return 3795;
        } else if ($amount <= 250000) {
            return 4370;
        } else if ($amount <= 500000) {
          return  6900;
        } else if ($amount <= 4000000) {
            return 13800;
        } else if ($amount <= 7000000) {
            return 23000;
        }  else {

            return NULL;
        }
    }

 
}

//$commission=new PaybillCommision();
//$result =$commission->umemecharges(7000000);
//echo $result;