<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
$flashes = new Flashes;
$flashes->render();
if(isset($data)) $json_data = json_encode($data);
else $json_data = "undefined";
//$_SESSION["facebook"] = $facebook;
?>

<style>
.hint {
    color:#999;
}
div.hint {
    float:left;
}
div.button-container {
    margin-top:10px;
    margin-left:25px;
}
div.button-container button {
    padding:3px;
    padding-left:10px;
    padding-right:10px;
}
textarea {
    font-family: Verdana, Geneva, sans-serif;
	width:100%;
	height:100px;
}
div.icon-header {
    margin-bottom:10px;
}
div.icon-header h1 {
    display:inline-block;
    width:950px;
    margin-left:10px;
}
div#email-options-form-container {
	display:none;
	width:100%;
}
input#subject-input-field{
	padding:5px;
	width:100%;
}
input.hidden-addresses {
	float:left;
	width:5%;
}
div#big-email-icon {
	margin-left:5%;
	display:inline-block;
	float:left;
}
div.email-fancy-header {
	background-color:#990099;
	font-weight:bold;
	color:white;
	height:35px;
	line-height: 35px;
}
div.email-fancy-header .email-fancy-header-text {

}
</style>

<div class="icon-header">
    <div class="icon-text-align-large"><?php echo StdLib::load_image("app-icon","48px"); ?></div>
    <h1>Department of <?php echo $dept->deptname; ?></h1>
</div>
        
<?php if($dept->regstatus == 0): ?>
<div class="ui-state-highlight ui-corner-all" style="padding:10px;margin-bottom:15px;">
    <div class="icon-text-align"><?php echo StdLib::load_image("warning","18px"); ?></div>
    <div style="display:inline-block;">
        Currently, the department is <strong>unregistered</strong>. You will need to complete registration by installing the front end on <i><?php echo $dept->apphost; ?></i>.<br/>
        You will need to use the appkey for this application: <strong><?php echo $dept->appkey; ?></strong>
    </div>
</div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="broadcast-message-form-submitted" />
    <div class="fancy-header">Broadcast a Message</div>
    <div class="posting-container">
        <div class="message-container" style="width:740px;float:left;">
            <textarea class="broadcast-message" name="broadcast-message" id="broadcast-message">Type message here...</textarea>
        </div>
        <div class="social-container" style="width:230px;float:right;margin-right:10px;margin-top:60px;">
            <?php foreach($social as $media): ?>
                <?php if($media->has_clear_status()) : ?>
                    <div class="social-button ui-corner-all" style="width:100%;border:1px solid #09f;padding:5px;margin-bottom:4px;">
                        <div style="margin:3px;display:inline-block;">
                            <?php $media->render_logo("16px"); ?> <?php echo $media->appcommonname; ?>
                        </div>
                        <div class="onoffswitch">
                            <input type="checkbox" name="onoffswitch-<?php echo @$media->appname; ?>" class="onoffswitch-checkbox" id="onoff-<?php echo @$media->appname; ?>" checked>
                            <label class="onoffswitch-label" for="onoff-<?php echo @$media->appname; ?>">
                                <div class="onoffswitch-inner"></div>
                                <div class="onoffswitch-switch"></div>
                            </label>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="social-button ui-corner-all disabled">
                        <div style="margin:3px;display:inline-block;">
                            <?php $media->render_logo("16px"); ?> <?php echo $media->appcommonname; ?>
                        </div>
                        <div style="float:right;margin-right:5px;padding-top:3px;">
                            Disconnected
                        </div>
                    </div>
                    
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div style="text-align:right;position:absolute;top:13px;right:5px;">
            <a href="#" class="special-blue-button" id="broadcast-button">
                Broadcast to the World!
            </a>
        </div>
		
	
		<div class="social-container" style="width:230px;float:right;margin-right:10px;">
                    <div class="social-button ui-corner-all" style="width:100%;border:1px solid #09f;padding:5px;margin-bottom:4px;">
                        <div style="margin:3px;display:inline-block;">
                            <?php $massemail->render_logo("16px"); ?> <?php echo $massemail->appcommonname; ?>
                        </div>
                        <div class="onoffswitch">
                            <input type="checkbox" name="on-off-<?php echo @$massemail->appname; ?>" class="onoffswitch-checkbox" id="onoff-<?php echo @$massemail->appname; ?>">
                            <label class="onoffswitch-label" for="onoff-<?php echo @$massemail->appname; ?>">
                                <div class="onoffswitch-inner"></div>
                                <div class="onoffswitch-switch"></div>
                            </label>
                        </div>
                    </div>
        </div>
		
        <br class="clear" />
    </div>
	<div id="email-options-form-container">	
		<?php $massemail->render_logo("40px"); ?>
		<input type="radio" class="hidden-addresses" name="hidden-addresses" value="bcc" checked><b>Hide recipient addresses (bcc).</b><br>
		<input type="radio" class="hidden-addresses" name="hidden-addresses" value="cc"><b>Leave addresses visible.</b><br><br> 
		<div class="email-fancy-header"><span class="email-fancy-header-text">&nbsp&nbspEmail Subject Text</span></div>
		<input type="text" name="subject-input-field" id="subject-input-field" value="Type subject here...">
		<div class="email-fancy-header"><span class="email-fancy-header-text">&nbsp&nbspReceiver Addresses</span></div>
		<textarea name="addresses-input-field" id="addresses-input-field">Type email addresses here, separated by commas...</textarea>
	</div>
