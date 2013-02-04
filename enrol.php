<?php

require_once 'includes/global.php';
$status = array();			// for sending alerts to the user

/* ======================= READ POST INPUT ======================= */

$action = @$_POST["Action"];
$context = @$_POST["Context"];
log_debug("action = $action context = $context");

/* =======================  HANDLE Login/Session/Cookie =======================  */

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

# this admin_mode code should be converted to calls to SessionMgr::admin_stuff()
#$admin_mode = SessionMgr::hasAdminAuth();
$admin_mode = SessionMgr::isRegisteredAdmin();
log_debug("admin_mode is currently : $admin_mode");

$save_changes = 0;
$xml = load_sessions_file($u->get('sessions_file'));

/* {{ ======================= BEGIN: HANDLE ACTIONS ======================= */

if ($action == "edit-notices") {
#if ($context == "notices") 
	if ($action == "Edit") {
		print_header($u->title, $c->get('version'));
		top_bar($u->title, "Edit Notices");
		$FILE = $u->get('notices_file');
		print_notices($FILE, $action == "Edit" ? 0 : $admin_mode);
		$notices = file_get_contents($FILE);
		echo "Editing: $FILE" . ":<br />";
		$self = $c->get('php_self');
		echo "<form method=\"post\" action=\"$self\">";
		echo "<textarea name=\"newnotices\" cols=160 rows=20>$notices</textarea>";
		echo "<p>\n";
		echo "<input type='hidden' name='Context' value='notices'>";
		echo "<input type='submit' name='Action' value='Cancel'>";
		echo "<input type='submit' name='Action' value='Save' >";
		echo "</form>";
		print_footer();
		exit(0);
	}
	if ($action == "Cancel") {
		// do nothing here. continue on as per normal
	}
	if ($action == "Save") {
		$newtext = $_POST["newnotices"];
		$FILE = $u->get('notices_file');
		$n = file_put_contents($FILE, $newtext);
		log_event("saved $n bytes to $FILE");
	}
} else if ($action == "Create New Session") {
//		echo "create new session!!!";
	$day = $_POST["sess_day"];
	$mon = $_POST["sess_month"];
	$year = $_POST["sess_year"];
	$hour = $_POST["sess_hour"];
	$min = $_POST["sess_minute"];
	$ampm = $_POST["sess_ampm"];
//		print "<h4>timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm)</h4>\n";
	$timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm);
	$newsession = $xml->addChild('session');
	$newsession->addChild("usid", (int)$xml->nextID);
	$newsession->addChild("active", "no");
	$newsession->addChild("when", $timestamp);
	$newsession->addChild("location", $_POST["Location"]);
	$newsession->addChild("maxusers", $_POST["Maxusers"]);
	$xml->nextID = (int)$xml->nextID + 1;

	$save_changes = 1;

	$when = date($c->get('logfmt_date'), (int)$newsession->when);
	log_event("created new session (ID = $xml->nextID, Time = $when, Location = $newsession->location)");
} else if ($action == "enrol") {
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
	$curr_size = count($enrolled_list);
	if ($curr_size >= $mu) {
		$status[] = "Sorry, that class is now full";
	} else {
		$enrolled_list[] = SessionMgr::getUsername();
		$session->userlist = implode( "|", $enrolled_list);
#			$status[] = "Added you to session $ID";
		$save_changes = 1;
		$when = date($c->get('logfmt_date'), (int)$session->when);
		log_event("enrolled in session (ID = $USID, Time = $when, Location = $session->location)");
	}
} else if ($action == "unenrol") {
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

	$me = SessionMgr::getUsername();
	$key = array_search($me, $enrolled_list);
	if ($key === FALSE) { // hmm... not found.. this is unexpected
		$status[] = "Error. Your name \"$me\" not found in list";
	} else {
		unset ( $enrolled_list[$key] );
		$session->userlist = implode( "|", $enrolled_list);
#			$status[] = "Removed you from session $ID";
		$save_changes = 1;
		$when = date($c->get('logfmt_date'), (int)$session->when);
		log_event("un-enrolled in session (ID = $USID, Time = $when, Location = $session->location)");
	}
} else if ($action == "AdminRemove") {
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
		log_event("Admin un-enrolled $user_to_remove from session (ID = $USID, Time = $when, Location = $session->location)");
	}
} else if ($action == "Open") {
	$USID = $_POST["USID"];
	$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
	$session = $sessionarray[0];
	$session->active = "yes";
	$save_changes = 1;
	$when = date($c->get('logfmt_date'), (int)$session->when);
	log_event("opened session (ID = $USID, Time = $when , Location $session->location)");
} else if ($action == "Close") {
	$USID = $_POST["USID"];
	$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
	$session = $sessionarray[0];

	$session->active = "no";
	$save_changes = 1;
	$when = date($c->get('logfmt_date'), (int)$session->when);
	log_event("closed session (ID = $USID, Time = $when , Location $session->location)");
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
#} else if ($action == "Edit" and $context == "editsession" ) {
} else if ($action == "edit-session") {
	print_header($u->get('title'), $c->get('version'));
	top_bar($u->get('title'), "Edit Session");

	$USID = $_POST["USID"];
	$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
	$session = $sessionarray[0];
	$self = $c->get('php_self');
	print "<fieldset>
		<legend>Edit Session (SessionID: $USID)</legend>
		<form method=\"post\" action=\"$self\">
		<input type=\"hidden\" name=\"Context\" value='editsession'>
		<input type=\"hidden\" name=\"USID\" value='$USID'>";

	print_datetime_selection((int)$session->when);
	print "
		Location: <input type=\"text\" size=\"36\" maxlength=\"36\" name=\"Location\" value=\"$session->location\">
		&nbsp;
		Maximum attendees:<input type=\"text\" size='2' maxlength='2' name='Maxusers' value='$session->maxusers'>
		&nbsp;
		<br />
		<br />
		<input type=\"submit\" name='Action' value='Cancel'>
		<input type=\"submit\" name='Action' value='Save'>";
	print "
		</form>
		</fieldset>";
	print_footer();
	exit(0);
} else if ($action == "Delete" and $context == "editsession" ) {
	$USID = $_POST["USID"];

	$xml = delete_session($USID, $xml);
	$save_changes = 1;
	log_event("deleted session (ID = $USID)");
} else if ($action == "Cancel" and $context == "editsession") {
	// do nothing
} else if ($action == "Save" and $context == "editsession") {
	// get POST values for edited session including USID
	$USID = $_POST["USID"];
	$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
	$session = $sessionarray[0];

	$day = $_POST["sess_day"];
	$mon = $_POST["sess_month"];
	$year = $_POST["sess_year"];
	$hour = $_POST["sess_hour"];
	$min = $_POST["sess_minute"];
	$ampm = $_POST["sess_ampm"];
	$timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm);
	$session->when = $timestamp;
	$session->location = $_POST["Location"];
	$session->maxusers = $_POST["Maxusers"];
	$save_changes = 1;
	$when = date($c->get('logfmt_date'), (int)$session->when);
	log_event("edited session (ID = $USID, Time = $when , Location $session->location)");
} else {
	if ($action != "") {
		log_event("unknown action: $action in context: $context");
//			echo "unknown action: $action in context: $context\n";
	}
}
/* }} ======================= END: HANDLE ACTIONS ======================= */

