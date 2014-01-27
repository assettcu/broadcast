<?php

class RSSFeeder {
    
    public $rss_items;
    
    public function load_rss($deptid, $default=5)
    {
        $this->dept = new DepartmentObj($deptid);
        if($this->dept->loaded) {
            $return = Functions::query_for_rss($this->dept->deptid,$default);
            foreach($return as $msgobj) {
                $this->rss_items[] = new RSSClass($msgobj);
            }
        }
    }
    
    public function load_fourwinds($deptid, $default=5)
    {
        $this->dept = new DepartmentObj($deptid);
        if($this->dept->loaded) {
            $useraccount = $this->dept->deptid;
            $return = Functions::query_for_fourwinds($useraccount,$default);
            foreach($return as $msgobj) {
                $this->rss_items[] = new RSSClass($msgobj);
            }
        }
    }
    
    public function render()
    {
        header("Content-Type: application/rss+xml; charset=ISO-8859-1");
        
        ob_start();
        echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
        echo '<rss version="2.0">';
        echo '<channel>';
        echo '<title>Fourwinds RSS Feed for '.@$this->dept->deptname.'</title>';
        echo '<link>http://compass.colorado.edu/broadcast/fourwinds?dept=5</link>';
        echo '<description>RSS Feed for Fourwinds</description>';
        echo '<language>en-us</language>';
        echo '<copyright>Copyright (C) '.date("Y").' ASSETT at the University of Colorado Boulder</copyright>';
     
        if(!empty($this->rss_items)) {
            foreach($this->rss_items as $rss_item) {
                $rss_item->render();
            }
        }
        else {
            echo '<item>';
            echo '<title>Feed currently empty</title>';
            echo '<description>Stay tuned for updates!</description>';
            echo '<link>';
        }
     
        echo '</channel>';
        echo '</rss>';
     
        $contents = ob_get_contents();
        ob_end_clean();
        
        return print $contents;
    }
    
}
