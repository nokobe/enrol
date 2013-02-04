<?php
function getBaseURL($SERVER_HASH) {
	if (isset($SERVER_HASH['HTTP_HOST'])) {
		$host = $SERVER_HASH['HTTP_HOST'];
	} else {
		$host = "unknown";
	}
	$uri = rtrim(dirname($SERVER_HASH['PHP_SELF']), '/\\');
	return $host.$uri;
}

function errorPage($errorMessage) {
	global $u, $c;
	require_once 'includes/global.php';
	session_start();
	SessionMgr::checkForSessionOrLoginOrCookie();

	$t = new stdClass;
	$t->title = $u->get('title');
	$t->base = ".";
	$t->username = SessionMgr::getUsername();
	$t->loggedIn = SessionMgr::isLoggedIn();
	$t->self = $c->get('php_self');

	$t->errorMessage = $errorMessage;

	require 'templates/errorPage.php';
}

function log_debug($text) {
	global $c;

	$DEBUG_LOG = "data/debug.log";

	$t = time();
	$ts = date($c->get('logfmt_date'));
	$fh = fopen($DEBUG_LOG, 'a') or die("can't open $DEBUG_LOG");
	$ip = $_SERVER['REMOTE_ADDR'];
	fwrite($fh, $ts. " ".$ip." : ".$text. "\n");
	fclose($fh);
}

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

?>
