<?php 

class Token extends TokenObj
{
    public function __construct($method=null)
    {
        $this->method   = $method;
        parent::__construct();
        $this->username = Yii::app()->user->name;
        if(isset($_SESSION["CURRENT_DEPARTMENT"])) {
            $dept = new DepartmentObj($_SESSION["CURRENT_DEPARTMENT"]);
            if($dept->user_has_access($this->username)) {
                $this->get_latest_token($dept->deptid);
            }
        }
    }
    
    public function remove_tokens()
    {
        $conn = Yii::app()->db;
        $query = "
            DELETE FROM     {{tokens}}
            WHERE           method = :method
            AND             username = :username;
        ";
        $transaction = $conn->beginTransaction();
        try {
            $command = $conn->createCommand($query);
            $command->bindParam(":method",$this->method);
            $command->bindParam(":username",$this->username);
            $command->execute();
            $transaction->commit();
        } 
        catch(Exception $e) {
            SystemObj::create("error", "Error deleting tokens from database.", $e);
            return false;
        }
        
        return true;
    }
}