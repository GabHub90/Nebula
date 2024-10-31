<?php
class Maestro {

	private $server="srvdb";
	private $user="sa";
	private $pw="Gns1999";
	private $db="GC_AZI_GABELLINI_prod";
	private $cset="UTF-8";
	
	private $db_handler;
	
	function __construct() {
		//$this->db_handler=mssql_connect($this->server,$this->user,$this->pw);
		//mssql_select_db($this->db,$this->db_handler);
		$connectionInfo = array("Database"=>$this->db, "UID"=>$this->user, "PWD"=>$this->pw, "CharacterSet" => $this->cset);
		$this->db_handler=sqlsrv_connect("srvdb",$connectionInfo);
		//$this->db_handler=mssql_connect($this->server,$this->user,$this->pw);
		//mssql_select_db($this->db,$this->db_handler);
		//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
		$query="SET LANGUAGE Italian";
		sqlsrv_query($this->db_handler,$query);
	}
	
	function __destruct() {
		sqlsrv_close($this->db_handler);
	}
	
	//FUNZIONI UTILITA'
	
	function chime_query($query) {
		$result=sqlsrv_query($this->db_handler,$query);
		return $result;
	}

}	
?>	