/* ======================= SAVE CHANGES ======================= */

if ($save_changes == 1) {
	if (save_session_file($xml, $u->get('sessions_file')) === FALSE) {
		die ("save failed. Please reload the page and try again");
	}
#	$base = $c->get('php_base');
	$self = $c->get('php_self');
	log_debug("redirecting to here: $self");
	header("Location: $self");
	exit(0);
	log_debug("not reached");
}

/* {{ ======================= BEGIN MAIN PAGE ======================= */

	$t = new stdClass;
	$t->title = $u->get('title');
	$t->base = ".";
	$t->username = SessionMgr::getUsername();
	$t->loggedIn = SessionMgr::isLoggedIn();
	$t->admin = SessionMgr::hasAdminAuth();

	$t->isAdmin = SessionMgr::isRegisteredAdmin();
	$t->adminView = SessionMgr::get('adminView');
	$t->hideClosedSessions = SessionMgr::get('hideClosedSessions');

	$t->notices = get_notices($u->get('notices_file'), SessionMgr::isRegisteredAdmin() and SessionMgr::get('adminView'));
	$t->self = $c->get('php_self');
	$t->sessions = prepareSessionData($xml);
	require "templates/main.php";
	exit(0);

/* }} ======================= END MAIN PAGE ======================= */
/* {{ ======================= BEGIN OLD MAIN PAGE ======================= */


	print_header($u->get('title'), $c->get('version'));
	top_bar($u->get('title'), "");
	print_notices($u->get('notices_file'), $action == "Edit" ? 0 : $admin_mode);
	print_refresh_button();
	print_admin_toolbox($action == "Edit" ? 0 : $admin_mode);

	// -- option to add new session... but only if we're not editing something else
	if ($admin_mode == 1 and $action != "Edit") {
		print "<div class=\"adminFunction\">\n";
		$self = $c->get('php_self');
		print "<fieldset>
			<legend>Add New Session</legend>
			<form method=\"post\" action=\"$self\">
			<input type=\"hidden\" name=\"Context\" value='sessions'>";

		if (@$_POST["sess_day"] != "") {
			$day = $_POST["sess_day"];
			$mon = $_POST["sess_month"];
			$year = $_POST["sess_year"];
			$hour = $_POST["sess_hour"];
			$min = $_POST["sess_minute"];
			$ampm = $_POST["sess_ampm"];
			$timestamp = my_mktime($day, $mon, $year, $hour, $min, $ampm);
			print_datetime_selection($timestamp);
		} else {
			print_datetime_selection("");
		}
		$loc_dflt =  @$_POST["Location"] != "" ? $_POST["Location"] : "";
		$siz_dflt =  @$_POST["Maxusers"] != "" ? $_POST["Maxusers"] : $u->get('default_session_size');

		print "
			Location: <input type=\"text\" size=\"36\" maxlength=\"36\" name=\"Location\" value='$loc_dflt'>
			&nbsp;
			Maximum attendees:<input type=\"text\" size='2' maxlength='2' name='Maxusers' value='$siz_dflt'>
			&nbsp;
			<input type=\"submit\" name='Action' value='Create New Session'>";
		print "
			</form>
			</fieldset>";
		print "</div>\n";
	}

	/* ======================= THE ENROLMENT TABLE ======================= */

