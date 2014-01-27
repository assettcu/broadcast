<?php

$social = array(
    "facebook"      => "Facebook",
    "googleplus"    => "Google Plus",
    "twitter"       => "Twitter",
    "fourwinds"     => "FourWinds",
);

?>
<div id="response"></div>
<style>
div.label {
    font-weight:bold;
    width:120px;
    text-align:right;
    padding:3px;
    padding-right:5px;
    border-right:2px solid #ccc;
    float:left;
    margin-right:10px;
    padding-top:5px;
}
div.media-platforms {
    width:200px;
    display:inline-block;
}
div.input-container {
    margin:5px;
}
</style>
<h1>Submit a Broadcast</h1>

<form name="broadcast-form">
    <div class="input-container">
        <div class="label">Username:</div> <input type="text" name="username" id="username" />
    </div>
    
    <div class="input-container">
        <div class="label">Media Platforms: </div>
        
        <div class="media-platforms">
            <?php foreach($social as $key=>$media): ?>
            <div class="social-button ui-corner-all" style="width:100%;border:1px solid #09f;padding:5px;margin-bottom:4px;">
                <div style="margin:3px;display:inline-block;">
                    <?php echo StdLib::load_image($key."_logo","16px"); ?> <?php echo $media; ?>
                </div>
                <div class="onoffswitch">
                    <input type="checkbox" name="media-<?php echo $key; ?>" class="onoffswitch-checkbox" id="media-<?php echo $key; ?>" checked>
                    <label class="onoffswitch-label" for="onoff-<?php echo $key; ?>">
                        <div class="onoffswitch-inner"></div>
                        <div class="onoffswitch-switch"></div>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="input-container">
        <div class="label">Message:</div>
        <textarea name="message" id="message"></textarea>
    </div>
    
    <br/>
</form>
    
<button>Submit Broadcast</button>


<script>
jQuery(document).ready(function($){
    
    $("button").click(function(){
        var formdata = $("form").serialize();
        $.ajax({
         "url":         "http://compass.colorado.edu/broadcast/api/broadcast",
         "data":        formdata,
         "success":     function(data) {
             $("#response").html(data);
             return false;
         },
         "error":       function(data) {
            if(data.status == 308) {
                $("#response").html("Invalid number of parameters for function call.");
                return false;
            }
         }
        });
    });
    
});
</script>
