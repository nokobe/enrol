<?php

class Logger {
	static $logger = '';
	static $Levels = array( "OFF", "FATAL", "ERROR", "WARN", "INFO", "DEBUG", "TRACE" );

	function __construct() {
		$this->logfile = "default.log";
		$this->logLevel = 'INFO';
	}

	static function addFileLogger($file) {
		self::getLogger()->logfile = $file;
	}

	static function getLogger() {
		if (self::$logger == '') {
			self::$logger = new logger();
		}
		return self::$logger;
	}

	static function setLogLevel($level) {
		self::getLogger()->_setLogLevelAttribute($level);
	}

	function _setLogLevelAttribute($level) {
		$this->logLevel = $level;
	}

	static function getLogLevel() {
		return self::getLogger()->logLevel;
	}

	static function loggingRequired($messageLevel) {
		if (Logger::getLogger()->getLogLevel() == "OFF") {
			return;
		}
		return array_search(self::getLogger()->getLogLevel(), self::$Levels) >= array_search($messageLevel, self::$Levels);
	}

	static function logFatal($message) { if (self::loggingRequired("FATAL")) { self::getLogger()->logMessage("fatal", $message); } }
	static function logError($message) { if (self::loggingRequired("ERROR")) { self::getLogger()->logMessage("error", $message); } }
	static function logWarn($message) { if (self::loggingRequired("WARN")) { self::getLogger()->logMessage("warn", $message); } }
	static function logInfo($message) { if (self::loggingRequired("INFO")) { self::getLogger()->logMessage("info", $message); } }
	static function logDebug($message) { if (self::loggingRequired("DEBUG")) { self::getLogger()->logMessage("debug", $message); } }
	static function logTrace($message) { if (self::loggingRequired("TRACE")) {
//		$stackTrace = debug_backtrace(false);

		# caller
		$class = isset($stackTrace[0]["class"]) ? $stackTrace[0]["class"]."::" : "";
		$function = isset($stackTrace[1]["function"]) ? $stackTrace[1]["function"]."()" : "main()";
		$file = $stackTrace[0]["file"];
		$line = $stackTrace[0]["line"];

		$caller = "$class$function at $file line $line";

		# caller's caller
		if (count($stackTrace) > 1) {
			$class = isset($stackTrace[1]["class"]) ? $stackTrace[1]["class"]."::" : "";
			$function = isset($stackTrace[2]["function"]) ? $stackTrace[2]["function"]."()" : "main()";
			$file = $stackTrace[1]["file"];
			$line = $stackTrace[1]["line"];
			$called_from = "\n\tcalled from $class$function at $file line $line";
		}
		else {
			$called_from = "";
		}


		self::getLogger()->logMessage("trace", "[$caller$called_from] $message"); }
	}

	/*
	 * local customisations:
	 * 	include in log (if possible):
	 * 	- REMOTE_ADDR
	 * 	- SessionMgr::getUsername()
	 * 	- Admin or User
	 */
	function logMessage($level, $message) {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : "";
		$who = SessionMgr::getUsername();
		$role = SessionMgr::isRegisteredAdmin() ? "admin:" : "user:";
	
		$logMessage = "[".date(DATE_RFC822)."] [$level] [$role $who@".$_SERVER['REMOTE_ADDR']."] $message\n";

		file_put_contents($this->logfile, $logMessage, FILE_APPEND | LOCK_EX);
	}
}

?>
