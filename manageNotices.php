<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (isset($_POST['Action']) == FALSE) {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	if (isset($_GET['target'])) {
		$target = $_GET['target'];
		if ($target == "notices") {
			$t = prepareTemplateEssentials();
			$t->notices = get_notices($u->get('notices_file'));
			$t->rawNotices = file_get_contents( $u->get('notices_file') );
			$t->information = "Notices are shown in the Notices tab only";
			$t->post = "manageNotices.php";
			$t->target = "notices";
			require 'templates/editNotices.php';
		} else if ($target == "announcements") {
			$t = prepareTemplateEssentials();
			$t->notices = get_notices($u->get('announcements_file'));
			$t->rawNotices = file_get_contents( $u->get('announcements_file') );
			$t->information = "Announcements are always shown at the top of the page";
			$t->post = "manageNotices.php";
			$t->target = "announcements";
			require 'templates/editNotices.php';
		} else {
			SessionMgr::storeMessage("manageNotices: target mismatch");
			Logger::logWarn("manageNotices was called with a bad target");
			header("Location: ".$c->get('index'));
		}
	}
	else {
			SessionMgr::storeMessage("manageNotices: target missing");
			Logger::logWarn("manageNotices was called with a missing target");
			header("Location: ".$c->get('index'));
	}
} else if ($_POST['Action'] == "save-edit-announcements") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$newtext = $_POST["newnotices"];

	$destFile = $u->get('announcements_file');

	if (! copy ( $destFile, "$destFile.bak" ) ) {
		Logger::logWarn("backup $destFile failed");
	}

	$bytes = file_put_contents($destFile, $newtext);
	Logger::logInfo("[file: $destFile] Saved $bytes bytes");
	header("Location: ".$c->get('index')."#information");
} else if ($_POST['Action'] == "save-edit-notices") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$newtext = $_POST["newnotices"];

	$destFile = $u->get('notices_file');

	$bytes = file_put_contents($destFile, $newtext);
	Logger::logInfo("[file: $destFile] Saved $bytes bytes");
	header("Location: ".$c->get('index')."#information");
} else if ($_POST['Action'] == 'cancel') {
	header("Location: ".$c->get('index'));
}

# vim:filetype=html:ts=4:sw=4
?>
