<?php
function getBaseURL($SERVER_HASH) {
	if (isset($SERVER_HASH['HTTP_HOST'])) {
		$host = $SERVER_HASH['HTTP_HOST'];
	} else {
		$host = "unknown";
	}
	$uri = rtrim(dirname($SERVER_HASH['PHP_SELF']), '/\\');
	return $host.$uri;
}
?>
