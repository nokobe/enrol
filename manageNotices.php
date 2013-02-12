<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if ($c->get('debug')) {
        echo "<pre>";
        echo "Session:";
        print_r($_SESSION);
        echo "Post:";
        print_r($_POST);
        echo "</pre>";
}

if (isset($_POST['Action']) == FALSE) {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$t = prepareTemplateEssentials();
	$t->notices = get_notices($u->get('notices_file'), SessionMgr::isRegisteredAdmin() and SessionMgr::get('adminView'));
	$t->rawNotices = file_get_contents( $u->get('notices_file') );
	$t->post = "manageNotices.php";
	require 'templates/editNotices.php';
} else if ($_POST['Action'] == "save-edit-notices") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$newtext = $_POST["newnotices"];
	$bytes = file_put_contents($u->get('notices_file'), $newtext);
	Logger::logInfo("Saved $bytes bytes to: ".$u->get('notices_file'));
	header("Location: ".$c->get('index')."#information");
} else if ($_POST['Action'] == 'cancel') {
	header("Location: ".$c->get('index'));
}

# vim:filetype=html:ts=2:sw=2
?>
