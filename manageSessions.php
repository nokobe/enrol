<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (!isset($_POST['Action']) and !isset($_GET['Action'])) { errorPage("missing Action"); die(); }
if (!isset($_POST['USID'])) { errorPage("missing USID"); die(); }

$sid = $_POST["USID"];

if ($_POST['Action'] == "create-session") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions = new Sessions($u->get('sessions_file'));
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
	try {
		$sessions->save();
		Logger::logInfo("create-session sid:$sid when: $timestamp location:".$_POST[Location]." maxusers:".$_POST[Maxusers]);
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	SessionMgr::storeMessage("Created new session (Inital state: Closed)");
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == "edit-session") {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions = new Sessions($u->get('sessions_file'));
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
	$sessions = new Sessions($u->get('sessions_file'));
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
	try {
		$sessions->save();
		Logger::logInfo("save-edit-session sid:$sid when: $timestamp location:".$_POST['Location']." maxusers:".$_POST['Maxusers']);
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'open-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->setAttr($sid, array('active' => 'yes'));
	try {
		$sessions->save();
		Logger::logInfo("open-session sid:$sid");
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'close-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->setAttr($sid, array('active' => 'no'));
	try {
		$sessions->save();
		Logger::logInfo("close-session sid:$sid");
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'delete-session') {
	if (SessionMgr::hasAdminAuth() === FALSE) {
		SessionMgr::storeMessage("Permission denied");
		header("Location: ".$c->get('index'));
		exit (0);
	}
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->removeSession($sid);
	try {
		$sessions->save();
		Logger::logInfo("delete-session sid:$sid");
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	SessionMgr::storeMessage("Deleted session sid:$sid");
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'enrol') {
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->enrolUser($sid, SessionMgr::getUsername());
	try {
		$sessions->save();
		Logger::logInfo("enrol sid:$sid, User:".SessionMgr::getUsername());
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'unenrol') {
	Logger::logTrace("");
	$sessions = new Sessions($u->get('sessions_file'));
	try {
		$sessions->unenrolUser($sid, SessionMgr::getUsername());
		$sessions->save();
		Logger::logInfo("unenrol sid:$sid, User:".SessionMgr::getUsername());
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else {
	errorPage('unknown action: '.$_POST['Action']);
}

# vim:filetype=html:ts=4:sw=4
?>
