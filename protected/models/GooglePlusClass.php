<?php

define("GOOGLE_CONNECT_SUCCESS",   1);
define("GOOGLE_UNINSTANTIATED",    0);

class GooglePlusClass extends SocialMedia
{
    public $appname             = "googleplus";             # Application Name
    public $appcommonname       = "Google Plus";            # Application Common Name
    public $status              = GOOGLE_UNINSTANTIATED;    # Status of connecting to twitter user
    public $status_message      = "";                       # Message associated with the status
    public $client              = null;
    public $plus                = null;
    public $user_account        = null;
    public $latest_status       = null;
    public $fullname            = "";           # User's full name
    
    private $token              = null;
    private $googleplussession  = "googleplus_session";     # Unique date/time session name
    private $googleplusprofile  = "googleplus_profile";     # Unique profile session name
    private $googleplusstatus   = "googleplus_status";      # Unique status session name
    private $session_length     = 300;                      # 300 seconds is how long to keep information in the session
    
    public function __construct()
    {
        require_once LOCAL_LIBRARY_PATH.'googleplus/google-api-php-client/src/Google_Client.php';
        require_once LOCAL_LIBRARY_PATH.'googleplus/google-api-php-client/src/contrib/Google_PlusService.php';
        
        parent::__construct();
        $this->init();
    }
    
    public function init()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('ASSETT Broadcast');
        $this->plus = new Google_PlusService($this->client);
        
        $this->token = new Token($this->appname);
        if($this->token->has_token()) {
            $this->client->setAccessToken($this->token->get_latest_token($this->appname));
        }
        
        if($this->client->getAccessToken()) {
            $this->set_status(GOOGLE_CONNECT_SUCCESS);
        }
        $this->pull_relinfo();
    }
    
    public function create_permanent_access()
    {
        if (isset($_GET['code'])) {
            $this->client->authenticate($_GET['code']);
            $this->token = new Token("googleplus");
            if($this->token->has_token()) {
                $this->token->remove_tokens();
            }
            $this->token->token   = $this->client->getAccessToken();
            if(!$this->token->save()) {
                Yii::app()->user->setFlash("error","Could not save token: ".$this->token->get_error());
            }
        }
        else {
            Yii::app()->user->setFlash("error","Cannot create permenant credentials without Google's OAuth code.");
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
        $this->user_account = @$_SESSION[$this->googleplusprofile];
        $this->userid       = @$this->user_account["id"];
        $this->username     = @$this->user_account["displayName"];
        $this->fullname     = @$this->user_account["displayName"];
        return $this->user_account;
    }
    
    public function get_latest_status()
    {
        $this->update_user_account();
        $this->latest_status = @$_SESSION[$this->googleplusstatus];
        return $this->latest_status;
    }

    public function update_user_account()
    {
        if($this->has_clear_status()) {
            if(!isset($_SESSION[$this->googleplusprofile],$_SESSION[$this->googleplusstatus])) {
                $this->pull_user_account();
            }
            else if(isset($_SESSION[$this->googleplussession])) {
                $tsession = $_SESSION[$this->googleplussession];
                $current_time = time();
                # Last update was more than defined maximum length?
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
        $this->user_account = $this->plus->people->get('me');
        
        # Get Latest Activity
        $activities = $this->plus->activities->listActivities("me", "public");
        if(!empty($activities["items"])) {
            $this->latest_activity = $activities["items"][0];
        }
        
        $_SESSION[$this->googleplussession]     = time();
        $_SESSION[$this->googleplusprofile]     = $this->user_account;
        $this->latest_status    = array(
            "id"        => @$this->latest_activity["id"],
            "message"   => @$this->latest_activity["object"]["content"],
            "url"       => @$this->latest_activity["url"],
            "updated"   => @$this->latest_activity["updated"]
        );
        $_SESSION[$this->googleplusstatus]      = $this->latest_status;
    }

    public function create_flash_message()
    {
        Yii::app()->user->setFlash("normal","ASSETT Broadcast does not have permission to post to Google Plus. <a href='".$this->client->createAuthUrl()."'>Re-authorize with Google Plus</a>");
    }
    
    public function get_user_picture_path()
    {
        $imageurl = $this->user_account["image"]["url"];
        $imageurl = preg_replace("/\?sz=[0-9]+/","",$imageurl);
        return $imageurl;
    }
    
    public function getLoginUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    public function broadcast($msgobj)
    {
        $this->create_moment($msgobj->broadcastid, $msgobj->to_useraccount, $msgobj->message);
    }
    
    public function create_moment($broadcastid, $user,$message)
    {
        # This sample assumes a client object has been created.
        # To learn more about creating a client, check out the starter:
        #  https://developers.google.com/+/quickstart/php
        
        # This example shows how to create moment that does not have a URL.
        $moment_body = new Google_Moment();
        $moment_body->setType("http://schemas.google.com/AddActivity");
        $item_scope = new Google_ItemScope();
        $item_scope->setId("target-id-1");
        $item_scope->setType("http://schemas.google.com/AddActivity");
        $item_scope->setName("ASSET Broadcast Message #".$broadcastid);
        $item_scope->setDescription($message);
        // $item_scope->setImage("https://developers.google.com/+/plugins/snippet/examples/thing.png");
        $moment_body->setTarget($item_scope);
        $momentResult = $this->plus->moments->insert("me", 'vault', $moment_body);
        
        return $momentResult;
    }
}
