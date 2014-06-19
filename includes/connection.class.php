<?php
class Connection {
	private $db_hostname = null;
	private $db_username = null;
	private $db_password = null;
	private $db_database = null;
	
	private $db_connection = null;
	private $is_connected = false;
	
	public function __construct($_db_hostname, $_db_username, $_db_password, $_db_database) {
		$this->db_hostname = $_db_hostname;
		$this->db_username = $_db_username;
		$this->db_password = $_db_password;
		$this->db_database = $_db_database;
	}
	
	public function open() {
		$retval = false;
		
		if(!$this->is_connected) {
			$this->db_connection = @mysql_connect(
										$this->db_hostname,
										$this->db_username,
										$this->db_password,
										true
									);
			
			if($this->db_connection) {
				@mysql_select_db($this->db_database, $this->db_connection);
				$this->is_connected = true;
				$retval = true;
			}
		}
		
		return $retval;
	}
	
	public function close() {
		$retval = false;
		
		if($this->is_connected) {
			@mysql_close($this->db_connection);
			$this->is_connected = false;
			$retval = true;
		}
		
		return $retval;
	}
	
	public function query($query_string) {
		$res = @mysql_query($query_string, $this->db_connection);
		
		return $res;
	}
	
	public function fetch_array($res) {
		return mysql_fetch_array($res);
	}
	
	public function fetch_assoc($res) {
		return mysql_fetch_assoc($res);
	}
	
	public function get_last_insert_id() {
		return mysql_insert_id($this->db_connection);
	}
	
	public function num_rows($res) {
		return mysql_num_rows($res);
	}
}
?>