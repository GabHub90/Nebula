<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/apps/tempo2/core/calendario.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/apps/tempo2/core/griglia.php');

class stReparto {

	private $today;
	private $tag_reparto="";
	
	private $reparto=array();
	private $collaboratori=array();
	
	private $sel_coll=0;
	
	private $panorama;
	
	private $marcature_aperte=array();
	private $ultime_marcature=array();
	
	private $odl_servizio=array();
	private $addebiti=array();
	private $stato_lam=array();

	private $maestro;
	private $db_handler;
	
	private $log;

	function __construct($db_handler,$maestro,$tag,$selected_coll) {
	
		$this->maestro=$maestro;
		$this->db_handler=$db_handler;
	
		$this->today=date('Ymd');
		$this->tag_reparto=$tag;
		$this->sel_coll=$selected_coll;
		
		/*$query="SELECT * FROM MAESTRO_reparti WHERE tag='".$tag."'";
		
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->reparto=$row;
			}
		}*/
		
		/*$inizio=substr($this->today,0,6).'01';
		$fine='21001231';
		$this->panorama=new QuartetPanorama($tag,$db_handler,$inizio,$fine);*/
		$this->panorama=$this->open_panorama($this->tag_reparto,$this->today);
		$cal= new calendario($this->db_handler,substr($this->today,0,4),$this->tag_reparto);

		$stored=false;
		$path=$_SERVER['DOCUMENT_ROOT'].'/apps/z_temp/isla/'.substr($this->today,0,6).'_'.$this->tag_reparto.'.txt';
		if ( $filedat=filemtime($path) ) {
			if ( date('Ymd',$filedat)==date('Ymd') ) {
				$stored=true;
				$cal->set_stored($stored);
			}
		}

		$cal->mese(substr($this->today,4,2),$this->panorama);

		if (!$stored) {
			$colls=$this->panorama->get_collaboratori_ststop($this->today,array());
		}
		else {
			$filedata=file_get_contents($path);
			$storedata=json_decode($filedata,true);
			$colls=$this->panorama->get_collaboratori_ststop($this->today,$storedata['isla']['ststop'][$this->today]);
		}

		ksort($colls);
			
		//===== marcature aperte ============================
		//array[]=marcatura (d=data , t=ora)
		$this->marcature_aperte=$maestro->st_get_marcature_aperte();
		
		//===== ultime marcature ============================
		//array[cod_operaio]=$row		
		$this->ultime_marcature=$maestro->st_get_ultime_marcature();
		
		
		//////////////////////////////////////////////////
		//ENVIRONMENT
		$env=new stEnv($this->maestro,$this->db_handler,$this->today);
		$this->odl_servizio=$env->get_servizio();
		$this->addebiti=$env->get_addebiti();
		$this->stato_lam=$env->get_stato_lam();
		//////////////////////////////////////////////////
		
		/*===== odl di servizio =============================
		$query="SELECT * FROM CROOM_serv_rif WHERE CAST(data_i AS INT)<='".$this->today."' AND CAST(data_f AS INT)>='".$this->today."'";
		if($result=sqlsrv_query($db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->odl_servizio[$row[rif]]=$row;
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
				$this->stato_lam[$row[indice]]=$row[testo];
			}
		}*/
		
		
		//===== panorama ====================================
		$this->reparto=$this->panorama->get_reparto($tag);

		//$this->log=$this->panorama->get_log();
		
		foreach ($colls as $id_marc=>$c) {
		
			$this->collaboratori[$id_marc]=new stCollaboratore($maestro,$c,$this->today,$this->sel_coll);
			
			$this->collaboratori[$id_marc]->load_odl_servizio($this->odl_servizio);
			$this->collaboratori[$id_marc]->load_addebiti($this->addebiti);
			$this->collaboratori[$id_marc]->load_stato_lam($this->stato_lam);
			
			if (array_key_exists($id_marc,$this->marcature_aperte)) {	
				
				//se ci sono più marcature, vengono gestite a livello COLLABORATORE
				if (count($this->marcature_aperte[$id_marc])>1) {
					$this->collaboratori[$id_marc]->load_m_aperte($this->marcature_aperte[$id_marc]);
				}
				//VERIFICA SE DATA MARCATURA È DIVERSA DA TODAY
				else {
					if ($this->marcature_aperte[$id_marc][0][d]==$this->today) {
						$this->collaboratori[$id_marc]->load_m_aperte($this->marcature_aperte[$id_marc]);
					}
					else {
						$this->chiudi_macatura_old($id_marc,$this->marcature_aperte[$id_marc]);
					}
				}
			}
			if (array_key_exists($id_marc,$this->ultime_marcature)) {
				$this->collaboratori[$id_marc]->load_ultima_marcatura($this->ultime_marcature[$id_marc]);
			}
			
		}
		
		//$this->log=$this->collaboratori;
		
	}
	
