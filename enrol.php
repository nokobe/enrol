<?php

require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

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
$t->announcements = get_notices($u->get('announcements_file'));
$t->notices = get_notices($u->get('notices_file'));
$t->use_logo = $u->get('use_logo');
$t->logo = $u->get('logo');
$t->sessions = prepareSessionData();
require "templates/main.php";
exit(0);

/* }} ======================= END MAIN PAGE ======================= */

function prepareSessionData() {
	global $c, $u;

	$x = array();

	$sessions = new ManageSessions($u->get('sessions_file'));
	$allSessions = $sessions->getSessions();

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
		$xo->classSize = ManageSessions::getClassSize($s);
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
			if (ManageSessions::userIsEnrolled($s, $user)) {
				$xo->sessionops[] = '<button class="btn btn-small btn-danger" type="submit" name="Action" value="unenrol">Un-enrol</button>'; 
			} else {
				if (!ManageSessions::classIsFull($s)) {
					 $xo->sessionops[] = '<button class="btn btn-small btn-success" type="submit" name="Action" value="enrol">Enrol</button>';
				}
			}
		}
		$xo->users = array();
		foreach (ManageSessions::getEnrolled($s) as $who) {
			$adminRemove = '<a href="adminRemove.php?sid='.$xo->usid.'&user='.urlencode($who).'" alt="admin remove user"><i class="icon-trash"></i></a>';
			if ($who == $user) {
				if ($isRegisteredAdmin and $adminView) {
					$xo->users[] = '<td class="place occupied self">'.htmlentities($who)." $adminRemove</td>";
				} else {
					$xo->users[] = "<td class='place occupied self'>".htmlentities($who)."</td>";
				}
			} else {
				if ($isRegisteredAdmin and $adminView) {
					$xo->users[] = "<td class='place occupied'>".htmlentities($who)." $adminRemove</td>";
				} else {
					$xo->users[] = "<td class='place occupied'>".htmlentities($who)."</td>";
				}
			}
		}
		$x[] = $xo;
	}
	return $x;
}

?>
