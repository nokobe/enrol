<?php

/*
 * provides an logging function for providing an audit trail
 *
 * Usage:
 *	Audit::setAuditFile('myaudit.log');
 * 	Audit::log([ $key1 => $value1, $key2 => $value2, ... ]);
 * 	$array = Audit::search($key, $value);
 */

class Audit {
	static $logger = '';

	function __construct() {
		$this->auditLogFile = "audit.log";
	}

	static function setAuditFile($file) {
		self::getLogger()->auditLogFile = $file;
	}

	static function getLogger() {
		if (self::$logger == '') {
			self::$logger = new Audit();
		}
		return self::$logger;
	}

	static function log($data) {
		self::getLogger()->logMessage($data);
	}

	function logMessage($data) {
		if (! is_array($data)) {
			die("not array\n");
		}
		if (! self::isAssoc($data)) {
			die("not assoc array\n");
		}
		$details = self::implodeAssoc(' => ', ', ', $data);
		$logMessage = "[".date(DATE_RFC822)."] $details\n";
		$date = date(DATE_RFC822);

		file_put_contents($this->auditLogFile, $date.'::'.json_encode($data)."\n", FILE_APPEND | LOCK_EX);
	}

	static function implodeAssoc($sep, $glue, $array) {
		foreach ($array as $key => $value) {
			$new[] = "$key$sep$value";
		}
		return implode($glue, $new);
	}

	static function isAssoc($array) {
		return array_values($array) === $array ? FALSE : TRUE;
	}

	static function search($key, $operator, $value) {
		$result = array();
		$fh = fopen(self::getLogger()->auditLogFile, 'r');
		$line = trim(fgets($fh));
		while (!feof($fh)) {
			list($when, $json) = explode('::', $line, 2);
			$tmp = json_decode($json, true);
			/* bugfix: role accidently suffixed with ':' */
			if (isset($tmp['session role'])) {
				if (preg_match("/:$/", $tmp['session role'])) {
					$tmp['session role'] = preg_replace("/:$/", '', $tmp['session role']);
				}
			}
			if ($operator == "equals") {
				if ($key == '' or (isset($tmp[$key]) and strcasecmp($tmp[$key], $value) == 0)) {
					$result[] = $tmp;
				}
			} elseif ($operator == "contains") {
				if ($key == '' or (isset($tmp[$key]) and preg_match("/$value/i", $tmp[$key]))) {
					$result[] = $tmp;
				}
			} else {
				throw new Exception("Audit::search: illegal operator: $operator");
			}
			$line = trim(fgets($fh));
		}
		fclose($fh);
		return $result;
	}
}

?>
