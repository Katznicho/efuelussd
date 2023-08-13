<?php

 //include("process.php");
include_once 'fuelsystem/checker.php';
$checker=new checker();

if($checker->checkbodaexist())
{
   // echo "welcome Boda";
    include("fuelsystem/Boda.php");
    
}
elseif($checker->checkAgentexist())
{
    include("fuelsystem/Agent.php"); 
}
else
{
    die("am here");
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
       

