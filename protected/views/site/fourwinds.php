<?php
$rss = new RSSFeeder();
if(!isset($_REQUEST["dept"])) {
    $rss->render();
    exit;
}
else {
    $user_account = $_REQUEST["dept"];
    if(!is_numeric($user_account)) {
        $user_account = Functions::get_deptid_from_name($user_account);
    }
}
$rss->load_fourwinds($user_account,3);
$rss->render();
?>