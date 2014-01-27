<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
// Theme name from Jquery UI themes
$theme = "chrono";
$this->pageTitle = "ASSETT Broadcast!";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/library/images/favicon.ico" />
    
    <!-- blueprint CSS framework -->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style1.css" />

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo  Yii::app()->params["LOCALAPP_JQUERY_VER"]; ?>/jquery.min.js" type="text/javascript"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo Yii::app()->params["LOCALAPP_JQUERYUI_VER"]; ?>/jquery-ui.min.js" type="text/javascript"></script>
    
    <link rel="stylesheet" href="<?php echo WEB_LIBRARY_PATH; ?>jquery/themes/<?=$theme?>/jquery-ui.css" type="text/css" />

    <script>
    jQuery(document).ready(function($){
        $("button").button();
    });
    </script>
</head>

<body>

<div class="container" id="page">

    <div id="header">
        <div id="logo" style="position:relative;">
            <div id="logo-image" style="position:absolute;top:-18px;left:15px;">
                <?php echo StdLib::load_image('broadcast2',"61px");?>
            </div>
            <div id="logo-text">
                <?php echo CHtml::encode(Yii::app()->name); ?>
            </div>
            <?php 
                 $user = new UserObj(Yii::app()->user->name);
            ?>
            <div id="mainmenu">
                <?php if(Yii::app()->user->isGuest): ?>
                <a href="<?=Yii::app()->createUrl('site/login')?>">Login</a>
                
				<?php elseif($user->permission_atleast(10)): ?>
				<a href="<?=Yii::app()->createUrl('site/logout')?>">Logout (<?=Yii::app()->user->name?>)</a>
                <a href="<?=Yii::app()->createUrl('site/apps'); ?>">My Apps</a>   
				<a href="<?=Yii::app()->createUrl('site/manageUsers'); ?>">Manage Users</a>   
				
				<?php else: ?>
                <a href="<?=Yii::app()->createUrl('site/logout')?>">Logout (<?=Yii::app()->user->name?>)</a>
                <a href="<?=Yii::app()->createUrl('site/apps'); ?>">My Apps</a>   
                <?php endif; ?>
                <a href="<?=Yii::app()->baseUrl;?>">Home</a>
            </div>
        </div>
    </div><!-- header -->
    
    <?php echo $content; ?>

    <div class="clear"></div>

    <div id="footer">
        <div id="footer-links">
            <a href="http://www.colorado.edu/">University of Colorado Boulder</a><br/>
            <a href="http://www.colorado.edu/legal-trademarks-0">Legal &amp; Trademark</a> | <a href="http://www.colorado.edu/legal-trademarks-0">Privacy</a> <br/>
            <a href="https://www.cu.edu/regents/">&copy; <?php echo date('Y'); ?> Regents of the University of Colorado</a>
        </div>
        <a id="assettlogo" href="http://assett.colorado.edu"></a>
        <div id="footer-text">
            Copyright &copy; <?php echo date('Y'); ?> by the University of Colorado Boulder.<br/>
            Developed by the <a href="http://assett.colorado.edu">ASSETT program</a><br/>
            Programmed by <span style="color:#0a0;">Ryan Carney-Mogan</span>
        </div>
    </div><!-- footer -->

</div><!-- page -->

</body>
</html>
