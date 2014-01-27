<?php

class DepartmentObj extends FactoryObj
{
    public function __construct($deptid=null)
    {
        parent::__construct("deptid", "departments", $deptid);
    }
    
    public function pre_save()
    {
        if(!$this->is_valid_id()) {
            if(!isset($this->appkey) or empty($this->appkey)) {
                do {
                    $this->appkey = $this->generate_appkey();
                } while($this->appkey_exists($this->appkey));
            }
            $parse = @parse_url($this->apphost);
            $this->apphost = (isset($parse['host'])) ? $parse['host'] : $this->apphost;
            $this->regdate = date("Y-m-d H:i:s");
        }
    }
    
    public function pre_load()
    {
        if(!$this->is_valid_id() and isset($this->appkey)) {
            $this->deptid = $this->load_deptid_from_appkey($this->appkey);
        } 
    }
    
    private function load_deptid_from_appkey($appkey) 
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      deptid
            FROM        {{departments}}
            WHERE       appkey = :appkey;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":appkey",$appkey);
        
        return $command->queryScalar();
    }
    
    public function user_has_access($username)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      COUNT(*)
            FROM        {{dept_access}}
            WHERE       deptid = :deptid
            AND         username = :username;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":deptid",$this->deptid);
        $command->bindParam(":username",$username);
        
        return ($command->queryScalar() != 0);
    }
 
    private function appkey_exists($appkey)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      COUNT(*)
            FROM        {{departments}}
            WHERE       appkey = :appkey
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":appkey",$appkey);
        return ($command->queryScalar()!=0);
    }
 
    private function generate_appkey($length=12)
    {
        $pool = "ABCDEF1234567890";
        $key  = "";
        
        for($a=0;$a<$length;$a++) {
            if($a%4==0 and $a!=0 and $a!=$length) {
                $key .= "-";
            }
            $key .= substr($pool,rand(0,strlen($pool)-1),1);
        }
        
        return $key;
    }
    
    public function load_broadcasts()
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      broadcastid
            FROM        {{broadcasts}}
            WHERE       deptid = :deptid
            ORDER BY    date_created DESC;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":deptid",$this->deptid);
        
        $result = $command->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $return = array();
        foreach($result as $row) {
            $return[] = new BroadcastObj($row["broadcastid"]);
        }
        
        return $return;
    }
    
    public function load_pending_broadcasts()
	{
        $conn = Yii::app()->db;
        $query = "
            SELECT      broadcastid
            FROM        {{broadcasts}}
            WHERE       deptid = :deptid
            AND         status = 0
            ORDER BY	date_created DESC;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":deptid",$this->deptid);
        
        return $command->queryAll();
	}
	
}
