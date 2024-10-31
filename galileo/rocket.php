<?php

class Rocket {
	
	protected $db_handler;

	protected $result;

	protected $fetchQueue=array();
	protected $fetchIndex=0;

	function __construct() {

		$this->open();
	}

	function open() {

		if ($fp = fsockopen("10.55.99.180",2638,$errCode,$errStr,3)) { 

			$connectionInfo = "HOST=10.55.99.180:2638;DBN=infinity01;UID=gabellini;PWD=rocket2022;ServerName=infinity_gabellini;IDLE=10";
			$this->db_handler=sasql_connect( $connectionInfo );
		}
		if ($fp) fclose($fp);
	}

	function close() {
		@sasql_close($this->db_handler);
	}

	function free($id) {
		sasql_free_result($this->fetchQueue[$id]);
	}
	
	function __destruct() {
		$this->close();
	}

	function loadResult($r) {
		$this->fetchIndex++;
		$this->fetchQueue[$this->fetchIndex]=$r;
		return $this->fetchIndex;
	}

	function reset() {
		$this->close();
		$this->open();
	}

	function getFetch($index) {

		if (!$this->fetchQueue[$index]) return array();

		try{
			$row=@sasql_fetch_assoc($this->fetchQueue[$index]);
			return $row;
		}catch(Exception $e) {
			unset($this->fetchQueue[$index]);
			return array();
		}
	}
	
	function query($query) {
		//echo '<div>'.$query.'</div>';
		$result=sasql_query($this->db_handler,$query);

		$error=sasql_errorcode($this->db_handler);
		
		if ($error<0) return false;
		else return $result;
	}

	function transaction_begin() {
		return sasql_query($this->db_handler,'BEGIN TRANSACTION');
	}
	
	function transaction_commit() {
		return sasql_commit( $this->db_handler );
	}
	
	function transaction_rollback() {
		return sasql_rollback( $this->db_handler );
	}
	
	function get_handler() {
		return $this->db_handler;
	}

	function closeDB() {
		@sasql_close($this->db_handler);
	}
}

?>