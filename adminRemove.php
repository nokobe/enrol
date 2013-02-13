<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (SessionMgr::hasAdminAuth() === FALSE) {
	SessionMgr::storeMessage("Permission denied");
	header("Location: ".$c->get('index'));
	exit (0);
}

$sid = $_GET['sid'];
$user = $_GET['user'];

$sessions = new Sessions($u->get('sessions_file'));
try {
	$sessions->unenrolUser($sid, $user);
	$sessions->save();
	Logger::logInfo("Admin removed $user from Session (ID: $sid)");
	SessionMgr::storeMessage("Admin removed $user from Session (ID: $sid)");
	header("Location: ".$c->get('index'));
} catch (Exception $e) {
	errorPage($e->getMessage());
}

# vim:filetype=html:ts=4:sw=4
?>
