<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FloatDeposits
 *
 * @author MAT
 */
class FloatDeposits {
    private $db;
    private $table;
    function __construct() {
        $this->db = new Cursorb();
        $this->table ="deposits";
    }
    /*this function will querry the table of deposits to get all deposits of,
     * a given petrol station
     */
    public function getAllTimeTotalDepossitsOfstation($stationId)
    {

            $result=$this->db->query("SELECT SUM(amount)TOTALAMOUNT FROM $this->table WHERE fuelStationId='$stationId'");
            if (count($result)) 
                {
            return $result[0]["TOTALAMOUNT"];
                } 
                else {
                    return 0;
                }
           
      
    }
    //put your code here
}
