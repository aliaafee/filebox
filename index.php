<?php

include_once 'settings.php';
include_once 'auth.php';
include_once 'filebox.db.php';
include_once 'humansize.php';
if ($settings['captchaon']) {
	include_once $settings['securimagepath'].'/securimage.php';
}

$db = new fileboxdb($settings);

if (isset($_GET['file'])) {
	if (isloggedin()) {
		$file = $db->getFilename($_GET['file']);	
		if ($file == false) {
			header('HTTP/1.0 404 Not Found');
			echo '<h1>File Not Found</h1>';
		} else {
			$filename = $settings['location'].'/'.$file['filename'];
			header("Content-Type: ".mime_content_type($filename));
			header("Content-Length: " . filesize($filename));

			readfile($filename);
		}
		exit();
	}
}

$page = array( 'status' => '&nbsp;');

if (isset($_FILES["file"]) and isset($_POST['comment'])) {
	function uploadfile() {
		global $_FILES, $_POST, $page, $db, $settings;
		try {
			$db->beginTransaction();

			switch ($_FILES['file']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException('No file sent.');
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Exceeded filesize limit.');
				default:
					throw new RuntimeException('Unknown errors.');
			}

			/*
			if ($_FILES['file']['size'] > 1000000) {
				throw new RuntimeException('Exceeded filesize limit.');
			}*/

			$filename = $db->insertFile(
				$_SERVER["REMOTE_ADDR"],
				$_FILES["file"]["size"],
				$_POST['comment'], 
				$_FILES["file"]["name"]
			);

			$filename = $settings['location'].'/'.$filename;

			if (!@move_uploaded_file($_FILES["file"]["tmp_name"], $filename)) {
				throw new RuntimeException('Failed to move uploaded file.');
			}

			$page['status'] = "Upload  success";

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();

			$page['status'] = "Upload error, ".$e->getMessage();
		}
	}

	if ($settings['captchaon']) {
		if (isloggedin()) {
			uploadfile();
		} else {
			if (isset($_POST['captcha_code'])) {
				$securimage = new Securimage();
				if ($securimage->check($_POST['captcha_code']) == false) {
					$page['status'] = "Wrong Captcha";
				} else {
					uploadfile();
				}
			}
		}
	} else {
		uploadfile();
	}
}

if (isloggedin()) {
	$page['loginbutton'] = '<a href="?logout">Logout</a>';
} else {
	$page['loginbutton'] = ''; #'<a href="?login">Login</a>';
}

$captcha = "";
if ($settings['captchaon']) {
	if (isloggedin()) {
		$captcha = "";
	} else {
		$captcha = '<div><img id="captcha" src="'.$settings['securimageuri'].'/securimage_show.php" alt="CAPTCHA Image" /></div>'.
					'<div><input type="text" id="captcha_code" name="captcha_code" size="10" maxlength="6" /></div>';
	}
}

$page['uploadbox'] = load_template('uploadbox', array( 'captcha' => $captcha ));

if (isloggedin()) {
	$page['filelist'] = $db->getFileList();
} else {
	$page['filelist'] = '';
}

echo load_template('main', $page);
?>
