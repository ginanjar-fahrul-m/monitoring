<?php
require_once('config.php');

function session_init() {
	session_start();
}

function session_set($name, $content) {
	global $config;
	
	$_SESSION[$config['session']['prefix'].$name] = $content;
}

function session_get($name) {
	global $config;
	
	if(isset($_SESSION[$config['session']['prefix'].$name])) {
		return $_SESSION[$config['session']['prefix'].$name];
	} else {
		return $config['function']['return']['failure'];
	}
}

function session_del($name) {
	global $config;
	
	unset($_SESSION[$config['session']['prefix'].$name]);
}
?>