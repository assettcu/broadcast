<?php

class AccessObj extends FactoryObj
{
    
    public function __construct($accessid=null)
    {
        parent::__construct("accessid", "dept_access", $accessid);
    }
    
    public function pre_load()
    {
        if(!$this->is_valid_id() and isset($this->deptid,$this->username)) {
            $conn = Yii::app()->db;
            $query = "
                SELECT      accessid
                FROM        {{".$this->table."}}
                WHERE       deptid = :deptid
                AND         username = :username;
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":deptid",$this->deptid);
            $command->bindParam(":username",$this->username);
            $this->accessid = $command->queryScalar();
        }
    }
    
}
