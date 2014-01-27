<?php
/**
 * Facebook Class
 * 
 * This abstracts connecting to Facebook, reauthentication, posting, reading posts, and various Facebook functions.
 */
 
define("FB_CONNECT_SUCCESS",1);
define("FB_NOT_LOGGEDIN",0);
define("FB_CANNOT_GETPROFILE",-1);
define("FB_CANNOT_POST",-2);

class FacebookClass extends SocialMedia
{   
    public $appname             = "facebook";   # Application Name
    public $appcommonname       = "Facebook";   # Application Common Name
    public $status              = 0;            # Status of connecting to facebook user
    public $status_message      = "";           # Message associated with the status
    public $fbuser              = null;         # Facebook user ID
    public $user_account        = array();      # Array of profile information for Facebook user
    public $latest_status       = null;
    public $fullname            = "";           # User's full name
    
    private $app_permissions    = array(        # Permissions app requires from Facebook user
        'scope'         => 'read_stream,publish_stream,publish_actions',
    );
    private $facebook           = null;         # Facebook SDK object
    private $facebooksession    = "facebook_session";
    private $facebookprofile    = "facebook_profile";
    private $facebookstatus     = "facebook_status";
    private $session_length     = 300;          # Length of session life
    
    protected $errorcodes         = array(
        0      => "Facebook class has not instantiated",
        1      => "ASSETT Broadcast does not have permission to post to Facebook.",
        2      => "Could not connect with Facebook's servers. They might be unavailable currently.",
        3      => "Could not authenticate.",
        4      => "Could not pull Facebook user profile information. You may need to reauthenticate.",
        5      => "Could not post message to Facebook user wall.",
    );
    
    public function __construct()
    {
        require_once(LOCAL_LIBRARY_PATH."facebook/facebook-php-sdk-master/src/facebook.php");
        require_once(LOCAL_LIBRARY_PATH."facebook/facebook-php-sdk-master/src/config.php");
        
        parent::__construct();
        $this->init();
    }
    
    public function init()
    {
        $this->facebook = new Facebook($this->get_config());
        $this->fbuser = $this->facebook->getUser();
        
        if ($this->fbuser) {
            $this->set_status(FB_CONNECT_SUCCESS);
            $this->pull_relinfo();
        }
        # else : the user is not logged in
        else {
            $this->set_status(FB_NOT_LOGGEDIN);
        }
    }
    
    protected function pull_relinfo() 
    {
        $this->get_user_profile();
        $this->get_latest_status();
    }
    
    public function get_user_profile()
    {
        $this->update_user_account();
        $this->user_account = @$_SESSION[$this->facebookprofile];
        $this->username     = $this->user_account["username"];
        $this->fullname     = $this->user_account["name"];
        return $this->user_account;
    }
    
    public function get_latest_status()
    {
        $this->update_user_account();
        $this->latest_status = @$_SESSION[$this->facebookstatus];
        return $this->latest_status;
    }
    
    public function update_user_account()
    {
        if($this->has_clear_status()) {
            if(!isset($_SESSION[$this->facebookprofile],$_SESSION[$this->facebookstatus])) {
                $this->pull_user_account();
            }
            else if(isset($_SESSION[$this->facebooksession])) {
                $tsession = $_SESSION[$this->facebooksession];
                $current_time = time();
                # Last update was more than 15 minutes ago?
                if(($current_time - $tsession) > ($this->session_length)) {
                    $this->pull_user_account();
                }
            }
            else {
                $this->pull_user_account();
            }
        }
    }
    
    public function pull_user_account()
    {
        # Get User Account
        try {
            $this->user_account = $this->facebook->api('/me');
        } 
        catch (FacebookApiException $e) {
            $this->set_status(FB_CANNOT_GETPROFILE);
            $this->status_message = $e->getMessage();
            $this->user_account = null;
            return;
        }
        
        # Get Latest Status
        $ret_obj = $this->facebook->api("/me/statuses?limit=1");
        if(isset($ret_obj["data"]) and !empty($ret_obj["data"])) {
            $status = @$ret_obj["data"][0];
        }
        
        $_SESSION[$this->facebooksession]     = time();
        $_SESSION[$this->facebookprofile]     = $this->user_account;
        
        $this->latest_status    = array(
            "id"        => @$status["id"],
            "message"   => @$status["message"],
            "url"       => "//facebook.com/".$this->user_account["username"]."/posts/".$status["id"],
            "updated"   => @$status["updated"]
        );
        $_SESSION[$this->facebookstatus]      = $this->latest_status;
    }
    
    private function get_config()
    {
        return array(
            "appId"     			=>  FB_APP_ID,
            "secret"    			=>  FB_CONSUMER_SECRET,
            "sharedSession"			=> false,
		    'fileUpload' 			=> false,
		    'allowSignedRequest' 	=> false,
        );
    }
    
    public function create_flash_message()
    {
        Yii::app()->user->setFlash("facebook",$this->get_status_error()." <a href='".$this->facebook->getLoginUrl($this->app_permissions)."'>Re-authorize with Facebook</a>");
    }
    
    public function broadcast($msgobj) {
        return $this->post_message($msgobj->to_useraccount,$msgobj->message);
    }
    
    public function post_message($towho,$message)
    {
        # If the user isn't loaded, authenticated, and allowed then return false
        if(!$this->has_clear_status()) {
            return false;
        }
        
        # Try posting to facebook wall
        try {
            $msgcontainer = array(
                'message'   => $message,
            );
            $ret_obj = $this->facebook->api(
                '/'.$towho.'/feed', 
                'POST',
                $msgcontainer
            );
            BroadcastObj::create("facebook", $towho, $this->user_account["id"], $message, $ret_obj);
        } 
        catch(FacebookApiException $e) {
            $this->set_status(FB_CANNOT_POST);
            $this->status_message = $e->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function get_user_picture_path()
    {
        return "https://graph.facebook.com/".$this->user_account["id"]."/picture?type=large";
    }
    
    public function disconnect()
    {
        $token = new Token("facebook");
        $token->delete();
        $this->facebook->destroySession();
    }
    
    public function getLoginUrl()
    {
        return $this->facebook->getLoginUrl($this->app_permissions);
    }
}
