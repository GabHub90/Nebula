<?php

class Maestro {

	private $server="srvdb";
	private $user="sa";
	private $pw="Gns1999";
	private $db="GC_AZI_GABELLINI_prod";
	private $cset="UTF-8";
	
	protected $db_handler;

	protected $result;

	protected $fetchQueue=array();
	protected $fetchIndex=0;
	
	function __construct() {
		$connectionInfo = array("Database"=>$this->db, "UID"=>$this->user, "PWD"=>$this->pw, "CharacterSet" => $this->cset,"LoginTimeout"=>3);
		$this->db_handler=sqlsrv_connect($this->server,$connectionInfo);
		//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
		$query="SET LANGUAGE Italian";
		sqlsrv_query($this->db_handler,$query);
	}
	
	function __destruct() {
		sqlsrv_close($this->db_handler);
	}

	function loadResult($r) {
		$this->fetchIndex++;
		$this->fetchQueue[$this->fetchIndex]=$r;
		return $this->fetchIndex;
	}

	function getFetch($index) {

		if (!$this->fetchQueue[$index]) return array();
		
		try{
			$row=sqlsrv_fetch_array($this->fetchQueue[$index],SQLSRV_FETCH_ASSOC);
			return $row;
		}catch(Exception $e) {
			unset($this->fetchQueue[$index]);
			return array();
		}
	}
	
	function query($query) {

		try {
			$result=sqlsrv_query($this->db_handler,$query);
		} catch (Exception $e) {
			// Gestisci l'eccezione catturata
			die('Si è verificato un errore: ' . $e->getMessage());
		}
		
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
	
	function closeDB() {
		sqlsrv_close($this->db_handler);
	}

	function close() {
		sqlsrv_close($this->db_handler);
	}
}

?>	