<?php

class MessageObj extends FactoryObj 
{
    public function __construct($messageid=null)
    {
        parent::__construct("msgid", "messages", $messageid);
    }    
    
    public function pre_save()
    {
        if(!$this->loaded) {
            $this->date_created = date("Y-m-d H:i:s");
            if($this->status == 1 and !$this->is_valid_id()) {
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
    
    protected function package($mat)
    {
        if(isset($this->$mat)) {
            $this->$mat = json_encode($this->$mat);
        }
    }
    
    protected function unpackage($mat)
    {
        if(isset($this->$mat)) {
            $this->$mat = json_decode($this->$mat,true);
        }
    }
    
    
    public function conditions_met() 
    {
        if(isset($this->method)) {
            $func = "conditions_met_".$this->method;
            if(method_exists("MessageObj",$func)) {
                return $this->$func();
            }
        }
    }
    
    public function meet_conditions()
    {
        if(isset($this->method)) {
            $func = "meet_conditions_".$this->method;
            if(method_exists("MessageObj",$func)) {
                return $this->$func();
            }
        }
    }
    // Fourwinds
    public function conditions_met_fourwinds()
    {
        if(json_decode($this->message) == null) {
            return false;
        }
        return true;
    }
    
    public function meet_conditions_fourwinds() 
    {
        $broadcast = new BroadcastObj($this->broadcastid);
        
        $rss = new RSSClass();
        $rss->title = "ASSETT Fourwinds Broadcast #".$this->broadcastid;
        $rss->description = $this->message;
        $rss->link = "//compass.colorado.edu/broadcast/fourwinds?dept=".$broadcast->deptid;
        $rss->pubDate = (isset($msgobj->date_created)) ? $this->date_created : date("Y-m-d H:i:s");
        
        $this->message = $rss->get_formatted_data();
        return true;
    }
	
	// Mail
	public function conditions_met_mail()
    {
		// addresses
        $recipients = (array)$this->metadata["receivers"];
		if(empty($recipients)) return false;
		foreach($recipients as $recipient) {
			if(!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/', $recipient)) return false;
		}      
		
		// body
		if(empty($this->message) || !is_string($this->message)) return false;
		
		// subject
		if(empty($this->metadata["subject"]) || !is_string($this->metadata["subject"])) return false;	
				
		return true;
    }
    
    public function meet_conditions_mail() 
    {
        return true;
    }
	
}