	function open_panorama ($tag,$today) {
		$inizio=substr($today,0,6).'01';
		//$fine='21001231';
		$fine=date('Ymt',mktime(0,0,0,(int)substr($today,4,2),1,(int)substr($today,0,4)));
		$ret=new QuartetPanorama($tag,$this->db_handler,$inizio,$fine);
		return $ret;
	}
	
	function chiudi_macatura_old($marcatempo,$m) {
		//{"d":"20181013","t":"08:20","num_rif_movimento":"1099882","cod_inconveniente":"A","cod_operaio":"10","num_riga":1,"dat_ora_inizio":{"date":"2018-10-13 08:20:00.000000","timezone_type":3,"timezone":"Europe\/Rome"},"dat_ora_fine":null,"qta_ore_lavorate":".00","des_note":"","cod_officina":null,"cod_tipo_manodopera":null,"ora_entrata_effettiva":{"date":"2018-10-13 08:20:34.000000","timezone_type":3,"timezone":"Europe\/Rome"},"ind_turno":" ","ind_straordinario":null,"num_rif_riltem":196066,"cod_off":"PV","flag_pitlane":"0","tipo_commessa":"OOP","num_rif_veicolo":"5014117","telaio":"WVWZZZAAZGD104386","targa":"FD463FC","des_veicolo":"ECO UP MOVE UP 1.0 68 CV"}
		
		$t_pan=$this->open_panorama($this->tag_reparto,$m[0][d]);
		$cal= new calendario($this->db_handler,substr($m[0][d],0,4),$this->tag_reparto);
		$cal->mese(substr($m[0][d],4,2),$t_pan);
		
		$t_colls=$t_pan->get_collaboratori_ststop($m[0][d],array());
		
		$t_coll=new stCollaboratore($this->maestro,$t_colls[$marcatempo],$m[0][d],$this->sel_coll);
		
		$t_coll->load_m_aperte($m);
		
		$arr_chiusura=$t_coll->chiudi_fine_turno();
		
		if ($arr_chiusura[limite]=="") {
			//$this->log.=$this->chiudi_old_macrorep($m[0],$t_pan->get_macrorep($this->tag_reparto));
			
			$macro_pan=$this->open_panorama($t_pan->get_macrorep($this->tag_reparto),$m[0][d]);
			//$macro_pan=$this->open_panorama('AZ',$m[0][d]);
			
			$t_wd=date('w',mktime(0,0,0,(int)substr($m[0][d],4,2),(int)substr($m[0][d],6,2),(int)substr($m[0][d],0,4)));
			$t_turni_pan=$macro_pan->get_oa_orario($t_wd);
			
			$t_limite="";
			foreach($t_turni_pan as $t) {
				//$t è un oggetto
				
				//se il turno non esiste continua
				if ($t->i=='00:00' && $t->f=='00:00') continue;
				
				if ($m[0][t]<$t->f) {
					$t_limite=$t->f;
				}
			}
			
			if ($t_limite=="") {
				//CANCELLA LA MARCATURA
				$this->maestro->st_cancella_marcatura($m[0][cod_operaio]); 
			}
			else {
				//CHIUDI LA MARCATURA
				$this->maestro->st_chiudi_marcatura($m[0][d].' '.$t_limite,$m[0][cod_operaio],"R");
				$this->ultime_marcature=$this->maestro->st_get_ultime_marcature();
				if (array_key_exists($m[0][cod_operaio],$this->ultime_marcature)) {
					$this->collaboratori[$m[0][cod_operaio]]->load_ultima_marcatura($this->ultime_marcature[$m[0][cod_operaio]]);
				}
			}
			
		}
		
		else {
			//CHIUDI LA MARCATURA
			$this->maestro->st_chiudi_marcatura($m[0][d].' '.$arr_chiusura[limite],$m[0][cod_operaio],"R");
			$this->ultime_marcature=$this->maestro->st_get_ultime_marcature();
			if (array_key_exists($m[0][cod_operaio],$this->ultime_marcature)) {
				$this->collaboratori[$m[0][cod_operaio]]->load_ultima_marcatura($this->ultime_marcature[$m[0][cod_operaio]]);
			}
		}
		
		//$this->log.=json_encode($t_coll->chiudi_fine_turno());
		//$this->log.=json_encode($m);
	}
	
