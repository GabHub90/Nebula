<?php
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/sms/sms_class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/apps/avalon/classi/wormhole.php");

class nebulaChime {

    protected $reparto="";
    protected $pren="";
    protected $modello="";

    protected $modelli=array();

    protected $lista=array();

    protected $sms=false;

    /*protected $callback=array(
        "class"=>'_nebulaApp',
        "func"=>'ribbonExecute',
        "args"=>array()
    );*/

    protected $wh;
    protected $odlFunc;
    protected $galileo;

    function __construct($reparto,$pren,$galileo) {

        $this->reparto=$reparto;
        $this->pren=$pren;
        $this->galileo=$galileo;

        //carica i modelli che corrispondono alle impostazioni

        //TEST
        $row=array(
            array(
                "ID"=>"1",
                "descrizione"=>"SMS avviso appuntamento standard",
                "stato"=>1,
                "reparto"=>"VWS",
                "pren"=>"S",
                "contatto"=>"SMS",
                "modello"=>"1",
                "default"=>1,
                "messaggio"=>"[saluto], le ricordiamo l'appuntamento in officina per il [giorno] alle [ora] [targa]. Scopra in ricezione le offerte per i nuovi pacchetti manutenzione!",
                "mittente"=>"Gabellini"
            ),
            array(
                "ID"=>"2",
                "descrizione"=>"SMS ringraziamento",
                "stato"=>1,
                "reparto"=>"VWS",
                "pren"=>"TG",
                "contatto"=>"SMS",
                "modello"=>"1",
                "default"=>1,
                "messaggio"=>"[saluto], nella speranza di averle offerto un servizio da 5 stelle la ringraziamo per averci scelto!",
                "mittente"=>"Gabellini"
            )
        );

        foreach ($row as $k=>$r) {
            if ($r['pren']==$this->pren) $this->modelli[$r['ID']]=$r;
        }
        //ENDTEST

        $this->odlFunc=new nebulaOdlFunc($this->galileo);

        $this->wh=new avalonWHole($reparto,$this->galileo);
    }

    function getLista($day,$pratica,$dms,$ID) {
        if ($this->pren=='S') $this->getListaPrenotazioni($day,$pratica,$dms,$ID);
        if ($this->pren=='TG') $this->getListaPassaggi($day,$pratica,$dms,$ID);
    }

    function setLista($p,$row,$m) {

        if (!array_key_exists($p,$this->lista)) {
            $this->lista[$p]=array(
                "info"=>$row,
                "pratica"=>new nebulaPraticaFunc($row['pratica'],'0',$m['dms'],'S',$this->odlFunc),
                "chime"=>array(
                    "intest"=>array(
                        "mail"=>array(
                            "valore"=>""
                        ),
                        "tel1"=>array(
                            "valore"=>""
                        ),
                        "tel2"=>array(
                            "valore"=>""
                        ),
                        "selected"=>"",
                        "check"=>0
                    ),
                    "util"=>array(
                        "mail"=>array(
                            "valore"=>""
                        ),
                        "tel1"=>array(
                            "valore"=>""
                        ),
                        "tel2"=>array(
                            "valore"=>""
                        ),
                        "selected"=>"",
                        "check"=>0
                    ),
                    "stato"=>"disabled",
                    "messaggi"=>false,
                    "msg"=>""
                )
            );

            $this->lista[$p]['pratica']->setDefaultAlert();

            $this->lista[$p]['info']['des_riga']=ucfirst(strtolower($this->lista[$p]['info']['des_riga']));
        }
        else $this->lista[$p]['info']['des_riga'].=' - '.ucfirst(strtolower($row['des_riga']));
    }

