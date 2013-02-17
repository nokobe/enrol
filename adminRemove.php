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

$sessions = new ManageSessions($u->get('sessions_file'));
$sessions->unenrolUser($sid, $user);
Logger::logInfo("Admin removed $user from Session (ID: $sid)");
SessionMgr::storeMessage("Admin removed $user from Session (ID: $sid)");
header("Location: ".$c->get('index'));

# vim:filetype=html:ts=4:sw=4
?>
