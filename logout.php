<?php

require_once 'includes/global.php';

session_start();
SessionMgr::checkForSessionOrLoginOrCookie();
SessionMgr::logout();
header("Location: ".$c->get('index'));

?>
