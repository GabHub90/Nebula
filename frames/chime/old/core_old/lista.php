<?php
include("../chime_ini.php");
require("../chime_func.php");

//connessine al database
$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

//leggi i dati passati in POST
$param=(array) json_decode($_POST[param]);
$report=(array) json_decode($param[report]);
$input=(array) json_decode($param[form_input]);
$data=$input[form_data];

//parametri selezionati passati dalla QUERY utili per inizializzare una lista NUOVA
$sms=$param[sms];
$address=$param[mail];
$programma=$param[link];

//trova gli sms
if ($report[sms]==1) {
	$query='SELECT t2.ID, t2.testo, t2.tag, t1.def FROM rep_sms AS t1 INNER JOIN sms AS t2 ON t1.sms=t2.ID WHERE t1.report='.$report[rep_id].' AND t2.attivo=1';
	if($result=mysqli_query($query)) {
		while ($row=mysqli_fetch_assoc($result)) {
			$sms_a[$row[ID]]=$row;
		}
	}
}

//trova le mail
if ($report[mail]==1) {
	$query='SELECT t2.ID, t2.codice, t2.tag, t2.descrizione, t1.def FROM rep_mail AS t1 INNER JOIN mail AS t2 ON t1.mail=t2.ID WHERE t1.report='.$report[rep_id].' AND t2.attivo=1';
	if($result=mysqli_query($query)) {
		while ($row=mysqli_fetch_assoc($result)) {
			$mail_a[$row[ID]]=$row;
		}
	}
}

//trova i link
if ($report[link]==1) {
	$query='SELECT t2.ID, t2.tag, t2.descrizione, t1.def FROM rep_link AS t1 INNER JOIN link AS t2 ON t1.link=t2.ID WHERE t1.report='.$report[rep_id].' AND t2.attivo=1';
	if($result=mysqli_query($query)) {
		while ($row=mysqli_fetch_assoc($result)) {
			$link_a[$row[ID]]=$row;
		}
	}
}

// il MODO di visualizzazione determina i comandi applicabili STORICO=solo visualizzazione , ATTUALE=possibile riaprire in quanto in una data valida , NUOVO=nessuno storico esistente
$modo=$param[modo];

//include l'oggetto specifico e lo inizializza nella variabile $LISTA
//print_r($report);

include("reports/".$report[inc].'_class.php');

//ricevi le chiavi dell'array degli elementi (NUM_EL è un ARRAY)
$num_el=$lista->numero_elementi();
$xnum=count($num_el);

//COMANDI
//visualizzazione comandi in funzione del MODO: STORICO=solo visualizzazione , ATTUALE=possibile re-inviare gli elementi in ERROR in quanto in una data valida , NUOVO=nessuno storico esistente
echo '<div class="div_comandi">';
	//BOTTONI
	echo '<div style="position:relative;height:30px;">';
		echo '<button id="but_annulla" style="position:absolute;left:20px;top:5px;" onclick="annulla();" ';
		if ($modo!="nuovo") echo 'disabled="disabled" ';
		echo '>Annulla</button>';
		
		echo '<button id="but_reset" style="position:absolute;left:100px;top:5px;" onclick="reset();" ';
		if ($modo!="attuale") echo 'disabled="disabled" ';
		echo '>Reset</button>';
		
		echo '<button id="but_salva" style="position:absolute;left:170px;top:5px;" onclick="salva();" ';
		if ($modo=="storico" || $xnum==0 || $param[stato_indice]=="done") echo 'disabled="disabled" ';
		echo '>Salva</button>';
		
		echo '<button style="position:absolute;left:100%;margin-left:-60px;top:5px;background-color:red;" onclick="invia();" ';
		if ($modo=="storico" || $xnum==0) echo 'disabled="disabled" ';
		echo '>INVIA</button>';
		
	echo '</div>';
	
	//DIV MESSAGGI
	echo '<div id="messaggi_lista" class="messaggi">';
	echo '</div>';
echo '</div>';

//scrivi intestazione lista
echo '<div class="div_intest">';
	$lista->intest();
