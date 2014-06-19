<?php
require_once('config.php');
require_once('connection.class.php');

function new_connection() {
	global $config;
	return new Connection($config['db']['hostname'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
}
?>