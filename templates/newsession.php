<?php
require "templates/header.php";

/* template requires:
 *		$t->post
 *		$t->when
 *		$t->location
 *		$t->maxusers
 */

echo <<<EOT
		<h2>Create New Session</h2>
		<h4>Enter new values:</h4>
		<form class="form-horizontal" method="post" action="$t->post">
EOT;
print_datetime_selection($t->when);
echo <<<EOT
	<div class="control-group">
	<label class="control-label">Location</label>
	<div class="controls">
		<input type="text" class="input-large" name="Location" value="$t->location" placeholder="Location">
	</div></div>
	<div class="control-group">
	<label class="control-label">Maximum Attendees</label>
	<div class="controls">
		<input type="text" class="input-mini" name='Maxusers' value="$t->maxusers">
	</div></div>
		<br />
		<br />
		<button type="submit" class="btn" name="Action" value="cancel">Cancel</button>
		<button type="submit" class="btn btn-success" name="Action" value="create-session">Save</button>
		<input type="hidden" name="USID" value="dummy value">
		</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