# load_sessions_file is not being done here because is it already loaded.
# but this is a place holder while we consider that we may need to re-load the
# file to reset the time marker (that we might use to detect changes to the file whilst we
# were trying to change it.) But this may be unncessary here because there is no saving of
# the data after this point... all the actions are performed above!

#	$xml = load_sessions_file($SESSIONS_FILE);

	$header_counter = 0;
	$in_use = array();

	// sort data
	$sortthis = $xml->xpath('/sessions/session');
	usort($sortthis, 'sort_sessions_by_time');

	$available = 0;
	$total = count($sortthis);
	$prev_week_number = -1;	// initial value must be invalid
	$jump = 100;	// jump number for anchor points
	foreach ($sortthis as $session) {
		$USID = (int) $session->usid;

		if (isset($in_use[$USID])) {
			die ("fatal: USID($USID) already in use. Please contact administrator");
		}
		$in_use[$USID] = 1;

		if ($session->active != "yes" and !$admin_mode) {
			continue;
		}
		if ($session->active == "yes") {
			$available ++;
		}

		$ulist = array();	# empty array
		if ($session->userlist != "") {
			$ulist = explode ( "|", $session->userlist);
			$count = count($ulist);
		} else {
			$count = 0;
		}
		$max = $session->maxusers;
		$jump ++;
		echo "<a name='jump$jump'></a>";
		echo '<div class="'.($session->active == "yes" ? "openSession" : "closedSession").'">';
		echo "<table class='table bordered'>";

		// calculate if we need to print a weekly header:
		// if this session is not in same week as prev session
		// 	print header for this week
		// prev = week number of this session
		$this_week_number = date("W", (int)$session->when);

		if ($this_week_number != $prev_week_number) {
			$mondaystr = weeknumber2monday($this_week_number, (int)$session->when);
			echo <<<EOT
<thead>
Sessions for week starting $mondaystr</div>
</thead>
EOT;
			echo "<div class='weeklyheader'>Sessions for week starting $mondaystr</div>\n";
#			echo "<tr bgcolor='".$u->get('tableheadercolor').'>";
#			if ($admin_mode) {
#				echo "<th class='border' colspan='2'>Administration</th>";
#				if ($show_session_id) {
#					echo "<th class='border'>ID</th>";
#				}
#			}
#			echo "<th class='border'>Actions</th><th class='border'>When</th><th class='border'>Location</th><th class='border' colspan=$max_enrolments_per_line>Attendees</th></tr>\n";
		}
		$prev_week_number = date("W", (int)$session->when);	// return value is a string, but we should be able to treat it as an integer.

		$header_counter ++;

		echo "<tr>";
		// 1 - Admin
		//	2 - SESS_ID
		// 3 - Actions
		// ...

		if ($admin_mode) {
			$self = $c->get('php_self');
			echo "<form method=\"post\" action=\"$self#jump$jump\" style='padding-top: 5px; padding-bottom: 0px; margin-bottom: 0px'>";
			if ($session->active == "yes") {
				echo "<td class='border' width='55' bgcolor='green' align='center'>Open</td><td class='border' width='95' align='center'>";
				echo "<button type='submit' name='Action' value='Close' alt='Close Session' title='Close Session' class='icon'><img src='icons/remove.png' class='icon'></button>";
				echo "<button type='submit' name='Action' value='Edit' alt='Edit Session' title='Edit Session' class='icon'><img src='icons/edit.png' class='icon'></button>";
			} else {
				echo "<td class='border' width='55' bgcolor='red' align='center'>Closed</td><td class='border' width='95' align='center'>";
				echo "<button type='submit' name='Action' value='Open' alt='Open Session' title='Open Session' class='icon'><img src='icons/add.png' class='icon'></button>";
				echo "<button type='submit' name='Action' value='Edit' alt='Edit Session' title='Edit Session' class='icon'><img src='icons/edit.png' class='icon'></button>";
				echo "<button type='submit' name='Action' value='Delete' alt='Delete Session' title='Delete Session' class='icon'><img src='icons/close.png' class='icon'></button>";
			}
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
			echo "<input type='hidden' name='Context' value='editsession'>";
			echo "</td>";
			echo "</form>";
			if ($u->get('show_session_id')) {
				echo "<td class='border' width='26' align='center'>$USID</td>";
			}
		}
		# Actions...
		echo "<td class='border' class='border' width='100' align='center' valign='center'>";
		if ($session->active == "yes") {
			$self = $c->get('php_self');
			echo "<form method=\"post\" action=\"$c->php_self#jump$jump\" style=' padding-bottom: 0px; margin-bottom: 0px'>";
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";

			$me = SessionMgr::getUsername();
			if ($me == "") {
				echo "<input type=\"submit\" value=\"Log in first\" disabled>";
			} else {
				if (array_search($me, $ulist) === FALSE) {	# if not enroled...
					if ($count < $max) {
						echo "<input style=\"background:lightgreen\" type=\"submit\" name='Action' value=\"Add Me\">";
					} else {
						echo "<input type=\"submit\" value=\"Class is full\" disabled>";
					}
				}
				else {
					echo "<input style=\"background:#FF4400\" type=\"submit\" name='Action' value=\"Remove Me\">";
				}
			}
			echo "</form>\n";
		}
		echo "</td>";
		$year = date('Y', (int)$session->when);
		$thisyear = date('Y');
		if ($year == $thisyear) {
			$reformat = date('D d M \a\t g:ia', (int)$session->when);
		} else {
			$reformat = date('D d M Y \a\t g:ia', (int)$session->when);
		}

		echo "<td class='border' align='center' width='200'>".$reformat."</td>";
		echo "<td class='border' align='center' width='150'>".$session->location."<br /></td>\n";

		# show session enrolments
		print "<td class='sessionholder'><table class='table sessionrows'><tr>\n";
		$me = SessionMgr::getUsername();
		for ($i = 0; $i < $u->get('max_enrolments_per_line'); $i ++) {
			if ($i < $count) {
				if ($ulist[$i] == $me) {
					$me = $u->get('mysquarecolor');
					echo "<td class='sessionelement' bgcolor='$me'>";
#					echo "<td class='sessionelement occupied' bgcolor='$me'>";
					echo "$ulist[$i]";
					echo "</td>";
				} else {
					$you = $u->get('yoursquarecolor');
					echo "<td class='sessionelement' bgcolor='$you'>";
#					echo "<td class='sessionelement occupied' bgcolor='$you'>";
					echo "$ulist[$i]";
					if ($admin_mode and $session->active == "yes") {
						echo "<div class=\"adminFunction\">\n";
						$self = $c->get('php_self');
						echo "<form method=\"post\" action=\"$self#jump$jump\">";
						echo "<input type=\"hidden\" name=\"Remove\" value=\"$ulist[$i]\">";
						echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
#						echo "<input type=\"submit\" name='Action' value=\"AdminRemove\">";
						echo "<i class='icon-trash'></i>";
						echo "</form>\n";
						echo "</div>\n";
					}
					echo "</td>";
				}
			} else if ($i < $max) { # empty slot
				$none = $u->get('nonesquarecolor');
				echo "<td class='sessionelement' bgcolor='$none'>";
#				echo "<td class='sessionelement free' bgcolor='$none'>";
				echo "&nbsp;";
						echo "<i class='icon-plus'></i>";
				echo "</td>";
			} else { # disabled (not a spot...just a placeholder)
				echo "<td class='sessionelement' bgcolor='#efe'>";
#				echo "<td class='sessionelement disabled' bgcolor='#efe'>";
				echo "&nbsp;";
				echo "</td>";
			}
#			if (($i+1) % $u->get('max_enrolments_per_line') == 0) {
#				print "</tr><tr>";
#			}
		}
		$remainder = $max % $u->get('max_enrolments_per_line');
		if ($remainder > 0) {
			print "<td>&nbsp;</td>\n";
		}
		print "</tr></table></td>";
		echo "</tr>";
		echo "</table>";
		echo "</div>";
	}
	$jump ++;
	echo "<a name='jump$jump'></a>";	// this is here so that the jump to the last item will still work - even if the last item is deleted!
	if ($available == 0) {
		print "<br /><br />There are currently no sessions available\n";
	}

	if ($admin_mode and $total == 0 and (int)$xml->nextID > 1) {
		echo "<br />";
		echo "<br />";
		echo "<table border='1' bgcolor='red' ><tr><td>";
		$self = $c->get('php_self');
		echo "<form method=\"post\" action=\"$self\" style=' padding-bottom: 0px; margin-bottom: 0px'>";
		echo "<input type='submit' name='Action' value='Reset Session ID'>";
		echo "</form>";
		echo "</td><td>";
		echo "This will reset the SessionID back to 1";
		echo "</td></tr></table>";
	}

	/* ======================= SHOW ANY STATUS ALERTS ======================= */
	if ($status) {
		foreach ($status as $m) {
			#echo "<div class='info'>$m</div>";
			echo "<script language=\"javascript\" type=\"text/javascript\">";
			echo "alert('$m')\n";
			echo "</script>";
		}
	}
	print_footer();
