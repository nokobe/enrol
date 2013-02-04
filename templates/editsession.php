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
echo <<<EOT
Session is currently: $t->status
		<h2>Edit Session (SessionID: $t->s->usid)</h2>
		<form class="form-inline" method="post" action="$t->self">
		<input type="hidden" name="USID" value='$t->s->usid'>"
EOT;
print_datetime_selection((int)$session->when);
echo <<<EOT
	Location: <input type="text" class="input-medium" name="Location" value="$t->s->location">
		Maximum attendees:<input type="text" class="input-small" name='Maxusers' value='$t->s->maxusers'>
		<br />
		<br />
		<button type="submit" name="Action" value="cancel">Cancel</button>
		<button type="submit" name="Action" value="save-session">Save</button>
		</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
