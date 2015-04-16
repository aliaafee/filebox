<?php

include_once 'settings.php';
include_once 'template.php';
include_once 'auth.php';
include_once 'filebox.db.php';

try {
	$db = new fileboxdb($settings['database']['file']);
	$db->createTables();
	authSetup();
	echo load_template('message', Array( 'message' => 'Setup Complete' ));
} catch (Exception $e) {
	echo load_template('message', Array( 'message' => $e->getMessage() ));
	exit();
}



?>