echo "</div>";

//ELEMENTI
echo '<div class="div_elementi">';
	foreach ($num_el as $chiave) {
	
		//I campi dei dati sono: CHK_EL_ , CHK_SMS_ , CHK_MAIL_ , CHK_LINK_ , PHONE_EL_ , MAIL_EL_
	
		$ok=0;
		$mail="";
		//leggi i riferimenti ed abilita i checkbox
		if ($report[sms]==1) {
			//legge l'array dei telefoni dell'elemento e ritorna l'array con i telefoni validati
			$request=$lista->telefoni($chiave);
			$telefoni=validate_cell($request);
			//se non ci sono telefoni validi allora $ok=0
			$ok=count($telefoni);
		}
		if ($report[mail]==1) {
			//legge la mail dell'elemento e ritorna la mail validata
			$request=$lista->mail($chiave);
			$mail=validate_mail($request);
			//se la mail è valida setta $ok
			if ($mail!="") $ok=1;
		}
		//LINK è sempre valido
		if ($report[link]==1) $ok=1;
		
		//prendi la stringa unica per l'identificazione
		$chsum="".$lista->chsum($chiave);
		
		//identifica il CHSUM per ritrovare l'oggetto
		//echo '<input type="hidden" id="el_"'.$chiave.'" value="'.$chsum.'" />';
		
		//CREA ELEMENTO JS E LO ATRIBUISCE ALL'ARRAY _ELEMENTI DEFINITO IN INDEX.PHP
		echo '<script> var x'.$chsum.'= new elemento("'.$chsum.'"); insert_elemento(x'.$chsum.');</script>';
		
		echo '<div id="div_el_'.$chsum.'" class="lista_elemento">';
		
			//scivi i dati dell'elemento
			echo '<div style="position:relative;height:auto;">';
				if($ok!=0) echo '<input type="checkbox" id="chk_el_'.$chsum.'" style="position:absolute;top:0px;left:8px;" checked="checked" onclick="chg_element(this,\'el_'.$chsum.'\');"/>';
				else echo '<input type="checkbox" id="chk_el_'.$chsum.'" style="position:absolute;top:0px;left:8px;" disabled="disabled"/>';
				
				echo '<div style="position:relative;width:560px;top:0px;left:30px;">';
					//passa l'informazione se l'elemento è abilitato o meno
					$lista->d_elemento($chiave,$ok);
				echo '</div>';
			echo '</div>';	
				
			//scrivi le opzioni di contatto dell'elemento
			
			//SMS
			if ($report[sms]==1) {
			
				echo '<div id="div_sms_'.$chsum.'" class="div_sms">'; 
					echo '<div style="position:relative;">';
						$x=count($telefoni);
						if ($x!=0) echo '<input type="checkbox" id="chk_sms_'.$chsum.'" checked="checked" onclick="chg_media(this,\'sms_'.$chsum.'\');"/>';
						else echo '<input type="checkbox" id="chk_sms_'.$chsum.'" disabled="disabled" />';
						
						echo '<b style="position:relative;left:5px;">SMS:</b>';
						echo '<select id="sel_sms_'.$chsum.'" style="position:absolute;left:80px;">';
							foreach ($sms_a as $sms_b) {
								echo '<option value="'.$sms_b[ID].'" ';
								if ($sms_b[ID]==$sms) echo 'selected="selected"';
								echo '>'.$sms_b[tag].'</option>';
							}
						echo '</select>';
						
						if ($x==1) {
							echo '<span class="media">'.$telefoni[0].'</span>';
							//registra il telefono valido					
							echo '<input type="hidden" id="phone_el_'.$chsum.'" value="'.$telefoni[0].'" />';
						}
						if ($x==2) {
							echo '<span class="media">'.$telefoni[1].'</span>';
							//registra il secondo telefono (quello registrato come mobile in CONCERTO)
							echo '<input type="hidden" id="phone_el_'.$chsum.'" value="'.$telefoni[1].'" />';		
						}
						
						//immagine report spedizione
						if ($x!=0) echo '<img id="report_sms_'.$chsum.'" class="report_img" src="img/phone.png" />';
						else echo '<img id="report_sms_'.$chsum.'" class="report_img" src="img/stop.png" />';
						
					echo '</div>';
				
				echo '</div>';
			}
			
			//MAIL
			if ($report[mail]==1) {
			
				echo '<div id="div_mail_'.$chsum.'" class="div_sms">'; 
						echo '<div style="position:relative;">';
							if ($mail!="") echo '<input type="checkbox" id="chk_mail_'.$chsum.'" checked="checked" onclick="chg_media(this,\'mail_'.$chsum.'\');"/>';
							else echo '<input type="checkbox" id="chk_mail_'.$chsum.'" disabled="disabled" />';
							
							echo '<b style="position:relative;left:5px;">MAIL:</b>';
							echo '<select id="sel_mail_'.$chsum.'" style="position:absolute;left:80px;">';
								foreach ($mail_a as $mail_b) {
									echo '<option value="'.$mail_b[ID].'" ';
									if ($mail_b[ID]==$address) echo 'selected="selected"';
									echo '>'.$mail_b[tag].'</option>';
								}
							echo '</select>';
							
							echo '<span class="media">'.substr($mail,0,35).'</span>';
							//registra la mail valida					
							echo '<input type="hidden" id="mail_el_'.$chsum.'" value="'.$mail.'" />';
							
							//immagine report spedizione
							if ($mail!="") echo '<img id="report_mail_'.$chsum.'" class="report_img" src="img/mail.png" />';
							else echo '<img id="report_mail_'.$chsum.'" class="report_img" src="img/stop.png" />';
							
						echo '</div>';
					
					echo '</div>';
				}
			
			//LINK
			if ($report[link]==1) {
			
				echo '<div id="div_link_'.$chsum.'" class="div_sms">'; 
						echo '<div style="position:relative;">';
							echo '<input type="checkbox" id="chk_link_'.$chsum.'" checked="checked" onclick="chg_media(this,\'link_'.$chsum.'\');"/>';
							
							echo '<b style="position:relative;left:5px;">LINK:</b>';
							echo '<select id="sel_link_'.$chsum.'" style="position:absolute;left:80px;">';
								foreach ($link_a as $link_b) {
									echo '<option value="'.$link_b[ID].'" ';
									if ($link_b[ID]==$programma) echo 'selected="selected"';
									echo '>'.$link_b[tag].'</option>';
								}
							echo '</select>';
							
							echo '<span class="media"></span>';
							//registra il link valido					
							echo '<input type="hidden" id="link_el_'.$chsum.'" value="'.$programma.'" />';
							
							//immagine report spedizione
							if ($programma!=0) echo '<img id="report_link_'.$chsum.'" class="report_img" src="img/link.png" />';
							else echo '<img id="report_link_'.$chsum.'" class="report_img" src="img/stop.png" />';
							
						echo '</div>';
					
					echo '</div>';
				}
	
		echo '</div>';
	}

echo "</div>";

//CODICE PER LA VALORIZZAZIONE DEGLI ELEMENTI
if ($modo!="nuovo" && $report[storico]>0) {

	//recupera l'ID dell'INDICE
	$query='SELECT ID FROM indici WHERE report='.$report[rep_id].' AND tag="'.$data.'"';
	if ($result=mysqli_query($query)) {
		if($row=mysqli_fetch_array($result)) {
			$id_indice=$row[ID];
		}
	}
	
	//recupera gli elementi
	$query='SELECT elementi FROM storico WHERE indice='.$id_indice;
	if ($result=mysqli_query($query)) {
		if($row=mysqli_fetch_array($result)) {
			$elementi=$row[elementi];
		}
	}
	
	//CODICE JS
	echo '<script type="text/javascript">';
		echo 'var elementi='.$elementi.';';
		//echo 'alert (JSON.stringify(elementi));';
		echo '$.each(elementi,function(key,val){fill_lista(val);});';
	echo '</script>';

}

mysqli_close($db_handler);
?>