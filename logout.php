<?php

session_start();
$user = $_SESSION['user'];
$PHP_SELF = htmlentities($_SERVER['PHP_SELF']);

$_SESSION['user'] = "";
session_destroy();
if (isset($_COOKIE['EnrolName'])) {
	setcookie('EnrolName', '', time()-3600);
}

if (file_exists("enrol.php")) {
	header("Location: enrol.php");
}
if (file_exists("index.php")) {
	header("Location: index.php");
}
