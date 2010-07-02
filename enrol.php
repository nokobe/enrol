<?php
	$version = "version 2.1.1";
	$BASE = "./";
	require_once $BASE.'config.inc';
	require_once $BASE.'datetime.php';

	/* ======================= INITIALISE VARIABLES ======================= */

	$PHP_SELF = $_SERVER['PHP_SELF'];

	$mysquarecolor = $config_colour_me;
	$yoursquarecolor = $config_colour_them;
	$nonesquarecolor = $config_colour_none;
	$tableheadercolor = $config_colour_table_header;
	$default_session_size = $config_default_session_size;
	$default_page_break = $config_default_page_break;
	$admin_user = $config_admin_user;


	$TITLE = $config_title;
	$SESSIONS_FILE = $config_sessions_data_file;
	$NOTICES_FILE = $config_notices_file;
	$EVENT_LOG = $config_log_file;

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

	/* ======================= SET NAME and ADMIN_MODE--- FROM COOKIE IF POSSIBLE/NECESSARY ======================= */

	if ($name == "" and isset($_COOKIE['Name'])) {
		$name = $_COOKIE['Name'];
	}
	setcookie('Name', $name);

	// Have to handle action==clear now

	if ($action == "Clear") {
		$name = "";
	}

	if ($name == $admin_user) {
		$admin_mode = 1;
	} else {
		$admin_mode = 0;
	}

	/* ======================= PRINT INTRO ======================= */

	print_header($TITLE, $version);

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
			footer();
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
		$enrolled_list = "";
		if ($session->userlist != "") {
			$enrolled_list = explode ( "|", $session->userlist);
		}
		$curr_size = count($enrolled_list);
		if ($curr_size >= $mu) {
			$status = "Sorry, that class is full";
		} else {
			$enrolled_list[] = $name;
			$session->userlist = implode( "|", $enrolled_list);
#			$status = "Added you to session $ID";
			$save_changes = 1;
			log_event("enrolled in session (ID = $USID) $session->whenstr at $session->location");
		}
	} else if ($action == "Remove") {
		$USID = $_POST["USID"];
		if ($USID == "") {
			die("missing USID");
		}
		$sessionarray = $xml->xpath("/sessions/session[usid=$USID]");
		$session = $sessionarray[0];

		$mu = $session->maxusers;

		$enrolled_list = "";
		if ($session->userlist != "") {
			$enrolled_list = explode ( "|", $session->userlist);
		}

		$key = array_search($name, $enrolled_list);
		if ($key === FALSE) { // hmm... not found.. this is unexpected
			$status = "Error. Your name $name not found in list";
		} else {
			unset ( $enrolled_list[$key] );
			$session->userlist = implode( "|", $enrolled_list);
#			$status = "Removed you from session $ID";
			$save_changes = 1;
			log_event("un-enrolled in session (ID = $USID) $session->whenstr at $session->location");
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
		footer();
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
		$timestring = date('g:ia l jS F', $timestamp);
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

	/* ======================= PRINT OUR BASIC PAGE ======================= */


	// Load and show any notices/announcements

	print_notices($NOTICES_FILE);

	// print refresh button
	if (0) {
		echo "<br />";
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
		echo "<input type='submit' value='Reload'>";
		echo "</form>";
	}

	// print name, with options to Set/Clear

	if ($name == "") {
		echo "<img border='0' alt='step 1 - set your name' src='step1-full.png' align='left'>";
	} else {
//		echo "<img border='0' alt='step 1 - set your name' src='step1-full-gray.png' align='left'>";
	}
	print_name($name);
//	echo "<br />\n";

	// -- option to add new session
	if ($admin_mode == 1) {
		print "<fieldset>
			<legend>Add New Session (SessionID: $nextID)</legend>
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
	}

	/* ======================= THE ENROLMENT TABLE ======================= */

# load_sessions_file is not being done here because is it already loaded.
# but this is a place holder while we consider that we may need to re-load the
# file to reset the time marker (that we might use to detect changes to the file whilst we
# were trying to change it.) But this may be unncessary here because there is no saving of
# the data after this point... all the actions are performed above!

#	list($xml, $save_changes) = load_sessions_file($SESSIONS_FILE);

	if ($name == "") {
//		echo "<img border='0' alt='step 2 - select your classes' src='step2-full-gray.png'>";
	} else {
		if ($admin_mode) {
			echo "<br />";
		} else {
			echo "<img border='0' alt='step 2 - select your classes' src='step2-full.png'>";
		}
	}
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
	foreach ($sortthis as $session) {
		$USID = (int) $session->usid;

		if (isset($in_use[$USID])) {
			die ("fatal: USID($USID) already in use. Please contact administrator");
		}
		$in_use[$USID] = 1;

		if ($session->active != "yes" and !$admin_mode) {
			continue;
		}
		$available ++;

		$ulist = array();	# empty array
		if ($session->userlist != "") {
			$ulist = explode ( "|", $session->userlist);
			$count = count($ulist);
		} else {
			$count = 0;
		}
		$max = $session->maxusers;

		$SESS_TITLE = "";
		$SESS_CELL = "";
		if ($admin_mode) {
			$SESS_TITLE = "<th>ID</td>";
			$SESS_CELL = "<td width='26' align='center'>$USID</td>";
		}
//			echo "<td width='110' align='center' valign='center'>";

		echo "<table border=\"1\">";
		if ($header_counter % $default_page_break == 0) {
			if ($admin_mode == 1) {
				echo "<tr bgcolor='$tableheadercolor'><th colspan='2'>Administration</th>$SESS_TITLE<th>When</th><th>Location</th><th colspan=$max>Attendees</th></tr>\n";
			} else {
				echo "<tr bgcolor='$tableheadercolor'><th></th>$SESS_TITLE<th>When</th><th>Location</th><th colspan=$max>Attendees</th></tr>\n";
			}
		}
		$header_counter ++;

		echo "<tr>";
	#	echo "<div class=\"session\">\n";
	#	echo "<fieldset>\n";

		// check if $name is in that list
		$enrolled = array_search($name, $ulist);

		if ($admin_mode == 1) {
			if ($session->active == "yes") {
				echo "<td width='55' bgcolor='green'>Open</td><td width='95' align='center'>";
			} else {
				echo "<td width='55' bgcolor='red'>Closed</td><td width='95' align='center'>";
			}
			echo "<form method=\"post\" action=\"$PHP_SELF\" style='padding-top: 5px; padding-bottom: 0px; margin-bottom: 0px'>";

			echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
			echo "<input type='hidden' name='Context' value='editsession'>";
			if ($session->active == "yes") {
				echo "<input type='image' src='icons/remove.png' name=\"Action\" value=\"Close\" alt='Close Session' title='Close Session'>";
				echo "&nbsp;";
				echo "<input type='image' src='icons/edit.png' name=\"Action\" value=\"Edit\" alt='Edit Session' title='Edit Session'>";
			} else {
				echo "<input type='image' src='icons/add.png' name=\"Action\" value=\"Open\" alt='Open Session' title='Open Session'>";
				echo "&nbsp;";
				echo "<input type='image' src='icons/edit.png' name=\"Action\" value=\"Edit\" alt='Edit Session' title='Edit Session'>";
				echo "&nbsp;";
				echo "&nbsp;";
				echo "<input type='image' src='icons/close.png' name='Action' value='Delete' alt='Delete Session' title='Delete Session'>";
			}
			echo "</form>";
			echo "</td>";
		} else {
			echo "<td width='130' align='center' valign='center'>";
			echo "<form method=\"post\" action=\"$PHP_SELF\" style=' padding-bottom: 0px; margin-bottom: 0px'>";
			echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
			echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
			if ($enrolled === FALSE) { // then not yet in list
				if ($count >= $max) {
					echo "<input type=\"submit\" value=\"Class is full\" disabled>";
				} else {
					if ($name == "") {
						echo "<input type=\"submit\" value=\"Set your name\" disabled>";
					} else {
						echo "<input style=\"background:lightgreen\" type=\"submit\" name='Action' value=\"Add Me\">";
					}
				}
	#		} else {
	#			echo "<input style=\"background:red\" type=\"submit\" value=\"Remove me\" name=\"remove\">";
			}
			echo "</form>\n";
			echo "</td>";
		}
		if ($admin_mode) {
			echo "$SESS_CELL";
		}
		echo "<td align='center' width='200'>";
		echo $session->whenstr;
//		echo $session->date . " at " . $session->time . "<br />\n";
		echo "</td>";
		echo "<td align='center' width='150'>";
		echo $session->location . "<br />\n";
		echo "</td>";

		for ($i = 0; $i < $max; $i ++) {
			if ($i < $count) {
				if ($ulist[$i] == $name) {
					echo "<td width=\"80px\" align=\"center\" bgcolor='$mysquarecolor'>";
					echo "$ulist[$i]";
					echo "<form method=\"post\" action=\"$PHP_SELF\">";
					echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
					echo "<input type=\"hidden\" name=\"USID\" value=\"$USID\">";
					echo "<input style=\"background:red\" type=\"submit\" name='Action' value=\"Remove\">";
					echo "</form>\n";
				} else {
					echo "<td width=\"80px\" align=\"center\" bgcolor='$yoursquarecolor'>";
					echo "$ulist[$i]";
				}
			} else {
				echo "<td width=\"80px\" align=\"center\" bgcolor='$nonesquarecolor'>";
				echo "&nbsp;";
			}
			echo "</td>";
		}

		echo "</tr>";
		echo "</table>";
	}
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

	footer();
	exit(0);

/* ======================= FUNCTIONS ======================= */

function load_sessions_file($file) {
	global $time_last_moded;

	if (!file_exists($file)) {
		die ("fatal: messing sessions file");
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
	// calculate minute to nearest 10 minute mark
	$min = round($min / 10) * 10;
	if ($min == 60) {
		$hours ++;
		$min = 0;
	}
	if ($hours > 12) {
		$hours -= 12;
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
	print "
<html>
<head>
<title>$title</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />
</head>
<body bgcolor='#ffffe0'>
<div style='font-weight: bold; font-size:24;'>$title</div>";
//<h2>$title</h2>";
	if ($version != "") {
		if (preg_match('/^devel/', $version)) {
			print '<div style="color: red; font-size: 10px;">'.$version.'</div>';
		} else {
			print '<div style="font-size: 10px;">'.$version.'</div>';
		}
	}
} // end: print_header

function footer() {
	global $admin_mode;
	echo "<br /><br />\n";
	if ($admin_mode) {
		echo "<div class='footer'>&bull; enrol software by mbates &bull; Icons by <a href='http://dryicons.com;'>DryIcons</a> &bull;</div>";
	} else {
		echo "<div class='footer'>&bull; enrol software by mbates &bull; </div>";
	}
	echo "</body></html>\n";
} // end: footer

function print_notices($file) {
	global $admin_mode;
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
		<div style="width: 60px; position: relative; top: 0px; left: 20px; text-align: center; font-weight: bold; background: #ffffe0; padding:3px; border: 0px solid black; z-index: 1;">Notices</div>
		<div style="position: relative; top: -8px; border: 1px solid black; z-index: 0;">
			<div style="padding-top:10px; padding-bottom:10px; padding-left:0px;">
	';

	echo "<ul>\n";
	foreach ( $lines as $line ) {
		if (preg_match('/^\s*$/', $line)) {
			print "<li>&nbsp;";
		} else {
			echo "<li><img src='bullet.gif' alt='-' border='0'> $line\n";
		}
	}
	echo "</ul>\n";
	if ($admin_mode == 1) {
		if ($check_tainted_content == 1) {
			echo "<font color='red'>SECURITY ALERT - found disallowed html tag - check source contents of Notices</font>\n";
		}
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		echo "<input type=\"hidden\" name=\"Name\" value=\"$name\">";
		echo "<input type=\"hidden\" name='Context' value=\"notices\">";
		echo "&nbsp;&nbsp;<input type=\"submit\" name='Action' value=\"Edit\">";
		echo "</form>";
	}
#echo "</fieldset>\n";
	echo "</div></div></div>\n";
} // end: print_notices

function print_name($name) {
	global $PHP_SELF;
	#echo "<fieldset>\n";
	#echo "<legend>Set your name</legend>\n";
	echo "<form method=\"post\" action=\"$PHP_SELF\">";
	if ($name == "") {
		echo "Name:<input type=\"text\" size=\"36\" maxlength=\"36\" name=\"Name\" value=\"$name\">";
	} else {
		echo "Name:<input type=\"text\" size=\"36\" maxlength=\"36\" name=\"Name\" value=\"$name\" disabled>";
	}
	if ($name == "") {
		echo "<input type=\"submit\" name='Action' value=\"Set\">";
	} else {
		echo "<input type=\"submit\" name='Action' value=\"Set\" disabled>";
		echo "<input type=\"submit\" name='Action' value=\"Clear\">";
	}
	echo "</form>";
	#echo "</fieldset>\n";
} // end: print_name

function log_event($text) {
	global $EVENT_LOG, $REMOTE_ADDR, $name;

	$t = time();
	$ts = date("D M j, Y, g:ia");
	$fh = fopen($EVENT_LOG, 'a') or die("can't open logfile");
	$ip = @$REMOTE_ADDR;
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
?>

