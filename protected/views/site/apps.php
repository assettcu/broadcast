<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
/**
 * Loaded variables
 * 
 * $user    The current user
 * $depts   The departments the current user has access to
 * 
 */ 

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
table#apps-container {
    margin-top:10px;
    border-spacing:3px;
}
table#apps-container tr td {
    border:2px solid #ccc;
    padding:8px;
}
table#apps-container thead tr th {
    background-color:#0066CC;
    color:#fff;
    font-weight:bold;
    padding:7px;
}
</style>


<div class="icon-header">
    <div class="icon-text-align-large"><?php echo StdLib::load_image("app-icon","48px"); ?></div>
    <h1>My Apps</h1>
</div>

<div class="ui-widget-content ui-corner-all" style="padding:10px;margin-bottom:15px;">
    This page has the overview of the applications you have access to. You may broadcast to various departments from this panel.
</div>

<a href="<?php echo Yii::app()->createUrl('register'); ?>"><div class="icon-text-align"><?php echo StdLib::load_image("plus","20px"); ?></div>Register New App</a>

<table id="apps-container">
    <thead>
        <tr>
            <th>Department</th>
            <th>Application Key</th>
            <th>Pending Broadcasts</th>
            <th width="130px">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($depts)) : ?>
            <?php foreach($depts as $dept): ?>
            <tr>
               <td>
                    <div class="icon-text-align"><?php echo StdLib::load_image("reg","20px"); ?></div>
                    <span class="text">
                        <a href="<?php echo Yii::app()->createUrl('broadcast')."?deptid=".$dept->deptid; ?>">[<?php echo $dept->deptcode; ?>] <?php echo $dept->deptname; ?></a>
                    </span>
                </td> 
                <td class="calign">
                    AppKey: <strong><?php echo $dept->appkey; ?></strong>
                </td>
                <td class="calign"><a href="#"><i><span style="color:#03f;">0</span> pending broadcasts</i></a></td>
                <td class="calign">
                    <span style="color:#f50;font-weight:bold;"><?php echo ($dept->regstatus == 0) ? "unregistered" : "registered"; ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="nocontent">
                    You have no applications! Begin by <a href="<?php echo Yii::app()->createUrl('register'); ?>">registering a new app</a>.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
