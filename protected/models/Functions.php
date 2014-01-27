<?php

class Functions {
 
    static function get_user_apps($username)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      deptid
            FROM        {{dept_access}}
            WHERE       username = :username;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":username",$username);
        $result = $command->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $return = array();
        foreach($result as $row) {
            $return[] = new DepartmentObj($row["deptid"]);
        }
        return $return;
    }
    
    static function query_for_rss($to_useraccount,$count)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      msgid
            FROM        {{messages}}
            WHERE       to_useraccount  = :to_useraccount
            AND         method          = :method
            AND         status          = :status;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":to_useraccount",$to_useraccount);
        $command->bindValue(":method","rss");
        $command->bindValue(":status",1);
        $result = $command->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $return = array();
        foreach($result as $row) {
            $return[] = new MessageObj($row["msgid"]);
        }
        return $return;
        
    }
   
    static function query_for_fourwinds($deptid,$count)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      msgid
            FROM        {{messages}}
            WHERE       broadcastid     = (SELECT broadcastid FROM broadcasts WHERE deptid = :deptid)
            AND         method          = :method
            AND         status          = :status;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":deptid",$deptid);
        $command->bindValue(":method","fourwinds");
        $command->bindValue(":status",1);
        $result = $command->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $return = array();
        foreach($result as $row) {
            $return[] = new MessageObj($row["msgid"]);
        }
        return $return;
        
    }
    
    static function get_deptid_from_name($deptname)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      deptid
            FROM        {{departments}}
            WHERE       deptname        LIKE :dept
            OR          deptcode        LIKE :dept;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":dept",$deptname);
        return $command->queryScalar();
    }
}