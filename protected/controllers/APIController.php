<?php
header('Access-Control-Allow-Origin: *');

class APIController extends Controller
{       

    public function actionBroadcast()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("username","media","message","appkey");
        $keys = array_keys($request);
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308);
        }
        
        $dept = new DepartmentObj();
        $dept->appkey = $request["appkey"];
        $dept->load();
        
        if(!$dept->loaded) {
            return RestUtils::sendResponse(310);
        }
        
        if($dept->regstatus == 0) {
            return RestUtils::sendResponse(312);
        }
        
        $broadcast              = new BroadcastObj();
        $broadcast->deptid      = $dept->deptid;
        $broadcast->message     = $request["message"];
        $broadcast->media       = $request["media"];
        $broadcast->metadata    = @$request["metadata"];    # Optional
        $broadcast->status      = 0;
        $broadcast->created_by  = $request["username"];
        
        if(!$broadcast->save()) {
            return RestUtils::sendResponse(311);
        }
        
        $mediums = explode(",",$broadcast->media);
        foreach($mediums as $medium) {
            $msgobj                 = new MessageObj();
            $msgobj->broadcastid    = $broadcast->broadcastid;
            $msgobj->method         = $medium;
            $msgobj->username       = $request["username"];
            $msgobj->message        = $request["message"];
            $msgobj->status         = $broadcast->status;
            if(!$msgobj->conditions_met()) {
                $msgobj->meet_conditions();
            }
            if(!$msgobj->save()) {
                # This will cascade delete all messages due to table foreign keys
                $broadcast->delete();
                return RestUtils::sendResponse(311);
            }
        }
        
        return RestUtils::sendResponse(200);
    }

	public function actionMassEmail()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
		$required = array("appkey", "user","metadata", "message", "broadcastid", "broadcast_status");
        $keys = array_keys($request);

		if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308);
        }
		
        // Email addresses and subject message are required 
		if(!isset($request["metadata"]["receivers"]) || !isset($request["metadata"]["subject"])) {
			return RestUtils::sendResponse(313);
		}	
		
        $dept = new DepartmentObj();
        $dept->appkey = $request["appkey"];
        $dept->load();
        
        if(!$dept->loaded) {
            return RestUtils::sendResponse(310);
        }
        
        if($dept->regstatus == 0) {
            return RestUtils::sendResponse(312);
        }
		
		// Save a message object
        $msgobj                 = new MessageObj();
        $msgobj->broadcastid    = $request["broadcastid"];
        $msgobj->method         = 'mail';
        $msgobj->username       = $request["user"]["username"];
        $msgobj->message        = $request["message"];
        $msgobj->status         = $request["broadcast_status"];
        $msgobj->metadata       = $request["metadata"];
		
        if(!$msgobj->conditions_met()) {
            return RestUtils::sendResponse(315);
        }
        if(!$msgobj->save()) {
            return RestUtils::sendResponse(311);
        }
		
		// Send the email to recipients
		$mail = new MailerObj();
		$mail-> send_mass_email($request["message"], $request["metadata"]["receivers"], $request["metadata"]["are_hidden"], $request["user"]["user_address"], $request["user"]["user_fullname"], $request["metadata"]["subject"]);
        
		return RestUtils::sendResponse(200);
    }
	
    public function actionRegister()
    {
        # Init
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        
        # Ensure required fields are sent
        $required = array("appkey");
        $keys = array_keys($request);
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); # Invalid Parameters Sent
        }
        
        # Load department based on appkey
        $dept = new DepartmentObj();
        $dept->appkey = $request["appkey"];
        $dept->load();
        
        if(!$dept->loaded) {
            return RestUtils::sendResponse(310); #AppKey Invalid
        }
        
        # Set registration status to true and save
        $dept->regstatus = 1;
        if(!$dept->save()) {
            return RestUtils::sendResponse(311); # Code Interrupt
        }
        
        # Return success status
        return RestUtils::sendResponse(200);
    }

    public function actionRegisterStatus()
    {
        # Init
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        
        # Ensure required fields are sent
        $required = array("appkey");
        $keys = array_keys($request);
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); # Invalid Parameters Sent
        }
        
        # Load department based on appkey
        $dept = new DepartmentObj();
        $dept->appkey = $request["appkey"];
        $dept->load();
        
        if(!$dept->loaded) {
            return print false;
        }
        
        return print ($dept->regstatus !=0);
    }

}
