<?php
require "templates/header.php";

$sid = $t->s->usid;
$loc = $t->s->location;
$whenTS = (float) $t->s->when;
echo <<<EOT
		<h3>Edit Session ($sid)</h3>
		<div class="well">
			<div class="sessioninfo">$t->sessionTime at $loc</div><div class="status">$t->status</div>
		</div>
		<br clear="both"/>
		<h4>Edit:</h4>
		<form class="form-horizontal" method="post" action="$t->post">
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
		<button type="submit" class="btn btn-small" name="Action" value="cancel">Cancel</button>
		<button type="submit" class="btn btn-small btn-success" name="Action" value="save-edit-session">Save Changes</button>
		</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