</form>

<table class="fancy-table">
    <thead>
        <tr>
            <th colspan="5">
                Recent Broadcasts
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="header">
            <td width="170px" class="calign">Posted To</td>
            <td width="180px" class="calign">Approved?</td>
            <td>Status</td>
        </tr>
        <?php if(isset($broadcasts) and !empty($broadcasts)): ?>
            <?php foreach($broadcasts as $broadcast): ?>
        <tr>
            <td class="calign">
                <?php
                    $mediums = explode(",",$broadcast->media);
                    foreach($mediums as $medium) {
                        echo "<span style='padding:2px;' title='".$medium."'>";
                        echo StdLib::load_image($medium."_logo","20px");
                        echo "</span>";
                    }
                ?>
            </td>
            <td class="calign" broadcastid="<?php echo $broadcast->broadcastid; ?>">
                <?php 
                    switch($broadcast->status) {
                        case "-1": echo "<strong>Disapproved</strong>"; break;
                        case "0": echo "<strong>Pending Approval</strong><br/><span><a href='#' style='color:#0a0;' class='approve-broadcast' >approve</a></span> | <span><a href='#' style='color:#f00;' class='disapprove-broadcast'>disapprove</a></span>"; break;
                        case "1": echo "<strong>Approved</strong>"; break;
                        default: echo "Unknown"; break;
                    }
                ?>
            </td>
            <td>
                <i><?php echo $broadcast->created_by; ?></i> at <i><?php echo StdLib::format_date($broadcast->date_created, "normal"); ?></i><br/>
                <?php echo $broadcast->message; ?>
            </td>
        </tr>
            <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="3" class="nocontent">
                There are no recent broadcasts.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<table id="tableofconnections" class="fancy-table">
    <thead>
        <tr>
            <th colspan='3'>
                Social Media Connections
            </th>
        </tr>
    </thead>
    <?php foreach($social as $media): ?>
    <tr class="<?php echo $media->appname; ?>">
        <td width="150px">
            <div>
                <div class="flash-icon"><?php $media->render_logo("22px"); ?></div> 
                <div class="icon-text-align" style="padding-top:4px;"><?php echo $media->appcommonname; ?></div>
            </div>
        </td>
        <td width="130px">
            <?php if($media->auth_required==1): ?>
				<?php if($media->has_clear_status()): ?>					
     				<div class="flash-icon"><?php echo StdLib::load_image('disconnect','16px'); ?></div>
					<button id="disconnect-from-media" class="connection-media-button" name="<?php echo Yii::app()->createUrl('_disconnect'); ?>?method=<?php echo $media->appname; ?>" style="background:none;border:none;padding:0;border-bottom:1px;">disconnect</button>
                <?php else: ?>
					<div class="flash-icon"><?php echo StdLib::load_image('sync','16px'); ?></div>
					<button id="reconnect-to-media" class="connection-media-button" name="<?php echo $media->getLoginUrl(); ?>" style="background:none;border:none;padding:0;border-bottom:1px;">reconnect</button>
					
                <?php endif; ?>
            <?php else: ?>
                <div class="flash-icon"><?php echo StdLib::load_image("connected","20px"); ?></div>
                permaconnect
            <?php endif; ?>
        </td>
        <td>
            <?php if($media->auth_required==1): ?>
                <?php if($media->has_clear_status()): ?>
                <div style="position:relative;">
                    <div class="flash-icon"><?php $media->render_profile_thumb("40px"); ?></div>
                    <div class="icon-text-align abspos">Connected as 
                        <a href="<?php echo $media->username; ?>" target="_blank"><?php echo $media->fullname; ?></a><br/>
                        <!--<a href="<?php //echo $media->latest_status["url"]; ?>" class="latest-status-link" target="_blank">--->
                            <div class="icon-text-align" style="color:#999;">
                                <i>"<?php echo substr($media->latest_status["message"],0,90); ?><?php echo (strlen($media->latest_status["message"])>100) ? "...": ""; ?>"</i>
                            </div>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                    <div class="flash-icon"><?php echo StdLib::load_image("notokay","20px"); ?></div>
                    <i>Currently not connected</i>
                <?php endif; ?>
            <?php else: ?>
                This media requires no additional Authentication and is always connected.
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
   
