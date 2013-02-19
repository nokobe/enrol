<?php

/*
 * My php toolbox
 * Provides:
 *	isAssoc($array) @return boolean - is it an associative array
 *	implodeAssoc($sep, $glue, $array)
 */

function isAssoc($array) {
	return array_values($array) === $array ? FALSE : TRUE;
}

/*
 * @param sep - separator between key and value)
 * @param glue - separator between key-sep-value pairs
 * @param array - Assoc array to be imploded
 */

function implodeAssoc($sep, $glue, $array) {
	foreach ($array as $key => $value) {
		$new[] = "$key$sep$value";
	}
	return implode($glue, $new);
}
?>
