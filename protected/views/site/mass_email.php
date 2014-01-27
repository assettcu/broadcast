<?
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
$json_data = json_encode($data);
//var_dump($json_data)
$flashes = new Flashes;
$flashes->render();
?>

<div> 
	<h1> Broadcast a mass email.  Test uses Communications department app.</h1>
</div>
<form method="post">
    <input type="hidden" name="mass-email-test-form" />
    <div class="fancy-header">Email Content</div>
	<div class="posting-container">
        <div class="message-container" style="width:740px;float:left;">
            <textarea class="broadcast-message" name="broadcast-message" id="broadcast-message">Type your message here...</textarea>
        </div>
        <div style="text-align:right;position:absolute;top:13px;right:5px;">
            <a href="#" class="special-blue-button" id="broadcast-button">
                Broadcast Email!
            </a>
        </div>
        <br class="clear" />
    </div>
</form>
<div>
	<button id="test-api-broadcast">Test Broadcast API function</button>
<div>

<script> 
jQuery(document).ready(function($){

  $(document).on("click","#broadcast-button",function(){
	var data = <?php echo $json_data; ?>;
	// Validate message and recipients.
	if($('#broadcast-message').val() ===""||$("#broadcast-message").val()==="Type your message here...") {
		// Ajax function sets flash and reloads page
		$.ajax({
           "url":       "<?php echo Yii::app()->createUrl("_add_flash"); ?>",
           "type":      "post",
           "data":      "type=error&message=You must enter a message to be emailed.",
           "success":   function(data) {
               window.location.reload();
           }
        });
		return false;
	}
	// If no recipients given, set this property to null in controller.
	if(data["metadata"]["receivers"]==false) {
		$.ajax({
           "url":       "<?php echo Yii::app()->createUrl("_add_flash"); ?>",
           "type":      "post",
           "data":      "type=error&message=You must enter recipients for this message to be emailed.",
           "success":   function(data) {
               window.location.reload();
           }
        });
		return false;
	}
	// Looks good, so add the message to json object
	data['message'] = $('#broadcast-message').val();
	
	$.ajax({
		"url": 'https://compass.colorado.edu/broadcast/api/massemail',
		"type": 'POST',
		"data": data,
        
		"success":   function(data) {
            console.log(data);	
			$.ajax({
				"url":       "<?php echo Yii::app()->createUrl("_add_flash"); ?>",
				"type":      "post",
				"data":      "type=success&message=Your message was successfully sent!",
				"success":   function(data) {
					window.location.reload();
				}
			});
			return false;
		}
	});
	
  });

  $(document).on("click","#test-api-broadcast",function(){
    var data = <?php echo $json_data; ?>;
	console.log(data["metadata"]["receivers"]==false);
  });

});
 
 </script>