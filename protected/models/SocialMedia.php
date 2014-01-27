<?php

class SocialMedia
{
    public $auth_required       = 1;            # This application needs authentication
    public $username            = "";           # Username associated with this account
    public $userid              = "";           # User ID associated with this account
    public $appname             = "";           # Type of the application (eg. facebook)
    public $appcommonname       = "";           # Application's common name (eg. Facebook)
    public $status              = 0;            # Status of connecting to facebook user
    public $status_message      = "";           # Message associated with the status
    
    protected $errorcodes       = array();      # Array of possible error codes
    
    public function __construct()
    {
        # Start the session!    
        if(!isset($_SESSION)) {
            session_start();
        }
    }
    
    public function set_status($status)
    {
        $this->status = $status;
    }
    
    public function has_clear_status()
    {
        return ($this->status > 0);
    }
    
    public function has_error_status()
    {
        return ($this->status <= 0);
    }
    
    public function get_status_error()
    {
        # If a message has already been loaded, return that message
        if($this->status_message != "" or $this->status > 0) {
            return "";
        }
        
        # Iterate through states and return corresponding message
        if(array_key_exists($this->status*-1, $this->errorcodes)) {
            $return = $this->errorcodes[$this->status*-1];
        }
        else {
            $return = "Unknown error. (".$this->status.")"; break;
        }
        
        return $return;
    }
    
    protected function pull_relinfo()
    {
        # Overload this function
    }
    
    public function destroy()
    {
        # Nothing here yet
    }
    
    public function render_logo()
    {
        # Load new imager with facebook logo (pulled dynamically from our library files)
        $imager = new Imager(StdLib::make_path_local(StdLib::load_image_source($this->appname."_logo","")));
        
        # If a custom width was passed in then set it here
        if(func_num_args() == 1) {
            $width          = func_get_arg(0);
            $width          = str_replace("px","",$width);
            $imager->resize((int)$width);
        }
        
        $imager->add_attribute("align", "left");
        $imager->add_attribute("style", "padding-right:5px;");
        $imager->add_attribute("title", $this->appcommonname);
        
        # Render image
        $imager->render();
    }
    
    public function render_profile_picture()
    {
        if(!$this->has_profile_picture()) {
            $this->pull_profile_picture();
        }
        
        $imager = new Imager($this->get_profile_picture());
        
        # If a custom width was passed in then set it here
        if(func_num_args() == 1) {
            $width          = func_get_arg(0);
            $width          = str_replace("px","",$width);
            $imager->resize($width);
        }
        else if(func_num_args() == 2) {
            $width          = func_get_arg(0);
            $width          = str_replace("px","",$width);
            $height         = func_get_arg(1);
            $height         = str_replace("px","",$height);
            $imager->resize($width,$height);
        }
        $imager->add_attribute("title", $this->fullname);
        $imager->render();
    }
    
    public function render_profile_thumb()
    {
        if(!$this->has_profile_thumb()) {
            $this->pull_profile_picture();
        }
        
        $imager = new Imager($this->get_profile_thumb());
        
        # If a custom width was passed in then set it here
        if(func_num_args() == 1) {
            $width          = func_get_arg(0);
            $width          = str_replace("px","",$width);
            $imager->resize($width);
        }
        else if(func_num_args() == 2) {
            $width          = func_get_arg(0);
            $width          = str_replace("px","",$width);
            $height         = func_get_arg(1);
            $height         = str_replace("px","",$height);
            $imager->resize($width,$height);
        }
        $imager->add_attribute("title", $this->fullname);
        $imager->render();
    }
    
    public function pull_profile_picture()
    {
        $contents = file_get_contents($this->get_user_picture_path());
        $localfile = $this->get_profile_picture();
        file_put_contents($localfile, $contents);
        
        # Now create the thumb for this image
        $imager = new Imager($localfile);
        $thumbfile = $this->get_profile_thumb();
        $imager->crop($thumbfile,100);
    }
    
    /**
     * Get User Picture Path
     * 
     * Returns the picture path for pulling. This function needs to be overloaded.
     * 
     * @return  (string)
     */
    public function get_user_picture_path()
    {
        # Overload this function
        return "";
    }
    
    public function has_profile_picture()
    {        
        return is_file($this->get_profile_picture());
    }
    
    public function has_profile_thumb()
    {   
        return is_file($this->get_profile_thumb());
    }
    
    public function get_profile_picture()
    {
        $rel        =  $this->appname."/profile/".$this->username."/";
        $dir        = LOCAL_LIBRARY_PATH.$rel;
        if(!is_dir($dir)) {
            mkdir($dir);
        }
        $filename   = $this->username."_large.jpg";
        
        return $dir.$filename;
    }
    
    public function get_profile_thumb()
    {
        $rel        = $this->appname."/profile/".$this->username."/";
        $dir        = LOCAL_LIBRARY_PATH.$rel;
        if(!is_dir($dir)) {
            mkdir($dir);
        }
        $thumb      = $this->username."_thumb.jpg";
        
        return $dir.$thumb;
    }
    
    public function disconnect()
    {
        $token = new Token($this->appname);
        $token->delete();
    }
    
    public function getLoginUrl()
    {
        # This function will be overloaded
    }
    
    public function conditions_met($message)
    {
        # This function will be overloaded
        return true;
    }
    
    public function meet_conditions($message)
    {
        # This function will be overloaded
        return $message;
    }
}
