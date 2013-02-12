<?php
require "templates/header.php";

/*
 * this template requires:
 *	$t->notices
 *	$t->rawNotices
 *	$t->post
 */


echo <<<EOT
<h4>Notices</h4>
	<div class="well">
	$t->notices
	</div>
	<br clear="both"/>
	<h4>Edit:</h4>
	<form method="post" action="$t->post">
	<textarea class="input-block-level" name="newnotices" rows="20">$t->rawNotices</textarea>
	<button type="submit" class="btn" name="Action" value="cancel">Cancel</button>
	<button type="submit" class="btn btn-success" name="Action" value="save-edit-notices">Save Changes</button>
	</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
