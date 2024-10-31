<?php
require_once(DROOT.'/nebula/core/odl/timb_func.php');

class lamentatoOdl {

	/*
	[{"rif":"1387722","cc":"OCO","cm":"OOP","ca":"C","ind_carrozzeria":"N","tipo_gar":"","cod_azione":"","t_riga":"I","descrizione":"VETTURA TIRA A DX","note_d":"provata stefano\nsost.testine settembre 2021","note_p":"","chiuso":"N","d_mov":"20211227","lam":"A","riga":1,"commessa":"0","subrep":"DIAPV","cod_pacchetto":"","ore":"3.75","ind_stato":"L"}]
	*/
	
	protected $info=array(
        "rif"=>"",
        "acarico"=>"",
        "cod_movimento"=>"",
        "cg"=>"",
        "cod_azione"=>"",
        "t_riga"=>"",
        "des_riga"=>"",
        "note_d"=>"",
        "note_p"=>"",
        "ind_chiuso"=>"",
        "dat_inserimento"=>"",
        "lam"=>"",
        "num_riga"=>"",
        "num_commessa"=>"",
        "subrep"=>"",
        "cod_pacchetto"=>"",
        "ore"=>"",
        "ind_stato"=>"",
		"pos"=>"",
		"dms"=>"",
		"pren"=>""
    );

	/*protected $extra=array(
		"pos"=>""
	);*/

	protected $elem=array(
		'M'=>array(),
		'R'=>array(),
		'V'=>array()
	);

	protected $blockIntest=array(
		'M'=>"Manodopera:",
		'R'=>"Ricambi:",
		'V'=>"Oneri:",
		'tot'=>""
	);
	
	protected $addebito=array();
	protected $split;
	
	protected $valori=array(
		'M'=>0,
		'R'=>0,
		'V'=>0,
		'tot'=>0,
		'ore_fatt'=>0,
		'ore_marc'=>0,
		'sconto'=>array(
			'M'=>0,
			'R'=>0,
			'V'=>0,
			'tot'=>0		
		),
		'ivato'=>array(
			'M'=>0,
			'R'=>0,
			'V'=>0,
			'tot'=>0	
		)
	);

	protected $rows=0;

	protected $storicoFlag=false;
	protected $storicoPrenotazione=false;

	//contiene tutte le marcature riferite al lamentato in ordine di data
	protected $marcature=array();
	//contiene i riferimenti dei tecnici che hanno una marcatura aperta
	protected $marcatureAperte=array();

	//contiene eventualii richieste materiali specifiche per il lamentato [riga][record richiesta]
	protected $richieste=array(); 

    protected $galileo;
    protected $odlFunc;
	protected $timbFunc;
    protected $wh;

	protected $log=array();
	
	function __construct($arr,$wh,$odlFunc,$galileo) {

        $this->wh=$wh;
        $this->odlFunc=$odlFunc;
        $this->galileo=$galileo;

		$this->timbFunc=new nebulaTimbFunc($this->galileo);
	
		foreach ($arr as $k=>$v) {
            if (array_key_exists($k,$this->info)) $this->info[$k]=$v;
        }

		$arr=array(
			"cod_movimento"=>$this->info['cod_movimento'],
			"acarico"=>$this->info['acarico'],
			"cg"=>$this->info['cg']
		);

		$this->addebito=$this->odlFunc->getAddebito($arr,$this->wh->getActualDms());

		if ($this->info['pren']=='N') {
			//lettura marcature riguardanti il lamentato
			$map=$this->timbFunc->getMarcatureOdl($this->info['rif'],$this->info['lam'],$this->info['dms']);

			if ($map['result']) {

				$fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

				while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
					$this->marcature[$row['num_rif_riltem']]=$row;
					if ($row['d_fine']=='') $this->marcatureAperte[$row['cod_operaio']]=$row;
				}
			}
		}

		//==== EXTRA ======
		/*$count=0;
		$this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('base','avalon');

		$this->galileo->getLamExtra(substr($this->wh->getActualDms(),0,1),$this->info['rif'],$this->info['lam']);

		//$this->log[]=array(substr($this->wh->getActualDms(),0,1),$this->info['rif'],$this->info['lam']);
		$this->log[]=$this->galileo->getLog('query');

		$fid=$this->galileo->preFetchBase('avalon');

        while ($row=$this->galileo->getFetchBase('avalon',$fid)) {

			$count++;

			foreach ($row as $k=>$v) {
				if (array_key_exists($k,$this->extra)) $this->extra[$k]=$v;
			}
        }*/

