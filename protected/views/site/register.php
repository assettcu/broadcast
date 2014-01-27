<?php
$this->pageTitle=Yii::app()->name . ' - Register App';

# Load the user (must exist, cannot get to this page without logging in)
$user = new UserObj(Yii::app()->user->name);

# Load success/warning/error messages
$flashes = new Flashes();
$flashes->render();
?>
<style>
table#post-form-table tr th {
    vertical-align: top;
}
table#post-form-table tr th div {
    padding:5px;
    border:2px solid #ccc;
    background-color:#f0f0f0;
}
table#post-form-table tr td {
    padding-left:15px;
}

input#contactname {
    width:200px;    
}
input#deptcode {
    width:100px;
}
input#deptname {
    width:500px;
}
input#appurl {
    width:300px;
}
input#contactemail {
    width:300px;
}
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
}
div.icon-header {
    margin-bottom:10px;
}
div.icon-header h1 {
    display:inline-block;
    width:950px;
    margin-left:10px;
}
</style>


<div class="icon-header">
    <div class="icon-text-align-large"><?php echo StdLib::load_image("reg","48px","48px"); ?></div>
    <h1>Register a New Application</h1>
</div>

<div class="ui-widget-content ui-corner-all" style="padding:10px;margin-bottom:15px;">
    When creating a new broadcast front-end you will need to register it with Broadcast.
</div>

<form method="post">
    <input type="hidden" name="form-submitted" />
    
    <table id="post-form-table">
        <tr>
            <th><div>Department/Program Code</div></th>
            <td>
                <input type="text" name="deptcode" id="deptcode" value="<?php echo @$_POST["deptcode"]; ?>" style="float:left;margin-right:15px;" maxlength="6" />
                <div class="hint" style="display:inline-block;padding-top:3px;">This is the letter code associated with the department. (eg. COMM)</div>
            </td>
        </tr>
        <tr>
            <th><div>Department/Program Name</div></th>
            <td>
                <input type="text" name="deptname" id="deptname" value="<?php echo @$_POST["deptname"]; ?>" /><br/>
                <div class="hint hide" id="auto-deptname">Use <i><a href="#" onclick="return false;"></a></i> as department name.</div>
            </td>
        </tr>
        <tr>
            <th><div>Application URL</div></th>
            <td>
                <input type="text" name="apphost" id="apphost" value="<?php echo @$_POST["apphost"]; ?>" /><br/>
            </td>
        </tr>
        <tr>
            <th><div>Terms of Agreement</div></th>
            <td>
                <input type="checkbox" name="agree" id="agree" style="float:left;margin-right:15px;" <?php echo (isset($_POST["agree"])) ? "checked='checked'" : ""; ?> />
                <div class="hint">I have read and agree to the <a href="#" class="toa-button">terms of agreement</a>.</div>
            </td>
        </tr>
    </table>
    
    <hr style="margin-top:25px;"/>
    
    <div class="agree-terms-hint ui-state-default ui-corner-all" style="padding:10px;">
        <div class="icon-text-align"><?php echo StdLib::load_image("warning","16px"); ?></div>
        <span class="hint" id="post-stop-hint"> You must agree to the terms of agreement.</span>
    </div>
        
    <div class="button-container">
        
        <button class="submit" disabled="disabled">Register App</button>
        <a href="<?php echo Yii::app()->createUrl('index'); ?>">cancel</a>
        
    </div>
</form>

<!-- Terms of Agreement -->
<div id="toa-dialog" title="Terms of Agreement">
    You agree that by registering a new Application that you are using it to promote department news and events. Any other use of the
    Broadcast system is strictly forbidden. Plus we'll get really mad.
</div>
    
<script>

jQuery(document).ready(function($){
    
    $("#deptname").autocomplete({
       source: "https://compass.colorado.edu/directory/api/deptnames",
       minLength: 2,
    });
    
    $("#auto-deptname a").click(function(){
       $("#deptname").val($(this).text());
       $("#auto-deptname").hide();
    });
    
    $("#deptcode").keyup(function(){
       $.ajax({
          url: "https://compass.colorado.edu/directory/api/deptname",
          data: "code="+$(this).val(),
          success: function(data) {
              console.log(data);
              if(data != "" && $("#deptname").val() == "") {
                  $("#deptname").val(data);
              } else if(data != "" && $("#deptname").val() != "" && $("#deptname").val() != data) {
                  $("#auto-deptname a").text(data);
                  $("#auto-deptname").show();
              }
          },
          failure: function(data) {
              console.log("Could not look up department: "+data);
          }
       });
    });
    
    // All buttons do not submit forms
    $("button").click(function(){
        return false;
    });
    
    if($("#agree").is(":checked")) {
        $("div.agree-terms-hint").hide();
        $("button.submit").button({
            "disabled": ""
        });
    }
    
    // Show/hide warning of agreeing to terms based on checkbox
    $("#agree").change(function(){
        if($("#agree").is(":checked")) {
            $("div.agree-terms-hint").hide('fade');
            $("button.submit").button({
                "disabled": ""
            });
        } else {
            $("div.agree-terms-hint").show('fade');
            $("button.submit").button({
                "disabled": "disabled"
            });
        }
    });

    // Init dialog box for terms of agreement
    $("#toa-dialog").dialog({
        "autoOpen":       false, 
        "modal":          true,
        "width":          500,
        "height":         300,
        "draggable":      false,
        "resizable":      false,
    });
    
    // Link for the Terms of Agreement
    $(".toa-button").click(function(){
        $("#toa-dialog").dialog("open");
        return false; 
    });

    // Submit the new Property post
    $("button.submit").click(function(){    
        $("button").button({"disabled":"disabled"});
        // If property is loaded (editing post), special conditions apply
        if(!$("#agree").is(":checked")) {
            alert("You must agree to the terms of agreement before registering.");
            return false;
        }

        // Let's submit the form
        $('form').submit();
        return false;
    });

});
</script>