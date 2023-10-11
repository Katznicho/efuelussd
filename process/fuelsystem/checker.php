<?php


include_once "cursorb.php";
class checker
{
    private $db;
    private $msisdn;
    private $table_boda;
    private $table_agent;
    public function __construct()
    {

        $this->db = new Cursorb();
        $this->msisdn = $_POST['phoneNumber'];
        $this->table_boda = "bodauser";
        $this->table_agent = "fuelagent";
    }
    public function checkbodaexist()
    {


        $count = $this->db->getRows($this->table_boda, ["bodaUserPhoneNumber"], ["bodaUserPhoneNumber" => $this->formatMobile($this->msisdn)]);




        if ($count) {
            return true;
        } else {
            return false;
        }
    }
    public function checkAgentexist()
    {
        $count = $this->db->getRows($this->table_agent, ["fuelAgentPhoneNumber"], ["fuelAgentPhoneNumber" => $this->formatMobile($this->msisdn)]);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    private function formatMobile($mobile)
    {
        $length = strlen($mobile);
        $m = '0';
        //format 1: +256752665888
        if ($length == 13) {
            return $m .= substr($mobile, 4);
        } elseif ($length == 12) {
            return $m .= substr($mobile, 3);
        } elseif ($length == 9) {
            return $m .= $mobile;
        } else {
            return $mobile;
        }
    }
}