/* }} ======================= END OLD MAIN PAGE ======================= */
	exit(0);

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

function save_session_file($xml, $file) {
	global $time_last_moded;

	$current_last_mode = filemtime($file);
	if ($current_last_mode != $time_last_moded) {
		return FALSE;
	}

	$bakfile = "$file.bak";
	if (! copy ( $file, $bakfile ) ) {
		die  ("Copy to backup failed. Please contact Administrator and report this error");
	}
	$xml->asXML( $file ) or die ("fatal: Unable to save XML file!");

	return TRUE;
} // end: save_session_file

// delete_session:
//	deletes session with given USID and returns new xml object

function delete_session($id, $xml) {

	// ==================== convert to DOM ====================
	$dom = new DOMDocument();
	$dom->loadXML($xml->asXML());

	// ==================== select the session to delete ====================
	$xpath = new DomXpath($dom);
	$session = $xpath->query("//sessions/session[usid=$id]");

	// ==================== delete element ====================
	$session->item(0)->parentNode->removeChild($session->item(0));

	// ==================== convert back to XML object ====================
	$xml = new SimpleXMLElement($dom->saveXML());

	return $xml;
} // end: delete_session


function print_header($title, $version) {
	print <<<EOT
<html>
<head>
<title>$title</title>
<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="css/bootstrap-responsive.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
<script type='text/javascript' src="js/showhide.js"></script>
</head>
<body>
<div class="container">
EOT;
}

