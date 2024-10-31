<?php
class stEnv {

	private $maestro;
	private $db_handler;
	
	private $today;
	
	private $odl_servizio;
	private $addebiti;
	private $stato_lam;
	

	function __construct($maestro,$db_handler,$today) {
	
		$this->maestro=$maestro;
		$this->db_handler=$maestro;
		
		$this->today=$today;
		
		//===== odl di servizio =============================
		$query="SELECT * FROM CROOM_serv_rif WHERE CAST(data_i AS INT)<='".$this->today."' AND CAST(data_f AS INT)>='".$this->today."'";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->odl_servizio[$row['rif']]=$row;
			}
		}
		
		//===== addebiti ===================================
		$query="SELECT 
				t1.ca,
				t1.cg,
				t1.cm,
				t1.descrizione,
				t2.codice as colore
				FROM CROOM_addebiti as t1
				LEFT JOIN CROOM_colori as t2 on t1.colore=t2.tag
				";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->addebiti[]=$row;
			}
		}
		
		//===== stato lamentato ==================================
		$query="SELECT * FROM STSTOP_stato_chiusura";	
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->stato_lam[$row['indice']]=$row['testo'];
			}
		}
					
	}
	
	function get_servizio() {
		return $this->odl_servizio;
	}
	
	function get_addebiti() {
		return $this->addebiti;
	}
	
	function get_stato_lam() {
		return $this->stato_lam;
	}
	
		
}
?>