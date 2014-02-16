<?php

$settings = array (
	'user'	=> 'ali',
	'pass'	=> 'ali.log',
	'templatepath'	=> realpath(dirname(__FILE__)).'/templates/basic',
	'templateuri'	=> 'templates/basic',
	'location'	=> '/Users/ali/Public/upload',
	'database'	=> array (
		'type' => 'sqlite',
		'file' => '/Users/ali/Public/upload/db.sqlite'
	),
	'captchaon'	=> true,
	'securimagepath' => '/Users/ali/Public/securimage',
	'securimageuri'	=> '/~ali/securimage'
);

?>
