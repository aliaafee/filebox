<?php

include_once 'settings.php';

function load_template($name, $data = array()) {
	global $settings;

	$data['templateuri'] = $settings['templateuri'];
	$template = file_get_contents($settings['templatepath']."/$name.html");

	foreach ($data as $key => $value) {
		$template = str_replace("{".$key."}", $value, $template);
	}

	return $template;
}

?>
