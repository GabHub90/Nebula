<?php 

class SandyMan {

	private $azione=array();
	private $lista=array();
	private $giorno='';
	private $wd=0;
	
	private $maestro;
	
	private $form_data=array();
	
	private $sett=array('Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato');
	
	private $url = 'http://www.nsgateway.net/smsscript/sendsms.php';
	
	private $log=array();

	function __construct($azione,$lista,$maestro) {
		$this->azione=$azione;
		$this->lista=$lista;
		$this->maestro=$maestro;
		
		$this->form_data=array('login'=>'sbuser804','password'=>'dx8wosm','tipo'=>1,'dest'=>'','testo'=>'','mitt'=>'Gabellini','status'=>1);

		// tipo = 1 -> spedisce SMS – tipo = 2 -> Ritorna num crediti		
	}
	
	function get_log() {
		return $this->log;
	}
	
	function execute_akt($codice,$giorno) {
	
		$this->giorno=$giorno;
		$temp=mktime(0,0,0,(int)substr($giorno,4,2),(int)substr($giorno,6,2),(int)substr($giorno,0,4));
		$this->wd=date('w',$temp);
	
		if(is_callable(array($this, $codice))){
			$res=$this->$codice();
		}
	}
	
	function trim_contatto($str) {
	
		if ($this->azione['tipo_contatto']=='sms') {
			//elimina tutto ciò che non è numerico
			$str=preg_replace("/[^0-9.]/", "", $str);
		}
		
		return $str;
	}
	
	function invio($l) {
	
		$contatto=$this->trim_contatto($l['contatto']);
		
		$d_invio=$l['d_invio'];
		$note='';
		
		if ($contatto=='') {
			$d_invio='-1';
		}
		
		//invio SMS
		if ($d_invio!='-1') {
		
			//$this->log[]=$contatto;
		
			$d_invio=date('Ymd');
		
			$this->form_data['dest']=$contatto;
			
			$this->form_data['testo']=utf8_decode($this->form_data['testo']);
			
			
			//url-ify the data for the POST
			foreach($this->form_data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
			rtrim($fields_string, '&');
			
			//open connection
			$ch = curl_init();
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_ENCODING ,"ISO-8859-1");
			curl_setopt($ch,CURLOPT_POST, count($this->form_data));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			//execute post
			$result = curl_exec($ch);
			
			///////////////////////////////////////////////////////
			//verifica RESULT
			
			$matchCount = preg_match_all('/OK/',$result,$matches);
			if ($matchCount<1) {
				$d_invio='-2';
				$note=$result;
			}
			
			//close connection
			curl_close($ch);
		}
		
		///////////////////////////////////////////////////////
		//scrittura GN_GAB_CHIME_storico
		$query="DELETE GN_GAB_CHIME_storico WHERE rif='".$l['rif']."' AND contatto='".$l['contatto']."' AND giorno='".$this->giorno."' AND azione='".$this->azione['ID']."'";
		
		$stmt=$this->maestro->chime_query($query);
		if ($stmt) {
			$query="INSERT INTO GN_GAB_CHIME_storico (ID,rif,contatto,azione,giorno,d_invio,note)
					VALUES ((SELECT isnull(max(ID),0)+1 FROM GN_GAB_CHIME_storico),'".$l['rif']."','".$l['contatto']."','".$this->azione['ID']."','".$this->giorno."','".$d_invio."','".$note."')
					";
			$stmt=$this->maestro->chime_query($query);
		}
	}
	
	function avv_app() {
	
		/*AZIONE
		array(
			"ID"=>1,
			"reparto"=>"VWS",
			"descrizione"=>"avviso appuntamento",
			"delta_i"=>"+1",
			"delta_f"=>"+3",
			"campi"=>"avv_app",
			"tipo_contatto"=>"sms",
			"qry"=>"avv_app",
			"send"=>"avv_app",
			"sostituzioni"=>"ID,chime_concerto,chime_giorno",
			"stato"=>1
		)*/
		
		/*LISTA
		
		*/
		
		/*RECORD GN_GAB_CHIME_storico
		"rif"=>'1134573',				preso da LISTA
		"contatto"=>'3331323292'		preso da LISTA
		
		"azione"=>1						preso da $this->azione[ID]		
		"giorno"=>'20190321'			preso da $this->giorno (giorno in cui l'appuntamento è schedulato in agenda)
		
		"d_invio"=>'-1'					preso da LISTA e ricalcolato dalla classe
		"note"=>''						calcolato dalla classe (errore di spedizione)

		*/
		
		//DATA INVIO = -1 => escluso
		//DATA INVIO = -2 => errore invio
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		foreach ($this->lista as $l) {
		
			$saluto="Buongiorno, ";
			
			$ora=(int)date('Hi');
			
			if ($ora>1530) $saluto='Buonasera, ';
		
			$this->form_data['testo']=$saluto.'le ricordiamo l\'appuntamento di '.substr($this->sett[$this->wd],0,3).' '.db_todata($this->giorno).' per la vettura '.$l['targa'].', grazie.';

			//avviso VWS
			if ((int)$this->azione['ID']==1) {
				$this->form_data['testo'].=' Scopra il nostro servizio di igienizzazione: https://bit.ly/IgienizGabel';
			}

		
			$this->invio($l);
			
		}
	}
	
	function feedback_pass() {
				
		foreach ($this->lista as $l) {
		
			$this->form_data['testo']='Grazie per averci scelto. La fiducia dei nostri clienti ci sprona a dare sempre un servizio migliore.';
		
			$this->invio($l);
			
		}
	}
	
}

?>