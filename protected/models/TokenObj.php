<?php

class TokenObj extends FactoryObj
{
    public $unpack_as_array = true;
    
    public function __construct($tokenid=null) 
    {
        parent::__construct("tokenid", "tokens", $tokenid);
    }
    
    public function pre_save()
    {
        $this->package("token");
        if(isset($_SESSION["CURRENT_DEPARTMENT"]) and !Yii::app()->user->isGuest and (!isset($this->accessid) or empty($this->accessid))) {
            $access = new AccessObj();
            $access->username = Yii::app()->user->name;
            $access->deptid = $_SESSION["CURRENT_DEPARTMENT"];
            $access->load();
            $this->accessid = $access->accessid;
        }
        if(!$this->loaded) {
            $this->date_created = date("Y-m-d H:i:s");
        }
        if(!isset($this->deptid) and isset($_SESSION["CURRENT_DEPARTMENT"])) {
            $this->deptid = $_SESSION["CURRENT_DEPARTMENT"];
        }
    }
    
    public function post_save()
    {
        $this->unpackage("token");
    }
    
    public function post_load()
    {
        $this->unpackage("token");
    }
    
    protected function package($mat)
    {
        if(isset($this->$mat)) {
            $this->$mat = json_encode($this->$mat);
        }
    }
    
    protected function unpackage($mat)
    {
        if(isset($this->$mat)) {
            $this->$mat = json_decode($this->$mat,$this->unpack_as_array);
        }
    }
    
    public function has_token($deptid=null,$method=null)
    {
        if(is_null($method) and isset($this->method)) {
            $method = $this->method;
        }
        
        if(is_null($deptid) and isset($_SESSION["CURRENT_DEPARTMENT"])) {
            $deptid = $_SESSION["CURRENT_DEPARTMENT"];
        }
        
        $conn = Yii::app()->db;
        $query = "
            SELECT      COUNT(*)
            FROM        {{tokens}}
            WHERE       method = :method
            AND         deptid = :deptid
            AND         username = :username
            ORDER BY    date_updated DESC;
        ";
        $username = Yii::app()->user->name;
        $command = $conn->createCommand($query);
        $command->bindParam(":method",$method);
        $command->bindParam(":deptid",$deptid);
        $command->bindParam(":username",$username);
        return $command->queryScalar();
    }
    
    public function get_latest_token($deptid=null, $method=null)
    {
        if(is_null($deptid) and isset($_SESSION["CURRENT_DEPARTMENT"])) {
            $deptid = $_SESSION["CURRENT_DEPARTMENT"];
        }
        if(is_null($method)) {
            $method = $this->method;
        }
        $conn = Yii::app()->db;
        $query = "
            SELECT      tokenid
            FROM        {{tokens}}
            WHERE       method = :method
            AND         deptid = :deptid
            AND         username = :username
            ORDER BY    date_updated DESC
            LIMIT       1;
        ";
        $username = Yii::app()->user->name;
        $command = $conn->createCommand($query);
        $command->bindParam(":method",$method);
        $command->bindParam(":deptid",$deptid);
        $command->bindParam(":username",$username);
        
        $this->tokenid = $command->queryScalar();
        $this->load();

        if($this->loaded) {
            return $this->token;
        }
        else {
            return null;
        }
    }
}
