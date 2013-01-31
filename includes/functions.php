<?php
function getBaseURL($SERVER_HASH) {
	$host = $SERVER_HASH['HTTP_HOST'];
	$uri = rtrim(dirname($SERVER_HASH['PHP_SELF']), '/\\');
	return $host.$uri;
}
?>
