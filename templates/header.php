<?php
/* this template requires:
 *	$t->title
 *	$t->base
 *	$t->loggedIn
 *	$t->username
 *	$t->loginpost
 *	$t->breadcrumbs

 *	$t->active-tab
 */

if (!isset($t->activetab)) {
	$t->activetab = "sessions";
}
if ($t->activetab == "sessions") {
	$sessions_tab_active = 'class="active"';
	$notices_tab_active = '';
} else {
	$sessions_tab_active = '';
	$notices_tab_active = 'class="active"';
}

echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="author" content="http://github.com/nokobe">
	<title>
		$t->title
	</title>
	<link rel="stylesheet" href="$t->base/css/bootstrap.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="$t->base/css/bootstrap-responsive.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="$t->base/css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	</link>
</head>
<body>

    <!-- begin NAV BAR -->

    <div class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container">
		<a class="brand" href="$t->home">$t->title</a>
          <div class="nav-collapse collapse">
EOT;
if ($t->loggedIn) {
	echo <<<EOT
		<p class="navbar-text pull-right">
		Logged in as <a href="#" class="navbar-link">$t->username</a> <a href="logout.php">Logout</a>
		</p>
EOT;
} else {
	echo <<<EOT
		<form class="navbar-form form-inline pull-right" method="post" action="$t->loginpost">
		<input type="text" class="span2" placeholder="Enter Your Name" name="Name" value="$t->username">
		<button type="submit" class="btn" name="submit-login" value="Sign In">Sign in</button>
		<label class="checkbox">
			<input type="checkbox" name="rememberMe" value="1"> Remember me
		</label>
		</form>
EOT;
}
	echo <<<EOT
        </div>
      </div>
    </div>
  </div>

    <!-- end NAV BAR -->

<div class="container">

EOT;
# vim:filetype=html
?>
