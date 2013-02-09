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

	$t = prepareTemplateEssentials();
	$t->errorMessage = $errorMessage;

	require 'templates/errorPage.php';
	die();
}

function log_debug($text) {
	global $c;

	$DEBUG_LOG = "data/debug.log";
	$LOGFMT_DATE = 'd/m/Y:G:i:s O';

	$t = time();
//	$ts = date($c->get('logfmt_date'));
	$ts = date($LOGFMT_DATE);
	$fh = fopen($DEBUG_LOG, 'a') or die("can't open $DEBUG_LOG");
	$ip = $_SERVER['REMOTE_ADDR'];
	fwrite($fh, $ts. " ".$ip." : ".$text. "\n");
	fclose($fh);
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

function my_mktime($day, $mon, $year, $hour, $min, $ampm) {
	if ($ampm == "PM" and $hour != 12) {
		$hour += 12;
	} else if ($ampm == "AM" and $hour == 12) {
		$hour -= 12;
	}
	return mktime($hour, $min, 0, $mon, $day, $year);
}
//		int mktime  ([  int $hour = date("H")  [,  int $minute = date("i")  [,  int $second = date("s")  [,  int $month = date("n")  [,  int $day = date("j")  [,  int $year = date("Y")  [,  int $is_dst = -1  ]]]]]]] )

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

	echo '<div class="control-group">';
	echo '<label class="control-label">Day of month</label>';
	echo '<div class="controls">';
	echo createDays('sess_day', $tsa["mday"]);
	echo '</div></div>';

	echo '<div class="control-group">';
	echo '<label class="control-label">Month</label>';
	echo '<div class="controls">';
	echo createMonths('sess_month', $tsa["mon"]);
	echo '</div></div>';
	echo '<div class="control-group">';
	echo '<label class="control-label">Year</label>';
	echo '<div class="controls">';
	echo createYears($now["year"], $now["year"] + 1, 'sess_year', $tsa["year"]);
	echo '</div></div>';
	echo '<div class="control-group">';
	echo '<label class="control-label">Hour</label>';
	echo '<div class="controls">';
	echo createHours('sess_hour', $hours);
	echo '</div></div>';
	echo '<div class="control-group">';
	echo '<label class="control-label">Minute</label>';
	echo '<div class="controls">';
	echo createMinutes('sess_minute', $min);
	echo '</div></div>';
	echo '<div class="control-group">';
	echo '<label class="control-label"></label>';
	echo '<div class="controls">';
	echo createAmPm('sess_ampm', $ampm);
	echo '</div></div>';
} // end print_datetime_selection

function prepareTemplateEssentials() {
	global $u, $c;
	$t = new stdClass;

	# add header and footer essentials

	$t->title = $u->get('title');
	$t->base = ".";
	$t->loggedIn = SessionMgr::isLoggedIn();
	$t->username = SessionMgr::getUsername();
	$t->loginpost = $c->get('index');
	$t->home = $c->get('index');

	# add footer essentials

	$t->version = $c->get('version');

	return $t;
}

?>