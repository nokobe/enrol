<?php
require_once 'includes/global.php';
session_start();
SessionMgr::checkForSessionOrLoginOrCookie();

if ( ! SessionMgr::hasAdminAuth()) {
	SessionMgr::storeMessage("Permission denied");
	header("Location: ".$c->get('index'));
	exit(0);
}

$t = prepareTemplateEssentials();
$t->post = "auth.php";
$t->heading = "Hmmmmmmmmmmmmmmmmmmmmmmmmmmm";

#echo "<pre>";
#var_dump($_POST);
#echo "</pre>";

$exactMatch = isset($_POST['exact-match']) ? true : false;

$searchField = $searchText = '';
if (isset($_POST['search-all-submit'])) {
	$results = Audit::search('', 'equals', '');
} elseif (isset($_POST['search-submit'])) {
	$searchField = isset($_POST['searchField']) ? $_POST['searchField'] : "";
	$searchText = isset($_POST['searchText']) ? $_POST['searchText'] : "";
	if ($searchField != '') {
		$operator = $exactMatch ? "equals" : "contains";
		$results = Audit::search($searchField, $operator, $searchText);
	}
} else {
	// first load. set defaults
	$exactMatch = true;
}

if (isset($results)) {
	foreach ($results as &$r) {
		if (isset($r['time'])) {
			$r['time'] = displayDate($r['time']);
		}
		if (!isset($r['time']) or $r['time'] == '') { $r['time'] = '-'; }
		if (!isset($r['status']) or $r['status'] == '') { $r['status'] = '-'; }
		if (!isset($r['desc']) or $r['desc'] == '') { $r['desc'] = '-'; }
		if (!isset($r['usid']) or $r['usid'] == '') { $r['usid'] = '-'; }
		if (!isset($r['session user']) or $r['session user'] == '') { $r['session user'] = '-'; }
		if (!isset($r['remote ip']) or $r['remote ip'] == '') { $r['remote ip'] = '-'; }
	}
}

$fruit = array('apple', 'orange', 'banana');
$apples = array('royal gala', 'pink lady', 'jonathan', 'golden delicious', 'fuji', 'granny smith');

$headings = array("Food type", "Commonly seen as", "My view");
$body = array("a", "b", "c");

$page = new SimpleTemplate('templates/audit.html');

# add header essentials
$page->add('base', '.');
$page->add('title', $u->get('title'));
$page->add('loggedIn', SessionMgr::isLoggedIn());
$page->add('username', htmlentities(SessionMgr::getUsername()));
$page->add('name', SessionMgr::getUsername()); // this one just for testing
$page->add('loginpost', $c->get('index'));
$page->add('home', $c->get('index'));

# add footer essentials
$page->add('version', $c->get('version'));

$page->add('breadcrumb', 'Audit Log');
$page->add('searchExactMatch', $exactMatch);

$page->add('post', 'audit.php');
if ($searchField != "") {
	$page->add('searchField', $searchField);

	if ($searchField == "time") { $page->add('searchWhen', true); }
	elseif ($searchField == "action") { $page->add('searchAction', true); }
	elseif ($searchField == "status") { $page->add('searchStatus', true); }
	elseif ($searchField == "session user") { $page->add('searchUser', true); }
	elseif ($searchField == "session role") { $page->add('searchRole', true); }
	elseif ($searchField == "remote ip") { $page->add('searchRemote', true); }
	elseif ($searchField == "desc") { $page->add('searchDesc', true); }
	elseif ($searchField == "usid") { $page->add('searchID', true); }
}
$page->add('searchText', $searchText);
$page->add('tableHeading', array('When', 'Action', 'Status', 'User', 'Role', 'Remote_IP', 'Description', 'Session_ID'));
if (isset($results)) {
	$page->add('results', $results);
	$page->add('total', count($results));
}

$page->add('fruit', $fruit);
$page->add('justapplies', $apples);
$page->add('table_head', $headings);
$page->add('table_row', $body);
print $page->render();

# vim:ts=4:sw=4
?>