// given a week number, return a string represent Monday of that week
function weeknumber2monday($week_number, $src_timestamp) {
	$tsa = getdate($src_timestamp);
	$monday_day = $tsa["yday"] - $tsa["wday"] + 1;

	$zero_day = mktime(12, 0, 0, 1, 1, $tsa["year"]);

	$monday_ts = $zero_day + ($monday_day * 86400);

	return date("l jS F, Y", $monday_ts);
}

function print_footer() {
	global $c, $u;

	$version = $c->get('version');
	echo "<br />\n";
	if (SessionMgr::hasAdminAuth() and Sessionmgr::get('adminView')) {
		echo "<div class='footer'>&bull; enrol (version $version) by mbates &bull; Icons by <a href='http://dryicons.com;'>DryIcons</a> &bull;</div>";
	} else {
		echo "<div class='footer'>&bull; enrol (version $version) by mbates &bull; </div>";
	}
	echo <<<EOT
<script src="js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
EOT;
	echo "</div>";
	echo "</body></html>\n";
} // end: print_footer

function get_notices($file, $showEditButton) {
	global $c;

	$results = "";

	$tainted = file_get_contents($file);
	$notices = strip_tags($tainted, "<a><font>");
	$lines = explode ("\n", $notices);
	$results .= '<div style="width:90%; padding-left:20px;">
		<div style="width: 60px; position: relative; top: 0px; left: 20px; text-align: center; font-weight: bold; background: white; padding:3px; border: 0px solid black; z-index: 1;">Notices</div>
		<div style="position: relative; top: -8px; border: 1px solid black; z-index: 0;">
			<div style="padding-top:10px; padding-bottom:10px; padding-left:0px;">
	';

	$results .= "<ul>\n";
	foreach ( $lines as $line ) {
		if (preg_match('/^\s*$/', $line)) {
			$results .= "<li>&nbsp;";
		} else {
			$results .= "<li><img src='icons/bullet.gif' alt='-' border='0'> $line\n";
		}
	}
	$results .= "</ul>\n";
	if ($showEditButton == 1) {
		if ($notices != $tainted) { # something was stripped out!
			$results .= "<font class='securityalert'>SECURITY NOTICE : disallowed html tags found in Notices</font>\n";
		}
		$results .= "<div class=\"adminFunction\">\n";
		$self = $c->get('php_self');
		$results .= "<form method=\"post\" action=\"$self\">";
		$results .= "<input type=\"hidden\" name='Context' value=\"notices\">";
		$results .= "&nbsp;&nbsp;<input type=\"submit\" name='Action' value=\"Edit\">";
		$results .= "</form>";
		$results .= "</div>";
	}
	$results .= "</div></div></div>\n";
	return $results;
} // end: get_notices