		//Se non ha trovato nessun record in LAMEXTRA
		//if ($count==0) {
			/*popola l'array di default e scrivi il record nel DB
			"ut"=> uguali ad INFO
			"subrep"=> tradotto attraverso ODLFUNC
			"dat_inizio"=>"",
			"dat_fine"=>"",
			"distro"=>'XDIS'
			*/
		//}



				
		/*======== Marcature =================
		$query="SELECT 
				t1.num_rif_movimento,
				t1.cod_inconveniente,
				t1.cod_operaio,
				t1.num_riga, 
				isnull(CONVERT(VARCHAR(8),t1.dat_ora_inizio,112),'') AS d1,
				CONVERT(VARCHAR(5),t1.dat_ora_inizio,108) AS t1,
				isnull(CONVERT(VARCHAR(8),t1.dat_ora_fine,112),'') AS d2,
				CONVERT(VARCHAR(5),t1.dat_ora_fine,108) AS t2,
				t1.qta_ore_lavorate AS ore,
				t1.des_note
				FROM of_riltem AS t1
				WHERE t1.num_rif_movimento='".$a['rif']."' AND t1.cod_inconveniente='".$a['lam']."'
				ORDER BY t1.cod_operaio,t1.dat_ora_inizio
		";
		
		 $result=$maestro->query($query);
		 
		 if($result) {
		 	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {

		 		//====================================================================
		 		//se la marcatura è APERTA
		 		//calcola ad adesso
		 		//indica in modo che sia segnalato nella stampa a video del lamentato
		 		//====================================================================
		 		if ($row['d2']=='') {
		 			$delta=GabFunc::gab_delta_tempo_2($row['d1'].':'.$row['t1'],date('Ymd:H:m:s'),'ut');
		 			$row['ore']=$delta/100;
		 			$this->flag_marc_open=1;
		 		}
		 		
		 		$this->marcature[$row['cod_operaio']][]=$row;
		 		$this->valori['ore_marc']+=$row['ore'];
		 		
		 	}
		 }
		 
		 //========== DISTRIBUZIONE=============
		 //if ($this->info[distribuzione]=='') $this->distribuzione=array("codice"=>"IMM","param"=>"");
		 //else $this->distribuzione=json_decode($this->info[distribuzione]);
		 $this->distribuzione=$this->info['distribuzione'];
		 */
	}

	function getLog() {
		return $this->log;
	}

	function setStorico($flag,$prenotazione) {
		$this->storicoFlag=$flag;
		$this->storicoPrenotazione=$prenotazione;
	}

	function getInfo() {
		return $this->info;
	}

	function getExtra() {
		return $this->extra;
	}
	
	function add($arr) {
		
		$this->elem[$arr['t_riga']][$arr['riga']]=$arr;
		
		//$this->valori[$arr[t_riga]]+=$arr[importo];
		$lordo=round($arr['prezzo']*$arr['qta'],2);
		$this->valori[$arr['t_riga']]+=$lordo;
		//$this->valori[sconto][$arr[t_riga]]+=round($lordo*($arr[prc_sconto]/100),2);
		$this->valori['sconto'][$arr['t_riga']]+=$lordo-$arr['importo'];

		//12.04.2021
		$this->valori['ivato'][$arr['t_riga']]+=$arr['ivato'];
		
		if ($arr['t_riga']=='M') $this->valori['ore_fatt']+=$arr['qta'];

		$this->rows++;
	}

	function loadRichieste($a) {

		foreach ($a as $riga=>$r) {
			$this->richieste[]=$r;
		}
	}

	function get_info() {
		return $this->info;
	}
	
	function get_addebito() {
		return $this->addebito;
	}
	
	function get_valori() {
		return $this->valori;
	}
	
	function get_pren() {
		$a=array(
			'ut'=>$this->ut_pren,
			'subrep'=>$this->info['subrep'],
			'distribuzione'=>$this->distribuzione,
			'd_inizio'=>$this->d_inizio_presunta,
			'd_fine'=>$this->d_fine_presunta
		);

		return $a;
	}	

	function get_inizio_presunta() {
		return $this->d_inizio_presunta;
	}
	
	//========================================================================

	function calcolaTot() {
		$this->valori['tot']=$this->valori['M']+$this->valori['R']+$this->valori['V'];
		$this->valori['sconto']['tot']=$this->valori['sconto']['M']+$this->valori['sconto']['R']+$this->valori['sconto']['V'];
		$this->valori['ivato']['tot']=$this->valori['ivato']['M']+$this->valori['ivato']['R']+$this->valori['ivato']['V'];
	}

	function drawLam() {

		$this->calcolaTot();
		$this->drawHead();
		$this->drawElements();
	}

	function drawHead() {

		echo '<div style="position:relative;'.($this->addebito?'background-color:'.$this->addebito['colore'].';':'').'" >';

			echo '<div>';
				echo '<div style="position:relative;display:inline-block;width:5%;cursor:pointer;" ';
					if (!$this->storicoFlag) echo 'onclick="window._nebulaOdl.switchLamEdit(\''.$this->info['lam'].'\');"';
				echo ' >';
					echo $this->info['lam'];
				echo '</div>';
				echo '<div style="position:relative;display:inline-block;width:85%;font-weight:bold;">';
					echo utf8_encode(strtoupper(substr($this->info['des_riga'],0,50)));
				echo '</div>';
				echo '<div style="position:relative;display:inline-block;width:10%;">';
					echo $this->info['acarico'];
				echo '</div>';
			echo '</div>';

			echo '<div style="font-size:0.9em;">';

				echo '<div style="position:relative;display:inline-block;width:5%;"></div>';

				echo '<div style="position:relative;display:inline-block;width:94%;">';
					if (strlen($this->info['des_riga'])>50) echo utf8_encode($this->info['des_riga']).' - ';
					echo $this->info['note_d'];
				echo '</div>';

			echo '</div>';

		echo '</div>';

		if (!$this->storicoFlag) {

			echo '<div id="odielle_body_lam_edit_'.$this->info['lam'].'" style="position:relative;padding:3px;box-sizing:border-box;display:none" >';
				echo '<textarea id="odielle_body_lam_edit_'.$this->info['lam'].'_txt" rows="4" style="width:100%;resize:none;" >'.utf8_encode($this->info['des_riga']).'</textarea>';
				echo '<div style="position:relative;width:100%;margin-top:10px;text-align:right;" >';
					echo '<button style="margin-right:20px;" onclick="window._nebulaOdl.confirmLamEdit(\''.$this->info['rif'].'\',\''.$this->info['lam'].'\',\''.$this->info['dms'].'\',\''.$this->info['pren'].'\')">Conferma</button>';
				echo '</div>';
			echo '</div>';
		}

		/*echo '<div>';
			echo json_encode($this->info);
		echo '</div>';*/

	}

	function drawHeadDedalo() {

		echo '<div style="position:relative;margin-top:6px;'.($this->addebito?'background-color:'.$this->addebito['colore'].';':'').'" >';

			echo '<div>';
				echo '<div style="position:relative;display:inline-block;width:8%;font-size:0.8em;padding-left:3px;box-sizing:border-box;">';
					echo $this->info['pos'].' - '.$this->info['lam'];
				echo '</div>';
				echo '<div style="position:relative;display:inline-block;width:82%;font-weight:bold;">';
					echo strtoupper(substr($this->info['descrizione'],0,45));
				echo '</div>';
				echo '<div style="position:relative;display:inline-block;width:10%;">';
					echo $this->info['cc'];
				echo '</div>';
			echo '</div>';

		echo '</div>';

		/*echo '<div>';
			echo json_encode($this->elem);
		echo '</div>';*/

	}

	function drawElements() {

		//{"rif":"1386753","t_riga":"M","tipo":"","articolo":"","flag_prelevato":"N","gr":"","operazione":"01030014","varie":"","descrizione":"ISPEZIONI CON CAMBIO OLIO .","qta":".90","prezzo":"54.0000","prc_sconto":".00","importo":"48.6000","ivato":"59.2900","cod_iva":"22","prc_iva":"22.00","note_d":"","note_p":"","lam":"A","riga":2}

		//echo '<div style="position:relative:width:95%;">';

			echo '<div style="position:relative;font-size:0.9em;color:darkgoldenrod;margin-bottom:5px;margin-top:5px;border-bottom: 1px solid #777777;">';
				echo '<div style="position:relative;color:black;">';
					if (!$this->storicoPrenotazione) {
						$this->drawHeadMarcature();
					}
				echo '</div>';
				echo '<div style="position:relative;">';
					$this->drawHeadBlock('tot');
				echo '</div>';
			echo '</div>';

			foreach ($this->elem as $tipo=>$t) {

				echo '<div style="position:relative;font-weight:bold;font-size:0.9em;color:black;margin-bottom:5px;margin-top:5px;">';
					$this->drawHeadBlock($tipo);
				echo '</div>';

				//se siamo su INFINITY ed è una prenotazione non scrivere i ricambi ma direttamente la richiesta materiale
				if ($tipo!='R' || $this->info['dms']!='infinity' || $this->info['pren']!='S') {

					if (count($this->elem[$tipo])>0) {

						foreach ($t as $e) {
							call_user_func_array(array($this, 'drawElem_'.$tipo), array($e));
						}
					}
				}

				if ($tipo=='R' && count($this->richieste)!=0) {

					echo '<div style="color:#bc57e9;font-weight:bold;" >Richieste Materiale</div>';
					
					$this->drawRichieste();
				}
			}

		//echo '</div>';

	}

	function drawRichieste() {

		//echo '<div>'.json_encode($this->richieste).'</div>';

		foreach ($this->richieste as $k=>$r) {

			echo '<div style="position:relative;color:#777777;">';
				
				echo '<div style="position:relative;display:inline-block;width:3%;text-align:left;">';
				echo '</div>';

				echo '<div style="position:relative;display:inline-block;width:4%;text-align:center;">';

					if ($r['precodice']!='ZZ') {
						if ($r['num_doc_ordcli']==0) {
							echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/richiesta_NO.png" />';
						}
						else {
							echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ricambi_OFF.png" />';
						}
					}
					
				echo '</div>';

				if ($r['precodice']=='ZZ') $color='#777777;';

				else {

					$dispo=$r['giacenza']-$r['impegnato'];
					
					if ($r['num_doc_ordcli']==0) $color='#777777;';
					else if ($dispo>=0) $color='green';
					else {
						if ($r['quantita_ordfor']>0) $color='#e34dd8';
						elseif ($r['giacenza']>0) $color='orange';
						else $color='red';
					}
				}
				
				echo '<div style="position:relative;display:inline-block;width:8%;text-align:right;font-size:0.9em;font-weight:bold;color:'.$color.';">';
					echo '<span style="margin-right:3px;">'.number_format($r['quantita_ric'],2,',','').'</span>';
				echo '</div>';
				
				echo '<div style="position:relative;display:inline-block;width:1%;">&nbsp;</div>';
				
				echo '<div style="position:relative;display:inline-block;width:22%;font-size:0.9em;">';
					echo $r['articolo'];
				echo '</div>';

				echo '<div style="position:relative;display:inline-block;font-size:0.9em;width:29%;">';
					echo utf8_encode($r['precodice'].'-'.substr(ucfirst(strtolower($r['descrizione'])),0,20));
				echo '</div>';
				
				//venatt
				echo '<div style="position:relative;display:inline-block;width:3%;text-align:right;">';
					//$this->flag_venatt($m);
				echo '</div>';
				
				echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
					echo number_format($r['listino'],2,',','');
				echo '</div>';

				$sconto=(float)$r['sconto1']+(float)$r['sconto2'];

				echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
					if ($sconto > 0 ) {
						echo number_format((float)$r['sconto1']+(float)$r['sconto2'],1,',','').'%';
					}
					else {
						echo '&nbsp;';
						//echo (float)$m[prc_sconto];
					}
				echo '</div>';
				
				$sconto=(float)$r['sconto1']+(float)$r['sconto2'];
				$importo=(float)$r['listino']*(float)$r['quantita_ordcli']*(1-($sconto/100));

				echo '<div style="position:relative;display:inline-block;width:12%;text-align:right;">';
					echo number_format($importo,2,',','');
				echo '</div>';

			echo '</div>';

		}

	}

	function drawHeadMarcature() {

		$stat=array(
            "flag"=>false,
            "ore_prenotate"=>0,
            "pos_lavoro"=>0,
            "ore_marcate"=>0,
            "tecnici"=>array()
        );
        
		if ($this->info['pren']=='N') {
			$res=$this->odlFunc->getLamStats($this->info['rif'],$this->info['lam'],$this->info['dms']);

			if ($res) {
				$pf=$this->odlFunc->getPiattaforma();
				$fid=$this->galileo->preFetchPiattaforma($pf,$res);

				while($row=$this->galileo->getFetchPiattaforma($pf,$fid)) {
					$stat['flag']=true;
					$stat['ore_prenotate']=$row['ore_prenotate_totali'];
					$stat['pos_lavoro']=$row['ore_fatturate_totali'];
					$stat['ore_marcate']+=$row['ore_lavorate'];
					$stat['tecnici'][$row['cod_operaio']]=$row;
				}
			}
		}

		//echo json_Encode($stat);
		/*{"flag":true,"ore_prenotate":"2.00","pos_lavoro":"1.7000000","ore_marcate":1.4,
		"tecnici":{"059":{"num_rif_movimento":3037,"ore_prenotate_totali":"2.00","ore_fatturate_totali":"1.7000000","ore_lavorate":"1.4000000","numero_doc":102548,"cod_inconveniente":1,"cod_operaio":"059"}}}
		*/
		if (!$stat['flag']) { 
			echo '<div style="color:red;">Statistica marcature non presente</div>';
			return;
		}

		echo '<div style="position:relative;color:black;">';

			echo '<table style="width:100%;font-size:0.9em;margin-bottom:10px;border-collapse:collapse;border:1px solid black;">';

				echo '<colgroup>';
					echo '<col span="3" style="width:20%;" />';
					echo '<col span="1" style="width:25%;" />';
					echo '<col span="1" style="width:15%;" />';
				echo '</colgroup>';

				echo '<thead style="">';
					echo '<tr>';
						echo '<th style="">Prenotato</th>';
						echo '<th style="">Pos. Lav.</th>';
						echo '<th style="">Marcato</th>';
						echo '<th style="">Tecnici</th>';
						echo '<th style="">Eff.</th>';
					echo '</tr>';
				echo '</thead>';

				echo '<tbody style="text-align:center;">';

					echo '<tr>';

						$temptd="";

						foreach ($stat['tecnici'] as $cod=>$c) {

							//COD è il codice operaio proprio del DMS da cui è stata tratta la statistica 
							$tempcod=$this->timbFunc->getRef($cod,$this->info['dms']);

							$temptd.='<div>';
								$xxx=($tempcod)?($tempcod.'<span style="font-size:0.8em;">&nbsp;('.$cod.')</span>'):'???';
								$temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;">'.$xxx.'</div>';

								$temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;">'.number_format($c['ore_lavorate'],2,'.','').'</div>';
								$temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;">';
									if (isset($this->marcatureAperte[$tempcod])) {
										$temptd.='<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
									}
								$temptd.='</div>';

							$temptd.='</div>';
						}

						echo '<td>'.number_format($stat['ore_prenotate'],2,'.','').'</td>';
						echo '<td>'.number_format($stat['pos_lavoro'],2,'.','').'</td>';
						echo '<td>'.number_format($stat['ore_marcate'],2,'.','').'</td>';

						echo '<td>';
							echo $temptd;
						echo '</td>';

						echo '<td>'.number_format(($stat['ore_marcate']==0)?0:(($stat['pos_lavoro']/$stat['ore_marcate'])*100),2,'.','').'%</td>';
	
					echo '</tr>';

				echo '</tbody>';
			
			echo '</table>';

		echo '</div>';

		if (count($this->marcature)>0) {

			$bl=new blockList('sto_'.$this->info['rif'].'_'.$this->info['lam'],0);

			$bl->setHead('<div style="font-weight:bold;color:blue;" >Marcature:</div>');

			ob_start();

				foreach($this->marcature as $k=>$m) {

					echo '<div>';
						/*{"anno":"2022","id_cliente":29269,"data_doc":"2022-03-08 00:00:00.000","tipo_doc":"LO01","numero_doc":102548,"id_riga":1,"cod_inconveniente":1,"num_rif_movimento":3037,"cod_officina":"PV","cod_movimento":"OOP","cod_operaio":"059","d_inizio":"20220308","o_inizio":"13:55","d_fine":"20220308","o_fine":"15:19","qta_ore_lavorate":"1.40","des_note":"","num_rif_riltem":2419,"ind_chiuso":"S","nome_operaio":"Dionigi Alessandro"}*/
						//echo json_encode($m);
						echo '<div style="position:relative;display:inline-block;width:37%;">'.$m['nome_operaio'].' ('.$m['cod_operaio'].')</div>';
						echo '<div style="position:relative;display:inline-block;width:25%;">';
							echo '<div style="position:relative;display:inline-block;width:80px;">'.mainFunc::gab_todata($m['d_inizio']).'</div>';
							echo '<div style="position:relative;display:inline-block;">'.$m['o_inizio'].'</div>';
						echo '</div>';
						echo '<div style="position:relative;display:inline-block;width:20%;">';
							if ($m['d_fine']=='' && $m['ind_chiuso']=='N') {
								echo '<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
							}
							else {
								echo '<div style="position:relative;display:inline-block;width:80px;">'.mainFunc::gab_todata($m['d_fine']).'</div>';
								echo '<div style="position:relative;display:inline-block;">'.$m['o_fine'].'</div>';
							}
						echo '</div>';
						echo '<div style="position:relative;display:inline-block;width:10%;">'.($m['des_note']!=""?substr($m['des_note'],0,3):'').'</div>';
						echo '<div style="position:relative;display:inline-block;width:7%;text-align:right;">'.number_format($m['qta_ore_lavorate'],2,'.','').'</div>';
						
					echo '</div>';
				}

			$bl->setBody(ob_get_clean());

			echo '<div style="margin-bottom:5px;">';

				echo $bl->draw();

			echo '</div>';
		}
		
	}

	function drawHeadBlock($tipo) {

		if (!$this->storicoFlag) {

			echo '<div style="position:relative;display:inline-block;width:3%;">&nbsp;</div>';

			echo '<div style="position:relative;display:inline-block;width:5%;">';
				if ($tipo!='tot') echo '<img style="width:10px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/v.png"/>';
				else echo '&nbsp;';
			echo '</div>';
		}

		echo '<div style="position:relative;display:inline-block;width:'.($this->storicoFlag?'22':'25').'%;color:#b98936;">';
			echo $this->blockIntest[$tipo];
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:9%;">';
			echo 'Lordo:';
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:12%;text-align:right;">';
			echo number_format($this->valori[$tipo],2,',','');
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:2%;">&nbsp;</div>';

		echo '<div style="position:relative;display:inline-block;width:9%;">';
			echo 'Sconto:';
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:'.($this->storicoFlag?'13':'14').'%;text-align:right;">';
			echo number_format($this->valori['sconto'][$tipo],2,',','');
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:2%;">&nbsp;</div>';

		echo '<div style="position:relative;display:inline-block;width:6%;">';
			echo 'Imp:';
		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:13%;text-align:right;">';
			echo number_format($this->valori[$tipo]-$this->valori['sconto'][$tipo],2,',','');
		echo '</div>';
	}

	function drawElem_M($m) {

		if ($m['tipo_riga']=='C') {
			$this->drawCommento($m);
			return;
		}

		echo '<div style="position:relative;';
			//if((float)$m[importo]==0) echo 'background-color:#fbf895;';
			if((float)$m['importo']==0) echo 'color:#ab2abf;';
			if (isset($m['evento']) && $m['evento']) echo "color:green;";
		echo '">';

			if (!$this->storicoFlag) {
			
				echo '<div style="position:relative;display:inline-block;width:3%;text-align:left;">';
					echo '<input id="rd_movdet_line_chk_'.$m['riga'].'" type="checkbox" style="position:relative;width:12px;" value="'.$m['riga'].'" />';
				echo '</div>';

				echo '<div style="position:relative;display:inline-block;width:4%;text-align:center;">';
				echo '</div>';
			}
			
			echo '<div style="position:relative;display:inline-block;width:8%;text-align:right;font-size:0.9em;">';
				echo '<span style="margin-right:3px;">'.number_format($m['qta'],2,',','').'</span>';
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:1%;">&nbsp;</div>';
			
			echo '<div style="position:relative;display:inline-block;width:'.($this->storicoFlag?'20':'22').'%;">';
				echo $m['operazione'];
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;font-size:0.9em;width:'.($this->storicoFlag?'25':'29').'%;">';
				echo utf8_encode(substr(ucfirst(strtolower($m['descrizione'])),0,24));
			echo '</div>';
			
			//venatt
			echo '<div style="position:relative;display:inline-block;width:3%;text-align:right;">';
				//$this->flag_venatt($m);
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				echo number_format((float)$m['prezzo'],2,',','');
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				if ( ((float)$m['prc_sconto']) > 0 ) {
					echo number_format((float)$m['prc_sconto'],2,',','').'%';
				}
				else {
					echo '&nbsp;';
					//echo (float)$m[prc_sconto];
				}
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;width:12%;text-align:right;">';
				echo number_format($m['importo'],2,',','');
			echo '</div>';

			if ($this->storicoFlag) {

				echo '<div style="position:relative;display:inline-block;width:12%;">';

					if (isset($m['evento']) && $m['evento']) {

						$this->eventIcon($m,true);
					}
					else $this->eventIcon($m,false);

				echo '</div>';
			}
			
		echo '</div>';
	}

	function drawElem_R($m) {

		if ($m['tipo_riga']=='C') {
			$this->drawCommento($m);
			return;
		}

		echo '<div style="position:relative;';					
			//if((float)$m[importo]==0) echo 'background-color:#fbf895;';
			if((float)$m['importo']==0) echo 'color:#ab2abf;';
			if (isset($m['evento']) && $m['evento']) echo "color:green;";
		echo '">';

			if (!$this->storicoFlag) {
			
				echo '<div style="position:relative;display:inline-block;width:3%;text-align:left;">';
					echo '<input id="rd_movdet_line_chk_'.$m['riga'].'" type="checkbox" style="position:relative;width:12px;" value="'.$m['riga'].'"/>';
				echo '</div>';

				/*echo '<div style="position:relative;display:inline-block;width:4%;text-align:center;">';
					echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ricambi_OFF.png" />';
				echo '</div>';*/
			}
			
			echo '<div style="position:relative;display:inline-block;width:8%;text-align:right;font-size:0.9em;">';
				echo '<span style="margin-right:3px;">'.number_format($m['qta'],2,',','').'</span>';
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:1%;">&nbsp;</div>';
			
			echo '<div style="position:relative;display:inline-block;width:'.($this->storicoFlag?'20':'22').'%;font-size:0.9em;';
				if ($m['flag_prelevato']!='S' && !$this->storicoFlag) echo 'background-color:#fdc7c7;';
			echo '">';
				echo $m['articolo'];
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;font-size:0.9em;width:'.($this->storicoFlag?'25':'29').'%;';
				if ($m['flag_prelevato']!='S' && !$this->storicoFlag) echo 'background-color:#fdc7c7;';
			echo '">';
				echo utf8_encode($m['tipo'].'-('.$m['gr'].') '.substr(ucfirst(strtolower($m['descrizione'])),0,20));
			echo '</div>';
			
			//venatt
			echo '<div style="position:relative;display:inline-block;width:3%;text-align:right;">';
				//$this->flag_venatt($m);
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				echo number_format($m['prezzo'],2,',','');
			echo '</div>';
			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				if ( ((float)$m['prc_sconto']) > 0 ) {
					echo number_format((float)$m['prc_sconto'],1,',','').'%';
				}
				else {
					echo '&nbsp;';
					//echo (float)$m[prc_sconto];
				}
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;width:12%;text-align:right;">';
				echo number_format($m['importo'],2,',','');
			echo '</div>';

			if ($this->storicoFlag) {

				echo '<div style="position:relative;display:inline-block;width:12%;">';

					if (isset($m['evento']) && $m['evento']) {

						$this->eventIcon($m,true);
					}
					else $this->eventIcon($m,false);
				echo '</div>';
			}

		echo '</div>';
	}

	function drawElem_V($m) {

		if ($m['tipo_riga']=='C') {
			$this->drawCommento($m);
			return;
		}

		echo '<div style="position:relative;';					
			//if((float)$m[importo]==0) echo 'background-color:#fbf895;';
			if((float)$m['importo']==0) echo 'color:#ab2abf;';
			if (isset($m['evento']) && $m['evento']) echo "color:green;";
		echo '">';

			if (!$this->storicoFlag) {
			
				echo '<div style="position:relative;display:inline-block;width:3%;text-align:left;">';
					echo '<input id="rd_movdet_line_chk_'.$m['riga'].'" type="checkbox" style="position:relative;" value="'.$m['riga'].'"/>';
				echo '</div>';

				echo '<div style="position:relative;display:inline-block;width:4%;text-align:center;">';
				echo '</div>';
			}
			
			echo '<div style="position:relative;display:inline-block;width:8%;text-align:right;font-size:0.9em;">';
				echo '<span style="margin-right:3px;">'.number_format($m['qta'],2,',','').'</span>';
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:1%;">&nbsp;</div>';
			
			echo '<div style="position:relative;display:inline-block;width:'.($this->storicoFlag?'20':'22').'%;">';
				echo $m['varie'];
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;font-size:0.9em;width:'.($this->storicoFlag?'25':'29').'%;">';
				echo utf8_encode(substr(ucfirst(strtolower($m['descrizione'])),0,24));
			echo '</div>';
			
			//venatt
			echo '<div style="position:relative;display:inline-block;width:3%;text-align:right;">';
				//$this->flag_venatt($m);
			echo '</div>';
			
			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				echo number_format($m['prezzo'],2,',','');
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
				if ( ((float)$m['prc_sconto']) > 0 ) {
					echo number_format((float)$m['prc_sconto'],1,',','').'%';
				}
				else {
					echo '&nbsp;';
					//echo (float)$m[prc_sconto];
				}
			echo '</div>';

			echo '<div style="position:relative;display:inline-block;width:12%;text-align:right;">';
				echo number_format($m['importo'],2,',','');
			echo '</div>';

			if ($this->storicoFlag) {

				echo '<div style="position:relative;display:inline-block;width:12%;">';

					if (isset($m['evento']) && $m['evento']) {

						$this->eventIcon($m,true);
						//echo $m['evento']['oggetto'];
					}
					else $this->eventIcon($m,false);
				echo '</div>';

				//echo '<div>'.json_encode($m).'</div>';
			}

		echo '</div>';
	}

	function eventIcon($m,$flag) {

		echo '<div style="position:relative;display:inline-block;width:30%;text-align:right;">';

			$str="";
			if ($m['t_riga']=='M') $str=$m['operazione'];
			elseif ($m['t_riga']=='V') $str=$m['varie'];
			elseif ($m['t_riga']=='R') $str=$m['tipo'].'_'.$m['articolo'];

			if ($flag) {
				
				///////////////////////////////////////////////////////////////////
				//if ($m['evento']['chk_std']==1 && $m['evento']['chk_actual']=='CHK') echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/chk.png" onclick="window._nebulaStorico.setINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\',\'INS\');" />';
				//elseif ($m['evento']['chk_std']==1 && $m['evento']['chk_actual']=='INS') echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/ok.png" onclick="window._nebulaStorico.delINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\');"/>';
				if ($m['evento']['chk_actual']=='CHK') echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/chk.png" onclick="window._nebulaStorico.setINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\',\'INS\');" />';
				elseif ($m['evento']['chk_actual']=='INS') echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/ok.png" onclick="window._nebulaStorico.delINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\');"/>';
				elseif ($m['evento']['chk_actual']=='DEL') echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/del.png" onclick="window._nebulaStorico.delINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\');" />';
				else echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/ok.png" onclick="window._nebulaStorico.setINS(\''.$this->info['rif'].'\',\''.$this->info['dms'].'\',\''.$str.'\',\'DEL\');" />';
			}

		echo '</div>';

		echo '<div style="position:relative;display:inline-block;width:70%;text-align:right;">';
			//se la riga è stata esclusa per una quantità errata comunque è editabile
			if ($m['flag_qta']) {
				echo '<img style="width:12px;height:12px;cursor:pointer;border:1px solid red;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/edit.png" onclick="window._nebulaStorico.editEvento(\''.$m['flag_qta'].'\',\''.$str.'\',\''.$m['t_riga'].'\');" />';
			}
			elseif ($flag) {
				echo '<span style="cursor:pointer;" onclick="window._nebulaStorico.editEvento(\''.$m['evento']['oggetto'].'\',\''.$str.'\',\''.$m['t_riga'].'\');" >'.$m['evento']['oggetto'].'</span>';
			}
			else {
				echo '<img style="width:12px;height:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/edit.png" onclick="window._nebulaStorico.editEvento(\'\',\''.$str.'\',\''.$m['t_riga'].'\');" />';
			}
		echo '</div>';
	}

	function drawCommento($m) {

		echo '<div style="position:relative;display:inline-block;width:90%;color:#777777;">';
			echo substr($m['descrizione'],0,60);
		echo '</div>';

	}




	












	
	/*function flag_venatt($a) {
		
		if ($a['va_actual']!="") {
			echo '<img style="position:relative;top:1px;width:10px;height:10px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/va/dot_red.png" onclick="window._odlobj.ot_delAll(\''.$a['va_actual'].'\');" />';
		}
		elseif (array_key_exists($a['venatt'],$this->venatt)) {
			echo '<img style="position:relative;top:1px;width:10px;height:10px;cursor:pointer;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/va/'.$this->venatt[$a['venatt']]['icona'].'" onclick="window._odlobj.ot_delLine(\''.$a['riga'].'\',\''.$a['va_group'].'\');"/>';
			
				//$this->log[]=$this->venatt[$r[venatt]];
		}
		elseif ($a['venatt']!="") {
			echo '<img style="position:relative;top:1px;width:10px;height:10px;cursor:pointer;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/va/jmp.png" />';
		}
		else {
			echo '&nbsp;';
		}
	}
	
	
	function draw_edit_lamentato() {
			
		//lamentato
		echo '<div id="dedalo_lams_'.$this->info['lam'].'" style="width:97%;background-color:'.$this->bk[$this->addebito].';font-weight:bold;margin-top:5px;" data-param="">';
			echo'<script style="text/javascript">';
				$tpar=str_replace('\r',' ',json_encode($this->info));
				$tpar=str_replace('\n',' ',$tpar);
				//$tpar=str_replace("'","\'",$tpar);
				//echo 'var temp=\''.$tpar.'\';';
				echo 'var temp='.$tpar.';';
				echo '$("#dedalo_lams_'.$this->info['lam'].'").data("param",JSON.stringify(temp));';
			echo '</script>';

			echo '<div style="display:inline-block;width:5%;">';
				echo '<img style="position:relative;margin-right:10px;width:15px;height:15px;cursor:pointer;vertical-align:top;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/edit.png" onclick="window._newlamobj.newlam_open(\''.$this->info['lam'].'\');"/>';
			echo '</div>';
			echo '<div style="display:inline-block;width:6%;vertical-align:top;">';
				echo '<span style="font-size:8pt;">'.$this->split.'</span>';
				echo '<span>'.$this->info['lam'].'</span>';
				echo '<span style="font-size:10pt;color:red;">'.$this->info['ind_stato'].'</span>';
			echo '</div>';
			echo '<div style="display:inline-block;width:72%;vertical-align:top;">';
				//echo $this->info[descrizione].$this->info[ca].$this->info[tipo_gar].$this->info[cm];
				echo '<div>'.$this->info['descrizione'].'</div>';
				//note prima SE CI SONO
				if ($this->info['note_p']!='') {
					echo '<div style="width:100%;font-weight:normal;min-height:2px;border:1px solid #4c6fa2;font-size:smaller;">';
						echo '<div id="rd_nota_prima_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_p'].'</div>';
					echo '</div>';
				}	
				//note
				echo '<div style="width:100%;font-weight:normal;min-height:2px;font-size:smaller;">';
					echo '<div id="rd_nota_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_d'].'</div>';
				echo '</div>';
			echo '</div>';
			echo '<div style="display:inline-block;width:13%;text-align:center;vertical-align:top;';
				if ($this->info['tipo_gar']=='X') echo 'color:red;';
			echo '">';
				echo $this->info['cc'];
				if ($this->info['tipo_gar']!='*') {
					echo '<span style="font-size:smaller;">-'.$this->info['tipo_gar'].'</span>';
				}
			echo '</div>';
			echo '<div style="display:inline-block;width:4%;text-align:center;vertical-align:top;">';
				if ($this->rows==1) {
					echo '<img style="width:15px;height:15px;position:relative;vertical-align:middle;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/del.png" onclick="_newlamobj.del(\''.$this->info['lam'].'\')"/>';
					//echo $this->rows;
				}
			echo '</div>';
		echo '</div>';
		
		//occupazione
		echo '<div style="width:97%;font-weight:normal;min-height:2px;color:#a22c2c;">';
			echo '<div style="display:inline-block;width:10%;text-align:right;">';
				echo number_format($this->ut_pren,2,',','');
			echo '</div>';
			echo '<div style="display:inline-block;width:15%;text-align:center;font-weight:bold;">';
				echo $this->info['subrep'];
			echo '</div>';
			echo '<div style="display:inline-block;width:23%;text-align:left;">';
				echo '<img style="width:25px;height:15px;top:2px;position:relative;top:2px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/odl/img/dist/'.substr($this->distribuzione,0,3).'.png"/>';
				echo '<span style="margin-left:5px;">'.$this->distribuzione.'</span>';
			echo '</div>';
			echo '<div style="display:inline-block;width:22%;text-align:left;color:black;">';
				echo GabFunc::gab_todata(substr($this->d_inizio_presunta,0,8)).'&nbsp;'.substr($this->d_inizio_presunta,9,5);
			echo '</div>';
			echo '<div style="display:inline-block;width:5%;text-align:center;">';
				echo'<img style="width:15px;height:8px;margin-top:4px;" src="http://'.$_SERVER["HTTP_HOST"].'/apps/gals/odl/img/arrow_right.png"/>';
			echo '</div>';
			echo '<div style="display:inline-block;width:22%;text-align:left;color:black;">';
				echo GabFunc::gab_todata(substr($this->d_fine_presunta,0,8)).'&nbsp;'.substr($this->d_fine_presunta,9,5);
			echo '</div>';
		echo '</div>';
	}
	
	function draw_catcher() {
	
		//lamentato
		echo '<div style="width:97%;background-color:'.$this->bk[$this->addebito].';font-weight:bold;margin-top:5px;overflow:fit-content;">';
			echo '<div style="display:inline-block;width:3%;">&nbsp;</div>';
			echo '<div style="display:inline-block;width:5%;vertical-align:top;">';
				echo '<span style="font-size:8pt;">'.$this->split.'</span>';
				echo $this->info['lam'];
			echo '</div>';
			echo '<div style="display:inline-block;width:78%;">';
				//echo $this->info[descrizione].$this->info[ca].$this->info[tipo_gar].$this->info[cm];
				echo '<div>'.$this->info['descrizione'].'</div>';
				//note prima SE CI SONO
				if ($this->info['note_p']!='') {
					echo '<div style="width:100%;font-weight:normal;min-height:2px;border:1px solid #4c6fa2;font-size:smaller;">';
						echo '<div id="rd_nota_prima_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_p'].'</div>';
					echo '</div>';
				}
				
				//note
				echo '<div style="width:100%;font-weight:normal;min-height:2px;font-size:smaller;">';
					echo '<div id="rd_nota_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_d'].'</div>';
				echo '</div>';
			echo '</div>';
			echo '<div style="display:inline-block;width:14%;text-align:center;vertical-align:top;">';
				echo $this->info['cc'];
				if ($this->info['tipo_gar']!='*') {
					echo '<span style="font-size:smaller;">-'.$this->info['tipo_gar'].'</span>';
				}
			echo '</div>';
		echo '</div>';
	}

	function draw_overture() {

		echo '<div id="ot_lamline_'.$this->info['lam'].'" style="width:97%;background-color:'.$this->bk[$this->addebito].';font-weight:bold;margin-top:5px;height:fit-content;" data-pack="'.$this->info['cod_pacchetto'].'" >';
			echo '<div style="display:inline-block;width:10%;vertical-align:top;">';
				//echo '<div style="text-align:center;">'.$this->info['lam'].'</div>';
				echo '<div style="text-align:center;">';
					echo '<button id="ot_lam_set_'.$this->info['lam'].'" onclick="_ot_main.set_lam(\''.$this->info['lam'].'\',true);">'.$this->info['lam'].'</button>';
				echo '</div>';
			echo '</div>';
			echo '<div style="display:inline-block;width:76%;">';
				//echo $this->info[descrizione].$this->info[ca].$this->info[tipo_gar].$this->info[cm];
				echo '<div>'.$this->info['descrizione'].'</div>';
				//note prima SE CI SONO
				if ($this->info['note_p']!='') {
					echo '<div style="width:100%;font-weight:normal;min-height:2px;border:1px solid #4c6fa2;font-size:smaller;">';
						echo '<div id="rd_nota_prima_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_p'].'</div>';
					echo '</div>';
				}
				
				//note
				echo '<div style="width:100%;font-weight:normal;min-height:2px;font-size:smaller;">';
					echo '<div id="rd_nota_lam_'.$this->info['lam'].'" style="width:100%;">'.$this->info['note_d'].'</div>';
				echo '</div>';
			echo '</div>';
			echo '<div style="display:inline-block;width:14%;text-align:center;vertical-align:top;">';
				echo $this->info['cc'];
				if ($this->info['tipo_gar']!='*') {
					echo '<span style="font-size:smaller;">-'.$this->info['tipo_gar'].'</span>';
				}
			echo '</div>';
		echo '</div>';

		echo '<div id="ot_lam_container_'.$this->info['lam'].'" style="width:95%;"></div>';

	}*/
	
}
?>