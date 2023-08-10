<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stage
 *
 * @author MAT
 */
class stage {
    private $table="stage";
    private $db;
     
    public function __construct() {
        
        $this->db =new Cursorb();
        

    }
       
  public function StageStatus($stageId)
    {
        // $employerId = $this->getEmployerIdByMobile($mobile);

        
        $result = $this->db->likeSelect($this->table, ["stageStatus"], ["stageId" => $stageId]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $boda) {
                $boda_status = $boda["stageStatus"];

            }
            return $boda_status;
        }

    }
    //put your code here
    
}
