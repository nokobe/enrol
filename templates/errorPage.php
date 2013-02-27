<?php
require "templates/header.php";

echo <<<EOT
<h3>We're sorry but an error has occured</h3>
<div class="well">
$t->errorMessage
</div>

<a href="$t->home">Home</a>

EOT;

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
