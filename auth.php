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

if ( SessionMgr::isRegisteredAdmin() === FALSE ) {
	SessionMgr::storeMessage("Permission denied");
	header("Location: ".$c->get('index'));
	exit(0);
}

if ( SessionMgr::hasAdminAuth()) {
	SessionMgr::storeMessage("Already authenticated");
	header("Location: ".$c->get('index'));
	exit(0);
}

if ( isset($_POST['auth-submit'])) {
	if (authUser(SessionMgr::getUsername(), md5($_POST['password']))) {
		SessionMgr::grantAdminAuth();
		SessionMgr::set('adminView', 1);
		header("Location: ".$c->get('index'));
	} else {
		SessionMgr::storeMessage("Incorrect password");
		header("Location: auth.php");
		exit(0);
	}
}

$t = prepareTemplateEssentials();
$t->post = "auth.php";
require 'templates/auth.php';

function authUser($user, $hashedPassword) {
	if ($user == "Mark Bates" && $hashedPassword == "912ec803b2ce49e4a541068d495ab570") {
		return true;
	} else { 
		return false;
	}
}

# vim:filetype=html:ts=2:sw=2
?>