	/*function chiudi_old_macrorep($m,$macrorep) {
		return json_encode($m).$macrorep;
	}*/
	
	function draw_bar() {
		//BAR
		echo '<div class="bar" style="color:';
			echo '#'.$this->reparto[fore].';';
		echo '">';
		
			echo '<div id="repbar" class="repbar">';
				echo '<div>'.$this->reparto[tag].' - '.$this->reparto[descrizione].'</div>';
			echo '</div>';
			
			echo '<div id="collbar" class="collbar">';
				foreach ($this->collaboratori as $k_marc=>$c) {
					echo '<div style="position:relative;margin-left:20px;top:3px;font-weight:bold;cursor:pointer;float:left;" onclick="ststop_select_coll('.$k_marc.');">['.$k_marc.']</div>';
				}
				
				if ($this->sel_coll!=0) {
					echo '<div id="st_bar_annulla" style="position:relative;margin-left:20px;top:3px;font-weight:bold;cursor:pointer;float:left;color:orange;" onclick="st_reset();">[annulla]</div>';
				}
				
			echo '</div>';
			
		echo '</div>';
	}
	
	function draw_colls() {
	
		//=======================================
		//metti in testa il selected_coll
		//=======================================
		$coda=array();
		$lista=array();
		
		if ($this->sel_coll!='0') {
			foreach ($this->collaboratori as $k=>$c) {
			
				if (count($lista)==0) {
				
					if ($k==$this->sel_coll) {
						$lista[$k]=$c;
						foreach ($coda as $k_coda=>$co) {
							$lista[$k_coda]=$co;
						}
					}
					else {
						$coda[$k]=$c;
					}
				}
				else {
					$lista[$k]=$c;
				}
			}
		}
		else {
			$lista=$this->collaboratori;
		}
	
		//echo json_encode($this->log);
		foreach ($lista as $l) {
			$l->draw_coll();
		}
		
		/*echo '<div>';
			echo json_encode($this->panorama->get_log());
		echo '</div>';*/
	}
	
	function draw_coll_open_head($coll,$tipo,$info) {
		$this->collaboratori[$coll]->draw_open_head($tipo,$info,$this->reparto['fore']);
	}
	
	
	/*function draw($tipo) {
		//echo '<div>'.$tipo.'</div>';
		$this->panorama->draw($tipo);
	}*/
	
	function draw_log() {
		echo json_encode($this->panorama->get_griglia('turni_sk'));
	}
	
}
?>