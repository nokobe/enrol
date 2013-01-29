<?php
	$version = "version 2.3.2-exp.2";
	$BASE = "./";
	require_once $BASE.'includes/config.inc.php';
	require_once $BASE.'includes/datetime.php';

	/* ======================= INITIALISE VARIABLES ======================= */

	$PHP_SELF = htmlentities($_SERVER['PHP_SELF']);
	$greeting = "Welcome";

	$mysquarecolor = $config_colour_me;
	$yoursquarecolor = $config_colour_them;
	$nonesquarecolor = $config_colour_none;
	$tableheadercolor = $config_colour_table_header;
	$default_session_size = $config_default_session_size;
	$admin_user = $config_admin_user;
	$show_session_id = $config_show_session_id;
	$max_enrolments_per_line = $config_max_enrolments_per_line;

	$TITLE = $config_title;
	$SESSIONS_FILE = "data/".$config_sessions_data_file;
	$NOTICES_FILE = "data/".$config_notices_file;
	$EVENT_LOG = "data/".$config_log_file;

	date_default_timezone_set("Australia/Melbourne");

	/* ======================= READ POST INPUT ======================= */

	$name = @$_POST["Name"];
	$submit = @$_POST["submit"];
	$add = @$_POST["add"];
	$remove = @$_POST["remove"];
	$open = @$_POST["Open"];
	$close = @$_POST["Close"];
	$edit_notices = @$_POST["Edit"];

	$action = @$_POST["Action"];
	$context = @$_POST["Context"];

	$status = array();

	/* ======================= SET NAME and ADMIN_MODE--- FROM COOKIE IF POSSIBLE/NECESSARY ======================= */
	/* =======================  HANDLE Login/Session/Cookie =======================  */

	session_start();
	$user = get_user_from_session_or_cookie();

	$name = $user;

	$admin_mode = 0;
	if ($name == $admin_user) {
		$admin_mode = 1;
	}
	if (in_array($name, $config_admin_users)) {
		$admin_mode = 1;
	}

	/* ======================= PRINT INTRO ======================= */

