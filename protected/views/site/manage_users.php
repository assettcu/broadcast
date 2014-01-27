<?php
/**
 * Loaded variables
 * 
 * $user    The current user
 * $depts   The departments the current user has access to
 * 
 */ 
$retainInput = "false";
if(Yii::app()->user->hasFlash('error')) $retainInput = "true";

$flashes = new Flashes;
$flashes->render();
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
}
div.icon-header {
    margin-bottom:10px;
}
div.icon-header h1 {
    display:inline-block;
    width:950px;
    margin-left:10px;
}
table#users-container {
    width:50%;
	margin-top:10px;
	margin-right:25%;
	margin-left:25%;
    border-spacing:3px;
}
table#users-container tr td {
    border:2px solid #ccc;
    padding:8px;
}
table#users-container thead tr th {
    background-color:#0066CC;
    color:#fff;
    font-weight:bold;
    padding:7px;
}
div.menu-create-user {
	margin-top:12px;
	margin-left:25%;
	margin-right:25%;
}
div.menu-create-user input[type="text"]{
	width:50%;
	clear:both;
}
div.menu-create-user button {
	font-size:150%;
}
form#createuserform {
	display:none;
	padding:10px;
}
form#createuserform input{
	padding:5px;
}
#submitcreateuser {
	float:right;
}
#dirlookup {
	width:4em;
}
</style>

<div class="icon-header">
    <h1>Manage Users</h1>
</div>

<div class="ui-widget-content ui-corner-all" style="padding:10px;margin-bottom:15px;">
    This page allows administrators to add and delete users, or update privileges.
</div>

<div class="menu-create-user">
	<button id="createuser"> Create User</button>
	<b>or select a user from the table to edit.</b>
	<form method="post" name= "createuserform" id="createuserform">
		<TABLE>
		<TR><label id="warning-existing-user"></label>
		<TR><label id="identikeyinputlabel">Identikey: </label>&nbsp
		<TR><input type="text" name="identikey" id="identikeyinputfield"> <b id="require-asterisk-identikey" style="font-size:200%;color:red;"><? echo (empty($identikey)) ? "*" : "";?></b>
		<button id="dirlookup"> Lookup</button><br>
		<TR><label id="user-department"></label>
		<TR>Email: <input type="text" name="email"><b style="font-size:200%;color:red;"><? echo (empty($email)) ? "*" : "";?></b><br>
		<TR>Full Name: <input type="text" name="fullname"><b style="font-size:200%;color:red;"><? echo (empty($fullname)) ? "*" : "";?></b><br>
		<TR><label id="user-department"></label><br>
		<TR>Permission Level: <input type="radio" id="perm0" name="permission" value="0">No Access <input type="radio" id="perm1" name="permission" value="1">Member <input type="radio" id="perm10" name="permission" value="10">Administrator <br><br>
		<input type="submit" value="Save/Update User">
		<input type="hidden" id="retainInput" value="<? echo ($retainInput == 'true') ? "true" : "false"?>">
		</TABLE>
	</form>
</div>

<table id="users-container">
    <thead>
        <tr>
            <th>User Name</th>
            <th>Permission Level</th>
			<th>Department</th>
            <!--<th>Pending Broadcasts</th>
            <th width="130px">Status</th> --->
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $key=>$user): ?>
			<tr>
                <td>
                    <span class="text">
						<a href="#" class="get-info-from-name" id="<?php echo $user["username"];  ?>"><?php echo $user["name"]; ?></a>
                    </span>
                </td> 
                <td class="calign">
					<? if($user["permission"] == "10") { echo "Administrator";} elseif($user["permission"] == "1") { echo "Member";} else echo "No-access member";?>
                </td>
				<td>
                    <span class="text">
						<?php echo (empty($user["department"])) ? "No Department" : $user["department"]; ?>
                    </span>
                </td> 
				<td>
                    <button class="delete-user-button" name="<?php echo $user["username"];  ?>">Delete</button>
                </td> 
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div id="confirm-delete-user" title="Delete User Confirmation">
	Are you sure you want to delete this user?<br/>
