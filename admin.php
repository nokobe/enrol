<?php

require 'includes/global.php';

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if ( SessionMgr::isRegisteredAdmin() === FALSE ) {
	SessionMgr::storeMessage("Permission denied");
	header("Location: ".$c->get('index'));
	exit (0);
}

if (isset($_POST['adminView'])) {
	$desired = $_POST['adminView'];

	if ($desired == 1 and SessionMgr::hasAdminAuth() === FALSE) {
		header("Location: auth.php");
		exit(0);
	}
	SessionMgr::set('adminView', $desired);
}

if (isset($_POST['hideClosedSessions'])) {
	SessionMgr::set('hideClosedSessions', $_POST['hideClosedSessions']);
}

header("Location: ".$c->get('index'));

?>
