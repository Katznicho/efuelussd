<?php


class payments
{

    private  $deposit_url = 'https://wallet.ssentezo.com/api/deposit';
    private  $withdraw_url = 'https://wallet.ssentezo.com/api/withdraw';
    private  $username = "611b26c4-2c6c-407c-ae24-c86029434b04";
    private  $password = "0b7062a15fc9a8417d25be8bcfde11ac";
    private $sucessRedirectLink = "http://207.154.233.21/efuelwebapp/views/payments/finished.php";



    public function deposit($amount, $reference, $phone, $reason = "paying back a loan")
    {

        $payload = array(
            "msisdn" => $this->formatMobileLocal($phone),
            "amount" => $amount,
            "reason" => $reason,
            "externalReference" => $reference,
            "callback" => $this->sucessRedirectLink,
            "currency" => "UGX",
            "environment" => "production",
        );
        $response = $this->sendRequest($payload, $this->deposit_url);

        return $response;
    }

    public function withdraw($amount, $reference, $phone, $reason = "with drawing")
    {

        $payload = array();
        $response = $this->sendRequest($payload, $this->withdraw_url);
        return $response;
    }


    private  function sendRequest($payload, $endpoint)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$payload,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic NjExYjI2YzQtMmM2Yy00MDdjLWFlMjQtYzg2MDI5NDM0YjA0OjBiNzA2MmExNWZjOWE4NDE3ZDI1YmU4YmNmZGUxMWFj',
                'Cookie: PHPSESSID=2mmajnr4lh0dkb11lnh5hopba6'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    

        return $response;
    }

    private function formatMobileLocal($mobile)
    {
        $length = strlen($mobile);
        $m = '0';
        //format 1: +256752665888
        if ($length == 13)
            return $m .= substr($mobile, 4);
        elseif ($length == 12) //format 2: 256752665888
            return $m .= substr($mobile, 3);
        elseif ($length == 10) //format 3: 0752665888

            return $mobile;
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }
}
