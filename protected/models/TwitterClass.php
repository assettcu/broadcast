<?php

define("TWITTER_CONNECT_SUCCESS",   1);
define("TWITTER_UNINSTANTIATED",    0);

class TwitterClass extends SocialMedia
{
    public $appname             = "twitter";    # Application Name
    public $appcommonname       = "Twitter";    # Application Common Name
    public $user_account        = null;         # Object of user information
    public $numtweets           = 30;
    public $fullname            = "";           # User's full name
    
    private $token              = null;
    private $connection         = null;
    private $oauthtoken         = "83794711-u4gnITWHJ7T8FUATQrWMohcpGDjipiPGvKOYsha5W";
    private $accesstoken        = null;
    
    private $twittersession     = "twitter_session";
    private $twitterprofile     = "twitter_profile";
    private $twitterstatus      = "twitter_status";
    private $session_length     = 300;          # Length of session life
    
    protected $errorcodes       = array(
        0       => "Twitter class has not instantiated",
        32      => "Could not authenticate you",
        34      => "Sorry, that page does not exist",
        68      => "The Twitter REST API v1 is no longer active. Please migrate to API v1.1. https://dev.twitter.com/docs/api/1.1/overview",
        88      => "Rate limit exceeded",
        89      => "Invalid or expired token",
        64      => "Your account is suspended and is not permitted to access this feature",
        130     => "Over capacity",
        131     => "Internal error",
        135     => "Could not authenticate you",
        187     => "Status is a duplicate",
        215     => "Bad authentication data",
        231     => "User must verify login",
    );
    
    protected $shorthand       = array(
        "today"                     => "2day",
        "boyfriend"                 => "BF",
        "girlfriend"                => "GF",
        "away from keyboard"        => "AFK",
        "at the moment"             => "ATM",
        "your"                      => "ur",
        "you're"                    => "ur",
        "youre"                     => "ur",
        "because"                   => "b/c",
        "by the way"                => "BTW",
        "facebook"                  => "FB",
        "for the loss"              => "FTL",
        "for the win"               => "FTW",
        "for your information"      => "FYI",
        "great"                     => "gr8",
        "i don't know"              => "IDK",
        "in my opinion"             => "IMO",
        "in my humble opinion"      => "IMHO",
        "in real life"              => "IRL",
        "just kidding"              => "JK",
        "just for fun"              => "J4F",
        "laughing out loud"         => "lol",
        "no problem"                => "np",
        "not safe for work"         => "NSFW",
        "not safe for life"         => "NSFL",
        "safe for work"             => "SFW",
        "not SFW"                   => "NSFW",
        "not SFL"                   => "NSFL",
        "people"                    => "ppl",
        "retweet"                   => "RT",
        "tomorrow"                  => "tmrw",
        "talk to you later"         => "TTYL",
        "thank you"                 => "TY",
        "welcome back"              => "WB",
        "your milage may vary"      => "YMMV",
        "original poster"           => "OP",
        "ask me anything"           => "AMA",
        "ask me almost anything"    => "AMAA",
        "explain like i'm five"     => "ELI5",
        "explain like im five"      => "ELI5",
        "fixed that for you"        => "FTFY",
        "if i recall correctly"     => "IIRC",
        "in this thread"            => "ITT",
        "today i learned"           => "TIL",
        "too long didn't read"      => "TL;DR",
        "too long; didn't read"     => "TL;DR",
        "does anyone else"          => "DAE",
        "mainstream media"          => "MSM",
        "you only live once"        => "#yolo",
    );
    
    public function __construct()
    {
        require_once(LOCAL_LIBRARY_PATH."twitter/twitteroauth-master/config.php");
        require_once(LOCAL_LIBRARY_PATH."twitter/twitteroauth-master/twitteroauth/twitteroauth.php");
        
        parent::__construct();
        
        $this->init();
    }
    
    public function init()
    {
        $this->token = new Token($this->appname);
        if($this->token->loaded) {
            $this->create_twitter_access();
        }
        $this->pull_relinfo();
    }
    
    protected function pull_relinfo() 
    {
        $this->get_user_profile();
        $this->get_latest_tweet();
    }

