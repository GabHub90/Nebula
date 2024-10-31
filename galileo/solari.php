<?php

class Solari {

	private $server="10.55.99.56\SQLEXPRESS";
	private $user="sa";
	private $pw="solari";
	private $db="dbstart";
	private $cset="UTF-8";
	
	protected $db_handler;

	protected $result;

	protected $fetchQueue=array();
	protected $fetchIndex=0;

	function __construct() {
		$connectionInfo = array("Database"=>$this->db, "UID"=>$this->user, "PWD"=>$this->pw, "CharacterSet" => $this->cset,"LoginTimeout"=>3);

		//if ($fp = fsockopen("10.55.99.56",1433,$errCode,$errStr,3)) { 

			$this->db_handler=sqlsrv_connect($this->server,$connectionInfo);
			//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
			$query="SET LANGUAGE Italian";

			if ($this->db_handler) {
				sqlsrv_query($this->db_handler,$query);
			}
		//}
		//if ($fp) fclose($fp);
	}
	
	function __destruct() {
		if ($this->db_handler) {
			sqlsrv_close($this->db_handler);
		}
	}

	function loadResult($r) {
		$this->fetchIndex++;
		$this->fetchQueue[$this->fetchIndex]=$r;
		return $this->fetchIndex;
	}

	function getFetch($index) {

		try{
			$row=sqlsrv_fetch_array($this->fetchQueue[$index],SQLSRV_FETCH_ASSOC);
			return $row;
		}catch(Exception $e) {
			unset($this->fetchQueue[$index]);
			return array();
		}
	}
	
	function query($query) {
		$result=sqlsrv_query($this->db_handler,$query);
		
		return $result;
	}

	function transaction_begin() {
		return sqlsrv_begin_transaction( $this->db_handler );
	}
	
	function transaction_commit() {
		return sqlsrv_commit( $this->db_handler );
	}
	
	function transaction_rollback() {
		return sqlsrv_rollback( $this->db_handler );
	}
	
	function get_handler() {
		return $this->db_handler;
	}

	function closeDB() {
		sqlsrv_close($this->db_handler);
		$this->db_handler=false;
	}

	function close() {
		sqlsrv_close($this->db_handler);
		$this->db_handler=false;
	}
}

?>