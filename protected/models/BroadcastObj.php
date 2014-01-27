<?php

class BroadcastObj extends FactoryObj
{    
    public function __construct($broadcastid=null)
    {
        parent::__construct("broadcastid","broadcasts",$broadcastid);
    }
    
    public function pre_save()
    {
        if(!$this->is_valid_id()) {
            $this->date_created = date("Y-m-d H:i:s");
            if(isset($this->approved_by) and !empty($this->approved_by) and $this->status == 1) {
                $this->date_approved = $this->date_created;
            }
        }
        if(isset($this->metadata) and !is_null($this->metadata) and !empty($this->metadata) and !is_string($this->metadata)) {
            $this->package("metadata");
        }
    }
    
    public function post_save()
    {
        if(isset($this->metadata) and !is_null($this->metadata) and !empty($this->metadata)) {
            $this->unpackage("metadata");
        }
    }
    
    public function create($method, $from_useraccount, $to_useraccount, $message, $metadata=null) 
    {
        if(!$this->is_valid_id()) {
            return $this->set_error("Broadcast must be loaded before saving new message.");
        }
        
        $msgobj                      = new MessageObj();
        $msgobj->broadcastid         = $this->broadcastid;
        $msgobj->method              = $method;
        $msgobj->from_useraccount    = $from_useraccount;
        $msgobj->to_useraccount      = $to_useraccount;
        $msgobj->message             = $message;
        $msgobj->metadata            = $metadata;
        $msgobj->username            = Yii::app()->user->name;
        
        return $msgobj->save();
    }
    
    public function load_media_messages()
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT      msgid
            FROM        {{messages}}
            WHERE       broadcastid = :broadcastid
            ORDER BY    method ASC;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":broadcastid",$this->broadcastid);
        
        $result = $command->queryAll();
        if(!$result or empty($result)) {
            return array();
        }
        
        $ret = array();
        foreach($result as $row) {
            $ret[] = new MessageObj($row["msgid"]);
        }
        return $ret;
    }
}
