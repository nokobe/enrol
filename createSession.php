<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if ($c->get('debug')) {
	echo 'XYZ';
        echo "<pre>";
        echo "Session:";
        print_r($_SESSION);
        echo "Post:";
        print_r($_POST);
        echo "</pre>";
}

if (SessionMgr::hasAdminAuth() == FALSE) {
	SessionMgr::storeMessage("Permission denied");
	header("Location: ".$c->get('index'));
	exit (0);
}

$t = prepareTemplateEssentials();
$t->post = "manageSessions.php";
$t->when = "";
$t->location = "";
$t->maxusers = $u->get('default_session_size');

require 'templates/newsession.php';

# vim:filetype=html:ts=2:sw=2
?>
