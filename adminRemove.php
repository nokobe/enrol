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

$details = $sessions->describeSession($sid);
SessionMgr::storeMessage("Admin removed $user from session [ $details ]");
logAudit(array('action' => 'adminRemove', 'usid' => $sid, 'removed' => $user));

header("Location: ".$c->get('index'));

# vim:filetype=html:ts=4:sw=4
?>
