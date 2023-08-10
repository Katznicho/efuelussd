<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
class config
{

    private $username='creditplusdev';
    private $password='Test1234@';

    // public function __construct($username, $password, $baseUrl = null)
    // {
    //     parent::__construct($baseUrl);
    //     $this->username = $username;
    //     $this->password = $password;
    // }

    public function getAuthenticationHeader()
    {
        return "Basic " . $this->encodeBase64();
    }

    private function encodeBase64()
    {
        $userPass = $this->username . ":" . $this->password;
        return base64_encode($userPass);
    }

}


$info=new config();
echo $info->getAuthenticationHeader();

?>