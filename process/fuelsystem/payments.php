<?php


class payments
{

    private  $deposit_url = 'https://wallet.ssentezo.com/api/deposit';
    private  $withdraw_url = 'https://wallet.ssentezo.com/api/withdraw';
    private  $username = "b7f4211a-24aa-4deb-a990-14dcc1f6b514";
    private  $password = "5ebb70e03f4b47bcfab0d87561ab5b8f";
    private $sucessRedirectLink = "http://boda.creditplus.ug/public_html/creditpluswebapp/views/payments/finished.php";



    public function deposit($amount, $reference, $phone, $reason="paying back a loan")
    {

        $payload = array(
            "msisdn" => $this->formatMobileLocal($phone),
            "amount" => $amount,
            "reason" => $reason,
            "externalReference" => $reference,
            "callback" =>$this->sucessRedirectLink,
            "currency" =>"UGX",
            "environment" =>"production",
        );
        $response = $this->sendRequest($payload, $this->deposit_url);
    
        return $response;
    }

    public function withdraw($amount, $reference, $phone, $reason="with drawing")
    {

        $payload = array(

        );
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
          CURLOPT_POSTFIELDS => $payload,
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic YjdmNDIxMWEtMjRhYS00ZGViLWE5OTAtMTRkY2MxZjZiNTE0OjVlYmI3MGUwM2Y0YjQ3YmNmYWIwZDg3NTYxYWI1Yjhm',
            'Cookie: PHPSESSID=611rckasb6rj6fs70g28cpcl3d'
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
