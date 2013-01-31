<?php

require 'includes/sessionMgr.php';

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (isset($_POST['adminView'])) {
	SessionMgr::set('adminView', $_POST['adminView']);
}

if (isset($_POST['viewClosedSessions'])) {
	SessionMgr::set('viewClosedSessions', $_POST['viewClosedSessions']);
}

header('Location: enrol.php');

?>
