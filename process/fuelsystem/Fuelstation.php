<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Fuelstation
 *
 * @author MAT
 */
class Fuelstation {
     protected $table_name = 'fuelstation';
     private $table_boda='bodauser';
     private $table_agent='fuelagent';

    private $db;
   public function __construct() {
       $this->db= new Cursorb();

    }
}
    
//    public function getall
//    //put your code here
//}
