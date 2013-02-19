<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (!isset($_POST['Action']) and !isset($_GET['Action'])) { errorPage("missing Action"); die(); }
if (!isset($_POST['USID'])) { errorPage("missing USID"); die(); }

$sid = $_POST["USID"];

// ok to call new ManageSessions() now as it doesn't do much but store the data filename
$sessions = new ManageSessions($u->get('sessions_file'));

if ($_POST['Action'] == "create-session") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$day = $_POST["sess_day"];
	$mon = $_POST["sess_month"];
	$year = $_POST["sess_year"];
	$hour = $_POST["sess_hour"];
	$min = $_POST["sess_minute"];
	$ampm = $_POST["sess_ampm"];
	$timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm);

	$sid = $sessions->addSession(
		array( "active" => "no", "when" => $timestamp, "location" => $_POST["Location"], "maxusers" => $_POST["Maxusers"] )
	);

	$details = $sessions->describeSession($sid);
	SessionMgr::storeMessage("Created session [ $details ]");
	logAudit(array('action' => 'create-session', 'usid' => $sid, 'desc' => $details));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == "edit-session") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$s = $sessions->getSession($sid);
	$isActive = $s->active == "yes";
	$t = prepareTemplateEssentials();
	$t->post = "manageSessions.php";
	$t->status = $s->sessionStatus = $isActive ?
		'<button class="btn btn-small btn-success disabled" type=button name="Action" value="opensession">Open</button>'
		: '<button class="btn btn-small btn-danger disabled" type=button name="Action" value="opensession">Closed</button>';
	$t->s = $s;
	$t->sessionTime = displayDate((int)$s->when);

	require 'templates/editsession.php';
} else if ($_POST['Action'] == 'cancel') {
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'save-edit-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$day = $_POST["sess_day"];
	$mon = $_POST["sess_month"];
	$year = $_POST["sess_year"];
	$hour = $_POST["sess_hour"];
	$min = $_POST["sess_minute"];
	$ampm = $_POST["sess_ampm"];
	$timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm);
	$changes = array();
	$changes['when'] = $timestamp;
	$changes['location'] = $_POST["Location"];
	$changes['maxusers'] = $_POST["Maxusers"];
	$sessions->setAttr($sid, $changes);

	$details = $sessions->describeSession($sid);
	SessionMgr::storeMessage("Edited session [ $details ]");
	logAudit(array('action' => 'edit-session', 'usid' => $sid, 'desc' => $details));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'open-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions->setAttr($sid, array('active' => 'yes'));

	$details = $sessions->describeSession($sid);
	SessionMgr::storeMessage("Opened session [ $details ]");
	logAudit(array('action' => 'open-session', 'usid' => $sid, 'desc' => $details));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'close-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions->setAttr($sid, array('active' => 'no'));

	$details = $sessions->describeSession($sid);
	SessionMgr::storeMessage("Closed session [ $details ]");
	logAudit(array('action' => 'close-session', 'usid' => $sid, 'desc' => $details));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'delete-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$details = $sessions->describeSession($sid);

	$sessions->removeSession($sid);

	SessionMgr::storeMessage("Deleted session [ $details ]");
	logAudit(array('action' => 'delete-session', 'usid' => $sid, 'desc' => $details));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'enrol') {
	if (SessionMgr::isLoggedIn() === FALSE) {
		SessionMgr::storeMessage("You need to be logged in to enrol");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions->enrolUser($sid, SessionMgr::getUsername());

	logAudit(array('action' => 'enrol', 'usid' => $sid));

	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'unenrol') {
	if (SessionMgr::isLoggedIn() === FALSE) {
		SessionMgr::storeMessage("You need to logged in to unenrol");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions->unenrolUser($sid, SessionMgr::getUsername());

	logAudit(array('action' => 'unenrol', 'usid' => $sid));

	header("Location: ".$c->get('index'));
} else {
	errorPage('unknown action: '.$_POST['Action']);
}

# vim:filetype=html:ts=4:sw=4
?>
