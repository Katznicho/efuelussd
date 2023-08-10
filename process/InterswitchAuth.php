<?php

class InterswitchAuth{

/*
   Created by Henry Nkuke on 17/11/2019
   Contact:+256705596470
*/

function __construct()
    {
    $this->TIMESTAMP = "TIMESTAMP";
	$this->NONCE = "NONCE";
	$this->SIGNATURE_METHOD = "SIGNATURE_METHOD";
	$this->SIGNATURE = "SIGNATURE";
	$this->AUTHORIZATION = "AUTHORIZATION";
	$this->AUTHORIZATION_REALM = "InterswitchAuth ";
	$this->ISO_8859_1 = "ISO-8859-1";
}

	public function generateInterswitchAuth(
		$httpMethod, 
		$resourceUrl,
		$clientId,
		$clientSecretKey,
		$additionalParameters,
		$signatureMethod
	  ) {

		date_default_timezone_set('Africa/Kampala');
    // Timestamp must be in seconds.
		$timestamp = time();
		$nonce = str_replace("-", "", uniqid());
		$clientIdBase64 = base64_encode($clientId);
		$authorization = $this->AUTHORIZATION_REALM.$clientIdBase64;
		$encodedResourceUrl=urlencode(iconv('UTF-8', $this->ISO_8859_1, $resourceUrl));
		$signatureCipher = $httpMethod."&".$encodedResourceUrl."&".$timestamp."&".$nonce ."&".$clientId."&".$clientSecretKey;
		//append additional parameters if available
		if($additionalParameters != null && $additionalParameters!="")
                $signatureCipher = $signatureCipher."&".$additionalParameters;
//echo $signatureCipher."this is the second";
		//pay attention to this in relation your signature method..sha256,change to fit yours
		//$signatureBytes=hash('sha256',implode("",unpack("C*",$signatureCipher)),true);

		$signatureBytes=hash('sha256',$signatureCipher,true);
		// encode signature as base 64 
		$signature = base64_encode($signatureBytes);
//		$interswitchAuth=array(
//			$this->AUTHORIZATION=>$authorization,
//			$this->TIMESTAMP =>strval($timestamp),
//			$this->NONCE =>$nonce,
//			$this->SIGNATURE_METHOD=>$signatureMethod,
//		    $this->SIGNATURE=>$signature
//		);
                $interswitchAuth=array();
                $interswitchAuth["authorization"]=$authorization;
                $interswitchAuth["nonce"]=$nonce;
                $interswitchAuth["signature"]=$signature;
                $interswitchAuth["timestamp"]=strval($timestamp);
                $interswitchAuth["cypher"]=$signatureCipher;
                

		return  $interswitchAuth;

	}

   

}

?>