    function getListaPrenotazioni($day,$pratica,$dms,$ID) {

        if (!array_key_exists($ID,$this->modelli)) die ("Modello messaggio non esistente!!!");

        $this->modello=$ID;

        if ($pratica=='') {

            $this->wh->getPrenotazioni($day,$day,$this->odlFunc->getDmsRep($dms,$this->reparto));

        }

        foreach ($this->wh->exportMap() as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    if ($row['cod_stato_commessa']=='CH') continue;
                    if (!$tempstato=$this->odlFunc->getStatoOdl($row['cod_stato_commessa'],$m['dms'])) continue;

                    if ($tempstato['codice']=='AP') continue;

                    $p=base64_encode($row['pratica']);

                    $this->setLista($p,$row,$m);

                    //###############################
                    //definizione CHIME in base al tipo di messaggio

                    $this->execLista($p,$row,$ID);
                }
            }
        }
    }

    function getListaPassaggi($day,$pratica,$dms,$ID) {

        if (!array_key_exists($ID,$this->modelli)) die ("Modello messaggio non esistente!!!");

        $this->modello=$ID;

        if ($pratica=='') {

            $this->wh->getFatture($day,$day,$this->odlFunc->getDmsRep($dms,$this->reparto));

        }

        foreach ($this->wh->exportMap() as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    $p=base64_encode($row['pratica']);

                    if (array_key_exists($p,$this->lista)) continue;

                    $row['addebito']=$this->odlFunc->getAddebito($row,$m['dms']);

                    $row['d_pren']=$row['d_fatt'];

                    $this->setLista($p,$row,$m);

                    //###############################
                    //definizione CHIME in base al tipo di messaggio

                    $this->execLista($p,$row,$ID);
                }
            }
        }

    }

    function execLista($p,$row,$ID) {

        if ($this->modelli[$ID]['contatto']=='SMS') {

            if ($row['util_tel']!="") {
                $temp=explode('-',$row['util_tel']);
                foreach ($temp as $k=>$t) {
                    if (substr($t,0,1)=='+') $t=substr($t,3);
                    if (substr($t,0,1)!='0' && strlen($t)>3) {
                        if ($this->lista[$p]['chime']['util']['tel1']['valore']=="") {
                            $this->lista[$p]['chime']['util']['tel1']['valore']=$t;
                            $this->lista[$p]['chime']['util']['check']=1;
                            $this->lista[$p]['chime']['util']['selected']=$t;
                            $this->lista[$p]['chime']['stato']='enabled';
                        }
                        elseif ($t!=$this->lista[$p]['chime']['util']['tel1']['valore']) {
                            $this->lista[$p]['chime']['util']['tel2']['valore']=$t;
                            break;
                        }
                    }
                }
            }

            if ($row['intest_tel']!="") {
                $temp=explode('-',$row['intest_tel']);
                foreach ($temp as $k=>$t) {
                    if (substr($t,0,1)=='+') $t=substr($t,3);
                    if (substr($t,0,1)!='0' && strlen($t)>3) {
                        if ($this->lista[$p]['chime']['intest']['tel1']['valore']=="" && $t!=$this->lista[$p]['chime']['util']['selected']) {
                            $this->lista[$p]['chime']['intest']['tel1']['valore']=$t;
                            $this->lista[$p]['chime']['intest']['check']=1;
                            $this->lista[$p]['chime']['intest']['selected']=$t;
                            $this->lista[$p]['chime']['stato']='enabled';
                        }
                        elseif ($t!=$this->lista[$p]['chime']['intest']['tel1']['valore'] && $t!=$this->lista[$p]['chime']['util']['selected']) {
                            $this->lista[$p]['chime']['intest']['tel2']['valore']=$t;
                            break;
                        }
                    }
                }
            }
        }

        //###############################
        //MAIL
        //###############################

        //##############################
        //esistenza di messaggi già inviati
        $temp=$this->lista[$p]['pratica']->getChimeApp($row['rif']);
        if ($temp) {
            $this->lista[$p]['chime']['messaggi']=$temp;
        }
        //##############################

        //se ci sono dei messaggi inviati ed è possibile inviarli cambia stato
        if ($this->lista[$p]['chime']['messaggi'] && $this->lista[$p]['chime']['stato']!='disabled') $this->lista[$p]['chime']['stato']='inviato';

        //##############################
        if ($this->lista[$p]['chime']['stato']=='disabled') $this->lista[$p]['chime']['msg']="Messaggio non inviabile";

        else $this->lista[$p]['chime']['msg']=$this->buildMsgApp($row,$ID);

    } 

    static function drawJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chime/code.js" /></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaChime=new window.nebulaChimeClass();';
            echo 'window._nebulaChime.exec=function(){this.busy=false;window._nebulaApp.ribbonExecute();};';
        echo '</script>';
    }

    function buildMsgApp($row,$ID) {

        $std=$this->modelli[$ID]['messaggio'];

        $min=mainFunc::gab_stringtomin(date("H:i"));
        if ($min>960) $std=str_replace('[saluto]','Buonasera',$std);
        else $std=str_replace('[saluto]','Buongiorno',$std);

        $d=substr($row['d_pren'],0,8);
        $std=str_replace('[giorno]',mainFunc::gab_todata($d),$std);

        $std=str_replace('[ora]',substr($row['d_pren'],9,5),$std);

        if ($row['mat_targa']!='') {
            $std=str_replace('[targa]','della '.$row['mat_targa'],$std);
        }

        return $std;
    }

    function drawHead($day,$pratica,$dms) {
        //"day" è il giorno di estrazione e "pratica" il riferimento alla singola pratica
        //possono essere "" in caso non siano corrispondenti al tipo di azione 

        if ($this->pren=="") return;

        echo '<div style="position:relative;width:100%;height:8%;">';

            echo '<div style="position:relative;display:inline-block;width:45%;vertical-align:top;font-size:1.1em;font-weight:bold;" >';
                if ($this->pren=='S') {
                    echo 'Messaggi per prenotazioni reparto "'.$this->reparto.'"';
                }
                if ($this->pren=='TG') {
                    echo 'Messaggi ringraziamento passaggio "'.$this->reparto.'"';
                }
                if ($this->pren=='N') {
                    echo 'Messaggi per commesse reparto "'.$this->reparto.'"';
                }
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;" >';
                echo '<select id="nebulaChime_tipoLista" style="width:95%;">';
                    foreach ($this->modelli as $k=>$m) {

                        if ($this->pren!=$m['pren']) continue;

                        echo '<option value="'.$m['ID'].'" ';
                            if ($m['default']==1) echo 'selected';
                        echo '>'.$m['descrizione'].'</option>';
                    }
                echo '</select>'; 
            echo '</div>';
            
            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<button data-reparto="'.$this->reparto.'" data-pren="'.$this->pren.'" data-day="'.$day.'" data-pratica="'.$pratica.'" data-dms="'.$dms.'" onclick="window._nebulaChime.cerca(this);" >Cerca</button>';
            echo '</div>';

        echo '</div>';

        echo '<div id="nebulaChime_body" style="position:relative;width:100%;height:92%;overflow:scroll;overflow-x:hidden;">';
        echo '</div>';
    }

    function drawLista() {

        //echo '<div>'.json_encode($this->lista).'</div>';

        foreach ($this->lista as $p=>$l) {

            echo '<div style="position:relative;border:2px solid black;padding:3px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;width:97%;">';

                echo '<div style="position:relative;margin-bottom:5px;">';
                    //riga di abilitazione e controllo CHIME
                    $color='#777777';
                    $src='http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_G.png';

                    if ($l['chime']['stato']=='disabled') $color='red';
                    
                    if ($l['chime']['stato']=='enabled' || $l['chime']['stato']=='inviato') {
                        $color='green';
                        if ($l['chime']['stato']=='enabled' || (isset($l['chime']['messaggi']['result']) && $l['chime']['messaggi']['result']!=true) ) $src='http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_V.png';
                        else $src='http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_G.png';
                    }

                    echo '<div style="position:relative;border:2px solid '.$color.';width:100%;padding:2px;box-sizing:border-box;min-height:25px;" >';

                        if ($l['chime']['messaggi']) {

                            echo '<div style="position:relative;margin-bottom:8px;font-size:0.9em;color:#777777;">';
                                
                                    //scrittura del messaggio precedentemente inviato
                                    /*{"modello":"1","msg":"Buonasera, le ricordiamo l'appuntamento in officina per il 27\/10\/2022 alle 07:45, a presto!","contatto":"3476131719","stato":"escluso","dataora":"20221026:16:26","result":"escluso"}*/
                                    //echo json_encode($l['chime']['messaggi']);
                                    echo '<span style="font-weight:bold;font-size:0.9em;">'.mainFunc::gab_todata(substr($l['chime']['messaggi']['dataora'],0,8)).' </span>';
                                    echo '<span style="font-weight:bold;font-size:0.9em;'.(( $l['chime']['messaggi']['stato']=='escluso' || $l['chime']['messaggi']['result']!=true)?'color:red;':($l['chime']['messaggi']['stato']=='enabled'?'color:green;':'')).'">['.($l['chime']['messaggi']['result']!=true?'ERROR':$l['chime']['messaggi']['stato']).'] </span>';
                                    echo $l['chime']['messaggi']['msg'];

                            echo '</div>';
                        }

                        echo '<div style="position:relative;" >';

                            echo '<div id="chime_msg_'.$p.'" style="position:relative;display:inline-block;width:95%;vertical-align:top;color:'.$color.';" data-pratica="'.$p.'" >';
                                echo $l['chime']['msg'];
                            echo '</div>';

                            echo '<div style="position:relative;display:inline-block;width:5%;vertical-align:top;" >';
                                if ($l['chime']['stato']!='disabled') {
                                    echo '<img id="chime_availIcon_'.$p.'" style="width:20px;height:20px;margin-top:-2px;cursor:pointer;" data-modello="'.$this->modello.'" data-dms="'.$l['info']['dms'].'" data-pratica="'.$p.'" data-rif="'.$l['info']['rif'].'" data-stato="'.$l['pratica']->getStatoCC($l['info']['rif']).'" data-pren="'.$this->pren.'" data-orig="'.$l['chime']['stato'].'" data-eff="'.$l['chime']['stato'].'" src="'.$src.'" onclick="window._nebulaChime.setAvail(this);" />';
                                }
                            echo '</div>';

                        echo '</div>';

                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;">';
                    
                    echo '<div style="position:relative;">';
                        echo '<div style="position:relative;display:inline-block;width:12%;vertical-align:top;font-weight:bold;">'.$l['info']['mat_targa'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;font-weight:bold;">'.$l['info']['mat_telaio'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:63%;vertical-align:top;font-size:0.9em;">'.utf8_encode($l['info']['des_veicolo']).'</div>';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;">';

                    echo '<div style="position:relative;font-size:1em;">';
                        if (isset($l['info']['addebito'])) echo '<div style="position:relative;width:100%;vertical-align:top;font-weight:bold;color:steelblue;">'.$l['info']['addebito']['tag'].'</div>';
                        //echo '<div style="position:relative;display:inline-block;width:100%;vertical-align:top;font-weight:bold;color:chocolate;">'.utf8_encode($l['info']['des_riga']).'</div>';
                        echo '<div style="position:relative;width:100%;vertical-align:top;font-weight:bold;color:chocolate;">'.utf8_encode($l['info']['des_riga']).'</div>';
                    echo '</div>';

                echo '</div>';

                if ($l['info']['intest_ragsoc']!="") {

                    echo '<div style="position:relative;width:98%;border:1px solid #777777;padding:2px;box-sizing:border-box;margin-top:3px;margin-bottom:3px;';
                        if ($l['chime']['intest']['check']==0) echo 'background-color:#dddddd;';
                    echo '">';

                        echo '<div style="position:relative;font-size:0.7em;font-weight:bold;" >Intestatario:</div>';
                        echo '<div style="position:relative;display:inline-block;width:100%;vertical-align:top;">'.$l['info']['intest_ragsoc'].'</div>';

                        echo '<div style="position:relative;">';
                            echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;">';
                                if ($l['chime']['intest']['mail']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['intest']['mail']['valore']==$this->lista[$p]['chime']['intest']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['intest']['mail']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['intest']['mail']['valore'].'</span>';
                                }
                            echo '</div>';
                            echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;">';
                                if ($l['chime']['intest']['tel1']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['intest']['tel1']['valore']==$this->lista[$p]['chime']['intest']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['intest']['tel1']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['intest']['tel1']['valore'].'</span>';
                                }
                            echo '</div>';
                            echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;">';
                                if ($l['chime']['intest']['tel2']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['intest']['tel2']['valore']==$this->lista[$p]['chime']['intest']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['intest']['tel2']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['intest']['tel2']['valore'].'</span>';
                                }
                            echo '</div>';
                        echo '</div>';

                    echo '</div>';

                }

                if ($l['info']['util_ragsoc']!="") {

                    echo '<div style="position:relative;width:98%;border:1px solid #777777;padding:2px;box-sizing:border-box;margin-top:3px;margin-bottom:3px;';
                        if ($l['chime']['util']['check']==0) echo 'background-color:#dddddd;';
                    echo '">';

                        echo '<div style="position:relative;font-size:0.7em;font-weight:bold;" >Utilizzatore:</div>';
                        echo '<div style="position:relative;display:inline-block;width:100%;vertical-align:top;">'.$l['info']['util_ragsoc'].'</div>';

                        echo '<div style="position:relative;">';
                            echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;">';
                                if ($l['chime']['util']['mail']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['util']['mail']['valore']==$this->lista[$p]['chime']['util']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['util']['mail']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['util']['mail']['valore'].'</span>';
                                }
                            echo '</div>';
                            echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;">';
                                if ($l['chime']['util']['tel1']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['util']['tel1']['valore']==$this->lista[$p]['chime']['util']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['util']['tel1']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['util']['tel1']['valore'].'</span>';
                                }
                            echo '</div>';
                            echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;">';
                                if ($l['chime']['util']['tel2']['valore']!="") {
                                    echo '<input name="chime_contatto_'.$p.'" type="radio" ';
                                        if ($l['chime']['util']['tel2']['valore']==$this->lista[$p]['chime']['util']['selected']) echo 'checked';
                                    echo ' value="'.$l['chime']['util']['tel2']['valore'].'" />';
                                    echo '<span style="margin-left:5px;">'.$l['chime']['util']['tel2']['valore'].'</span>';
                                }
                            echo '</div>';
                        echo '</div>';

                    echo '</div>';

                }

            echo '</div>';
        }

        echo '<div style="position:relative;border:2px solid black;padding:3px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;width:97%;text-align:center;">';
            echo '<button id="chime_button" style="height:40px;text-align:center;width:300px;" onclick="window._nebulaChime.invia(\''.$this->reparto.'\',\''.$this->pren.'\');" >INVIA</button>';
        echo '</div>';

    }

    function send($lista) {

        foreach ($lista as $l) {

            /*{
                "pratica":$(this).data('pratica'),
                "dms":$(this).data('dms'),
                "rif":$(this).data('rif'),
                "stato":$(this).data('stato'),
                "pren":$(this).data('pren'),
                "chime":{
                    "modello":$(this).data('modello'),
                    "msg":$('div[id^="chime_msg_"][data-pratica="'+$(this).data('pratica')+'"]').html(),
                    "contatto":$('input[name="chime_contatto_'+$(this).data('pratica')+'"]:checked').val(),
                    "stato":$(this).data('eff')
                }
            */

            if (!array_key_exists($l['chime']['modello'],$this->modelli)) continue;
            
            $l['pratica']=base64_decode($l['pratica']);
            $l["chime"]['dataora']=date('Ymd:H:i');
            $l["chime"]['result']="";

            if ($l["chime"]['stato']=='escluso') {
                $l["chime"]['result']="escluso";
            }
            else {
                if ($this->modelli[$l['chime']['modello']]['contatto']=='SMS') {

                    if (!$this->sms) {
                        $this->sms=new nebulaSms();
                    }
                    $l["chime"]['result']=$this->sendSMS($l['chime']);
                }
            }

            //############################
            //aggiornamento ALERT - AVALON_stato_lam
            $this->wh->setChimeApp($l);
            //############################
        }

    }

    function sendSMS($chime) {

        $config=$this->sms->getConfig();

        $form_data = array('login' => $config['login'], 'password' => $config['password'], 'tipo' => $config['tipo'], 'dest' => $chime['contatto'], 'testo' => $chime['msg'], 'mitt' => $this->modelli[$chime['modello']]['mittente'], 'status' => $config['status']);
	    
        $risultato = $this->sms->send($form_data);

        $matchCount = preg_match_all('/OK/',$risultato,$matches);
		if ($matchCount>1) {
			return 'OK';
		}
		else return $risultato;

        //echo json_encode($form_data);
        //echo $risultato;
        
        //return 'prova';

    }

}
?>