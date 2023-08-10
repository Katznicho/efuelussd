<?php

class PaybillCommision {
    
    public function route_commion($billerid,$amount)
    {
        
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
    
    public function schedulec1($amount)
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
            return $amount*$percent;
        }
        else
        {
            return false;
        }
    }
    
    
   

    //put your code here
}
