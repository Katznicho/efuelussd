<?php



error_reporting(0);
//ini_set('display_errors', 1);

class smsb
{

    public function formatMobileInternational($mobile)
    {
        $length = strlen($mobile);
        $m = '+256';
        //format 1: +256752665888
        if ($length == 13)
            return $mobile;
        elseif ($length == 12) //format 2: 256752665888
            return "+" . $mobile;
        elseif ($length == 10) //format 3: 0752665888
            return $m .= substr($mobile, 1);
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }



    public function sendsms($from, $to, $msg)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://apidocs.speedamobile.com/api/SendSMS?api_id=API34247417254&api_password=!Log10tan10&sms_type=P&encoding=T&sender_id=" . $from . "&phonenumber=" . $to . "&textmessage=" . urlencode($msg),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            //            CURLOPT_HTTPHEADER => array(
            //                "cache-control: no-cache",
            //                "postman-token: ca383ae7-063a-2e07-a8b8-acb6712e61c4"
            //            ),
        ));

        $response = curl_exec($curl);
        // echo $response;
        $err = curl_error($curl);

        curl_close($curl);
        if ($response) {

            $decodedcontent = json_decode($response);


            $data['tel'] = $to;
            $data['message'] = $msg;

//            $data['message_id'] = $decodedcontent->{'message_id'};
//            $data['success_code'] = $decodedcontent->{'remarks'};
//            $data['status'] = $decodedcontent->{'status'};
//
//            $db = new Cursorb();
//            $table = "sms_gateway";
//            $id = $db->insert($table, $data);

            return 1;
        } else {
            return 0;

            //echo $ex;
        }
    }

    

    public function sms_faster($message, $receivers , $status, $username = "CreditPlus")
    {
        if ($status == 1) {
            $receipients = $receivers;
            // foreach ($receivers as $receiver) {
            //     $receipients .= $receiver . ",";
            // }
            // $receipients = substr($receipients, 0, -1);

            // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.africastalking.com/version1/messaging');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS,  "username=thinkxcloud" . "&to=" . urlencode($receipients) . "&message=" . urlencode($message));

            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Apikey: bb48eaa0bbb148854918bac1fb0577e1289725dd392978dd266837f9f2ec2d5b';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            return substr($result, 0, 4); // "1701" indicates success */
        }
    }


    
}

