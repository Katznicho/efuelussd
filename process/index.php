<?php

 

//include("process.php");
// include_once 'fuelsystem/checker.php';
// $checker=new checker();

// if($checker->checkbodaexist()==TRUE)
// {
//    // echo "welcome Boda";
//     include("fuelsystem/Boda.php");
    
// }
// elseif($checker->checkAgentexist()==TRUE)
// {
//     include("fuelsystem/Agent.php"); 
// }
// else
// {
    
//     //echo "hello";
//     include("process.php"); 
// }

 function writeResponse($msg,$isend = false){
		$resp_msg = 'responseString='.urlencode($msg);
		 if($isend)
		 	$resp_msg .= '&action=end';
		 else
			$resp_msg .= '&action=request';
         echo $resp_msg;
	}
       
    writeResponse("Welcome Creditplud Boda Loan",true);
