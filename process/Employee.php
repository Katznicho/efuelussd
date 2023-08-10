<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// include("cursor.php");

/**
 * Employee controller
 */
class Employee
{

    private $db_table = "active_employees";
    private $db_table2 = "cp_employer";
    private $db_table3="loan";
    private $db;
    // private $db_table4="current_loans";
    /**
     * Constructor
     *
     * @return void
     */
//    // public function __construct()
//    // {
//
//    // $db=new DB();
//
//}
    
//    public function __construct() {
//        $this->db=new Cursor;
//        
//    }







    public function getByMobile($mobile)
    {
        // $this->db->where("mobile",$mobile);
        $db = new Cursor;

        $q = $db->getRows($this->db_table, ["mobile"], ["mobile" => $mobile]);


        if ($q > 0) {
            return $q;
            // print_r($q);
        }

        return null;
    }

    public function isActive($mobile)
    {
        // $this->db->where("mobile",$mobile);
        $db = new Cursor;

        $q = $db->getRows($this->db_table, ["mobile"], ["mobile" => $mobile, "Active" => 1]);


        if ($q > 0) {
            return $q;
            // print_r($q);
        }

        return null;
    }


    public function status($mobile)
    {
        // $this->db->where("mobile",$mobile);
        $db = new Cursor;

        $q = $db->getRows($this->db_table, ["mobile"], ["mobile" => $mobile, "status" => 1]);


        if ($q > 0) {
            return $q;
            // print_r($q);
        }

        return null;
    }