function print_notices($file, $showEditButton) {
	global $c;

	$tainted = file_get_contents($file);
	$notices = strip_tags($tainted, "<a><font>");
	$lines = explode ("\n", $notices);
	echo '<div style="width:90%; padding-left:20px;">
		<div style="width: 60px; position: relative; top: 0px; left: 20px; text-align: center; font-weight: bold; background: white; padding:3px; border: 0px solid black; z-index: 1;">Notices</div>
		<div style="position: relative; top: -8px; border: 1px solid black; z-index: 0;">
			<div style="padding-top:10px; padding-bottom:10px; padding-left:0px;">
	';

	echo "<ul>\n";
	foreach ( $lines as $line ) {
		if (preg_match('/^\s*$/', $line)) {
			print "<li>&nbsp;";
		} else {
			echo "<li><img src='icons/bullet.gif' alt='-' border='0'> $line\n";
		}
	}
	echo "</ul>\n";
	if ($showEditButton == 1) {
		if ($notices != $tainted) { # something was stripped out!
			echo "<font class='securityalert'>SECURITY NOTICE : disallowed html tags found in Notices</font>\n";
		}
		print "<div class=\"adminFunction\">\n";
		$self = $c->get('php_self');
		echo "<form method=\"post\" action=\"$self\">";
		echo "<input type=\"hidden\" name='Context' value=\"notices\">";
		echo "&nbsp;&nbsp;<input type=\"submit\" name='Action' value=\"Edit\">";
		echo "</form>";
		echo "</div>";
	}
#echo "</fieldset>\n";
	echo "</div></div></div>\n";
} // end: print_notices

