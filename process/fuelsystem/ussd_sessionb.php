<?php

// defined('BASEPATH') || exit('No direct script access allowed');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("cursorb.php");
// include("cursor.php");
class ussd_sessionb {

    /** @var string Name of the table. */
    protected $table_name = 'ussd_session';
    private $db;
   public function __construct() {
       $this->db= new Cursorb();

    }
    // protected $soft_deletes = true;
    // protected $log_user = false;

    /**
     * Constructor
     *
     * @return void
     */
//    // public function __construct()
//
//    // {
//    //     parent::__construct();
//    // }
    // public function delete($id = 0)
    // {
    //     $result = parent::delete($id);
    //     return $result;
    // }
    // public function find($id = null)
    // {
    //     return parent::find($id);
    // }
    // public function find_all()
    // {
    //     return parent::find_all();
    // }

    public function insert($data = array()) {
   
        $table = $this->table_name;
        $id = $this->db->insert($table, $data);

        return $id;
    }

    public function update($data = array(), $id) {

        
        $table = $this->table_name;
        if (empty($id)) {
            return null;
        }

        $result = $this->db->update($table, $data, ["transaction_id" => $id]);

        return $result;
    }

    public function getByTransactionId($transactionId) {

        if (empty($transactionId)) {
            return null;
        }
    
        $table = $this->table_name;

        $result = $this->db->likeSelect($table, ["last_usercode"], ["transaction_id" => $transactionId, "deleted" => 0]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["last_usercode"];
            }
            return $user_session;
        }
    }

    public function getdata1($transactionId) {

        if (empty($transactionId)) {
            return null;
        }
    
        $table = $this->table_name;

        $result = $this->db->likeSelect($table, ["data1"], ["transaction_id" => $transactionId, "deleted" => 0]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["data1"];
            }
            return $user_session;
        }
    }

    public function getdata2($transactionId) {

        if (empty($transactionId)) {
            return null;
        }
    
        $table = $this->table_name;

        $result = $this->db->likeSelect($table, ["data2"], ["transaction_id" => $transactionId, "deleted" => 0]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["data2"];
            }
            return $user_session;
        }
    }

    public function databysessionid($transactionId) {
  
        $table = $this->table_name;
        $result = $this->db->likeSelect($table, [], ["transaction_id" => $transactionId, "deleted" => 0]);
        return $result;
    }

    public function getdata3($transactionId) {

        if (empty($transactionId)) {
            return null;
        }
     
        $table = $this->table_name;

        $result = $this->db->likeSelect($table, ["data3"], ["transaction_id" => $transactionId, "deleted" => 0]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["data3"];
            }
            return $user_session;
        }
    }
    public function getalldata($transactionId) {

        if (empty($transactionId)) {
            return null;
        }
      
        $table = $this->table_name;

        $result = $this->db->likeSelect($table,NULL, ["transaction_id" => $transactionId, "deleted" => 0]);
          if (empty($result)) {
            return null;
        } else {
           
            return $result;
        }
    }

    public function delete($id) {


        $table = $this->table_name;
        if (empty($id)) {
            return null;
        }

        $result = $this->db->update($table, ["deleted" => 1], ["transaction_id" => $id]);

        return $result;
    }
    public function writeResponse($msg, $isend = false) {
        $resp_msg = 'responseString=' . urlencode($msg);
        if ($isend)
            $resp_msg .= '&action=end';
        else
            $resp_msg .= '&action=request';
        echo $resp_msg;
    }

}


?>
