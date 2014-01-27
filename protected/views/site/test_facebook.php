<?
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


$facebook = new FacebookClass();
if(isset($_GET["code"]) and $facebook->has_clear_status()) {
    $this->redirect(Yii::app()->createUrl('testfacebook'));
    exit;
}
?>

<a href="<?php echo $facebook->getLoginUrl(); ?>">
    <div class="flash-icon"><?php echo StdLib::load_image('sync','16px'); ?></div> reconnect
</a>
<hr/>
<?php
echo "<strong>REQUEST</strong><br/>";
var_dump($_REQUEST);
print "<hr/>";
echo "<strong>SESSION</strong><br/>";
var_dump($_SESSION);
print "<hr/>";
echo "<strong>Facebook Class</strong><br/>";
var_dump($facebook);
print "<hr/>";
echo "<strong>Has Clear Status?</strong><br/>";
var_dump($facebook->has_clear_status());
?>				 