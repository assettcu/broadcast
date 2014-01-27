<?php

class SystemObj extends FactoryObj
{
    public $decode2array   = true;
    
    public function __construct($sysid=null) 
    {
        parent::__construct("sysid", "system", $sysid);
    }
    
    public static function create($type,$message,$details) 
    {
        $sysobj             = new SystemObj();
        $sysobj->type       = $type;
        $sysobj->message    = $message;
        $sysobj->details    = $details;
        return $sysobj->save();
    }
    
    public function pre_save()
    {
        if(!$this->loaded) {
            $this->username     = Yii::app()->user->name;
            $this->date_created = date("Y-m-d H:i:s");
        }
        
        $this->package();
    }
    
    public function post_save()
    {
        $this->unpackage();
    }
    
    public function post_load()
    {
        $this->unpackage();
    }
    
    private function package()
    {
        $this->details = json_encode($this->details);
    }
    
    private function unpackage()
    {
        $this->details = json_decode($this->details, $this->decode2array);
    }
    
}
