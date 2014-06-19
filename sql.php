<?php
require_once('includes/config.php');
require_once('includes/database.php');
require_once('includes/session.php');

global $config;
	
$conn = new_connection();
$conn->open();
$command = "../../mysql/bin/mysqldump --opt --host=127.0.0.1 --user=root --password= monitor > backup.sql";
passthru($command, $error);
if ($error) {
	echo "Error: ". $error;
}
$conn->close();

?>