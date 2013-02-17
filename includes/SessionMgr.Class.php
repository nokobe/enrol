<?php

/*
 * SessionMgr: use PHP_SESSION info and/or cookies to manage the user session
 * Provides:
 *	isLoggedIn()
 *	getUsername()
 *	isRegisteredAdmin()
 *	hasAdminAuth()
 *	login($name, $setCookie)
 *	checkForSessionOrLoginOrCookie()
 *	logout()
 *	get($var)
 *	set($var, $value)
 *	storeMessage($msg)
 *	getMessage( )
 */

class SessionMgr {
	function isLoggedIn() {
		Logger::logTrace("");
		if (isset($_SESSION['logged_in'])) {
			return $_SESSION['logged_in'] == 1;
		} else {
			return 0;
		}
	}

	function getUsername() {
		Logger::logTrace("");
		if (SessionMgr::isLoggedIn()) {
			return $_SESSION['user'];
		} else {
			return "";
		}
	}

	function isRegisteredAdmin() {
		global $u;
		Logger::logTrace("");

		if (SessionMgr::isLoggedIn() == FALSE) {
			return FALSE;
		}
		return in_array( $_SESSION['user'], array_keys( $u->get('admin_users') ) );
	}

	function hasAdminAuth() {
		Logger::logTrace("");
		if (SessionMgr::isRegisteredAdmin() == FALSE) {
			return FALSE;
		}
		return $_SESSION['admin_auth'] == 1;
	}

	function grantAdminAuth() {
		Logger::logTrace("");
		if (SessionMgr::isLoggedIn() == FALSE) {
			return FALSE;
		}
		$_SESSION['admin_auth'] = 1;
	}

	function login($name, $setCookie) {
		global $u;

		Logger::logTrace("");

		if ($name == "") {
			// silently reject it!
			header("Location:logout.php");
			exit(0);
		}

		$_SESSION['user'] = $name;
		$_SESSION['logged_in'] = 1;
		$_SESSION['admin_auth'] = 0;
		$_SESSION['login_time'] = time();
		if ($setCookie) {
			$twoMonths = 60 * 60 * 24 * 60 + time();
			setcookie('EnrolName', $name, $twoMonths);
		}
		else {
			setcookie('EnrolName', '', time()-3600);	# clear any existing cookie
		}
		if ($u->get('forceAdminAuth') and SessionMgr::isRegisteredAdmin()) {
			# force authentication immediately!
			SessionMgr::set('adminView', 1);
		}
	}

	function checkForSessionOrLoginOrCookie() {
		Logger::logTrace("");
		if (SessionMgr::isLoggedIn()) {
			if ($_SESSION['user'] == "") { /* error/unexpected! */
				header("Location:logout.php");
				exit(0);
			}
			return;
		}
		if (isset($_POST['submit-login']) and isset($_POST['Name'])) {
			SessionMgr::login($_POST['Name'], isset($_POST['rememberMe']) ? 1 : 0);
			return;
		}
		if (isset($_COOKIE['EnrolName'])) {
			SessionMgr::login($_POST['EnrolName'], 1);
		}
	}

	function logout() {
		Logger::logTrace("");
		unset($_SESSION['user']);
		unset($_SESSION['logged_in']);
		unset($_SESSION['login_time']);
		unset($_SESSION['admin_auth']);
		session_destroy();
		if (isset($_COOKIE['EnrolName'])) {
			setcookie('EnrolName', '', time()-3600);
		}
	}

	function get($var) {
		Logger::logTrace("");
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		else {
			return 0;
		}
	}

	function set($var, $value) {
		Logger::logTrace("");
		$_SESSION[$var] = $value;
	}

	function storeMessage($message) {
		Logger::logTrace("");
		$_SESSION["messages"][] = $message;
		Logger::logDebug("stored message: $message");
	}

	function getMessage() {
		Logger::logTrace("");
		if (isset($_SESSION["messages"])) {
			$type = gettype($_SESSION["messages"]);
			if (gettype($_SESSION["messages"]) == "array") {
				return array_shift( $_SESSION["messages"] );
			} else {
				Logger::logWarn("stored message is a $type, not an array!");
			}
		}
	}
}

?>