function log_event($text) {
	global $c, $u;

	$t = time();
	$ts = date($c->get('logfmt_date'));
	$fh = fopen($u->get('event_log'), 'a') or die("can't open logfile");
	$ip = $_SERVER['REMOTE_ADDR'];
	fwrite($fh, $ts. " ".$ip." as ".SessionMgr::getUsername(). ": ".$text. "\n");
	fclose($fh);
}

function my_mktime($day, $mon, $year, $hour, $min, $ampm) {
	if ($ampm == "PM" and $hour != 12) {
		$hour += 12;
	} else if ($ampm == "AM" and $hour == 12) {
		$hour -= 12;
	}
	return mktime($hour, $min, 0, $mon, $day, $year);
}
//		int mktime  ([  int $hour = date("H")  [,  int $minute = date("i")  [,  int $second = date("s")  [,  int $month = date("n")  [,  int $day = date("j")  [,  int $year = date("Y")  [,  int $is_dst = -1  ]]]]]]] )

function top_bar($title, $breadcrumb) {
	global $c;

	$BREAD = "";
	if ($breadcrumb != "") {
		$BREAD = " > $breadcrumb ";
	}
	$self = $c->get('php_self');
	echo <<<EOT
	<div class='topbar'>
		<div id='title'><a href="$self">$title</a>$BREAD</div>
		<div id='login'>
EOT;
		$ADMIN = "";
		if (SessionMgr::isRegisteredAdmin())	{ $ADMIN = "(Admin access available) "; }
		if (SessionMgr::hasAdminAuth())		{ $ADMIN = "(Admin access enabled) "; }
		if(SessionMgr::getUsername() != "") {
			$user = SessionMgr::getUsername();
			print "Welcome, $user. $ADMIN| <a href=\"logout.php\">Logout</a>";
		} else {
			$self = $c->get('php_self');
			print <<<EOT
			<form method="post" action="$self">
			Enter your name: <input type="text" size="36" maxlength="36" name="Name">
			<input type="submit" value="Login" name="submit-login">
			<input type="checkbox" name="rememberMe" value="1"> Remember me
			</form>
EOT;
		}
		echo <<<EOT
		</div>
	</div>
EOT;
}

function print_refresh_button() {
	global $c, $u;

	// print refresh button
	if ($u->get('on_production') === FALSE) {
		$self = $c->get('php_self');
		echo "<form method=\"post\" action=\"$self\">";
		echo "<input type='submit' value='Reload'>";
		echo "</form>";
	}
}

function print_admin_toolbox($admin_mode) {
	if ($admin_mode == 1) {
		echo '<div id="adminBox">';
		echo <<<EOT
			<fieldset>
			<legend>Admin Toolbox</legend>
EOT;
		echo "<a href=\"javascript:showHideDivClass('adminFunction');\" class=\"linkAsButton\">show/hide Admin Functions</a>";
		echo "<br />";
		echo "<a href=\"javascript:showHideDivClass('closedSession');\" class=\"linkAsButton\">show/hide Closed Sessions</a>";
		echo '</div>';
		echo "<br />";
		echo "</fieldset>\n";
	}
}

