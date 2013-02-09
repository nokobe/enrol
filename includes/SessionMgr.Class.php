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
		if (isset($_SESSION['logged_in'])) {
			return $_SESSION['logged_in'] === 1;
		} else {
			return 0;
		}
	}

	function getUsername() {
		if (SessionMgr::isLoggedIn()) {
			return $_SESSION['user'];
		} else {
			return "";
		}
	}

	function isRegisteredAdmin() {
		global $config_admin_users;

		if (SessionMgr::isLoggedIn() === FALSE) {
			return 0;
		}
		return in_array($_SESSION['user'], $config_admin_users);
	}

	function hasAdminAuth() {
		if (!SessionMgr::isRegisteredAdmin()) {
			return 0;
		}
		return $_SESSION['admin_auth'] === 1;
	}

	function grantAdminAuth() {
		if (SessionMgr::isLoggedIn() === FALSE) {
			return 0;
		}
		$_SESSION['admin_auth'] = 1;
	}

	function login($name, $setCookie) {
		$_SESSION['user'] = $name;
		$_SESSION['logged_in'] = 1;
		$_SESSION['admin_auth'] = 0;
		$_SESSION['login_time'] = time();
		if ($setCookie) {
			$twoMonths = 60 * 60 * 24 * 60 + time();
			setcookie('EnrolName', $user, $twoMonths);
		}
		else {
			setcookie('EnrolName', '', time()-3600);	# clear any existing cookie
		}
	}

	function checkForSessionOrLoginOrCookie() {
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
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		else {
			return 0;
		}
	}

	function set($var, $value) {
		$_SESSION[$var] = $value;
	}

	function storeMessage($message) {
		$_SESSION["messages"][] = $message;
		file_put_contents("data/debug.log", "stored message: $message", FILE_APPEND | LOCK_EX);
	}

	function getMessage() {
		if (isset($_SESSION["messages"])) {
			$type = gettype($_SESSION["messages"]);
			if (gettype($_SESSION["messages"]) == "array") {
				file_put_contents("data/debug.log", "yes", FILE_APPEND | LOCK_EX);
				return array_shift( $_SESSION["messages"] );
			} else {
				file_put_contents("data/debug.log", "no. it's a $type", FILE_APPEND | LOCK_EX);
			}
		}
	}
}

?>