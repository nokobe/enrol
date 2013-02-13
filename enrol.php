<?php

require_once 'includes/global.php';
$status = array();			// for sending alerts to the user
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

/*
if ($action == "AdminRemove") {
	$USID = $_POST["USID"];
	if ($USID == "") {
		die("missing USID");
	}
	$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
	$session = $sessionarray[0];

	$mu = $session->maxusers;

	$enrolled_list = array();
	if ($session->userlist != "") {
		$enrolled_list = explode ( "|", $session->userlist);
	}

	$user_to_remove = @$_POST["Remove"];
	$key = array_search($user_to_remove, $enrolled_list);
	if ($key === FALSE) { // hmm... not found.. this is unexpected
		$status[] = "Error. Name \"$user_to_remove\" not found in session";
	} else {
		unset ( $enrolled_list[$key] );
		$session->userlist = implode( "|", $enrolled_list);
		$save_changes = 1;
		$when = date($c->get('logfmt_date'), (int)$session->when);
		Logger::logInfo("Admin un-enrolled $user_to_remove from session (ID = $USID, Time = $when, Location = $session->location)");
	}
} else if ($action == "Reset Session ID") {
	// count sessions
	$sortthis = $xml->xpath('/sessions/session');
	$total = count($sortthis);
	if ($total == 0) {
		$xml->nextID = 1;
		$save_changes = 1;
	} else {
		print "<h2>Error: attempt to reset SessionID when there are still sessions</h2>";
	}
}
*/

/* {{ ======================= BEGIN MAIN PAGE ======================= */

if ((SessionMgr::get('adminView') == 1) and (SessionMgr::hasAdminAuth() === FALSE)) {
	header("Location: auth.php");
	exit(0);
}
$t = prepareTemplateEssentials();
$t->isAdmin = SessionMgr::isRegisteredAdmin();
$t->admin = SessionMgr::hasAdminAuth();
$t->adminView = SessionMgr::get('adminView');
$t->hideClosedSessions = SessionMgr::get('hideClosedSessions');
$t->announcements = get_notices($u->get('notices_file'));
$t->announcements = implode("\n", array_splice(explode("\n", $t->announcements), 0, 3)); // trialling... just show the first few lines
		$session->userlist = implode( "|", $enrolled_list);
$t->notices = get_notices($u->get('notices_file'));
$xml = load_sessions_file($u->get('sessions_file'));
$t->sessions = prepareSessionData($xml);
require "templates/main.php";
exit(0);

/* }} ======================= END MAIN PAGE ======================= */

/* ======================= FUNCTIONS ======================= */

function load_sessions_file($file) {
	global $time_last_moded;

	if (!file_exists($file)) {
		die ("fatal: missing sessions file ($file)");
	}
	if (filesize($file) == 0) {
		// starting from scratch - empty sessions file
		$xml = new SimpleXMLElement("<sessions><nextID>1</nextID></sessions>");
	} else {
		$xml = simplexml_load_file($file) or die ("Unable to load XML file!");
	}
	$time_last_moded = filemtime($file);	// NOTE: part 1 - load file and part 2 - get last mod
							// really should be an ATOMIC operation

	return $xml;
} // end: load_sessions_file

function prepareSessionData($xml) {
	global $c, $u;

	$x = array();
	$allSessions = $xml->xpath('/sessions/session');
	usort($allSessions, 'sort_sessions_by_time');
	$sessions = new Sessions($u->get('sessions_file'));

	$isRegisteredAdmin = SessionMgr::isRegisteredAdmin();
	$adminView = SessionMgr::get('adminView');
	$hideClosedSessions = SessionMgr::get('hideClosedSessions');
	$user = SessionMgr::getUsername();
	$isLoggedIn = SessionMgr::isLoggedIn();
	foreach ($allSessions as $s) {
		if ($s->active != 'yes' and ($isRegisteredAdmin == false or $adminView == false)) {
			continue;
		}
		if ($hideClosedSessions and $s->active != 'yes') {
			continue;
		}

		$xo = new stdClass;
		$xo->usid = (int)$s->usid;
		$xo->when = (int)$s->when;
		$xo->whenstr = displayDate($s->when);
		$xo->location = (string)$s->location;
		$xo->classSize = classSize($s);
		$xo->maxClassSize = (int)$s->maxusers;

		$xo->numelements = ceil($xo->maxClassSize / 6) * 6;

		$isActive = $s->active == "yes";

		$xo->sessionStatus = "";
		$xo->sessionops = array();
		if ($isRegisteredAdmin and $adminView) {
			$xo->sessionStatus = $isActive ?
			       	'<button class="btn btn-small btn-success disabled" type=button name="Action" value="opensession">Open</button>'
				: '<button class="btn btn-small btn-danger disabled" type=button name="Action" value="opensession">Closed</button>';

			if (!$isActive) { $xo->sessionops[] = '<button class="btn btn-small" type="submit" name="Action" value="open-session">Open Session</button>'; }
			$xo->sessionops[] = '<button class="btn btn-small" type="submit" name="Action" value="edit-session">Edit Session</button>';
			if ($isActive) { $xo->sessionops[] = '<button class="btn btn-small" type="submit" name="Action" value="close-session">Close Session</button>'; }
			if (!$isActive) { $xo->sessionops[] = '<button class="btn btn-small" type="submit" name="Action" value="delete-session" onclick="return confirm(\"Are you Sure\");">Delete Session</button>'; }
		}
		if ($isActive and $isLoggedIn) {
			if (userIsEnrolled($user, $s)) {
				$xo->sessionops[] = '<button class="btn btn-small btn-danger" type="submit" name="Action" value="unenrol">Un-enrol</button>'; 
			} else {
				if (!classIsFull($s)) {
					 $xo->sessionops[] = '<button class="btn btn-small btn-success" type="submit" name="Action" value="enrol">Enrol</button>';
				}
			}
		}
		$xo->users = array();
		foreach (enrolled($s) as $who) {
			if ($who == $user) {
#				$xo->users[] = '<td class="place occupied self">$who <i class="icon-trash"></i></td>';
				$xo->users[] = "<td class='place occupied self'>".htmlentities($who)."</td>";
			} else {
				$xo->users[] = "<td class='place occupied'>".htmlentities($who)."</td>";
			}
		}
		$x[] = $xo;
	}
	return $x;
}

function userIsEnrolled($user, $s) {
	if ($s->userlist == "") {
		return 0;
	}
	$users = explode("|", $s->userlist);
	return array_search($user, $users) === FALSE ? FALSE : TRUE;
}

function classSize($s) {
	if ($s->userlist == "") {
		return 0;
	}
	return count(explode("|", $s->userlist));
}

function classIsFull($s) {
	return $s->maxusers == classSize($s);
}

function enrolled($s) {
	if ($s->userlist == "") {
		return array();
	}
	return explode("|", $s->userlist);
}

function sort_sessions_by_time($a, $b) {
	if ((int)$a->when == (int)$b->when) {
		return 0;
	}
	return ((int)$a->when < (int)$b->when) ? -1 : 1;
}

?>