<table id="adminactions" class="fancy-table">
    <thead>
        <tr>
            <th colspan='3'>
                Administrative Control
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="<?php echo Yii::app()->createUrl('fourwinds'); ?>?dept=<?php echo urlencode($dept->deptname); ?>" target="_blank"><div class="flash-icon"><?php echo StdLib::load_image("rss","16px"); ?></div> Fourwinds Feed</a></td>
            <td>This will take you to this department's Fourwind's RSS Feed.</td>
        </tr>
        <?php if($dept->regstatus == 1): ?>
        <tr>
            <td><a href="#"><div class="flash-icon"><?php echo StdLib::load_image("undo","16px"); ?></div> Unregister Application</a></td>
            <td>
                Unregistering application will prevent anyone from broadcasting messages.
           </td>
        </tr>
        <?php else: ?>
        <tr>
            <td><a href="#" id="registerapp"><div class="flash-icon"><?php echo StdLib::load_image("redo","16px"); ?></div> Register Application</a></td>
            <td>
                Registering application will allow users to submit broadcasts to the system.
           </td>
        </tr>    
        <?php endif; ?>
        <tr>
            <td><a href="#" id="deleteapp"><div class="flash-icon"><?php echo StdLib::load_image("deleteapp","16px"); ?></div> Delete Application</a></td>
            <td>
                This will completely remove this application and all of its broadcasts, tokens, and message history. Use for administrators only.<br/>
                <i>Confirmation box will appear before deletion.</i>
           </td>
        </tr>
    </tbody>
</table>
<div id="confirm-connection-media" title="Leave Page Confirmation">
	Are you sure you want to navigate away from this page?  Anything you entered will be lost.<br/>
