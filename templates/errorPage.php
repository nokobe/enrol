<?php
require "templates/header.php";

echo "<h2>We're sorry but an error has occured</h2>";
echo '<div class="well">';
echo htmlentities($t->errorMessage);
echo '</div>';

echo '<a href="enrol.php">Return to enrol home</a>';

require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
