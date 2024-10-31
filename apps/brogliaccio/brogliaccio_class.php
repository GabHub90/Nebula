<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/brogliaccio/classi/daylight.php');

require_once(DROOT.'/nebula/core/panorama/intervallo.php');
require_once(DROOT.'/nebula/core/alan/alan.php');

class brogliaccioApp extends appBaseClass {

    protected $contesto=array(
        "first"=>true,
        "data_i"=>"",
        "data_f"=>""
    );

    protected $bgcReparti=array();
    //lista dei reparti realmente da disegnare
    protected $actualRep=array();

    //collaboratori esportati da "intervallo"
    protected $collaboratori=array();
    //giorni per ogni reparto esportati da "intervallo"
    protected $giorni=array();

    //oggetti daylight per ogni reparto,IDColl
    protected $daylight=array();

    //risultti di ALAN [reparto][coll]
    protected $alanRes=array();

    //eventi collegati ai collaboratori
    protected $eventi=array();

    //eventi in attesa di conferma (tutta l'azienda)
    protected $alerts=array(
        "periodi"=>array(),
        "permessi"=>array(),
        "extra"=>array()
    );

    //codiciDB [reparto][idcoll][tag] nel periodo di analisi per tutta l'azienda
    protected $codiciDB=array();

    //array a cui vanno aggiunti gli stati di tutti i reparti
    protected $statoOverall=array();

    protected $bgcCalnav;
    protected $bgcIntervallo;
    protected $bgcAlan;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/brogliaccio/';

        $this->param['bgc_macroreparto']="";
        $this->param['bgc_reparto']="";
        $this->param['bgc_today']="";
        $this->param['bgc_tutti']="";

        $this->loadParams($param);

        if ($this->param['bgc_today']=="") $this->param['bgc_today']=date('Ymd');

        if ($this->param['bgc_macroreparto']=="") {
            die("macroreparto non definito !!!");
        }

        $config=array(
            "index"=>"bgc",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>"m Y",
            "m1"=>array("mese","1"),
            "p1"=>array("mese","1"),
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>"#d5ffec"
        );

        $this->bgcCalnav=new calnav('M',$this->param['bgc_today'],$config,$css,$this->galileo);

        $this->galileo->getReparti($this->param['bgc_macroreparto'],'');
        $fetID=$this->galileo->preFetchBase('reparti');

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->bgcReparti[$row['reparto']]=$row;
            
