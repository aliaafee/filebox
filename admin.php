<?php

include_once 'settings.php';
include_once 'template.php';
include_once 'auth.php';
include_once 'filebox.db.php';

if (!isloggedin()) {
	echo "No access";
	exit();
}

if (!hasPermission('admin')) {
	echo "No access";
	exit();
}

?>

<html>
<head>
	<title>Admin</title>
</head>

<body>
	<h1>Administration</h1>

</body>

</html>
