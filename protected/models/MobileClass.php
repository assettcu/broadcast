<?php
 
class MobileClass 
{
    public $validated_carrier   = 0;
    public $error_flag          = 0;
    public $error_msg           = "";
    public $carrier             = "";
    public $carriers            = array(
        "tmobile"       => "@tmomail.net",
        "virgin"        => "@vmobl.com",
        "cingular"      => "@cingularme.com",
        "sprint"        => "@messaging.sprintpcs.com",
        "verizon"       => "@vtext.com",
        "nextel"        => "@messaging.nextel.com",
        "uscellular"    => "@email.uscc.net",
        "suncom"        => "@tms.suncom.com",
        "powertel"      => "@ptel.net",
        "at&t"          => "@txt.att.net",
        "alltel"        => "@message.alltel.com",
        "metropcs"      => "@MyMetroPcs.com"
    );
    
    public function set_carrier($carrier) 
    {
        $this->carrier = $carrier;
        $this->validate_carrier($this->carrier);
    }
    
    public function validate_carrier($carrier)
    {
        $this->validated_carrier = (array_key_exists($carrier, $this->carriers));
        return $this->validated_carrier;
    }
    
    public function send_text($phone,$message,$carrier=null)
    {
        if(is_null($carrier)) {
            $carrier = $this->carrier;
        }
        if(!$this->validate_carrier($carrier)) {
            return !$this->set_error("Carrier {".$carrier."} is not a valid phone carrier.");
        }
        if(empty($message) or strlen($message) >= 141) {
            return !$this->set_error("The message is too long, please limit to 141 characters.");
        }
        $phone = preg_replace("/[^0-9]/","",$phone);
        if(strlen($phone)!=10) {
            return !$this->set_error("The phone number should be 10 digits in length. eg. 123-444-5678");
        }
        $tophone = $phone.$this->carriers[$carrier];
        
        # Had some issues with the default time zone, manually set it here
        date_default_timezone_set('America/Denver');
        
        # Import the PHPMailer class
        require_once(LOCAL_LIBRARY_PATH.'/mailer/class.phpmailer.php');
        
        # Load current user
        $user = new UserObj(Yii::app()->user->name);
        
        # Create new mailer object and set its options
        $mail             = new PHPMailer();

        $mail->IsSMTP();                                      # telling the class to use SMTP
        $mail->SMTPDebug  = 1;                                # enables SMTP debug information (for testing)
                                                              // 1 = errors and messages
                                                              // 2 = messages only
        $mail->SMTPAuth   = true;                             # enable SMTP authentication
        $mail->Host       = "assett.colorado.edu";            # sets the SMTP server
        $mail->Port       = 25;                               # set the SMTP port for the GMAIL server
        $mail->Username   = "broadcast@assett.colorado.edu";  # SMTP account username
        $mail->Password   = "southernize1 avize";             # SMTP account password
        $mail->SMTPSecure = 'ssl';                            # Make it SMTP secure using SSL
        $mail->isHTML(false);                                 # Not sending HTML text

        # Custom from mailing header
        $mail->SetFrom('assett@colorado.edu', 'ASSETT Broadcast System');
        
        # Specific email subject
        $mail->Subject    = "";

        # Include the message
        $mail->Body       = $message;
        
        # Setup the email to send to the current user
        $mail->AddAddress($tophone,$phone);
        
        # Send the email and log any errors
        if(!$mail->Send()) {
            SystemObj::create("error", "Could not send text via email: ".$mail->ErrorInfo, $details);
            return !$this->set_error("Could not send text via email: ".$mail->ErrorInfo);
        }
        
        BroadcastObj::create("mobile", $phone, "assett@colorado.edu", $message);
        
        return true;
    }
    
    private function set_error($message) 
    {
        $this->error_flag   = 1;
        $this->error_msg    = $message;
        
        return true;   
    }
    
    private function clear_error()
    {
        $this->error_flag   = 0;
        $this->error_msg    = "";
    }
    
    public function get_error()
    {
        return $this->error_msg;
    }
}