            if ($this->contesto['first']) {
                $this->bgcAlan=new nebulaAlan($this->param['bgc_macroreparto'],'bgc_'.$row['reparto'],null,$this->galileo);
                $this->bgcAlan->importa();
                $this->contesto['first']=false;
            }
        }

        //ALERTS
        $this->galileo->executeGeneric('tempo','getAllUncheckedPeriodi',array(),'');
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('tempo');
            while($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->alerts['periodi'][$row['coll']][$row['ID']]=$row;
            }
        }
        $this->galileo->executeGeneric('tempo','getAllUncheckedEvents',array("tipo"=>"permessi"),'');
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('tempo');
            while($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->alerts['permessi'][$row['coll']][$row['ID']]=$row;
            }
        }
        $this->galileo->executeGeneric('tempo','getAllUncheckedEvents',array("tipo"=>"extra"),'');
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('tempo');
            while($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->alerts['extra'][$row['coll']][$row['ID']]=$row;
            }
        }

    }

    function initClass() {
        return ' brogliaccioCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function build() {

        /*protected $config=array(
            "contesto"=>"reparto",
            "presenza"=>"totali",
            "badge"=>false,
            "schemi"=>false,
            "agenda"=>false,
            "brogliaccio"=>false,
            "intervallo"=>"libero",
            "data_i"=>"",
            "data_f"=>"",
            "actualReparto"=>""
        );*/

        $a=array(
            "agenda"=>true,
            "brogliaccio"=>true,
            "intervallo"=>'mese',
            "data_i"=>$this->param['bgc_today']
        );

        $this->bgcIntervallo=new quartetIntervallo($a,$this->bgcReparti,$this->galileo);
        $this->bgcIntervallo->calcola();

        $this->collaboratori=$this->bgcIntervallo->getCollaboratori();
        $this->giorni=$this->bgcIntervallo->getGrigliaCal();
        $this->eventi=$this->bgcIntervallo->getCollEventi();

        if ($this->param['bgc_tutti']=='0') $this->actualRep[]=$this->param['bgc_reparto'];
        else {
            foreach ($this->bgcReparti as $reparto=>$r) {
                $this->actualRep[]=$reparto;
            }
        }

        $this->contesto['first']=true;

        foreach ($this->actualRep as $reparto) {

            $this->statoOverall[$reparto]='OK';

            if (array_key_exists($reparto,$this->collaboratori)) {

                foreach ($this->collaboratori[$reparto] as $IDcoll=>$c) {

                    $this->daylight[$reparto][$IDcoll]=new bgcDaylight($reparto.'_'.$IDcoll,$this->galileo);
                    $this->daylight[$reparto][$IDcoll]->loadDays($this->giorni[$reparto]);

                    foreach ($c as $cc) {
                        $this->daylight[$reparto][$IDcoll]->setConfig('title',$cc['cognome'].' '.$cc['nome'].' ('.$cc['IDMAT'].')');
                        $this->daylight[$reparto][$IDcoll]->setConfig('foot',true);
                        $this->daylight[$reparto][$IDcoll]->setInfo(array("IDcoll"=>$cc['ID_coll']));
                        $this->daylight[$reparto][$IDcoll]->setInfo(array("reparto"=>$reparto));

                        if (isset($this->eventi[$reparto][$IDcoll])) {
                            //if (array_key_exists($IDcoll,$this->eventi[$reparto])) {
                                $temp=array();
                                /*"9": {
                                    "periodi": [
                                        {
                                        "ID": 1974,
                                        "coll": "40",
                                        "tipo": "F",
                                        "data_i": "20210129",
                                        "data_f": "20210129",
                                        "utente_inserimento": null,
                                        "utente_modifica": null,
                                        "utente_conferma": null,
                                        "dat_inserimento": null,
                                        "dat_modifica": null,
                                        "dat_conferma": "20210621"
                                        }
                                    ]
                                    "permessi": [
                                        {
                                        "ID": 1762,
                                        "coll": "9",
                                        "data": "20210105",
                                        "ora_i": "09:30",
                                        "ora_f": "12:00",
                                        "tipo": "P",
                                        "utente_inserimento": "m.cecconi",
                                        "utente_modifica": "m.cecconi",
                                        "utente_conferma": "m.cecconi",
                                        "dat_inserimento": "20210722",
                                        "dat_modifica": "20210722",
                                        "dat_conferma": "20210722"
                                        }
                                    ]
                                },*/

                                //I TIPI DI PERMESSO E PERIODO SONO GLOBALMENTE UNIVOCI

                                if (array_key_exists('permessi',$this->eventi[$reparto][$IDcoll])) {
                                    
                                    foreach ($this->eventi[$reparto][$IDcoll]['permessi'] as $p) {
                                        $temp[$p['data']][]=$p;
                                    }
                                }

                                if (array_key_exists('periodi',$this->eventi[$reparto][$IDcoll])) {

                                    foreach ($this->eventi[$reparto][$IDcoll]['periodi'] as $p) {

                                        $index=mainFunc::gab_tots($p['data_i']);
                                        $end=mainFunc::gab_tots($p['data_f']);

                                        while ($index<=$end) {
                                            $temp[date('Ymd',$index)][]=$p;
                                            $index=strtotime('+1 day',$index);
                                        }
                                    }
                                }

                                $this->daylight[$reparto][$IDcoll]->setInfo(array("eventi"=>$temp));
                            //}
                        }

                        //attraverso questo ciclo vengono settati anche "data_i" e "data_f" (contesto opertivo)
                        foreach ($this->giorni[$reparto] as $tag=>$g) {

                            if ($this->contesto['first']) {
                                if ($this->contesto['data_i']=="") $this->contesto['data_i']=$tag;
                                $this->contesto['data_f']=$tag;
                            }

                            $turno[$tag]=$this->bgcIntervallo->getTurnoCollDay($reparto,$IDcoll,$tag);
                        }

                        $this->bgcAlan->setCollaboratore($cc,$turno);
                        $this->bgcAlan->leggi($this->contesto['data_i'],$this->contesto['data_f']);
                        $this->bgcAlan->build();
                        ob_start();
                            $this->bgcAlan->draw();
                        ob_clean();
                        $this->alanRes[$reparto][$IDcoll]=$this->bgcAlan->getRes();
                        /*array(
                            "minuti"=>0,
                            "arrotondato"=>0,
                            "oreSTD"=>$std,
                            "tipoSTR"=>$str,
                            "actual"=>$actual,
                            "actualBro"=>$actualBro,
                            "stato"=>'OK',
                            "blocks"=>array()
                        );*/

                        //##################################
                        //caricare i SUBS con i dati di ALAN
                        $a=array(
                            "label"=>"Presenza",
                            "lista"=>$this->alanRes[$reparto][$IDcoll]
                        );
                        $this->daylight[$reparto][$IDcoll]->loadSub('presenza',$a);
                        //##################################

                        break;
                    }

                    $this->contesto['first']=false;

                }
            }

        }

        unset($this->bgcAlan);

        $wc="tag>='".$this->contesto['data_i']."' AND tag<='".$this->contesto['data_f']."'";
        $this->galileo->executeSelect('tempo','TEMPO_dettaglio_bgc',$wc,'reparto,coll,tag');

        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('tempo');
            while($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->codiciDB[$row['reparto']][$row['coll']][$row['tag']]=$row;
            }
        }

        foreach ($this->codiciDB as $reparto=>$r) {
            foreach ($r as $IDcoll=>$c) {
                if (array_key_exists($reparto, $this->daylight)) {
                    if (array_key_exists($IDcoll,$this->daylight[$reparto])) {
                        $this->daylight[$reparto][$IDcoll]->loadCodiciDB($c);
                    }
                }
            }
        }

    }

    function setRepStato($reparto,$stato) {

        if (!array_key_exists($reparto,$this->statoOverall)) $this->statoOverall[$reparto]='OK';

        if ($stato=='KO') $this->statoOverall[$reparto]='KO';

        if ($stato=='ALL') {
            if ($this->statoOverall[$reparto]!='KO') $this->statoOverall[$reparto]='ALL';
        }
    }

    function customDraw() {

        echo '<div style="height:15%;">';

            echo '<div style="display:inline-block;width:40%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $this->bgcCalnav->draw();
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:45%;margin-left:3%;vertical-align:top;/* border: 2px solid black; */box-sizing: border-box;margin-top: 3px;padding: 10px;">';
                echo '<div style="">';
                    echo '<select id="bgc_reparto_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.bgcChangeRep(this.value);" ';
                    echo '>';
                        echo '<option value="">Seleziona un reparto...</option>';
                        foreach ($this->bgcReparti as $reparto=>$r) {
                            echo '<option value="'.$reparto.'" ';
                                if ($reparto==$this->param['bgc_reparto']) echo 'selected="selected"';
                            echo '>'.$r['reparto'].' - '.$r['descrizione'].'</option>';
                        }
                        echo '<option value="tutti" ';
                            if ($this->param['bgc_tutti']=='1') echo 'selected="selected"';
                        echo '>----- Tutti -----</option>';
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:10%;text-align:right;">';
                if ($this->param['bgc_reparto']!="" || $this->param['bgc_tutti']=='1') {
                    echo '<img style="margin-top:30px;width:30px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/brogliaccio/img/esporta.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.bgcEsporta();" />';
                }
            echo '</div>';
        
        echo '</div>';

        echo '<div style="height:85%;">';
            if ($this->param['bgc_reparto']!="" || $this->param['bgc_tutti']=='1') {
                $this->drawBrogliaccio();
            }
        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->eventi);
        echo '</div>';*/

        echo '<script type="text/javascript">';
            ob_start();
                include (DROOT.'/nebula/apps/brogliaccio/core/default.js');
            ob_end_flush();
        echo '</script>';

    }

    function drawBrogliaccio() {
        //echo $this->param['bgc_reparto'].' '.$this->param['bgc_today'].' '.$this->param['bgc_tutti'];
        $arr=array();

        $this->build();

        /////////////////////////////////////////////
        $divo=new Divo('bgc','8%','92%',true);

        $divo->setBk('#ffe6d5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.5em",
            "margin-left"=>"15px",
            "margin-top"=>"8px"
        );

        $css2=array(
            "width"=>"20px",
            "height"=>"20px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);
        /////////////////////////////////////////////

        foreach ($this->actualRep as $reparto) {

            $txt="";

            if (array_key_exists($reparto,$this->collaboratori)) {

                $txt.='<div style="width:97%;overflow:scroll;">';

                    foreach ($this->collaboratori[$reparto] as $IDcoll=>$c) {

                        ob_start();
                            echo '<div style="width:100%;border-bottom:1px solid #bbbbbb;">';
                                $this->daylight[$reparto][$IDcoll]->draw();
                                $this->setRepStato($reparto,$this->daylight[$reparto][$IDcoll]->getStatoOverall());
                            echo '</div>';
                        $txt.=ob_get_clean();
                    }

                    $txt.='<div style="height:25px;"></div>';

                $txt.='</div>';
            
            }

            $col='V';
            if ($this->statoOverall[$reparto]=='KO') $col='R';
            elseif ($this->statoOverall[$reparto]=='ALL') $col='G';

            $divo->add_div($reparto,'black',1,$col,$txt,0,$css);

        }

        ////////////////////////////////
        $txt="";
        $this->alerts['conteggio']=0;

        ob_start();
            echo '<div style="width:97%;border-bottom:1px solid #bbbbbb;">';
                foreach ($this->actualRep as $reparto) {

                    echo '<div style="width:90%;background-color:#cccccc;font-weight:bold;margin-top:10px;">'.$reparto.'</div>';

                    if (array_key_exists($reparto,$this->collaboratori)) {
                        foreach ($this->collaboratori[$reparto] as $IDcoll=>$c) {
                            $this->drawCollAlerts($IDcoll);
                        }
                    }
                }
            echo '</div>';
        $txt.=ob_get_clean();

        $divo->add_div('Alerts','black',1,($this->alerts['conteggio']>0)?'R':'V',$txt,0,$css);

        unset($txt);

        $divo->build();
        $divo->draw();

    }

    function drawAlertTitle($ev) {
        echo '<div style="font-weight:bold;border-top:1px solid #cccccc;margin-top:5px;">';
            echo $ev['cognome'].' '.$ev['nome'];
        echo '</div>';
    }

    function drawCollAlerts($IDcoll) {

        $title=false;

        if (array_key_exists($IDcoll,$this->alerts['periodi'])) {
            foreach ($this->alerts['periodi'][$IDcoll] as $evID=>$e) {
                if (!$title) {
                    $this->drawAlertTitle($e);
                    $title=true;
                }
                echo '<div style="margin-top:5px;width:95%;">';

                    echo '<div style="display:inline-block;width:50%;text-align:center;" >';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/periodo'.$e['tipo'].'.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo mainFunc::gab_todata($e['data_i']);
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/blackarrowR.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo mainFunc::gab_todata($e['data_f']);
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:50%;text-align:left;" >';
                        echo '<div style="display:inline-block;width:50%;">';
                            echo "Inserito il: ";
                            echo ($e['dat_inserimento']!="")?mainFunc::gab_todata($e['dat_inserimento']):'';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo 'da: '.$e['utente_inserimento'];
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            //echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data_i'],0,6).'01\',\''.$IDcoll.'\');"/>';
                        echo '</div>';
                    echo '</div>';

                echo '</div>';

                $this->alerts['conteggio']++;
            }
        }

        if (array_key_exists($IDcoll,$this->alerts['permessi'])) {
            foreach ($this->alerts['permessi'][$IDcoll] as $evID=>$e) {
                if (!$title) {
                    $this->drawAlertTitle($e);
                    $title=true;
                }
                echo '<div style="margin-top:5px;width:95%;">';

                    echo '<div style="display:inline-block;width:50%;text-align:center;" >';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/permesso'.$e['tipo'].'.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo mainFunc::gab_todata($e['data']);
                        echo '</div>';
                        echo '<div style="display:inline-block;width:20%;">';
                            echo $e['ora_i'];
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/blackarrowR.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:20%;">';
                            echo $e['ora_f'];
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:50%;text-align:left;" >';
                        echo '<div style="display:inline-block;width:50%;">';
                            echo "Inserito il: ";
                            echo ($e['dat_inserimento']!="")?mainFunc::gab_todata($e['dat_inserimento']):'';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo 'da: '.$e['utente_inserimento'];
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            //echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data'],0,6).'01\',\''.$IDcoll.'\');"/>';
                        echo '</div>';
                    echo '</div>';

                echo '</div>';

                $this->alerts['conteggio']++;
            }
        }

        if (array_key_exists($IDcoll,$this->alerts['extra'])) {
            foreach ($this->alerts['extra'][$IDcoll] as $evID=>$e) {
                if (!$title) {
                    $this->drawAlertTitle($e);
                    $title=true;
                }
                echo '<div style="margin-top:5px;width:95%;">';

                    echo '<div style="display:inline-block;width:50%;text-align:center;" >';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/extra'.$e['tipo'].'.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo mainFunc::gab_todata($e['data']);
                        echo '</div>';
                        echo '<div style="display:inline-block;width:20%;">';
                            echo $e['ora_i'];
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            echo '<img style="width:25px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/blackarrowR.png" />';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:20%;">';
                            echo $e['ora_f'];
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:50%;text-align:left;" >';
                        echo '<div style="display:inline-block;width:50%;">';
                            echo "Inserito il: ";
                            echo ($e['dat_inserimento']!="")?mainFunc::gab_todata($e['dat_inserimento']):'';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:40%;">';
                            echo 'da: '.$e['utente_inserimento'];
                        echo '</div>';
                        echo '<div style="display:inline-block;width:10%;">';
                            //echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data'],0,6).'01\',\''.$IDcoll.'\');"/>';
                        echo '</div>';
                    echo '</div>';

                echo '</div>';

                $this->alerts['conteggio']++;
            }
        }

    }

    function esporta() {

        $this->build();

        $txt="";

        $head=false;

        foreach ($this->actualRep as $reparto) {

            if (array_key_exists($reparto,$this->collaboratori)) {

                foreach ($this->collaboratori[$reparto] as $IDcoll=>$c) {

                    foreach ($c as $cc) {

                        if (!$head) {

                            $txt.="rep;cod;collaboratore;";
                            $txt.=$this->daylight[$reparto][$IDcoll]->exportHead();
                            $head=true;
                        }

                        $txt.=$reparto.';'.$cc['IDMAT'].';'.$cc['cognome'].' '.$cc['nome'].';';

                        $txt.=$this->daylight[$reparto][$IDcoll]->export();

                        $txt.=';'.';'.$cc['cognome'].' '.$cc['nome'].'_;';

                        $txt.=$this->daylight[$reparto][$IDcoll]->exportCodici();
                        
                        break;
                    }
                }
            }

        }

        return $txt;

    }

}

?>