    public function getIdByMobile($mobile)
    {

        if (empty($mobile)) {
            return null;
        }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["id"], ["mobile" => $mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["id"];

            }
            return $user_session;
        }


    }

    //retrieve employee id by Mobile
    public function getEmployerIdByMobile($mobile)
    {

        if (empty($mobile)) {
            return null;
        }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["employer_id"], ["mobile" => $mobile]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["employer_id"];

            }
            return $user_session;
        }


    }

    public function getAdvanceLimit($mobile)
    {
        $employerId = $this->getEmployerIdByMobile($mobile);

        $db = new Cursor;
        $table = $this->db_table2;
        $result = $db->likeSelect($table, ["advance_limit"], ["employer_ID" => $employerId]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["advance_limit"];

            }
            return $user_session;
        }



    }
    public function getPayDay($mobile)
    {
        $employerId = $this->getEmployerIdByMobile($mobile);

        $db = new Cursor;
        $table = $this->db_table2;
        $result = $db->likeSelect($table, ["payday"], ["employer_ID" => $employerId]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["payday"];

            }
            return $user_session;
        }



    }
    public function employerStatus($mobile)
    {
        $employerId = $this->getEmployerIdByMobile($mobile);

        $db = new Cursor;
        $table = $this->db_table2;
        $result = $db->likeSelect($table, ["status"], ["employer_ID" => $employerId]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["status"];

            }
            return $user_session;
        }



    }
    public function employeeStatus($mobile)
    {
        // $employerId = $this->getEmployerIdByMobile($mobile);

        $db = new Cursor;
        $table = $this->db_table;
        $result = $db->likeSelect($table, ["status"], ["mobile" => $mobile]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["status"];

            }
            return $user_session;
        }

    }
    
    
    public function getTypeByMobile($mobile)
    {
       
         $db = new Cursor;
        $table = $this->db_table;
//        echo $table;
//        echo $mobile;
        $result= $db->likeSelect($table, ["limit_months"], ["mobile" =>$mobile]);
//        print_r($result);
         if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["limit_months"];
                return $user_session;

            }
            
        }
        
    }

    public function getSalaryByMobile($mobile)
    {

        if (empty($mobile)) {
            return null;
        }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["salary"], ["mobile" => $mobile]);

        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["salary"];

            }
            return $user_session;
        }


    }
    public function advanceLimitAmount($mobile)
    {

        $amount=0;
     $percent=$this->getAdvanceLimit($mobile);
     //echo $percent;
     
    $salary=$this->getSalaryByMobile($mobile);
//    echo $salary;
    $type= $this->getTypeByMobile($mobile);
//    echo $type;
    
        $amount=(($percent*$salary)/100)*$type;
        if($amount<=0)
        {
            return 0;
        }

        return $amount;


    }

    public function getTotalBorrowed($mobile)
    {

    $amount=0;
       
        // $db = new Cursor;

        // $q = $db->getRows($this->db_table4, ["amount_borrowed"], ["employee_id" => $employeeId]);

        $activation= new activation();
         $employeeId=$activation->getIdByMobile($mobile);

        $q=$activation->getrows($employeeId);
        if ($q > 0) {
//            return $q;
            // $result = $db->likeSelect($this->db_table4, ["amount_borrowed"], ["employee_id" =>$employeeId]);
//            print_r($result);
        $result=$activation->AdvancesByEployeeId($employeeId);
            if (empty($result))
            {
                return null;
            }
            else {
                foreach ($result as $session) {
                    $amount += $session["amount_borrowed"];
                   // return $amount;

                }

            }
//
        }

        return $amount;


    }
    public function insert($data)
    {
          $db=new Cursor;
        $table=$this->db_table3;      
        $id = $db->insert("loan", $data);
    //    print_r($data);
        return $id;
    }

    public function getEmployeeName($mobile)
    {

        if (empty($mobile)) {
            return null;
        }
        $db = new Cursor;
        $table = $this->db_table;

        $result = $db->likeSelect($table, ["full_name"], ["mobile" => $mobile]);
        // print_r($result);
        # this works very similar to the select except that it matches every whose name starts with pia..
        if (empty($result)) {
            return null;
        } else {
            foreach ($result as $session) {
                $user_session = $session["full_name"];

            }
            return $user_session;
        }


    }
    


    public function getListBorrowed($mobile)
    {

    $amount=0;
        $employeeId=$this->getIdByMobile($mobile);
        $db = new Cursor;

        $q = $db->getRows($this->db_table3, ["id","amount_borrowed"], ["employee_id" => $employeeId]);


        if ($q > 0) {

//            return $q;
            //$result = $db->likeSelect($this->db_table3, ["id","amount_borrowed","created_on"], ["employee_id" =>$employeeId]);
            $result =$db->query("SELECT * from loan where employee_id=$employeeId ORDER BY id DESC LIMIT 3");
//            print_r($result);

            if (empty($result))
            {
                return null;
            }
            else {

                // foreach ($result as $session) {
                //     $amount += $session["amount_borrowed"];
//                    
                    return $result;

                // }

            }
//
        }

       


    }
    public function Activate($data, $mobile)
    {
        
          $db=new Cursor;
            $table=$this->db_table; 
        if (empty($mobile)) {
            return null;
        }

        $result = $db->update($table, $data, ["mobile"=>$mobile]);
       
        return $result;
    }


 public function update($data, $mobile)
    {
        
          $db=new Cursor;
            $table=$this->db_table; 
        if (empty($mobile)) {
            return null;
        }

        $result = $db->update($table, $data, ["mobile"=>$mobile]);
       
        return $result;
    }



}


// $mobile="0772093837";
// // $mobile="0705382662";
//$employeei=new Employee();
// $mobile="0772093837";
// $data['Active'] = 0;
// $data['amount_borrowed'] = 3000;
// $data['employee_id'] =$employeei->getIdByMobile($mobile);
// $data['employer_id'] = $employeei->getEmployerIdByMobile($mobile);
// $data['send_charge'] = 0;
// $data['withdraw_charge'] = 0;
// $data['service_fee'] = 9;
// // $data['total_amount_due'] = 90;
//echo $employeei->getTypeByMobile($mobile);
//echo $employeei->getIdByMobile($mobile);

//echo $employeei->advanceLimitAmount($mobile);

?>