// DEBUG
// print "[[ name = $name";
// print "&nbsp;";
// print "action = $action";
// print "&nbsp;";
// print "context = $context ]]<br />";

	list($xml, $save_changes) = load_sessions_file($SESSIONS_FILE);
	$nextID = (int) $xml->nextID;

	/* ======================= HANDLE ACTIONS ======================= */

	if ($context == "notices") {
		if ($action == "Edit") {
			print_header($TITLE, $version);
			top_bar($TITLE, "Edit Notices");
			print_notices($NOTICES_FILE, $action == "Edit" ? 0 : $admin_mode);
//			print_refresh_button();
//			print_admin_toolbox($action == "Edit" ? 0 : $admin_mode);

			$notices = file_get_contents($NOTICES_FILE);
//			echo "Edit Notices:<br />";
			echo "Editing: $NOTICES_FILE" . ":<br />";
			echo "<form method=\"post\" action=\"$PHP_SELF\">";
			echo "<textarea name=\"newnotices\" cols=160 rows=20>$notices</textarea>";
			echo "<p>\n";
			echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
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
			$n = file_put_contents($NOTICES_FILE, $newtext);
			log_event("saved $n bytes to $NOTICES_FILE");
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
		$timestring = date('g:ia l jS F', $timestamp);
		$newsession = $xml->addChild('session');
		$newsession->addChild("usid", $nextID);
		$newsession->addChild("active", "no");
		$newsession->addChild("when", $timestamp);
		$newsession->addChild("whenstr", $timestring);
		$newsession->addChild("location", $_POST["Location"]);
		$newsession->addChild("maxusers", $_POST["Maxusers"]);
		$nextID ++;
		$xml->nextID = $nextID;

		$save_changes = 1;
		log_event("created new session (ID = $nextID) $newsession->whenstr at $newsession->location");
	} else if ($action == "Add Me") {
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
			$enrolled_list[] = $name;
			$session->userlist = implode( "|", $enrolled_list);
#			$status[] = "Added you to session $ID";
			$save_changes = 1;
			log_event("enrolled in session (ID = $USID) $session->whenstr at $session->location");
		}
	} else if ($action == "Remove Me") {
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

		$key = array_search($name, $enrolled_list);
		if ($key === FALSE) { // hmm... not found.. this is unexpected
			$status[] = "Error. Your name \"$name\" not found in list";
		} else {
			unset ( $enrolled_list[$key] );
			$session->userlist = implode( "|", $enrolled_list);
#			$status[] = "Removed you from session $ID";
			$save_changes = 1;
			log_event("un-enrolled in session (ID = $USID) $session->whenstr at $session->location");
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
			$status[] = "Error. Name \"$user_to_remove\" not found in list";
		} else {
			unset ( $enrolled_list[$key] );
			$session->userlist = implode( "|", $enrolled_list);
			$save_changes = 1;
			log_event("Admin user un-enrolled $user_to_remove from session (ID = $USID) $session->whenstr at $session->location");
		}
	} else if ($action == "Open") {
		$USID = $_POST["USID"];
		$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
		$session = $sessionarray[0];
		
		$save_changes = 1;
		$session->active = "yes";
		log_event("opened session (ID = $USID) $session->whenstr at $session->location");
	} else if ($action == "Close") {
		$USID = $_POST["USID"];
		$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
		$session = $sessionarray[0];

		$session->active = "no";
		$save_changes = 1;
		log_event("closed session (ID = $USID) $session->whenstr at $session->location");
	} else if ($action == "Reset Session ID") {
		// count sessions
		$sortthis = $xml->xpath('/sessions/session');
		$total = count($sortthis);
		if ($total == 0) {
			$xml->nextID = 1;
			$nextID = 1;
			$save_changes = 1;
		} else {
			print "<h2>Error: attempt to reset SessionID when there are still sessions</h2>";
		}
	} else if ($action == "Edit" and $context == "editsession" ) {
		print_header($TITLE, $version);
		top_bar($TITLE, "Edit Session");
//		print_notices($NOTICES_FILE, $action == "Edit" ? 0 : $admin_mode);
//		print_refresh_button();
//		print_admin_toolbox($action == "Edit" ? 0 : $admin_mode);

		$USID = $_POST["USID"];
		$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
		$session = $sessionarray[0];
		print "<fieldset>
			<legend>Edit Session (SessionID: $USID)</legend>
			<form method=\"post\" action=\"$PHP_SELF\">
			<input type=\"hidden\" name=\"Name\" value=\"$name\">
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
#		$timestring = date('g:ia l jS F', $timestamp);
		$timestring = date('D d M y g:ia', $timestamp);
		$session->when = $timestamp;
		$session->whenstr = $timestring;
		$session->location = $_POST["Location"];
		$session->maxusers = $_POST["Maxusers"];
		$save_changes = 1;
		log_event("edited session (ID = $USID) $session->whenstr at $session->location");
	} else if ($action == "Clear") {
		// nothing need be done - has already been handled
	} else if ($action == "Set") {
		// nothing need be done...
	} else {
		if ($action != "") {
			log_event("unknown action: $action in context: $context");
//			echo "unknown action: $action in context: $context\n";
		}
	}

	/* ======================= SAVE CHANGES ======================= */

	if ($save_changes == 1) {
		if (save_session_file($xml, $SESSIONS_FILE) === FALSE) {
			die ("save failed. Please reload the page and try again");
		}
	}

	/* ======================= BEGIN MAIN PAGE ======================= */

	print_header($TITLE, $version);
	top_bar($TITLE);
	print_notices($NOTICES_FILE, $action == "Edit" ? 0 : $admin_mode);
	print_refresh_button();
	print_admin_toolbox($action == "Edit" ? 0 : $admin_mode);

	// -- option to add new session... but only if we're not editing something else
	if ($admin_mode == 1 and $action != "Edit") {
		print "<div class=\"adminFunction\">\n";
		$sess_str = $show_session_id ? " (SessionID: $nextID)" : "";
		print "<fieldset>
			<legend>Add New Session$sess_str</legend>
			<form method=\"post\" action=\"$PHP_SELF\">
			<input type=\"hidden\" name=\"Name\" value=\"$name\">
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
		$siz_dflt =  @$_POST["Maxusers"] != "" ? $_POST["Maxusers"] : $default_session_size;

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

#	list($xml, $save_changes) = load_sessions_file($SESSIONS_FILE);

	$header_counter = 0;
	$in_use = array();

	// sort data
	$sortthis = $xml->xpath('/sessions/session');
	function sort_sessions($a, $b) {
		if ((int)$a->when == (int)$b->when) {
			return 0;
		}
		return ((int)$a->when < (int)$b->when) ? -1 : 1;
	}
	usort($sortthis, 'sort_sessions');

	// NEED TO SORT THIS BY THE WHEN (timestamp) FIELD
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
		echo "<table>";

		// calculate if we need to print a weekly header:
		// if this session is not in same week as prev session
		// 	print header for this week
		// prev = week number of this session
		$this_week_number = date("W", (int)$session->when);

		if ($this_week_number != $prev_week_number) {
			$mondaystr = weeknumber2monday($this_week_number, (int)$session->when);
			echo "<div class='weeklyheader'>Sessions for week starting $mondaystr</div>\n";
			echo "<tr bgcolor='$tableheadercolor'>";
			if ($admin_mode) {
				echo "<th class='border' colspan='2'>Administration</th>";
				if ($show_session_id) {
					echo "<th class='border'>ID</th>";
				}
			}
			echo "<th class='border'>Actions</th><th class='border'>When</th><th class='border'>Location</th><th class='border' colspan=$max>Attendees</th></tr>\n";
		}
		$prev_week_number = date("W", (int)$session->when);	// return value is a string, but we should be able to treat it as an integer.

		$header_counter ++;

		echo "<tr>";
		// 1 - Admin
		//	2 - SESS_ID
		// 3 - Actions
		// ...

		if ($admin_mode) {
			echo "<form method=\"post\" action=\"$PHP_SELF#jump$jump\" style='padding-top: 5px; padding-bottom: 0px; margin-bottom: 0px'>";
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
			echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
			echo "<input type='hidden' name='Context' value='editsession'>";
			echo "</td>";
			echo "</form>";
			if ($show_session_id) {
				echo "<td class='border' width='26' align='center'>$USID</td>";
			}
		}
		# Actions...
		echo "<td class='border' class='border' width='100' align='center' valign='center'>";
		if ($session->active == "yes") {
			echo "<form method=\"post\" action=\"$PHP_SELF#jump$jump\" style=' padding-bottom: 0px; margin-bottom: 0px'>";
			echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";

			if ($name == "") {
				echo "<input type=\"submit\" value=\"Log in first\" disabled>";
			} else {
				if (array_search($name, $ulist) === FALSE) {	# if not enroled...
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
		$reformat = date('D d M y g:ia', getdate($session->whenstr));
		echo "<td class='border' align='center' width='200'>".$reformat."</td>";
		echo "<td class='border' align='center' width='150'>".$session->location."<br /></td>\n";

		# show session enrolments
		print "<td class='sessionholder'><table class='sessionrows'><tr>\n";
		for ($i = 0; $i < $max; $i ++) {
			if ($i < $count) {
				if ($ulist[$i] == $name) {
					echo "<td class='sessionelement' bgcolor='$mysquarecolor'>";
					echo "$ulist[$i]";
#					if ($session->active == "yes") {
#						echo "<form method=\"post\" action=\"$PHP_SELF#jump$jump\">";
#						echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
#						echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
#						echo "<input type=\"submit\" name='Action' value=\"Remove Me\">";
#						echo "</form>\n";
#					}
					echo "</td>";
				} else {
					echo "<td class='sessionelement' bgcolor='$yoursquarecolor'>";
					echo "$ulist[$i]";
					if ($admin_mode and $session->active == "yes") {
						echo "<div class=\"adminFunction\">\n";
						echo "<form method=\"post\" action=\"$PHP_SELF#jump$jump\">";
						echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
						echo "<input type=\"hidden\" name=\"Remove\" value=\"$ulist[$i]\">";
						echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
						echo "<input type=\"submit\" name='Action' value=\"AdminRemove\">";
						echo "</form>\n";
						echo "</div>\n";
					}
					echo "</td>";
				}
			} else {
				echo "<td class='sessionelement' bgcolor='$nonesquarecolor'>";
				echo "&nbsp;";
				echo "</td>";
			}
			if (($i+1) % $max_enrolments_per_line == 0) {
				print "</tr><tr>";
			}
		}
		$remainder = $max % $max_enrolments_per_line;
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

	if ($admin_mode and $total == 0 and $nextID > 1) {
		echo "<br />";
		echo "<br />";
		echo "<table border='1' bgcolor='red' ><tr><td>";
		echo "<form method=\"post\" action=\"$PHP_SELF\" style=' padding-bottom: 0px; margin-bottom: 0px'>";
		echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
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
		$save_changes = 1;
	} else {
		$xml = simplexml_load_file($file) or die ("Unable to load XML file!");
		$save_changes = 0;
	}
	$time_last_moded = filemtime($file);	// NOTE: part 1 - load file and part 2 - get last mod
							// really should be an ATOMIC operation

	$result[] = $xml;
	$result[] = $save_changes;
	return $result;
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

function print_datetime_selection($ts) {

	// get date and time and put in as defaults
	if ($ts != "") {
		$tsa = getdate($ts);
		$now = getdate();
	} else {
		$tsa = getdate();
		$now = $tsa;
	}

	$hours = $tsa["hours"];
	$ampm = "AM";
	$min = $tsa["minutes"];
//	// calculate minute to nearest 10 minute mark
//	$min = round($min / 10) * 10;
//	change to ... nearest 15 minute mark
	$min = round($min / 15) * 15;
	if ($min == 60) {
		$hours ++;
		$min = 0;
	}
	if ($hours > 12) {
		$hours -= 12;
		$ampm = "PM";
	}
	if ($hours == 12) {
		$ampm = "PM";
	}
	if ($hours == 0) {
		$hours = 12;
		$ampm = "AM";
	}

/*
	print "
	<table>
	<tr><td>Year</td><td>Month</td><td>Day</td><td>Hour</td><td>Minute</td></tr>
	<tr>
	";
	print "<td>";
*/
	echo createDays('sess_day', $tsa["mday"]);
/*
	print "</td>";
	print "<td>";
*/
	echo createMonths('sess_month', $tsa["mon"]);
/*
	print "</td>";
	print "<td>";
*/
	echo createYears($now["year"], $now["year"] + 1, 'sess_year', $tsa["year"]);
/*
	print "</td>";
	print "<td>";
*/
	echo createHours('sess_hour', $hours);
/*
	print "</td>";
	print "<td>";
*/
	echo createMinutes('sess_minute', $min);
/*
	print "</td>";
	print "<td>";
*/
	echo createAmPm('sess_ampm', $ampm);
/*
	print "</td>";
	print "</tr>";
	print "</table>";
*/
} // end print_datetime_selection

function print_header($title, $version) {
	print <<<EOT
<html>
<head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script type='text/javascript' src="js/showhide.js"></script>
</head>
<body>
EOT;
}

// given a week number, return a string represent Monday of that week
function weeknumber2monday($week_number, $src_timestamp) {
	$tsa = getdate($src_timestamp);
	$monday_day = $tsa["yday"] - $tsa["wday"] + 1;

	$zero_day = mktime(12, 0, 0, 1, 1, $tsa["year"]);

	$monday_ts = $zero_day + ($monday_day * 86400);

	return date("l jS F", $monday_ts);
}

function print_footer() {
	global $admin_mode, $version;
	echo "<br />\n";
	if ($admin_mode) {
		echo "<div class='footer'>&bull; enrol (version $version) by mbates &bull; Icons by <a href='http://dryicons.com;'>DryIcons</a> &bull;</div>";
	} else {
		echo "<div class='footer'>&bull; enrol (version $version) by mbates &bull; </div>";
	}
	echo "</body></html>\n";
} // end: print_footer

# old code... delete me
#	if ($version != "") {
#		if (preg_match('/^devel/', $version)) {
#			print '<div style="color: red; font-size: 10px;">'.$version.'</div>';
#		} else {
#			print '<div style="font-size: 10px;">'.$version.'</div>';
#		}
#	}

function print_notices($file, $admin_mode) {
	global $name;
	global $PHP_SELF;

	$tainted = file_get_contents($file);
	$notices = strip_tags($tainted, "<a><font>");
	$lines = explode ("\n", $notices);
	$check_tainted_content = 0;
	if ($notices != $tainted) { # something was stripped out!
		$check_tainted_content = 1;
	}
	#echo "<fieldset><legend>Notices<legend>\n"; // this doesn't work on all platforms... so doing it manually...
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
	if ($admin_mode == 1) {
		if ($check_tainted_content == 1) {
			echo "<font class='securityalert'>SECURITY ALERT - found disallowed html tag - check source contents of Notices</font>\n";
		}
		print "<div class=\"adminFunction\">\n";
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
		echo "<input type=\"hidden\" name='Context' value=\"notices\">";
		echo "&nbsp;&nbsp;<input type=\"submit\" name='Action' value=\"Edit\">";
		echo "</form>";
		echo "</div>";
	}
#echo "</fieldset>\n";
	echo "</div></div></div>\n";
} // end: print_notices

function log_event($text) {
	global $EVENT_LOG, $name;

	$t = time();
	$ts = date("D M j, Y, g:ia");
	$fh = fopen($EVENT_LOG, 'a') or die("can't open logfile");
	$ip = $_SERVER['REMOTE_ADDR'];
	fwrite($fh, $ts. " ".$ip." as ".$name. ": ".$text. "\n");
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
	global $PHP_SELF;
	global $greeting;
	global $user;
	global $admin_mode;

	if ($breadcrumb != "") {
		$BREAD = " > $breadcrumb ";
	}
	echo <<<EOT
	<div class='topbar'>
		<div id='title'><a href="$PHP_SELF">$title</a>$BREAD</div>
		<div id='login'>
EOT;
		if ($admin_mode) {
			$ADMIN = "(administrator access granted) ";
		}
		if($user != "") {
			print "$greeting, $user. $ADMIN| <a href=\"logout.php\">Logout</a>";
		} else {
			print <<<EOT
			<form method="post" action="$PHP_SELF">
			Enter your name: <input type="text" size="36" maxlength="36" name="Name">
			<input type="submit" value="Login">
			<input type="checkbox" name="rememberMe" value="1"> Remember me
			</form>
EOT;
		}
		echo <<<EOT
		</div>
	</div>
EOT;
}

function get_user_from_session_or_cookie() {
	global $greeting;

	$user = $_SESSION['user'];
	if ($user == "") {
		# A: a new user may have just logged on -> check POST['Name']
		# B: check cookies for EnrolName
		# or should that be, check B, then A??

		if (isset($_POST['Name'])) {
			$user = $_POST['Name'];
			$_SESSION['user'] = $user;
			if (isset($_POST['rememberMe'])) {
				$twoMonths = 60 * 60 * 24 * 60 + time();
				setcookie('EnrolName', $user, $twoMonths);
			}
		}
		else if (isset($_COOKIE['EnrolName'])) {
			$user = $_COOKIE['EnrolName'];
			$_SESSION['user'] = $user;
			$greeting = "Welcome back";
		}
		# else you really aren't logged in. Too bad.
	}
	return $user;
}

function print_refresh_button() {
	global $config_on_production, $PHP_SELF, $name;

	// print refresh button
	if ($config_on_production === FALSE) {
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
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

?>
