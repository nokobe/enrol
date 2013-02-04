<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

$t = new stdClass;
$t->title = $u->get('title');
$t->base = ".";
$t->username = SessionMgr::getUsername();
$t->loggedIn = SessionMgr::isLoggedIn();
$t->self = $c->get('php_self');

if (!isset($_POST['Action'])) { errorPage("missing Action"); die(); }
if (!isset($_POST['USID'])) { errorPage("missing USID"); die(); }

$usid = $_POST["USID"];

if ($_POST['Action'] == "edit-session") {
	$sessions = new Sessions($u->get('sessions_file'));
	log_debug("loaded sessions file (hopefully): ".$u->get('sessions_file'));
	$s = $sessions->getSession($usid);
	#print_r($s);

	$isActive = $s->active == "yes";

	$t->status = $s->sessionStatus = $isActive ?
		'<button class="btn btn-small btn-success disabled" type=button name="Action" value="opensession">Open</button>'
		: '<button class="btn btn-small btn-danger disabled" type=button name="Action" value="opensession">Closed</button>';
	$t->s = $s;

	require 'templates/editsession.php';
} else {
	echo "NYI";
}
?>
