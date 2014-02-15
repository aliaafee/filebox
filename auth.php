<?php
session_start();

include_once 'settings.php';
include_once 'template.php';

function login() {
	global $settings;

	if (isloggedin()) {
		return;
	}
	
	$status = "";
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		if ($_POST['user'] == $settings['user'] && $_POST['pass'] == $settings['pass']) {
			session_regenerate_id();
			$_SESSION['SESS_MEMBER_ID'] = $settings['user'];
			session_write_close();
			return;
		} else {
			$status = "Invalid user name or password";
		}
	}

	echo load_template('auth', array('loginuri' => '?login', 'status' => $status));
	exit();
}

function logout() {
	unset($_SESSION['SESS_MEMBER_ID']);
}

function isloggedin() {
	global $settings;

	if (isset($_SESSION['SESS_MEMBER_ID'])) {
		if ($_SESSION['SESS_MEMBER_ID'] == $settings['user']) {
			return true;
		}
	}
	return false;
}

if (isset($_GET['login'])) {
	login();
}


if (isset($_GET['logout'])) {
	logout();
}

?>
