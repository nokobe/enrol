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
		<form class="form-horizontal" method="post" action="$t->post" id="sessioncreateform" onsubmit="return checkEntry();">
EOT;
print_datetime_selection($t->when);
echo <<<EOT
	<div class="control-group">
	<label class="control-label">Location</label>
	<div class="controls">
		<input type="text" class="input-large" id="location" name="Location" value="$t->location" placeholder="Location">
	</div></div>
	<div class="control-group">
	<label class="control-label">Maximum Attendees</label>
	<div class="controls">
		<input type="text" class="input-mini" name='Maxusers' value="$t->maxusers">
	</div></div>
		<br />
		<br />
		<!--		<button type="submit" class="btn" name="Action" value="cancel">Cancel</button> -->
		<button type="submit" class="btn" name="Action" value="cancel" onclick="this.form.button='cancel'">Cancel</button>
		<button type="submit" class="btn btn-success" name="Action" value="create-session" onclick="this.form.button='create'">Create New Session</button>
		<input type="hidden" name="USID" value="dummy value">
		</form>

<script type="text/javascript"><!--
//<![CDATA[
        var form = document.forms['sessioncreateform'];
        function checkEntry() {
								if (form.Location.value.length > 0 || this.form.button == "cancel") {
									return true;
								} else {
									alert('Please enter a location');
									document.getElementById("location").focus();
									return false;
								}
        }
//]]> -->
</script>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
