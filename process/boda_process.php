<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boda_process
 *
 * @author MAT
 */
class boda_process {
    
  public function writeResponse($msg,$isend = false){
 		$resp_msg = 'responseString='.urlencode($msg);
 		 if($isend)
 		 	$resp_msg .= '&action=end';
 		 else
 			$resp_msg .= '&action=request';
          echo $resp_msg;
  }
         
    
    //put your code here
}
$boda =new boda_process();
$boda->writeResponse("wellcome to Boda", True);



