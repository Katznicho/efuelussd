<?php

 

//include("process.php");
include_once 'fuelsystem/checker.php';
$checker=new checker();

if($checker->checkbodaexist()==TRUE)
{
   // echo "welcome Boda";
    include("fuelsystem/Boda.php");
    
}
elseif($checker->checkAgentexist()==TRUE)
{
    include("fuelsystem/Agent.php"); 
}
else
{
    
    //echo "hello";
    writeResponse("Your Not Registered on E-Fuel", true);
}

function writeResponse($msg, $isend = false) {
    $resp_msg = '';

    if ($isend) {
        $resp_msg .= 'END ' . urlencode($msg);
    } else {
        $resp_msg .= 'CON ' . urlencode($msg);
    }

    echo $resp_msg;
}
       
//     writeResponse("Welcome Creditplud Boda Loan",true);
