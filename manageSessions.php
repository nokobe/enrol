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

$sid = $_POST["USID"];

if ($c->get('debug')) {
        echo "<pre>";
        echo "Session:";
        print_r($_SESSION);
        echo "Post:";
        print_r($_POST);
        echo "</pre>";
}

if ($_POST['Action'] == "edit-session") {
	$sessions = new Sessions($u->get('sessions_file'));
	$s = $sessions->getSession($sid);

	$isActive = $s->active == "yes";
	$t->status = $s->sessionStatus = $isActive ?
		'<button class="btn btn-small btn-success disabled" type=button name="Action" value="opensession">Open</button>'
		: '<button class="btn btn-small btn-danger disabled" type=button name="Action" value="opensession">Closed</button>';
	$t->s = $s;
	$t->sessionTime = displayDate((int)$s->when);

	require 'templates/editsession.php';
} else if ($_POST['Action'] == 'cancel-edit-session') {
	header("Location: enrol.php");
} else if ($_POST['Action'] == 'save-edit-session') {
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
	$sessions->save();
	header("Location: enrol.php");
} else if ($_POST['Action'] == 'open-session') {
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->setAttr($sid, array('active' => 'yes'));
	$sessions->save();
	header("Location: enrol.php");
} else if ($_POST['Action'] == 'close-session') {
	$sessions = new Sessions($u->get('sessions_file'));
	$sessions->setAttr($sid, array('active' => 'no'));
	$sessions->save();
	header("Location: enrol.php");
} else if ($_POST['Action'] == 'enrol') {
	$sessions = new Sessions($u->get('sessions_file'));
	try {
		$sessions->enrolUser($sid, SessionMgr::getUsername());
		$sessions->save();
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: enrol.php");
} else if ($_POST['Action'] == 'unenrol') {
	log_debug('Action = unenrol');
	$sessions = new Sessions($u->get('sessions_file'));
	try {
		$sessions->unenrolUser($sid, SessionMgr::getUsername());
		$sessions->save();
	} catch (Exception $e) {
		errorPage($e->getMessage());
	}
	header("Location: enrol.php");
} else {
	errorPage('unknown action: '.$_POST['Action']);
}

?>
