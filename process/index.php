<?php

include_once 'fuelsystem/checker.php';
$checker = new checker();



if ($checker->checkbodaexist()) {
    include_once("fuelsystem/Boda.php");
} elseif ($checker->checkAgentexist()) {
    include_once("fuelsystem/Agent.php");
} else {

    writeResponse("Your Not Registered on E-Fuel", true);
}

function writeResponse($msg, $isend = false)
{
    $resp_msg = '';

    if ($isend) {
        $resp_msg .= 'END ' . urlencode($msg);
    } else {
        $resp_msg .= 'CON ' . urlencode($msg);
    }

    echo $resp_msg;
}
