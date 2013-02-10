<?php

class Logger {
	static $logger = '';
	static $Levels = array( "OFF", "FATAL", "ERROR", "WARN", "INFO", "DEBUG", "TRACE" );

	function __construct() {
		$this->logfile = "logtest.log";
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

	static function logFatal($message) { if (self::loggingRequired("FATAL")) { self::getLogger()->logMessage("Fatal: $message"); } }
	static function logError($message) { if (self::loggingRequired("ERROR")) { self::getLogger()->logMessage("Error: $message"); } }
	static function logWarn($message) { if (self::loggingRequired("WARN")) { self::getLogger()->logMessage("Warn: $message"); } }
	static function logInfo($message) { if (self::loggingRequired("INFO")) { self::getLogger()->logMessage("Info: $message"); } }
	static function logDebug($message) { if (self::loggingRequired("DEBUG")) { self::getLogger()->logMessage("Debug: $message"); } }
	static function logTrace($message) { if (self::loggingRequired("TRACE")) { self::getLogger()->logMessage("Trace: $message"); } }

	function logMessage($message) {
		$logMessage = "[".date(DATE_RFC822)."]";
		if (isset( $_SERVER['REMOTE_ADDR'] )) {
			$logMessage .= " [".$_SERVER['REMOTE_ADDR']."]";
		}
		$logMessage .= " $message\n";

		file_put_contents($this->logfile, $logMessage, FILE_APPEND | LOCK_EX);
	}
}

?>
