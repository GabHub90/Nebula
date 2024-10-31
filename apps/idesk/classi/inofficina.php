<?php
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/blocklist/blocklist.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/timb_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");
require_once("wormhole.php");

class ideskInofficina {

    protected $param=array();

    protected $pratiche=array();
    protected $sospeso="";
    protected $esterno="";
    protected $ricambio="";
    protected $pronto="";
    protected $timeline=array();

    protected $search=false;


    protected $odlFlag=false;

    protected $defMarche=array('A','C','N','S','V','P');

    //raccoglie le marche presenti nelle pratiche estratte
    protected $marche=array();

    protected $marcatureAperte=array();

    protected $wh;
    protected $odlFunc;
    protected $timbFunc;
    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        /*
            "reparto"
            "visuale"
            "rc"
            "inizio"
            "fine"
        */

        $this->param=$param;
        $this->galileo=$galileo;

        //se VISUALE=='rep' -> prendi tutte le pratiche aperte del reparto
        //se VISUALE=='rc' -> prendi TUTTI gli ordini aperti con l'id dell'RC
        //??????? ipotizzare tutti gli ordini di tutti i reparti per l'rc ?????????

        $this->odlFunc=new nebulaOdlFunc($this->galileo);
        $this->timbFunc=new nebulaTimbFunc($this->galileo);
        $this->wh=new ideskWHole($param['reparto'],$this->galileo);

        $this->param['cliente']=(isset($this->param['cliente']) && $this->param['cliente']!="")?$this->param['cliente']:'cliente';

        if (!isset($param['timeless']) || !$param['timeless']) {

            if (!isset($this->param['officina'])) {

                $a=array(
                    "inizio"=>$this->param['inizio'],
                    "fine"=>$this->param['fine']
                );
        
                $this->wh->build($a);

                $this->param['officina']=$this->odlFunc->getDmsRep($this->wh->getTodayDms($this->param['inizio']),$this->param['reparto']);

                //ricalcolare INIZIO in base alla data di inizio del periodo DMS del reparto
                $this->param['inizio']=$this->wh->getInizioDms($this->param['inizio']);
            }

            if ($this->param['cliente']=='apprip') $this->param['timeless']=true;
        }

        //////////////////////////////////////////////////
        //si chiama marcature aperte ma estrae le ULTIME MARCATURE
        $this->timbFunc->calcolaMarcatureAperte();
        
