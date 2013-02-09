<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if (!isset($_POST['Action']) and !isset($_GET['Action'])) { errorPage("missing Action"); die(); }
if (!isset($_POST['USID'])) { errorPage("missing USID"); die(); }

$sid = $_POST["USID"];

if ($c->get('debug')) {
        echo "<pre>";
        echo "Session:";
        print_r($_SESSION);
        echo "Post:";
        print_r($_POST);
        echo "</pre>";
}

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
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	SessionMgr::storeMessage("Deleted session ($sid)");
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'enrol') {
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->enrolUser($sid, SessionMgr::getUsername());
	try {
		$sessions->save();
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else if ($_POST['Action'] == 'unenrol') {
	log_debug('Action = unenrol');
	$sessions = new Sessions($u->get('sessions_file'));
	try {
		$sessions->unenrolUser($sid, SessionMgr::getUsername());
		$sessions->save();
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: ".$c->get('index'));
} else {
	errorPage('unknown action: '.$_POST['Action']);
}

?>
