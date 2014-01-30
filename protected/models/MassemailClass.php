<?php

define("MASSEMAIL_CONNECTED",   1);

class MassemailClass extends SocialMedia
{
    public $auth_required       = 0;
    public $appname             = "email";    # Application Name
    public $appcommonname       = "Mass Email";    # Application Common Name
    
    public function __construct() {
        $this->set_status(MASSEMAIL_CONNECTED);
    }
}

?>