<?php

require 'includes/sessionMgr.php';

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (isset($_POST['adminView'])) {
	SessionMgr::set('adminView', $_POST['adminView']);
}

if (isset($_POST['hideClosedSessions'])) {
	SessionMgr::set('hideClosedSessions', $_POST['hideClosedSessions']);
}

header('Location: enrol.php');

?>
