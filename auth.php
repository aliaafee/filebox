<?php
session_start();

include_once 'settings.php';
include_once 'template.php';
include_once 'auth.db.php';

try {
	$authdb = new authdb($settings['database']['file']);
} catch (PDOException $e) {
	echo load_template('error', Array( 'message' => $e->getMessage() ));
	exit();
}


function passhash($pass, $salt) {
	return crypt($pass, sha1($salt));
}

function login() {
	global $settings, $authdb;

	if (isloggedin()) {
		return;
	}
	
	$status = "";
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		$db_user = $authdb->getUser($user);
		if ($db_user == false) {
			$h_pass = passhash($pass, time());
			$status = "Invalid username or password";
		} else {
			$h_pass = passhash($pass, $db_user['date']);
			if ($h_pass == $db_user['pass']) {
				session_regenerate_id();
				$_SESSION['USER'] = $db_user['user'];
				$_SESSION['PERMISSIONS'] = $db_user['permissions'];
				$_SESSION['FULLNAME'] = $db_user['fullname'];
				session_write_close();
				header('location: .');
				exit();
			} else {
				$status = "Invalid username or password";
			}
		}
	}

	echo load_template('auth', array('loginuri' => '?login', 'status' => $status));
	exit();
}

function logout() {
	session_regenerate_id();
	unset($_SESSION['USER']);
	unset($_SESSION['PERMISSIONS']);
	unset($_SESSION['FULLNAME']);
	session_write_close();
	header('location: .');
	exit();
}

function isloggedin() {
	global $settings;

	if (isset($_SESSION['USER'])) {
		return true;
	}
	return false;
}

function hasPermission($permission) {
	if (!isloggedin()) {
		return false;
	}
	if (strpos($_SESSION['PERMISSIONS'], $permission) !== false) {
		return true;
	}
	return false;
}

function getFullname() {
	if (isset($_SESSION['FULLNAME'])) {
		return $_SESSION['FULLNAME'];
	}
	return '';
}

function authSetup() {
	global $settings, $authdb;
	$authdb->createTables();
	if ($authdb->getUser('admin') == false) {
		$date = time();
		$pass = 'admin';
		$h_pass = passhash($pass, $date);
		$authdb->addUser('admin', $h_pass, 'admin,fileup,filedown', $date, 'Administrator');
		#$authdb->addUser('fileup', $h_pass, 'fileup', $date, 'FileUP');
		#$authdb->addUser('filedown', $h_pass, 'filedown', $date, 'FileDown');
		#$authdb->addUser('adminonly', $h_pass, 'admin', $date, 'AdminOnly');
	}
}

if (isset($_GET['login'])) {
	login();
}


if (isset($_GET['logout'])) {
	logout();
}

?>