</div>
<script>
jQuery(document).ready(function($){
	// Send mass email if the $data parameter is set. Unset when finished.
	if('<?echo $json_data;?>'!=='undefined') {
		var data = <?php echo $json_data; ?>;
		$.ajax({
			"url": 'https://compass.colorado.edu/broadcast/api/massemail',
			"type": 'POST',
			"data": data,
			
			"success":   function(data) {
				console.log(data);	
				return false;
			}
		});
	}
	
	$("a.dc-link").click(function(){
        $.post($(this).attr("href"),"",function(){
            window.location.reload(); 
        });
        return false;
   });
   $(document).on("focus","textarea",function(){
      if($(this).val() == "Type message here..." || $(this).val() == "Type email addresses here, separated by commas..." || $(this).val() == "Type subject here...") {
          $(this).html("");
      } 
   });
   $(document).on("focus","input",function(){
      if($(this).val() == "Type subject here...") {
          $(this).val("");
      } 
   });
   $(document).on("blur","textarea",function(){
	  if($(this).val().trim() == "" && $(this).attr('id')==="addresses-input-field") {
          $(this).html("Type email addresses here, separated by commas...");
      }
	  else if($(this).val().trim() == "") {
          $(this).html("Type message here...");
      } 	  
   });
   $(document).on("blur","input",function(){
	  if($(this).val().trim() == "" && $(this).attr('id')==="subject-input-field") {
          $(this).val("Type subject here...");
      }
   });
   $(document).on("click","#registerapp",function(){
        $.ajax({
           "url":    "<?php echo Yii::app()->createUrl("_register"); ?>",
           "type":      "post",
           "data":   "deptid=<?php echo $dept->deptid; ?>",
           "success":   function(data) {
               window.location.reload();
           },
           "error":     function(data) {
               alert("Error registering department application.");
           }
        });
        return false;
   });
   
   // Confirmation for reconnecting to media  
	$(".connection-media-button").click(function(){
		var url = $(this).attr("name");
		$("#confirm-connection-media").data("url", url).dialog("open");
	});
	$("div#confirm-connection-media").dialog({
		autoOpen:	false,
		resizable:	false,
		height:		300,
		width:		400,
		modal:		true,
		buttons: {
			"Confirm": function() {
				window.location.href = $("#confirm-connection-media").data("url");
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
   
   $(document).on("click",".approve-broadcast",function(){
       var broadcastid = $(this).parent().parent().attr("broadcastid");
      if(typeof broadcastid !== null) {
        $.ajax({
           "url":    "<?php echo Yii::app()->createUrl("_approve"); ?>",
           "type":      "post",
           "data":   "broadcastid="+broadcastid,
           "success":   function(data) {
               window.location.reload();
           },
           "error":     function(data) {
                alert("Error approving broadcast.");
           }
        });
      } 
      return false;
   });
   
   $(document).on("click",".disapprove-broadcast",function(){
       return false;
       var broadcastid = $(this).parent().parent().attr("broadcastid");
      if(typeof broadcastid !== null) {
        $.ajax({
           "url":       "<?php echo Yii::app()->createUrl("_disapprove"); ?>",
           "type":      "post",
           "data":      "broadcastid="+broadcastid,
           "success":   function(data) {
               window.location.reload();
           },
           "error":     function(data) {
                alert("Error approving broadcast.");
           }
        });
      } 
      return false;
   });
   
   $(document).on("click","#broadcast-button",function(){
      // If email is on, call API and submit the form upon returning.
	  $("form")[0].submit(); 
      console.log("after submission");
	  return false;
   });
   // Email options
   /*$(document).on("click", "#onoff-email",function() {
		if($("#onoff-email").prop("checked", false)) {
			$("#onoff-email").attr("checked", "checked");
			alert("checked!");
		}
   });*/
   $("#onoff-email").change(function() {
		if($(this).is(':checked')){
			//display options
			$("#email-options-form-container").slideDown("slow").css('display', 'block');
			//$("#big-email-icon").slideDown("slow").css('display', 'inline-block');
		}
		else{
			//hide options
			$('#email-options-form-container').slideUp();
			//$("#big-email-icon").slideUp();
		}
   });
});
</script>















