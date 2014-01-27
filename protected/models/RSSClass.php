<?php

class RSSClass {
    
    public $msgobj;
    
    public function __construct($messageObj=null) 
    {
        $this->msgobj = $messageObj;
        if(!is_null($this->msgobj) and $this->msgobj->loaded) {
            $this->process($this->msgobj->message);
        }
    }
    
    private function set_error($message) {
        $this->status = -1;
        $this->error_msg = $message;
        return false;
    }
    
    public function render()
    {
        ob_start();
        echo "<item>";
        echo "<title>".$this->title."</title>";
        echo "<description>".$this->description."</description>";
        echo "<link>".$this->link."</link>";
        echo "<pubDate>".$this->pubDate."</pubDate>";
        echo "</item>";
        $contents = ob_get_contents();
        ob_end_clean();
        
        return print $contents;
    }
    
    public function process($data) {
        $data = @json_decode($data,true);
        if(is_array($data)) {
            $required = array("title","description","link","pubDate");
            $keys = array_keys($data);
            if(count(array_intersect($required, $keys)) != count($required)) {
                return $this->set_error("Malformed RSS Feed message.");
            }
            foreach($data as $index=>$val) {
                $this->$index = $val;
            }
        }
    }
    
    public function get_formatted_data()
    {
        if(isset($this->title,$this->description,$this->link,$this->pubDate)) {
            $data = array(
                "title" => $this->title,
                "description"   => $this->description,
                "link" => $this->link,
                "pubDate" => $this->pubDate,
            );
            return json_encode($data);
        }
        return json_encode(array());
    }
    
}