        foreach ($this->timbFunc->getMarcature() as $tecnico=>$m) {

            if ($m['o_fine']=='') {
                $this->marcatureAperte[$m['num_rif_movimento'].'_'.$m['cod_inconveniente'].'_'.$m['dms']]=true;
            }
        }

    }

    function setSearch($x) {
        //true / false
        $this->search=$x;
    }

    function getlines() {
        //raccoglie i lamentati degli ODL aperti ordinati per PRATICA, COMMESSA, LAMENTATO
        $this->wh->getInofficina($this->param['inizio'],$this->param['fine'],$this->param['officina'],($this->param['visuale']=='rc')?$this->param['rc']:'',$this->param['cliente']);

        $this->evalLines();
    }

    function evalLines() {

        foreach ($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid) ) {

                    /*{"dat_inserimento":"20220816:14:52","cod_officina":"PV","cod_movimento":"OOP","acarico":"OCO","cg":"","rif":15131,"pratica":"2022-08-16 14:52:29.473","ordine_lavoro":"L110548\/2022","ind_preventivo":"N","ind_chiuso":"N",
                    "lam":1,"des_riga":"iISPEZIONE CON CAMBIO OLIO pacman Data scadenza contratto:\t19\/10\/2023   Km contratto: 38834 NO AZIONI DIGITALE ISPEZIONE OLIO E FILTRO OLIO COMPRESI",
                    "note_d":"","note_p":"","cod_pacchetto":null,"d_ricon":"20220816:17:30","d_entrata":"20220816:14:52","d_fine":"xxxxxxxx:xx:xx","ore":"2.00","subrep":"PVMECC","d_inc":"xxxxxxxx:xx:xx",
                    "num_rif_veicolo":5054891,"cod_anagra_util":0,"cod_anagra_intest":169994,"cod_accettatore":"m.perla","id_inconveniente_infinity":16713,"anno":"2022","id_cliente":169994,
                    "data_doc":"2022-08-16 00:00:00.000","tipo_doc":"LO01","numero_doc":110548,
                    "mat_targa":"GB155YJ","mat_telaio":"WVWZZZAWZLY073429","cod_veicolo":"AW13LZ","des_veicolo":"POLO 1.0 TSI COMFORTLINE 95CV DSG","mat_motore":"DKL","util_ragsoc":"","intest_ragsoc":"VENTURINI LUCA","pos":""}
                    */

                    //$this->pratiche[]=$row['rif'].'_'.$row['lam'];

                    //$this->log[]=$row;

                    $this->evalCore($row,$m,(isset($this->param['timeless'])?true:false));
                    
                }
            }

            $this->sortPratiche();

            //$this->splitStato();
            
        }
    }

    function sortPratiche() {

        //strcmp Returns <0 if string1 is less than string2; > 0 if string1 is greater than string2, and 0 if they are equal.}
        usort($this->pratiche,function($a,$b) { 
            $rif_a=($a['riconsegna']=='xxxxxxxx:xx:xx')?($a['entrata']=='xxxxxxxx:xx:xx'?'00000000:00:00':$a['entrata']):$a['riconsegna'];
            $rif_b=($b['riconsegna']=='xxxxxxxx:xx:xx')?($b['entrata']=='xxxxxxxx:xx:xx'?'00000000:00:00':$b['entrata']):$b['riconsegna'];
            //if ($b['riconsegna']=='xxxxxxxx:xx:xx') $b['riconsegna']='00000000:00:00'; 
            //return strcmp($a['riconsegna'],$b['riconsegna']);
            return strcmp($rif_a,$rif_b);
        });

    }

    function splitStato() {
        //INUTILIZZATO 19.03.2023

        foreach ($this->pratiche as $k=>$p) {

            $stato=$p['pratica']->getStato("","");

            if ($stato=='EX') {
                $this->esterno[$k]=$p;
                unset($this->pratiche[$k]);
            }

            if ($stato=='SO') {
                $this->sospeso[$k]=$p;
                unset($this->pratiche[$k]);
            }

            if ($stato=='RO') {
                $this->ricambio[$k]=$p;
                unset($this->pratiche[$k]);
            }

            if ($stato=='LA' || $stato=='OK') {
                $this->pronto[$k]=$p;
                unset($this->pratiche[$k]);
            }

        }

    }


    function evalCore($row,$m,$timeless) {

        //if ($timeless && (!isset($row['numero_contratto']) || $row['numero_contratto']==0) ) return false;

        $id=base64_encode($row['pratica']);

        if (!array_key_exists($id,$this->pratiche)) {

            //se siamo su PORSCHE INTERNI su CONCERTO occorre verificare se esiste un contratto in INFINITY
            if ($m['dms']=='concerto' && ($row['cod_officina']=='PI' || $row['cod_officina']=='PU' || $row['cod_officina']=='PN') ) {

                $h=$this->wh->getContrattoInfinity($row['mat_telaio']);

                if ($h['result']) {
                    $fid2=$this->galileo->preFetchPiattaforma($h['piattaforma'],$h['result']);
                    while ($row2=$this->galileo->getFetchPiattaforma($h['piattaforma'],$fid2)) {

                        foreach ($row2 as $k2=>$v2) {
                            $row[$k2]=$v2;
                        }
                    }

                    //$this->log[]=$row;
                }

                //è necessario nuovamento il controllo dopo aver valutato i CONTRATTI su INFINITY
                if ($timeless) {
                    if ($row['numero_contratto']=='0' || $row['d_uscita']!='' || $row['apprip']==0) return false;
                }
                else {
                    if ($row['numero_contratto']!='0' && $row['d_uscita']=='') return false;
                }
            }

            ///////////////////////////////////////////////////////////

            $marca=(in_array($row['cod_marca'],$this->defMarche))?$row['cod_marca']:'X';

            if ($marca=='X' && !array_key_exists('X',$this->marche)) $this->marche['X']='Generico';
            if ($marca!='X' && !array_key_exists($marca,$this->marche)) $this->marche[$marca]=$row['des_marca'];

            $this->pratiche[$id]=array(
                "riconsegna"=>$row['d_ricon_pratica'],
                "entrata"=>$row['d_entrata_pratica'],
                "marca"=>$marca,
                "id_pratica"=>$id,
                "info"=>$row,
                "pratica"=>new nebulaPraticaFunc($row['pratica'],(isset($row['pratica_pren'])?$row['pratica_pren']:'0'),$m['dms'],'N',$this->odlFunc),
                "commesse"=>array()
            );

            $this->pratiche[$id]['pratica']->setDefaultAlert();
        }

        if (!array_key_exists($row['rif'],$this->pratiche[$id]['commesse'])) {

            $this->pratiche[$id]['commesse'][$row['rif']]=array(
                "stato"=>false,
                "info"=>$row,
                "lams"=>array()
            );

            //if ($row['d_ricon']<$this->pratiche[$id]['riconsegna']) $this->pratiche[$id]['riconsegna']=$row['d_ricon'];
            //if ($row['d_entrata']<$this->pratiche[$id]['entrata']) $this->pratiche[$id]['entrata']=$row['d_entrata'];
            //if ($row['d_fine']>$this->pratiche[$id]['fine'] && $row['d_fine']!='xxxxxxxx:xx:xx') $this->pratiche[$id]['fine']=$row['d_fine'];
        }

        $this->pratiche[$id]['commesse'][$row['rif']]['lams'][$row['lam']]=$row;
        $this->pratiche[$id]['pratica']->addLam($row);

        return $id;
    }

    function exportPratiche() {
        //return $this->galileo->getLog('query');
        return $this->pratiche;
    }

    function exportMarche() {

        ksort($this->marche);
        return $this->marche;
    }

    function drawInofficina($split) {

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        foreach ($this->pratiche as $id=>$p) {

            //ob_start();

                $txt='<div id="nebula_pratica_'.$id.'" data-marca="'.$p['marca'].'" data-pratica="'.$p['id_pratica'].'" style="position:relative;border:2px solid #777777;box-sizing:border-box;margin-top:2px;margin-bottom:10px;padding:2px;';
                    if (isset($this->param['marca']) && $this->param['marca']!="") {
                        if ($this->param['marca']!=$p['marca']) $txt.= 'display:none;';
                    }
                $txt.='" >';

                    ob_start();
                        $mainstato=$this->drawLine($id,$p);
                    $txt.=ob_get_clean();

                $txt.='</div>';

            //se lo split non è da fare
            if (!$split) {
                echo $txt;
            }
            elseif (!$p['pratica']->getPresenza()) {
                $this->sospeso.=$txt;
            }
            elseif ($mainstato=='EX') {
                $this->esterno.=$txt;
                //ob_end_clean();
            }

            elseif ($mainstato=='SO') {
                $this->sospeso.=$txt;
                //ob_end_clean();
            }

            elseif ($mainstato=='RO') {
                $this->ricambio.=$txt;
                //ob_end_clean();
            }

            elseif ($mainstato=='LA' || $mainstato=='OK') {
                $this->pronto.=$txt;
                //ob_end_clean();
            }

            else {
                echo $txt;
            }

            unset($txt);
        }

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        //echo '<div>'.json_encode($this->log).'</div>';
    }

    function drawEsterno() {
        return $this->esterno;
    }

    function drawRicambio() {
        return $this->ricambio;
    }

    function drawSospeso() {
        return $this->sospeso;
    }

    function drawPronto() {
        return $this->pronto;
    }

    function getTimeline() {
        return $this->timeline;
    }

    function drawPratica() {

        /*ob_start();
            $this->drawInofficina(false);
        $temp=ob_get_clean();

        if ($this->esterno!="") echo $this->drawEsterno();
        elseif ($this->ricambio!="") echo $this->drawRicambio();
        elseif ($this->sospeso!="") echo $this->drawSospeso();
        else echo $temp;*/

        $this->drawInofficina(false);
    }

    function drawLine($id,$p) {

        $tempstato=$this->pratiche[$id]['pratica']->getStato('','');
        $mainstato=$tempstato;
        $p['stato']=$tempstato?$this->odlFunc->getStatoOdl($tempstato,$this->pratiche[$id]['info']['dms']):false;

        $actualCC=$this->pratiche[$id]['pratica']->getActualCC();

        $cc="";

        //ob_start();

            echo '<div style="position:relative;font-size:0.9em;background-color:';
                echo ($p['stato'])?$p['stato']['colore']:'white';
                //echo 'white';
            echo ';padding: 2px;border-radius: 6px;" >';

                echo '<div style="position:relative;display:inline-block;width:60%;vertical-align:top;text-align:left;" >';
                    //echo substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($p['entrata'],0,8)))),0,3).' '.mainFunc::gab_todata(substr($p['entrata'],0,8)).' '.substr($p['entrata'],9,5);
                    //echo mainFunc::gab_todata(substr($p['entrata'],0,8)).' '.substr($p['entrata'],9,5);
                    echo mainFunc::gab_todata(substr($p['entrata'],0,8));
                    echo '<img style="position:relative;width:15px;height:10px;margin-left:5px;margin-right:5px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" data-pratica="'.$p['id_pratica'].'" data-d="'.(substr($p['riconsegna'],0,8)=='xxxxxxxx'?'':mainFunc::gab_toinput(substr($p['riconsegna'],0,8))).'" data-o="'.(substr($p['riconsegna'],9,2)=='xx'?'':substr($p['riconsegna'],9,2)).'" data-m="'.(substr($p['riconsegna'],12,2)=='xx'?'':substr($p['riconsegna'],12,2)).'" onclick="window._nebulaPraticaAlert.openRicon(this,\''.$p['info']['dms'].'\');" />';
                    echo (substr($p['riconsegna'],0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($p['riconsegna'],0,8)))),0,3)).' '.substr(mainFunc::gab_todata(substr($p['riconsegna'],0,8)),0,5).' '.substr($p['riconsegna'],9,5);
                echo '</div>';

                $d_fine=$this->pratiche[$id]['pratica']->getFine('','');

                if (isset($p['info']['id_nuovo']) && isset($p['info']['d_arrivo']) && $p['info']['id_nuovo']!=0 && $p['info']['d_arrivo']=="") {
                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;text-align:left;color:red;font-weight:bold;font-size:0.9em;" >';
                        echo 'NON ARRIVATA';
                    echo '</div>';
                }
                else {
                    
                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;text-align:left;" >';
                            if (substr($d_fine,0,8)>substr($p['riconsegna'],0,8)) echo '<img style="position:relative;width:16px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/idesk/img/alert.png" />';
                            else echo '<img style="position:relative;width:14px;top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/idesk/img/work2.png" />';
                        echo '<span style="margin-left:5px;vertical-align:top;">';
                            //echo (substr($p['fine'],0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($p['fine'],0,8)))),0,3)).' '.mainFunc::gab_todata(substr($p['fine'],0,8)).' '.substr($p['fine'],9,5);
                            if (isset($this->param['timeless'])) {
                                echo (substr($d_fine,0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($d_fine,0,8)))),0,3)).' '.mainFunc::gab_todata(substr($d_fine,0,8));
                            }
                            else echo (substr($d_fine,0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($d_fine,0,8)))),0,3)).' '.mainFunc::gab_todata(substr($d_fine,0,8)).' '.substr($d_fine,9,5);
                        echo '</span>';
                    echo '</div>';
                }
                
            echo '</div>';

            echo '<div id="timeless_pratica_head_'.$id.'" data-pratica="'.$p['id_pratica'].'" >';

                //if (isset($this->param['timeless']) || (in_array($p['info']['cod_officina'],$this->odlFunc->getInterni()) && isset($p['info']['intest_contratto']) && $p['info']['intest_contratto']!="") ) {
                if (isset($p['info']['numero_contratto']) && $p['info']['numero_contratto']!=0) {

                    echo '<div style="color:blue;">';

                        echo '<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;">';
                            echo 'Contratto:';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:73%;vertical-align:top;line-height:20px;">';
                            echo $p['info']['numero_contratto'].' - '.($p['info']['status_contratto']=='C'?'chiuso':'aperto');
                            if ($p['info']['d_uscita']!="") {
                                echo ' - Consegnata: '.mainFunc::gab_todata($p['info']['d_uscita']);
                            }
                        echo '</div>';

                    echo '</div>';
                }

                echo '<div>';

                    echo '<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;font-size:1em;font-weight:bold;">';
                        echo $p['info']['mat_targa'];
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:bold;line-height:20px;">';

                        if (isset($this->param['timeless']) || (in_array($p['info']['cod_officina'],$this->odlFunc->getInterni()) && isset($p['info']['intest_contratto']) && $p['info']['intest_contratto']!="") ) {
                            //echo $this->param['cliente'];
                            echo strtoupper(substr($p['info']['intest_contratto'],0,30));
                        }
                        else {
                            if ($p['info']['util_ragsoc']!="") {
                                echo strtoupper(substr($p['info']['util_ragsoc'],0,30));
                            }
                            else {
                                echo strtoupper(substr($p['info']['intest_ragsoc'],0,30));
                            }
                        }
                    echo '</div>';

                echo '</div>';

                echo '<div>';

                    echo '<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:15px;font-size:1em;font-weight:normal;font-size:0.9em;">';
                        echo '<div>'.$p['info']['cod_marca'].' - '.substr($p['info']['des_marca'],0,8).'</div>';
                        echo '<div style="font-size:0.9em;">'.substr($p['info']['cod_accettatore'],0,20).'</div>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:normal;line-height:15px;">';
                        echo '<div>'.$p['info']['mat_telaio'].'</div>';
                        echo '<div style="font-size:0.9em;">'.substr($p['info']['des_veicolo'],0,45).'</div>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        //$temp=ob_get_clean();
        
        if (isset($actualCC['scadenza']) && $actualCC['scadenza']!="") { 

            ///////
            $cc='<div style="position:relative;font-size:0.9em;text-align:center;font-weight:bold;background-color:';
                $cc.=($p['stato'])?$p['stato']['colore']:'white';
            $cc.=';">';
                $cc.=mainFunc::gab_todata($actualCC['scadenza']);
            $cc.='</div>';

            $cc.='<div style="position:relative;font-size:1em;text-align:center;font-weight:bold;color:#ff00a4;border:1px dotted violet;margin-top:2px;margin-bottom:2px;box-sizing:border:box;">';
                $cc.=$actualCC['nota'];
            $cc.='</div>';

            $cc.='<div style="font-size:0.9em;">';

                $cc.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;font-size:1em;font-weight:bold;">';
                    $cc.=$p['info']['mat_targa'];
                $cc.='</div>';

                $cc.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:bold;line-height:20px;">';

                    if (isset($this->param['timeless']) || (in_array($p['info']['cod_officina'],$this->odlFunc->getInterni()) && isset($p['info']['intest_contratto']) && $p['info']['intest_contratto']!="") ) {
                        $cc.=strtoupper(substr($p['info']['intest_contratto'],0,25));
                    }
                    else {
                        if ($p['info']['util_ragsoc']!="") {
                            $cc.=strtoupper(substr($p['info']['util_ragsoc'],0,25));
                        }
                        else {
                            $cc.=strtoupper(substr($p['info']['intest_ragsoc'],0,25));
                        }
                    }
                $cc.='</div>';

            $cc.='</div>';

            $cc.='<div style="font-size:0.9em;">';

                $cc.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:15px;font-size:1em;font-weight:normal;font-size:0.9em;">';
                    $cc.='<div>'.$p['info']['cod_marca'].' - '.substr($p['info']['des_marca'],0,6).'</div>';
                    //$cc.='<div style="font-size:0.9em;">'.substr($p['info']['cod_accettatore'],0,15).'</div>';
                $cc.='</div>';

                $cc.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:normal;line-height:15px;">';
                    $cc.='<div>'.$p['info']['mat_telaio'].'</div>';
                    //$cc.='<div style="font-size:0.9em;">'.substr($p['info']['des_veicolo'],0,35).'</div>';
                $cc.='</div>';

            $cc.='</div>';
            ///////

            if (!array_key_exists($actualCC['scadenza'],$this->timeline)) {
                $this->timeline[$actualCC['scadenza']]=array();
            }

            $this->timeline[$actualCC['scadenza']][]='<div style="width:90%;border:1px solid black;box-sizing:border-box;margin-top:10px;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].cercaNotifica(\''.($p['info']['mat_targa']!=''?$p['info']['mat_targa']:$p['info']['mat_telaio']).'\');">'.$cc.'</div>';

            unset($cc);
        }

        //echo $temp;

        echo '<div id="timeless_pratica_head_edit_'.$id.'" data-pratica="'.$p['id_pratica'].'" style="display:none;margin-top:10px;background-color:beige;">';
        echo '</div>';

        ///////////////////////////////////////////////////////

        foreach ($p['commesse'] as $kc=>$c) {

            $tempstato=$this->pratiche[$id]['pratica']->getStato($kc,'');
            $c['stato']=$tempstato?$this->odlFunc->getStatoOdl($tempstato,$this->pratiche[$id]['info']['dms']):false;

            echo '<div style="margin-top:5px;font-size:0.9em;padding:2px;background-color:';
                //echo ($c['stato'])?$c['stato']['colore']:'white';
                echo 'white;';
                if ($c['info']['ind_chiuso']=='S') echo 'border:2px solid red;';
                else echo 'border:1px solid black;';
            echo '" >';

                echo '<div style="position:relative;">';

                    echo '<div style="position:relative;display:inline-block;width:34%;vertical-align:top;">';

                        echo '<div style="position:relative;">';
                        echo '<img style="position:relative;right:2px;width:16px;height:16px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openCommessa(\''.$kc.'\',\''.$c['info']['dms'].'\');" />';
                            if ($c['info']['cod_movimento']=='OOA') echo '<span style="font-weight:bold;font-size:0.9em;vertical-align:top;">Interno:</span>';
                            elseif ($c['info']['cod_movimento']=='OOP') echo '<span style="font-weight:bold;vertical-align:top;">Pagamento:</span>';
                            else echo '<span style="font-weight:bold;vertical-align:top;">????:</span>';
                            echo '<span style="margin-left:5px;vertical-align:top;" >'.substr($c['info']['dms'],0,1).$kc.'</span>';
                        echo '</div>'; 

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;text-align:left;" >';
                        echo '<img style="position:relative;width:14px;top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/idesk/img/work2.png" />';
                        echo '<img style="position:relative;top:-1px;width:15px;margin-left:5px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                        echo '<span style="margin-left:5px;vertical-align:top;">';

                            $d_fine=$this->pratiche[$id]['pratica']->getFine($kc,'');
                            //echo (substr($c['info']['d_fine'],0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($c['info']['d_fine'],0,8)))),0,3)).' '.mainFunc::gab_todata(substr($c['info']['d_fine'],0,8)).' '.substr($c['info']['d_fine'],9,5);
                            echo (substr($d_fine,0,8)=='xxxxxxxx'?'':substr(mainFunc::gab_weektotag(date('w',mainFunc::gab_tots(substr($d_fine,0,8)))),0,3)).' '.mainFunc::gab_todata(substr($d_fine,0,8)).' '.substr($d_fine,9,5);
                        echo '</span>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:24%;vertical-align:top;text-align:left;height:15px;border-radius:10px;text-align:center;background-color:';
                        echo ($c['stato'])?$c['stato']['colore']:'white';
                    echo ';" >';
                        echo ($c['stato'])?$c['stato']['testo']:'';
                    echo '</div>';

                echo '</div>';

                if ($c['info']['ind_chiuso']=='S') {
                    echo '<div style="position:relative;width:100%;text-align:center;">';
                        echo '<span style="font-weight:bold;color:red;font-size:0.8em;">CHIUSO</span>';
                    echo '</div>';
                }       

            //echo '</div>';

                $prefix=($this->search)?'search':'avalon';

                $this->pratiche[$id]['pratica']->drawLine($kc,'',true,false,$prefix);

                echo '<div id="'.$prefix.'_odl_block_nota_'.$kc.'_" >';
                    echo $this->pratiche[$id]['pratica']->getNota($kc,'');
                echo '</div>';

                echo '<div id="'.$prefix.'_odl_block_'.$c['info']['rif'].'" style="margin-top:5px;">';

                    foreach ($c['lams'] as $kl=>$l) {

                        $addebito=$this->odlFunc->getAddebito($l,$this->pratiche[$id]['info']['dms']);

                        echo '<div style="margin-top:2px;">';

                            echo '<div style="position:relative;display:inline-block;width:5%;vertical-align:top;font-size:1em;border:1px solid black;text-align:center;';
                                if ($addebito) echo 'background-color:'.$addebito['colore'].';';
                                else echo 'background-color:white;';
                            echo '" >'.$l['lam'].'</div>';

                            echo '<div style="position:relative;display:inline-block;vertical-align:top;font-size:1em;margin-left:5px;width:90%;">';

                                if (isset($this->param['timeless'])) {
                                    echo '<div style="position:relative;">- '.strtoupper(iconv("ISO-8859-1","UTF-8",substr($l['des_riga'],0,38))).'</div>';
                                }
                                else {
                                    echo '<div style="position:relative;">- '.strtoupper(iconv("ISO-8859-1","UTF-8",substr($l['des_riga'],0,50))).'</div>';
                                }

                                $play=false;
                                if (array_key_exists($l['rif'].'_'.$l['lam'].'_'.$this->pratiche[$id]['info']['dms'],$this->marcatureAperte) ) $play=true;

                                if ($l['inc_marc_chiuse']>0 || $play) {

                                    //echo '<div>gdgsdd</div>';

                                    $txt="";

                                    ///////////////////////////////////
                                    $map=$this->timbFunc->getMarcatureOdl($l['rif'],$l['lam'],$l['dms']);

                                    if ($map['result']) {

                                        $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                                        while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

                                            $txt.='<div style="font-size:1em;">';
						
					                            $txt.='<div style="position:relative;display:inline-block;width:40%;">'.$row['nome_operaio'].' ('.$row['cod_operaio'].')</div>';
                                                $txt.='<div style="position:relative;display:inline-block;width:30%;">';
                                                    $txt.='<div style="position:relative;display:inline-block;width:70px;">'.mainFunc::gab_todata($row['d_inizio']).'</div>';
                                                    $txt.='<div style="position:relative;display:inline-block;">'.$row['o_inizio'].'</div>';
                                                $txt.='</div>';
                                                $txt.='<div style="position:relative;display:inline-block;width:15%;">';
                                                    if ($row['d_fine']=='' && $row['ind_chiuso']=='N') {
                                                        $txt.='<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
                                                    }
                                                    else {
                                                        //$txt.='<div style="position:relative;display:inline-block;width:70px;">'.mainFunc::gab_todata($row['d_fine']).'</div>';
                                                        $txt.='<div style="position:relative;display:inline-block;">'.$row['o_fine'].'</div>';
                                                    }
                                                $txt.= '</div>';
                                                //$txt.='<div style="position:relative;display:inline-block;width:10%;">'.($row['des_note']!=""?substr($row['des_note'],0,3):'').'</div>';
                                                $txt.='<div style="position:relative;display:inline-block;width:15%;text-align:right;">'.number_format($row['qta_ore_lavorate'],2,'.','').'</div>';
                                                    
					                        $txt.='</div>';   
                                        }
                                    }
                                    ///////////////////////////////////

                                    $bl=new blockList('idk_'.$l['rif'].'_'.$l['lam'],0);

                                    $head='<div style="position:relative;" >';
                                        $head.='<span>Marcature ('.number_format($l['inc_marc_chiuse'],2,'.','').' ut)</span>';
                                        if ($play) {
                                            $head.='<img style="position:relative;width:15px;height:15px;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
                                        }
                                    $head.='</div>';

			                        $bl->setHead($head);

                                    $bl->setBody($txt);

                                    echo $bl->draw();
                                }

                            echo '</div>';

                        echo '</div>';
                    }

                    //echo '<div>'.$p['info']['d_ricon_pratica'].'</div>';

                echo '</div>';

                echo '<div id="'.$prefix.'_odl_block_edit_'.$c['info']['rif'].'" style="display:none;background-color:beige;">';
                echo '</div>';

            echo '</div>';

        }

        return $mainstato;
    }

}

?>