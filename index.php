<?php

include_once 'settings.php';
include_once 'auth.php';
include_once 'dbconnection.class.php';
include_once 'humansize.php';

$page = array( 'status' => '&nbsp;');

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

if (isset($_FILES["file"])) {
	if ($_FILES["file"]["error"] > 0) {
		$page['status'] = "Error: " . $_FILES["file"]["error"];
	} else {
		$filename = $_FILES["file"]["name"];
		$filename = $db->insertFile(
			$_SERVER["REMOTE_ADDR"],
			$_FILES["file"]["size"],
			$_POST['comment'], 
			$filename);
		$filename = $settings['location'].'/'.$filename;
		move_uploaded_file($_FILES["file"]["tmp_name"], $filename);

		$page['status'] = "Upload success";
	}
}

if (isloggedin()) {
	$page['loginbutton'] = '<a href="?logout">Logout</a>';
} else {
	$page['loginbutton'] = '<a href="?login">Login</a>';
}

$page['uploadbox'] = load_template('uploadbox');

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
