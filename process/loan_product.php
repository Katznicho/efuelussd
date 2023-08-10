<?php

class loa_product
{
    private $table1="loan_product";
    private $db;
    public function __construct() {
        $this->db=new Cursor();
    }

    public function addloanproduct($loan_product_name, $percentage, $payment_period, $payment_type, $loan_period)
    {
       $data=[];
       $data["loan_product_name"]=$loan_product_name;
       $data["percentage"]=$percentage;
       $data["payment_period"]=$payment_period;
       $data["payment_type"]=$payment_type;
       $data["loan_period"]=$loan_period;
       $result= $this->db->insert($this->table1,$data);
       if($result>0)
       {
           return TRUE;
       }
        else
        {
       FALSE;
       }
    }
    
    
    public function getLoanProduct($columns=[], $where=[])
    {
        $result= $this->db->select($this->table1,$columns,$where);
        if(is_array($result))
        {
            return $result;
        }
        else
        {
            return FALSE;
        }
    }
    public function updateProduct($data=[], $where=[])
    {
        $result= $this->db->update($this->table1, $data, $where);
        if($result==1)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}













?>