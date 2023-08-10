<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loan
 *
 * @author MAT
 */
class loanb
{
    private $table;
    private $boder;

    private $db;
    public function __construct()
    {
        $this->db = new Cursorb();
        $this->table = "loan";
        $this->boder = "bodauser";
    }

    public function createloan($data)
    {
        $result = $this->db->insert($this->table, $data);
        if ($result > 0) {
            return $result;
        } else {
            return FALSE;
        }
    }

    public function updatebodastatus($bodanumber)
    {
        $data = [];
        $data["bodaUserStatus"] = 2;
        if (empty($bodanumber)) {
            return null;
        }

        $result = $this->db->update($this->boder, $data, ["bodaUserPhoneNumber" => $bodanumber]);

        return $result;
    }
    public function update($data, $id)
    {


        $table = $this->table_name;
        if (empty($id)) {
            return null;
        }

        $result = $this->db->update($table, $data, ["transaction_id" => $id]);

        return $result;
    }
    //put your code here

    public function getLatestUnpaidLoan($bodaNumber)
    {
        $sql = "SELECT * FROM loan WHERE boadUserId = $bodaNumber AND status=1 ORDER BY loanId DESC LIMIT 1";
        $result =  $this->db->query($sql);
        if (count($result)) {
            return $result[0];
        } else {
            return NULL;
        }
    }
     /*this function will querry the table of loan to get all total loans of,
     * a given petrol station
     */
    
    public function getAmountsumofallloansoffuelstaion($stationId)
    {
       
        $sql="select sum(loanAmount)TOTALAMOUNT from $this->table where fuelSationId = '$stationId'";
        $result =  $this->db->query($sql);
        if (count($result)) {
            return $result[0]["TOTALAMOUNT"];
        } else {
            return 0;
        }
    }
}
