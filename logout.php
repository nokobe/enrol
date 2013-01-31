<?php

require 'includes/sessionMgr.php';

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();
SessionMgr::logout();

if (file_exists("enrol.php")) {
	header("Location: enrol.php");
}
if (file_exists("index.php")) {
	header("Location: index.php");
}