function sort_sessions_by_time($a, $b) {
	if ((int)$a->when == (int)$b->when) {
		return 0;
	}
	return ((int)$a->when < (int)$b->when) ? -1 : 1;
}

# SessionMgr::hasAdminAuth();
# SessionMgr::isRegisteredAdmin();
# SessionMgr::get('adminView');
# SessionMgr::get('hideClosedSessions');

function prepareSessionData($xml) {
	$x = array();
	$allSessions = $xml->xpath('/sessions/session');
	usort($allSessions, 'sort_sessions_by_time');
	foreach ($allSessions as $s) {
		if ($s->active != 'yes' and (!SessionMgr::isRegisteredAdmin() or !SessionMgr::get('adminView'))) {
			continue;
		}
		if (SessionMgr::get('hideClosedSessions') and $s->active != 'yes') {
			continue;
		}

		$xo = new stdClass;
		$xo->usid = (int)$s->usid;
		$xo->when = displayDate($s->when);
		$xo->location = (string)$s->location;
		$xo->classSize = classSize($s);
		$xo->maxClassSize = (int)$s->maxusers;

		$isActive = $s->active == "yes";
		$user = SessionMgr::getUsername();

		$xo->sessionStatus = "";
		$xo->sessionops = array();
		if (SessionMgr::isRegisteredAdmin() and SessionMgr::get('adminView')) {
			$xo->sessionStatus = $isActive ?
			       	'<button class="btn btn-small btn-success disabled" type=button name="Action" value="opensession">Open</button>'
				: '<button class="btn btn-small btn-danger disabled" type=button name="Action" value="opensession">Closed</button>';

			if (!$isActive) { $xo->sessionops[] = '<button class="btn btn-small btn-link" type="submit" name="Action" value="open-session">Open Session</button>'; }
			$xo->sessionops[] = '<button class="btn btn-small btn-link" type="submit" name="Action" value="edit-session">Edit Session</button>';
			if ($isActive) { $xo->sessionops[] = '<button class="btn btn-small btn-link" type="submit" name="Action" value="close-session">Close Session</button>'; }
			if (!$isActive) { $xo->sessionops[] = '<button class="btn btn-small btn-link" type="submit" name="Action" value="delete-session">Delete Session</button>'; }
		}
		if ($isActive and SessionMgr::isLoggedIn()) {
			if (userIsEnrolled($user, $s)) {
				$xo->sessionops[] = '<button class="btn btn-small btn-danger" type="submit" name="Action" value="unenrol">Un-enrol</button>'; 
			} else {
				if (!classIsFull($s)) {
					 $xo->sessionops[] = '<button class="btn btn-small btn-success" type="submit" name="Action" value="enrol">enrol</button>';
				}
			}
		}
		$xo->users = array();
		foreach (enrolled($s) as $who) {
			if ($who == $user) {
#				$xo->users[] = '<td class="place occupied self">$who <i class="icon-trash"></i></td>';
				$xo->users[] = "<td class='place occupied self'>$who</td>";
			} else {
				$xo->users[] = "<td class='place occupied'>$who</td>";
			}
		}
#		print "<pre>";
#		print_r($xo);
#		print "</pre>";

		$x[] = $xo;
	}
	return $x;
}

function userIsEnrolled($user, $s) {
	if ($s->userlist == "") {
		return 0;
	}
	$users = explode("|", $s->userlist);
	return array_search($user, $users);
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

/*
 * return nicely formated date
 */
function displayDate($timestamp) {
	$year = date('Y', (int) $timestamp);
	$thisyear = date('Y');
	if ($year == $thisyear) {
		return date('D d M \a\t g:ia', (int)$timestamp);
	} else {
		return date('D d M Y \a\t g:ia', (int)$timestamp);
	}
}

?>