    public function get_user_profile()
    {
        $this->update_profile();
        $this->user_account = @$_SESSION[$this->twitterprofile];
        $this->userid       = @$this->user_account->id_str;
        $this->username     = @$this->user_account->screen_name;
        $this->fullname     = @$this->user_account->name;
        return $this->user_account;
    }
    
    public function get_latest_tweet()
    {
        $this->update_profile();
        $this->latest_status = @$_SESSION[$this->twitterstatus];
        return $this->latest_status;
    }
    
    public function update_profile()
    {
        if($this->has_clear_status()) {
            if(!isset($_SESSION[$this->twitterprofile],$_SESSION[$this->twitterstatus])) {
                $this->pull_user_account();
            }
            else if(isset($_SESSION[$this->twittersession])) {
                $tsession = $_SESSION[$this->twittersession];
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
        $params = array(
            "include_entities"  => false,
        );
        $this->user_account = $this->connection->get("https://api.twitter.com/1.1/account/verify_credentials.json",$params);
        $params = array(
            "count"     => 1
        );
        $this->latest_tweet = $this->connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json",$params);
        $_SESSION[$this->twittersession]    = time();
        $_SESSION[$this->twitterprofile]    = $this->user_account;
        
        $this->latest_status    = array(
            "id"        => @$this->latest_tweet[0]->id_str,
            "message"   => @$this->latest_tweet[0]->text,
            "url"       => "//twitter.com/".@$this->user_account->screen_name."/status/".@$this->latest_tweet[0]->id_str,
            "updated"   => @$this->latest_tweet[0]->created_at
        );
        $_SESSION[$this->twitterstatus] = $this->latest_status;
    }
    

    public function create_flash_message()
    {
        Yii::app()->user->setFlash("normal","ASSETT Broadcast does not have permission to post to Twitter. <a href='".$this->get_temp_access_url()."'>Re-authorize with Twitter</a>");
    }
    
    public function get_temp_access_url()
    {
        /* Build TwitterOAuth object with client credentials. */
        $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
         
        /* Get temporary credentials. */
        $request_token = $connection->getRequestToken(TWITTER_OAUTH_CALLBACK);
        
        /* Save temporary credentials to session. */
        $_SESSION['oauth_token']        = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
         
        $url = $connection->getAuthorizeURL($token);
        return $url;
    }

    public function create_permanent_access()
    {
        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        
        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        
        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $this->token = new Token("twitter");
        if($this->token->has_token()) {
            $this->token->remove_tokens();
        }
        $this->token->token   = $access_token;
        
        if($this->token->save()) {
            /* Remove no longer needed request tokens */
            unset($_SESSION['oauth_token']);
            unset($_SESSION['oauth_token_secret']);
            return true;
        }
        else {
            Yii::app()->user->setFlash("error","Could not save token: ".$this->token->get_error());
        }
        return false;
    }

    public function create_twitter_access()
    {
        $access_token = $this->token->get_latest_token();
        
        /* Create a TwitterOauth object with consumer/user tokens. */
        $this->connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        
        $this->set_status(TWITTER_CONNECT_SUCCESS);
    }
    
    private function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) 
    {
        $connection = new TwitterOAuth($cons_key, $cons_secret,$oauth_token,$oauth_token_secret);
        return $connection;
    }
    
    public function broadcast($msgobj)
    {
        return $this->post_tweet($msgobj->message);
    }
    
    public function post_tweet($message)
    {
        if(!$this->conditions_met($message)) {
            $this->meet_conditions($message);
        }
        $content = $this->connection->post("statuses/update",array("status"=>$message));
        if(!empty($content->errors)) {
            $this->set_status($content->errors[0]->code * -1);
        }
        
        return $content;
    }
    
    public function getLoginUrl()
    {
        return $this->get_temp_access_url();
    }
    
    /**
     * Get User Picture Path
     * 
     * Returns the picture path for pulling. (Overloaded from SocialMedia class)
     * 
     * @return  (string)
     */
    public function get_user_picture_path()
    {
        return @$this->user_account->profile_image_url_https;
    }
    
    public function conditions_met($msgobj) 
    {
        return (strlen($msgobj->message)<=140);
    }
    
    public function meet_conditions($msgobj)
    {
        return substr($msgobj->message,0,140);
    }
    
}
