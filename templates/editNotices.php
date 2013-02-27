<?php
require "templates/header.php";

/*
 * this template requires:
 *	$t->notices
 *	$t->rawNotices
 *	$t->information
 *	$t->target
 *	$t->post
 */


echo <<<EOT
<h3>$t->breadcrumb</h3>
	<div class="alert alert-info"><i class="icon-info-sign"></i> $t->information</div>
	<div class="well">
	$t->notices
	</div>
	<br clear="both"/>
	<h4>Edit:</h4>
	<form method="post" action="$t->post">
	<button type="submit" class="btn btn-small" name="Action" value="cancel">Cancel</button>
	<button type="submit" class="btn btn-small btn-success" name="Action" value="save-edit-$target">Save Changes</button>
	<textarea class="input-block-level" name="newnotices" rows="20">$t->rawNotices</textarea>
	<button type="submit" class="btn btn-small" name="Action" value="cancel">Cancel</button>
	<button type="submit" class="btn btn-small btn-success" name="Action" value="save-edit-$target">Save Changes</button>
	</form>
EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
