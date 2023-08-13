<?php

// Include necessary files and classes

// Define your writeResponse function
function writeResponse($msg, $isend = false) {
    $resp_msg = '';

    if ($isend) {
        $resp_msg .= 'END ' . urlencode($msg);
    } else {
        $resp_msg .= 'CON ' . urlencode($msg);
    }

    echo $resp_msg;
}

// Your existing logic to determine which section to include

// Uncomment and modify the section you want to include
// if ($checker->checkbodaexist() == TRUE) {
//     include("fuelsystem/Boda.php");
// } elseif ($checker->checkAgentexist() == TRUE) {
//     include("fuelsystem/Agent.php");
// } else {
//     include("process.php");
// }

// Use writeResponse to send the appropriate message
// For example, sending a continuation message:
// writeResponse("Please select an option:\n1. Check Balance\n2. Make a Payment");

// Sending an ending message:
writeResponse("Welcome Creditplud Boda Loan", true);
?>
