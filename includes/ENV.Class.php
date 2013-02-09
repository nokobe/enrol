<?php

class SERVER {
	function get($var) {
		if (isset($_SERVER[$var])) {
			return htmlentities($_SERVER[$var]);
		} else {
			return "";
		}
	}
}

class POST {
	function get($var) {
		if (isset($_POST[$var])) {
			return htmlentities($_POST[$var]);
		} else {
			return "";
		}
	}
}

?>
