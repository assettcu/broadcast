<?php
/**
 * Mailer Class, creates mail and sends them
 * 
 * The purpose of this class is to centralize the email code into an object.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Auditions_Classes
 * @version     1.0.2
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class MailerObj
{
    /**
     * Test Send Confirmation Email
     * 
     * Sends a test email using custom email text.
     * 
     * @param   (string)    $email_text     The email text to parse and send.
     */
	public function test_send_confirmation_email($email_text="") 
	{
        # Had some issues with the default time zone, manually set it here
		date_default_timezone_set('America/Denver');
		
        # Import the PHPMailer class
		require_once('/mailer/class.phpmailer.php');
		
        # Load current user
		$user = new UserObj(Yii::app()->user->name);
		
        # Set up preamble to the email
		$email_text = "--------<br/>!! THIS IS A TEST RESERVATION EMAIL !!<br/>------------<br/>".$email_text;
        
        # Parse through keywords and replace them with the user's information (making the email "real" looking)
		/*$email_text = str_replace("[firstname]",$user->firstname,$email_text);
		$email_text = str_replace("[lastname]",$user->lastname,$email_text);
		$email_text = str_replace("[fullname]",$user->firstname." ".$user->lastname,$email_text);
		$email_text = str_replace("[slotdatetime]",date("l, F d, Y")." at ".date("g:i a"),$email_text);
		$email_text = str_replace("[reservationid]","123456789",$email_text);
		$email_text = str_replace("[auditiondate]",date("l, F d, Y"),$email_text);*/
		
		# We do it this way to render the HTML output to a buffer then include it
		ob_start();
		echo $email_text;
		$body = ob_get_contents();
		ob_end_clean();
		
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
		$mail->Password   = "fustaianizing homeothermal";     # SMTP account password
		$mail->SMTPSecure = 'ssl';                            # Make it SMTP secure using SSL

		# Custom from mailing header
		$mail->SetFrom('thtrdnce@colorado.edu', 'Department of Theatre Mainstage');
		$mail->AddReplyTo("thtrdnce@colorado.edu","Department of Theatre Mainstage");

        # Specific email subject
		$mail->Subject    = "Audition Slot Confirmation";

        # Include the message
		$mail->MsgHTML($body);

        # Setup the email to send to the current user
		$mail->AddAddress('thomas.dressler1@gmail.com', 'Thomas Dressler');
		
		# Send the email and log any errors
		if(!$mail->Send()) {
			$this->error = $mail->ErrorInfo;
		} else {
			$this->error = "";
		}
	}

    /**
     * Send Confirmation Email
     * 
     * Sends the confirmation email to the passed in email address with the specific
     * reservation information from the passed in reservation ID.
     * 
     * @param   (string)    $address        The email address to send the confirmation.
     * @param   (integer)   $reservationid  The reservation ID associated with this confirmation.
     */
	public function send_confirmation_email($address,$reservationid)
	{
        # Had some issues with the default time zone, manually set it here
		date_default_timezone_set('America/Denver');

        # Import the PHPMailer class
		require_once('/mailer/class.phpmailer.php');

        # Load up all the relevant information objects
		$reservation  = new ReservationObj($reservationid);
		$user         = new UserObj($reservation->username);
		$slot         = new SlotObj($reservation->slotid);
		$audition     = new AuditionObj($reservation->auditionid);
        
        # If audition has blank email text, load up the default email text
		if($audition->email_text=="") {
			$settings = new SettingsManager();
			$email_text = $settings->get_setting_value("default_email_text");
		} else {
			$email_text = $audition->email_text;
		}
	
        # Parse through keywords and replace them with the applicant's information
		$email_text = str_replace("[fullname]",$user->firstname." ".$user->lastname,$email_text);
		$email_text = str_replace("[slotdatetime]",$slot->get_slot_datetime("l, F d, Y")." at ".$slot->get_slot_datetime("g:i a"),$email_text);
		$email_text = str_replace("[reservationid]",$reservation->reservationid,$email_text);
		$email_text = str_replace("[auditiondate]",$slot->get_slot_datetime("l, F d, Y"),$email_text);

		# We do it this way to render the HTML output to a buffer then include it
		ob_start();
		echo $email_text;
		$body = ob_get_contents();
		ob_end_clean();

        # Create new mailer object and set its options
        $mail             = new PHPMailer();
        
        $mail->IsSMTP();                                      # telling the class to use SMTP
        $mail->SMTPDebug  = 1;                                # enables SMTP debug information (for testing)
                                                              // 1 = errors and messages
                                                              // 2 = messages only
        $mail->SMTPAuth   = true;                             # enable SMTP authentication
        $mail->Host       = "assett.colorado.edu";            # sets the SMTP server
        $mail->Port       = 25;                               # set the SMTP port for the GMAIL server
        $mail->Username   = "auditions@assett.colorado.edu";  # SMTP account username
        $mail->Password   = "4ud1t10n5";                      # SMTP account password
        $mail->SMTPSecure = 'ssl';                            # Make it SMTP secure using SSL
        
        # Custom from mailing header
		$mail->SetFrom('thtrdnce@colorado.edu', 'Department of Theatre Mainstage');
		$mail->AddReplyTo("thtrdnce@colorado.edu","Department of Theatre Mainstage");

        # Specific email subject
		$mail->Subject    = "Audition Slot Confirmation";

        # Include the message
		$mail->MsgHTML($body);

        # Setup the email to send to the applicant
		$mail->AddAddress($address, $user->firstname." ".$user->lastname);

        # Send the email and log any errors
		if(!$mail->Send()) {
			$this->error = $mail->ErrorInfo;
		} else {
			$this->error = "";
		}
	}

	
	/**
     * Send Mass email 
     * 
     * Sends an email to multiple recipients.  Called by the broadcast APIController (this is part of an AJAX call).  
     * 
     * @param   (string)    $email_text     The email text to parse and send.
     */
	public function send_mass_email($message, $recipients, $are_hidden, $from, $full_name, $subject) 
	{
        # Had some issues with the default time zone, manually set it here
		date_default_timezone_set('America/Denver');
		
        # Import the PHPMailer class
		require_once('/mailer/class.phpmailer.php');
		
		$recipients = (array) $recipients;
		
		# We do it this way to render the HTML output to a buffer then include it
		ob_start();
		echo $message;
		$body = ob_get_contents();
		ob_end_clean();
		
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
		$mail->Password   = "fustaianizing homeothermal";     # SMTP account password
		$mail->SMTPSecure = 'ssl';                            # Make it SMTP secure using SSL

		# Custom from mailing header
		$mail->SetFrom($from, $full_name);
		$mail->AddReplyTo($from, $full_name);

        # Specific email subject
		$mail->Subject    = $subject;

        # Include the message
		$mail->MsgHTML($body);

		// Add addresses in the $recipients array, as BCC if specified by user.
		if($are_hidden) {
			foreach($recipients as $recipient) {
				$mail->AddBCC($recipient);
			}
		}
		else {
			foreach($recipients as $recipient) {
				$mail->AddAddress($recipient);
			}
		}
		
		# Send the email and log any errors
		if(!$mail->Send()) {
			$this->error = $mail->ErrorInfo;
		} else {
			$this->error = "";
		}
	}
}


?>