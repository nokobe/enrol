<?php
echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="author" content="http://github.com/nokobe">
	<title>
		$title
	</title>
	<link rel="stylesheet" href="$base/css/bootstrap.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="$base/css/bootstrap-responsive.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="$base/css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	</link>
</head>
<body>

    <!-- begin NAV BAR -->

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#">$title</a>
		<ul class="nav nav-pills">
		<li><a href="#information" data-toggle="tab">Info</a></li>
		<li class="active"><a href="#sessions" data-toggle="tab">Sessions</a></li>
		<li><a href="#admin" data-toggle="tab">Admin</a></li>
		</ul>
          <div class="nav-collapse collapse">
EOT;
if ($loggedIn) {
	echo <<<EOT
		<p class="navbar-text pull-right">
		Logged in as <a href="#" class="navbar-link">$username</a>
		</p>
EOT;
} else {
	echo <<<EOT
		<form class="navbar-form form-inline pull-right">
		<input type="text" class="span2" placeholder="Enter Your Name">
		<button type="submit" class="btn">Sign in</button>
		<label class="checkbox">
			<input type="checkbox"> Remember me
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
