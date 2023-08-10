<?php

include_once("pinib.php");
include_once("payments.php");
//include_once("loanb");


class MakePayments
{
    // private $usernam
    private $msisdn;
    private $pin;
    private $loanRef;
    private $loan;
    private $external_reference;
    private $db;
    private $table = "payments";
    private $payments;
    private $loan_table = "loan";
    private $loan_id;
    // private $narrative = "paying back a loan";




    public function __construct($msisdn, $mobile)
    {

        $this->loan = new loanb();
        $this->pin =  new pinb();
         $this->db = new Cursorb();
         $this->payments = new payments();
        $this->msisdn =  $msisdn;
        $rand = $this->pin->randomkey(10);
        $this->external_reference =  $rand;
        $this->loanRef =  $this->loan->getLatestUnpaidLoan($mobile)['loanRef'];
        $this->loan_id =  $this->loan->getLatestUnpaidLoan($mobile)['loanId'];
        

        
    }

 
    public function initPayment($amount, $narrative)
    {

        $new_ref =  time().rand(1000,9999);

        //update the loan ref
        try {
            $updated = $this->db->update($this->loan_table ,['loanRef'=>$new_ref], ['loanId'=>$this->loan_id]);
           
        } catch (\Throwable $th) {
            //throw $th;
            // var_dump($th->getMessage());
            // die("am here");
        }

        //insert
        $this->db->insert($this->table, ["external_ref" => $new_ref, 'msisdn' => $this->msisdn, 'amount' => $amount,  'narrative' => $narrative]);
        //insert

        return $this->payments->deposit($amount,$new_ref ,$this->msisdn , $narrative);
    }

    private function formatPhoneNumber($msisdn){
        //remove the first 3 digit and replace them with a zero
        $msisdn = substr($msisdn, 3);
        $msisdn = "0".$msisdn;
        return $msisdn;
    }
}
