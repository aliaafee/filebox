<?php

include_once 'settings.php';
include_once 'auth.php';
include_once 'dbconnection.class.php';
include_once 'humansize.php';
if ($settings['captchaon']) {
	include_once $settings['securimagepath'].'/securimage.php';
}

$db = new dbConnection($settings);
$db->connect();

if (isset($_GET['file'])) {
	if (isloggedin()) {
		$file = $db->getFilename($_GET['file']);
		$filename = $settings['location'].'/'.$file['filename'];
		if ($file == false) {
			header('HTTP/1.0 404 Not Found');
			echo '<h1>File Not Found</h1>';
		} else {
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
		if ($_FILES["file"]["error"] > 0) {
			$page['status'] = "Error: " . $_FILES["file"]["error"];
		} else {
			$filename = $db->insertFile(
				$_SERVER["REMOTE_ADDR"],
				$_FILES["file"]["size"],
				$_POST['comment'], 
				$_FILES["file"]["name"]);
			$filename = $settings['location'].'/'.$filename;
			move_uploaded_file($_FILES["file"]["tmp_name"], $filename);

			$page['status'] = "Upload  success";
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
	$files = $db->getFileList();

	$page['filelist'] = '<table><tr><th>Date</th><th>From</th><th>Comment</th><th>File</th><th>Size</th></tr>';

	while ( $row = $files->fetchArray(SQLITE3_ASSOC) ) {
		$page['filelist'] .= '<tr>';
		$page['filelist'] .= '<td>'.$row['date'].'</td>';
		$page['filelist'] .= '<td>'.$row['ip'].'</td>';
		$page['filelist'] .= '<td>'.$row['comment'].'</td>';
		$page['filelist'] .= '<td><a href=?file='.$row['id'].' >'.$row['ofilename'].'</a></td>';
		$page['filelist'] .= '<td>'.humanSize($row['size']).'</td>';
		$page['filelist'] .= '</tr>';
	}

	$page['filelist'] .= '</table>';
} else {
	$page['filelist'] = '';
}

echo load_template('main', $page);
?>
