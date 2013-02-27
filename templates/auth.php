<?php
require "templates/header.php";

/* template requires:
 * 		$t->post
 *		$t->name
 *		$t->heading
 */

echo <<<EOF
<h3>$t->heading</h3>
<form class="form-horizontal" method="post" action="$t->post">
	<div class="control-group">
		<label class="control-label" for="inputEmail">Name</label>
		<div class="controls">
			<input class="uneditable-input" type="text" value="$t->username" disabled>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputPassword">Password</label>
		<div class="controls">
			<input type="password" id="inputPassword" placeholder="Password" name="password" autofocus>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn">Authenticate</button>
		</div>
	</div>
	<input type="hidden" name="auth-submit">
</form>
EOF;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