</div>
<script>
jQuery(document).ready(function(){
	// If there was an error, make visible the form and retain input data.
	if($('#retainInput').val()==='true') {
		var perm = '<? echo $permission; ?>';
		$("#createuserform").css('visibility', 'visible');
		$('input[name=identikey]').val('<? echo $identikey;?>');
		$('input[name=fullname]').val('<? echo $fullname;?>');
		$('input[name=email]').val('<? echo $email;?>');
		
		if(perm !== 'null') {
			$('#perm'+perm).prop( "checked", true );      
		}
	}
	
	$(document).on('click',"#createuser",function(){
		if($("#createuserform").css( "display" ) == "none") {
			$("#createuserform").slideDown("slow").css('display', 'block');
		}
		$('input[name=identikey]').val('');
		$('input[name=fullname]').val('');
		$('input[name=email]').val('');
		$('input[name=department]').val('');
		$('#perm0').prop( "checked", false ); 
		$('#perm1').prop( "checked", true ); 
		$('#perm10').prop( "checked", false ); 
		
		// Show the identikey input field, label, and lookup button
		$('#identikeyinputlabel').text("Identikey: ");
		$('#dirlookup').css('visibility', 'visible');
		$('#identikeyinputlabel').css('color', 'dimgray');
		$('#identikeyinputfield').css('visibility', 'visible');
		$('b#require-asterisk-identikey').css('visibility', 'visible');
		// Hide the department row and existing user warning 
		$('#warning-existing-user').css('display', 'none');
		$('#user-department').css('display', 'none');
	});
	// Ajax call to lookup existing user data in database.
	$(document).on('click',".get-info-from-name",function(){
		'<div id="myDiv"></div>'
		var user_identikey = $(this).attr('id');
		hideIdentikeyRow(user_identikey);
		$("#createuserform").slideDown("slow").css('display', 'block');
		$('#warning-existing-user').css('display', 'none');
		
		$.ajax({
			"url": 		"<?=Yii::app()->createUrl('_get_current_user_info');?>",
		    "type":     "post",
			"data": 	"user_identikey="+user_identikey,
			"dataType": "JSON",
			"success": 	function(data) {
				$('#user-department').html("Department: " + data.department+"<br>").css('display', 'inline');
				$('input[name=identikey]').val(data.username);
				$('input[name=fullname]').val(data.name);
				$('input[name=email]').val(data.email);
				//$('input[name=department]').val(data.department);
				$('#perm'+data.permission).prop( "checked", true ); 
			}
		});
		return false;
	});
	// Ajax call to lookup user in directory by identikey.
	$(document).on('click',"#dirlookup",function(){
		var user_identikey = $('input[name=identikey]').val();
		$("#createuserform").css('visibility', 'visible');
		// Hide the department row
		$('#user-department').css('display', 'none');
		
		$.ajax({
			"url": 		"<?=Yii::app()->createUrl('_lookup_user_adauth');?>",
		    "type":     "post",
			"data": 	"user_identikey="+user_identikey,
			"dataType": "JSON",
			"success": 	function(data) {
				var flattened = flattenObject(data);
				console.log(flattened);
				$('input[name=fullname]').val(flattened['displayname.0']);
				$('input[name=email]').val(flattened['mail.0']);
				if(flattened['is_existing'] && flattened['is_existing']==='true') {
					$('#perm'+flattened['permission_level']).prop( "checked", true ); 
					$('#warning-existing-user').html("Caution: the user you looked up already exists on ASSETT Broadcast.<br>").css('display', 'inline');
					$('#warning-existing-user').css('color', 'red');
					hideIdentikeyRow(user_identikey);
				}
			}
			/*"error": function(data) {
				if(user_identikey === '') {
					$('#dirlookup').add( "<div id='warning-enter-id'>Enter Identikey</div>").css( "font-color", "red" );
					$('#warning-enter-id').html('something');
				}
				else {
					var message = $("<div>&nbsp&nbsp&nbsp&nbsp A user with this identikey username was not found.</div>");
					
				}
			}*/
		});
		return false;
	});
	// Delete user from database and confirmation dialog box for deleting users.  
	$(".delete-user-button").click(function(){
		var identikey = $(this).attr("name");
		$("#confirm-delete-user").data("identikey", identikey).dialog("open");
	});
	$("div#confirm-delete-user").dialog({
		autoOpen:	false,
		resizable:	false,
		height:		300,
		width:		400,
		modal:		true,
		buttons: {
			"Delete User": function() {
				$.ajax({
					"url": 		"<?=Yii::app()->createUrl('_delete_user_from_database');?>",
					"type":     "post",
					"data": 	"identikey="+$("#confirm-delete-user").data("identikey"),
					"success": 	function(data) {
						$(document).scrollTop(0);
						window.location.reload();
					}
				});
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	// Helper function for hiding the full identikey row in form
	var hideIdentikeyRow = function(user) {
		var identikey_bold = $('<b>' + user + '</b>');
		$('#identikeyinputlabel').text("You are editing current user: " + identikey_bold.html());
		$('#identikeyinputlabel').css('color', 'blue');
		$('#identikeyinputfield').css('visibility', 'hidden');
		$('b#require-asterisk-identikey').css('visibility', 'hidden');
		$('#dirlookup').css('visibility', 'hidden');
	};
	
	
	
	// Helper function for flattening multidimensional object returned from Ajax call
	var flattenObject = function(ob) {
		var toReturn = {};
	
		for (var i in ob) {
			if (!ob.hasOwnProperty(i)) continue;
		
			if ((typeof ob[i]) == 'object') {
				var flatObject = flattenObject(ob[i]);
				for (var x in flatObject) {
					if (!flatObject.hasOwnProperty(x)) continue;
				
					toReturn[i + '.' + x] = flatObject[x];
				}
			} else {
				toReturn[i] = ob[i];
			}
		}
		return toReturn;
	};
});
</script>	
