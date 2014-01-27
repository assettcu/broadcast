<?php

define("FOURWINDS_CONNECTED",   1);

class FourwindsClass extends SocialMedia
{
    public $auth_required       = 0;
    public $appname             = "fourwinds";    # Application Name
    public $appcommonname       = "FourWinds";    # Application Common Name
    
    public function __construct() {
        $this->set_status(FOURWINDS_CONNECTED);
    }
}
