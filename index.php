<?php
ini_set("display_errors",1);
error_reporting(E_ALL);

define("APP_LOCAL_DIR",getcwd());

// define appkey
defined('SESSION_APPKEY') or define('SESSION_APPKEY','Broadcast_Session_API_Key');

// define local library paths (see StdLib class)
defined('LOCAL_LIBRARY_PATH') or define('LOCAL_LIBRARY_PATH','c:\\web\\compass.colorado.edu\\broadcast\\library\\');
defined('LOCAL_IMAGE_LIBRARY') or define('LOCAL_IMAGE_LIBRARY',LOCAL_LIBRARY_PATH."images\\");

// define web library paths (see StdLib class)
defined('WEB_LIBRARY_PATH') or define('WEB_LIBRARY_PATH','//compass.colorado.edu/broadcast/library/');
defined('WEB_IMAGE_LIBRARY') or define('WEB_IMAGE_LIBRARY',WEB_LIBRARY_PATH."images\\");

// change the following paths if necessary
$yii=dirname(__FILE__).'/../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
