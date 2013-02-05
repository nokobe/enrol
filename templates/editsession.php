<?php
require "templates/header.php";

if ($c->get('debug')) {
	echo "<pre>";
	echo "Session:";
	print_r($_SESSION);
	echo "Post:";
	print_r($_POST);
	echo "</pre>";
}
$sid = $t->s->usid;
$loc = $t->s->location;
$whenTS = (float) $t->s->when;
echo <<<EOT
		<h2>Edit Session (SessionID: $sid)</h2>
		<div class="well">
			<div class="sessioninfo">$t->sessionTime at $loc</div><div class="status">$t->status</div>
		</div>
		<br clear="both"/>
		<h4>Enter new values:</h4>
		<form class="form-horizontal" method="post" action="$t->self">
		<input type="hidden" name="USID" value='$sid'>
EOT;
print_datetime_selection($whenTS);
$maxusers = $t->s->maxusers;
echo <<<EOT
	<div class="control-group">
	<label class="control-label">Location</label>
	<div class="controls">
	<input type="text" class="input-large" name="Location" value="$loc">
	</div></div>
	<div class="control-group">
	<label class="control-label">Maximum Attendees</label>
	<div class="controls">
		<input type="text" class="input-mini" name='Maxusers' value="$maxusers">
	</div></div>
		<br />
		<br />
		<button type="submit" class="btn" name="Action" value="cancel-edit-session">Cancel</button>
		<button type="submit" class="btn btn-success" name="Action" value="save-edit-session">Save</button>
		</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
