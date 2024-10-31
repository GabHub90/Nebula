<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

require_once(DROOT.'/nebula/core/panorama/intervallo.php');
require_once(DROOT.'/nebula/core/panorama/schemi.php');
require_once(DROOT.'/nebula/core/alan/alan.php');

class tempoApp extends appBaseClass {

    protected $tpoReparti=array();
    protected $collaboratori=array();
    protected $eventi=array();
    protected $alerts=array(
        "periodi"=>array(),
        "permessi"=>array(),
        "extra"=>array()
    );
    protected $panorama=array();

    protected $responsabili=array();

    //infiormazioni generali
    //INT           margini dell'intervallo di analisi
    //view          segnala che non si è dentro MAESTRO-TEMPO
    //loggedCheck   verifics che il collaboratore loggato appartenga al reparto in esame
    protected $info=array(
        "color"=>"#e7d5ff",
        "int_i"=>"",
        "int_f"=>"",
        "contesto"=>"reparto",
        "presenza"=>"totali",
        "badge"=>false,
        "schemi"=>false,
        "agenda"=>true,
        "brogliaccio"=>false,
        "alert"=>false,
        "tabPresenza"=>false,
        "tabSchemi"=>false,
        "tabAgenda"=>false,
        "tabBrogliaccio"=>false,
        "tabBadge"=>false,
        "tabAlert"=>false,
        "evPeriodo"=>false,
        "evPermesso"=>false,
        "evExtra"=>false,
        "evSposta"=>false,
        "autorizza"=>false,
        "cancella"=>false,
        "intervallo"=>"mese",
        "data_i"=>"",
        "actualReparto"=>"",
        "actualMese"=>"",
        "view"=>false,
        "vincola_utente"=>false,
        "sostituzioni"=>false,
        "loggedCheck"=>false
    );

    //array del mese da visualizzare generato da CALENDARIO
    protected $giorni=array();

    //array che contiene le stringhe dei turni dei collaboratori per ogni giorno dell'intervallo
    protected $totCollDayTurni=array();

    //array per gli eventi da scrivere in "badge" generato durate la scrittura di "presenza"
    //[coll][tag]={}
    protected $badgeEvents=array();

    protected $calendario;
    protected $tpoCalnav;
    protected $tpoIntervallo;
    protected $schemi;
    protected $alan;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/tempo/';

        $this->param['tpo_macroreparto']="";
        $this->param['tpo_reparto']="";
        $this->param['tpo_today']="";
        $this->param['tpo_coll']="";
        $this->param['tpo_divo']="presenza";

        $this->loadParams($param);

        if ($this->param['tpo_today']=="") $this->param['tpo_today']=date('Ym01');

        if ($this->param['tpo_macroreparto']=="") {
            die("macroreparto non definito !!!");
        }

        $config=array(
            "index"=>"tempo",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>"m Y",
            "m1"=>array("mese","1"),
            "p1"=>array("mese","1"),
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>$this->info['color']
        );
    
        $this->tpoCalnav=new calnav('M',$this->param['tpo_today'],$config,$css,$this->galileo);

        $this->galileo->getReparti($this->param['tpo_macroreparto'],'');
        $fetID=$this->galileo->preFetchBase('reparti');

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->tpoReparti[$row['reparto']]=$row;
        }

        //////////////////////////
        $this->galileo->executeSelect('tempo','TEMPO_responsabili',"stato!='0'","");
        $fetID=$this->galileo->preFetch('tempo');

        while($row=$this->galileo->getFetch('tempo',$fetID)) {
            $this->responsabili[$row['gruppo']]=$row;
        }

    }

    function initClass() {
        return ' tempoCode(\''.$this->param['nebulaFunzione']['nome'].'\',\''.$this->param['tpo_coll'].'\',\''.$this->id->getCollID().'\');';
    }

    function build($a) {

        $chk=false;

        foreach ($this->responsabili as $gruppo=>$g) {

            if ($this->id->checkIDgruppi($gruppo)) {

                $chk=true;

                if ($g['tabPresenza']=="1") $this->info['tabPresenza']=true;

                if ($g['tabSchemi']=="1") {
                    $this->info['tabSchemi']=true;
                    $this->info['schemi']=true;
                }
                if ($g['tabAgenda']=="1") {
                    $this->info['tabAgenda']=true;
                }
                if ($g['tabBrogliaccio']=="1") {
                    $this->info['tabBrogliaccio']=true;
                    $this->info['brogliaccio']=true;
                }
                if ($g['tabBadge']=="1") {
                    $this->info['tabBadge']=true;
                    $this->info['badge']=true;
                    $this->info['tabAlert']=true;
                    $this->info['alert']=true;
                }

                if ($g['evPeriodo']=="1") $this->info['evPeriodo']=true;
                if ($g['evPermesso']=="1") $this->info['evPermesso']=true;
                if ($g['evExtra']=="1") $this->info['evExtra']=true;
                if ($g['evSposta']=="1") $this->info['evSposta']=true;
                
                if ($g['autorizza']=="1") $this->info['autorizza']=true;
                if ($g['cancella']=="1") $this->info['cancella']=true;
                if ($g['vincola_utente']=="1") $this->info['vincola_utente']=true;
                if ($g['sostituzioni']=="1") $this->info['sostituzioni']=true;
            }
        }

        if (!$chk) die('Utente non configurato per l\'applicazione');

        //$d=mainFunc::gab_tots($this->param['tpo_today']);

        /*$a=array(
            "contesto"=>"reparto",
            "presenza"=>"totali",
            "badge"=>false,
            "schemi"=>false,
            "agenda"=>true,
            "brogliaccio"=>false,
            "intervallo"=>"mese",
            "data_i"=>$this->param['tpo_today'],
            "actualReparto"=>$this->param['tpo_reparto']
        );
        */

        //TEST
        /*
        $a=array(
            "contesto"=>"reparto",
            "presenza"=>"totali",
            "badge"=>false,
            "schemi"=>false,
            "agenda"=>true,
            "brogliaccio"=>false,
            "intervallo"=>"libero",
            "data_i"=>'20210101',
            "data_f"=>'20210107',
            "actualReparto"=>$this->param['tpo_reparto']
        );*/
        //END TEST

        $a["data_i"]=$this->param['tpo_today'];
        $a["actualReparto"]=$this->param['tpo_reparto'];

        foreach ($this->info as $k=>$v) {
            if ( array_key_exists($k,$a) ) $this->info[$k]=$a[$k];
        }

        if ($this->info['data_i']=="") $this->info['data_i']=date('Ymd');
        $this->info['actualMese']=substr($this->info['data_i'],4,2);

        $this->tpoIntervallo=new quartetIntervallo($this->info,$this->tpoReparti,$this->galileo);

        if ($this->info['actualReparto']!="") {

            $this->schemi=new ensambleSchemi($this->info['data_i'],$this->galileo);
            $trange=$this->tpoIntervallo->getIntRange();
            $this->schemi->getCollaboratoriRepartoIntervallo($this->info['actualReparto'],$trange[0],$trange[1]);
            $this->panorama=$this->schemi->setPanorama('A',$this->info['actualReparto']);
            $this->schemi->setCollSkIntervallo('A',$this->info['actualReparto'],$trange[0],$trange[1]);
        }

    }

    function getPresenzaZero($tipo,$reparto,$coll,$da,$a) {
        //tipo=ACTUAL / NOMINALE

        $res=array();

        $pointer=mainFunc::gab_tots($da);
        $end=mainFunc::gab_tots($a);

        while ($pointer<=$end) {

            $tag=date('Ymd',$pointer);
            //echo($reparto.' '.$coll.' '.$tag);
            $temparr=$this->tpoIntervallo->getPresenzaCollDay($reparto,$coll,$tag);
            //echo '-'.$temparr['actual'].'-';
            if ($temparr) {
                if ($temparr[$tipo]==0) $res[]=$tag;
            }
            else $res[]=$tag;

            $pointer=strtotime("+1 day",$pointer);
        }

        return $res;

    }

    function customDraw() {

        echo '<div style="height:15%;">';

            echo '<div style="display:inline-block;width:40%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $this->tpoCalnav->draw();
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:35%;margin-left:3%;vertical-align:top;/* border: 2px solid black; */box-sizing: border-box;margin-top: 3px;padding: 10px;">';
                echo '<div style="">';
                    echo '<select id="tpo_reparto_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tempoChangeRep(this.value);" ';
                        if($this->info['view']) echo 'disabled="disabled"';
                    echo '>';
                        echo '<option value="">Seleziona un reparto...</option>';
                        foreach ($this->tpoReparti as $reparto=>$r) {
                            echo '<option value="'.$reparto.'" ';
                                if ($reparto==$this->param['tpo_reparto']) echo 'selected="selected"';
                            echo '>'.$r['reparto'].' - '.$r['descrizione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            ////////////////////////////
            echo '<input id="tpoConfig" type="hidden" data-config="" />';
            echo '<script type="text/javascript">';
                echo 'var temp='.json_encode($this->info).';';
                echo '$("#tpoConfig").data("config",temp);';
            echo '</script>';
            ///////////////////////////

            echo '<div id="tpoHeadTools" style="position:absolute;top:0px;left:0px;width:100%;height:14%;padding:2px;box-sizing:border-box;overflow:hidden;background-color:#dcd6e4;border-width:3px;border-style:solid;border-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/bordo_div.png) 3;box-shadow: 1px 1px 1px #777777;z-index:25;display:none;">';
                echo '<div id="tpoHeadSelected" style="position:relative;display:inline-block;width:20%;height:100%;vertical-align:top;border-right:2px solid black;padding:2px;box-sizing:border-box;"></div>';

                echo '<div style="position:relative;display:inline-block;width:26%;height:100%;vertical-align:top;border-right:2px solid black;box-sizing:border-box;">';
                    if ($this->info['evPeriodo']) {
                        echo '<img style="position:relative;width:11.1%;margin-left:11.1%;top:50%;margin-top:-20px;cursor:pointer;" tpoid="" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/azioni/periodo.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.toggleHeadEvent(\'periodo\',this);" />';
                    }
                    if ($this->info['evPermesso']) {
                        echo '<img id="tpo_eventCaller_permesso" style="position:relative;width:11.1%;margin-left:11.1%;top:50%;margin-top:-20px;cursor:pointer;" tpoid="" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/azioni/permesso.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.toggleHeadEvent(\'permesso\',this);" />';
                    }
                    if ($this->info['evExtra']) {
                        echo '<img id="tpo_eventCaller_extra" style="position:relative;width:11.1%;margin-left:11.1%;top:50%;margin-top:-20px;cursor:pointer;" tpoid="" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/azioni/extra.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.toggleHeadEvent(\'extra\',this);" />';
                    }
                    if ($this->info['evSposta']) {
                        echo '<img style="position:relative;width:11.1%;margin-left:11.1%;top:50%;margin-top:-20px;cursor:pointer;" tpoid="" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/azioni/sposta2.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.toggleHeadEvent(\'sposta\',this);" />';
                    }
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:54%;height:100%;vertical-align:top;padding:3px;box-sizing:border-box;">';

                    ///////////////////////////////////////////////////////////
                
                    echo '<div id="tpoHeadEvento_periodo" style="height:100%;display:none;" >';

                        echo '<div style="display:inline-block;width:15%;vertical-align:top;text-align:center;">';
                            echo '<div style="position:relative;height:30px;" >';
                                echo '<input name="tpoEventoInput_periodoTipo" style="position:relative;top:-5px;" type="radio" checked value="F" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/periodoF.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:30px;" >';
                                echo '<input name="tpoEventoInput_periodoTipo" style="position:relative;top:-5px;" type="radio" value="M" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/periodoM.png" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:60%;vertical-align:top;text-align:center;">';

                            echo '<div>';
                                echo '<div id="tpoEventoContainer_periodoDa" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div style="font-weight:bold;">Giorno Da:</div>';
                                    echo '<div>';
                                        echo '<input id="tpoEventoInput_periodoDa" style"width:90%;" type="date" />';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div id="tpoEventoContainer_periodoA" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div style="font-weight:bold;">Giorno A:</div>';
                                    echo '<div>';
                                        echo '<input id="tpoEventoInput_periodoA" style"width:90%;" type="date" />';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';

                            echo '<div id="tpoEventoInput_periodoError" style="color:red;font-weight:bold;font-size:0.9em;">';
                            echo '</div>';
                        
                        echo '</div>';

                        echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:center;" >';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<input id="tpoEventoInput_periodoConfirm" style="position:relative;top:-5px;" type="checkbox" ';
                                    if ($this->info['autorizza']) {
                                        echo 'checked';
                                    }
                                    else echo 'disabled';
                                echo '/>';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/thumbup.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<button id="tpoEventoInput_periodoButton" style="position:relative;top:8px;font-weight:bold;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoConfirmPeriodo();">Nuovo</button>';
                                echo '<input id="tpoEventoInput_periodoID" type="hidden" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:5%;vertical-align:bottom;text-align:center;" >';
                            echo '<img id="tpoEventoInput_periodoTrash" style="position:relative;width:92%;margin-bottom:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/trash.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoDelPeriodo();"/>';
                        echo '</div>';

                    echo '</div>';

                    ///////////////////////////////////////////////////////////

                    echo '<div id="tpoHeadEvento_permesso" style="height:100%;display:none;" >';

                        echo '<div style="display:inline-block;width:15%;vertical-align:top;text-align:center;">';
                            echo '<div style="position:relative;height:30px;" >';
                                echo '<input name="tpoEventoInput_permessoTipo" style="position:relative;top:-5px;" type="radio" checked="checked" value="P" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/permessoP.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:30px;" >';
                                echo '<input name="tpoEventoInput_permessoTipo" style="position:relative;top:-5px;" type="radio" value="S" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/permessoS.png" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:60%;vertical-align:top;text-align:center;">';
                            
                            echo '<div>';
                                echo '<div id="tpoEventoContainer_permessoGiorno" style="display:inline-block;border:2px solid transparent;box-sizing:border-box;padding:2px;font-weight:bold;text-align:left;width:70%;">';
                                    echo '<span>Giorno:</span>';
                                    echo '<span id="tpoEventoInput_permessoGiorno" style="margin-left:10px;" data-giorno=""></span>';
                                echo '</div>';
                                echo '<div style="display:inline-block;font-weight:bold;">';
                                    echo '<span>qtà:</span>';
                                    echo '<span id="tpoEventoInput_permessoQta" style="margin-left:10px;"></span>';
                                echo '</div>';
                            echo '</div>';

                            echo '<div>';
                                echo '<div id="tpoEventoContainer_permessoDa" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">Da:</span>';
                                        echo '<select id="tpoEventoInput_permessoDa" style="margin-left:2px;font-size:1.1em;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoCalcolaQta(\'permesso\');">';
                                            echo '<option value="">Seleziona giorno</option>';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div id="tpoEventoContainer_permessoA" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">A:</span>';
                                        echo '<select id="tpoEventoInput_permessoA" style="margin-left:2px;font-size:1.1em;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoCalcolaQta(\'permesso\');">';
                                            echo '<option value="">Seleziona giorno</option>';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';

                            echo '<div id="tpoEventoInput_permessoError" style="color:red;font-weight:bold;font-size:0.9em;">';
                            echo '</div>';
                        
                        echo '</div>';

                        echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:center;" >';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<input id="tpoEventoInput_permessoConfirm" style="position:relative;top:-5px;" type="checkbox" checked="checked" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/thumbup.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<button id="tpoEventoInput_permessoButton" style="position:relative;top:8px;font-weight:bold;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoConfirmPermesso();">Nuovo</button>';
                                echo '<input id="tpoEventoInput_permessoID" type="hidden" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:5%;vertical-align:bottom;text-align:center;" >';
                            echo '<img id="tpoEventoInput_permessoTrash" style="position:relative;width:92%;margin-bottom:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/trash.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoDelPermesso();" />';
                        echo '</div>';

                    echo '</div>';

                    ///////////////////////////////////////////////////////////

                    echo '<div id="tpoHeadEvento_extra" style="height:100%;display:none;" >';

                        echo '<div style="display:inline-block;width:15%;vertical-align:top;text-align:center;">';
                            echo '<div style="position:relative;height:30px;" >';
                                echo '<input name="tpoEventoInput_extraTipo" style="position:relative;top:-5px;" type="radio" checked="checked" value="E" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/extraE.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:30px;" >';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:60%;vertical-align:top;text-align:center;">';

                            echo '<div id="tpoEventoContainer_extraGiorno" style="border:2px solid transparent;box-sizing:border-box;padding:2px;font-weight:bold;text-align:left;">';
                                echo '<span>Giorno:</span>';
                                echo '<span id="tpoEventoInput_extraGiorno" style="margin-left:10px;" data-giorno=""></span>';
                            echo '</div>';

                            echo '<div>';
                                echo '<div id="tpoEventoContainer_extraDa" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">Da:</span>';
                                        echo '<select id="tpoEventoInput_extraDa" style="margin-left:2px;font-size:1.1em;" >';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div id="tpoEventoContainer_extraA" style="display:inline-block;width:50%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">A:</span>';
                                        echo '<select id="tpoEventoInput_extraA" style="margin-left:2px;font-size:1.1em;" >';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';

                            echo '<div id="tpoEventoInput_extraError" style="color:red;font-weight:bold;font-size:0.9em;">';
                            echo '</div>';
                        
                        echo '</div>';

                        echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:center;" >';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<input id="tpoEventoInput_extraConfirm" style="position:relative;top:-5px;" type="checkbox" checked="checked" />';
                                echo '<img style="position:relative;width:28px;margin-left:5px;margin-top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/thumbup.png" />';
                            echo '</div>';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<button id="tpoEventoInput_extraButton" style="position:relative;top:8px;font-weight:bold;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoConfirmExtra();">Nuovo</button>';
                                echo '<input id="tpoEventoInput_extraID" type="hidden" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:5%;vertical-align:bottom;text-align:center;" >';
                            echo '<img id="tpoEventoInput_extraTrash" style="position:relative;width:92%;margin-bottom:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/trash.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoDelExtra();"/>';
                        echo '</div>';     

                    echo '</div>';

                    ///////////////////////////////////////////////////////////

                    echo '<div id="tpoHeadEvento_sposta" style="height:100%;display:none;" >';

                        echo '<div style="display:inline-block;width:75%;vertical-align:top;text-align:center;">';

                            echo '<div id="tpoEventoContainer_spostaGiorno" style="border:2px solid transparent;box-sizing:border-box;padding:2px;font-weight:bold;text-align:left;">';
                                echo '<span>Giorno:</span>';
                                echo '<span id="tpoEventoInput_spostaGiorno" style="margin-left:10px;" data-giorno=""></span>';
                            echo '</div>';

                            echo '<div>';
                                echo '<div id="tpoEventoContainer_spostaDa" style="display:inline-block;width:37%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">Da:</span>';
                                        echo '<select id="tpoEventoInput_spostaDa" style="margin-left:2px;font-size:1.1em;" >';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div id="tpoEventoContainer_spostaA" style="display:inline-block;width:37%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        echo '<span style="font-weight:bold;">A:</span>';
                                        echo '<select id="tpoEventoInput_spostaA" style="margin-left:2px;font-size:1.1em;" >';
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div id="tpoEventoContainer_spostaSub" style="display:inline-block;width:26%;vertical-align:top;text-align:center;border:2px solid transparent;box-sizing:border-box;" >';
                                    echo '<div>';
                                        //echo '<span style="font-weight:bold;">Agenda:</span>';
                                        echo '<select id="tpoEventoInput_spostaSub" style="margin-left:2px;font-size:1.1em;" >';
                                            echo '<option value="">Nessuno</option>';
                                            if (!is_null($this->schemi)) {
                                                foreach ($this->schemi->exportSubrep('A') as $ksrp=>$srp) {
                                                    echo '<option value="'.$ksrp.'">'.$ksrp.'</option>';
                                                }
                                            }
                                        echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';

                            echo '<div id="tpoEventoInput_spostaError" style="color:red;font-weight:bold;font-size:0.9em;">';
                            echo '</div>';
                        
                        echo '</div>';

                        echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:center;" >';
                            echo '<div style="position:relative;height:50%;">';
                            echo '</div>';
                            echo '<div style="position:relative;height:50%;">';
                                echo '<button id="tpoEventoInput_spostaButton" style="position:relative;top:8px;font-weight:bold;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoConfirmSposta();">Nuovo</button>';
                                echo '<input id="tpoEventoInput_spostaID" type="hidden" />';
                                echo '<input id="tpoEventoInput_spostaPanorama" type="hidden" value="'.$this->panorama['ID'].'" />';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:5%;vertical-align:bottom;text-align:center;" >';
                            echo '<img id="tpoEventoInput_spostaTrash" style="position:relative;width:92%;margin-bottom:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/trash.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tpoDelSposta();" />';
                        echo '</div>';     

                    echo '</div>';

                    ///////////////////////////////////////////////////////////

                echo '</div>';

                echo '<img style="position:absolute;top:0px;right:0px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/annulla.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tempoSetColl(\'\');" />';
            echo '</div>';

        echo '</div>';

        echo '<div style="height:85%;">';

            if ($this->param['tpo_reparto']!="") {
                $this->drawReparto();
            }

        echo '</div>';

        echo '<script type="text/javascript">';
            
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.init();';

            ob_start();
                include (DROOT.'/nebula/apps/tempo/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

    function drawReparto() {

        //$this->build();

        $this->tpoIntervallo->calcola();

        $this->tpoIntervallo->calcolaIntTot();

        $t=$this->tpoIntervallo->getIntRange();
        $this->info['int_i']=$t[0];
        $this->info['int_f']=$t[1];

        $this->calendario=new nebulaCalendario(substr($this->info['data_i'],0,4),$this->galileo);
        $this->calendario->setReparto($this->info['actualReparto']);
        $this->calendario->mese(substr($this->info['data_i'],4,2));

        $this->collaboratori=$this->tpoIntervallo->getCollaboratori();
        $this->eventi=$this->tpoIntervallo->getCollEventi();

        /////////////////////////////////////////////////////
        //alimenta gli ALERTS
        if ($this->info['alert']) {

            $this->galileo->executeSelect('tempo','TEMPO_periodi',"isnull(dat_conferma,'')=''","coll,data_i");
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('tempo');

                while($row=$this->galileo->getFetch('tempo',$fetID)) {
                    //if (array_key_exists($row['coll'],$this->collaboratori[$this->param['tpo_reparto']])) {
                        $this->alerts['periodi'][$row['coll']][$row['ID']]=$row;
                    //}
                }
            }

            $this->galileo->executeSelect('tempo','TEMPO_permessi',"isnull(dat_conferma,'')=''","coll,data");
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('tempo');

                while($row=$this->galileo->getFetch('tempo',$fetID)) {
                    //if (array_key_exists($row['coll'],$this->collaboratori[$this->param['tpo_reparto']])) {
                        $this->alerts['permessi'][$row['coll']][$row['ID']]=$row;
                    //}
                }
            }

            $this->galileo->executeSelect('tempo','TEMPO_extra',"isnull(dat_conferma,'')=''","coll,data");
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('tempo');

                while($row=$this->galileo->getFetch('tempo',$fetID)) {
                    //if (array_key_exists($row['coll'],$this->collaboratori[$this->param['tpo_reparto']])) {
                        $this->alerts['extra'][$row['coll']][$row['ID']]=$row;
                    //}
                }
            }

        }
        /////////////////////////////////////////////////////

        //echo json_encode($this->tpoIntervallo->getLog());

        echo '<div class="tpLeft">';

            $this->schemi->getStyle();

            echo '<div class="tpRepDiv">';

                foreach ($this->collaboratori as $reparto=>$r) {

                    //#########################################
                    //setta griglie eventi dai dati contenutiu nel record collaboratori
                    $this->calendario->setGrid('presenza');

                    if (isset($this->eventi[$reparto])) {

                        foreach ($this->eventi[$reparto] as $coll=>$eva) {

                            //segnaposto skemi
                            if ( array_key_exists('mark',$eva) ) {
                                foreach ($eva['mark'] as $tag=>$t) {
                                    foreach ($t as $skema=>$b) {
                                        $tempry=array(
                                            "reparto"=>$reparto,
                                            "coll"=>$coll,
                                            "evento"=>"mark",
                                            "skema"=>$skema,
                                            "blocco"=>$b['blocco'],
                                            "flag_agg"=>$b['flag_agg'],
                                            "flag_cnc"=>(isset($b['flag_cnc']))?true:false,
                                            "dat_conferma"=>"x"
                                        );
                                        
                                        $this->calendario->insertGroupInGrid('presenza',$tag,$tag,$tempry,array());
                                    }
                                }
                            }

                            //periodi
                            if ( array_key_exists('periodi',$eva) ) {
                                foreach ($eva['periodi'] as $ev) {

                                    $esclusioni=$this->getPresenzaZero('nominale',$reparto,$coll,$ev['data_i'],$ev['data_f']);
                                    
                                    $tempry=array(
                                        "reparto"=>$reparto,
                                        "coll"=>$coll,
                                        "evento"=>"periodo".$ev['tipo'],
                                        "data_i"=>$ev['data_i'],
                                        "data_f"=>$ev['data_f'],
                                        "ID"=>$ev['ID'],
                                        "dat_conferma"=>$ev['dat_conferma'],
                                        "txt"=>substr($ev['data_i'],6,2).'/'.substr($ev['data_i'],4,2).' - '.substr($ev['data_f'],6,2).'/'.substr($ev['data_f'],4,2)
                                    );
                                    
                                    $this->calendario->insertGroupInGrid('presenza',$ev['data_i'],$ev['data_f'],$tempry,$esclusioni);

                                }
                            }

                            //permessi
                            if ( array_key_exists('permessi',$eva) ) {
                                foreach ($eva['permessi'] as $ev) {

                                    //il controllo dovrebbe già funzionare in INTERVALLO
                                    //$esclusioni=$this->getPresenzaZero('actual',$reparto,$coll,$ev['data'],$ev['data']);
                                    
                                    $tempry=array(
                                        "reparto"=>$reparto,
                                        "coll"=>$coll,
                                        "evento"=>"permesso".$ev['tipo'],
                                        "ora_i"=>$ev['ora_i'],
                                        "ora_f"=>$ev['ora_f'],
                                        "ID"=>$ev['ID'],
                                        "dat_conferma"=>$ev['dat_conferma'],
                                        "giorno"=>$ev['data'],
                                        "txt"=>$ev['ora_i'].' - '.$ev['ora_f']
                                    );
                                    
                                    $this->calendario->insertGroupInGrid('presenza',$ev['data'],$ev['data'],$tempry,array());

                                }
                            }

                            //extra
                            if ( array_key_exists('extra',$eva) ) {
                                foreach ($eva['extra'] as $ev) {
                                    
                                    $tempry=array(
                                        "reparto"=>$reparto,
                                        "coll"=>$coll,
                                        "evento"=>"extra".$ev['tipo'],
                                        "ora_i"=>$ev['ora_i'],
                                        "ora_f"=>$ev['ora_f'],
                                        "ID"=>$ev['ID'],
                                        "dat_conferma"=>$ev['dat_conferma'],
                                        "giorno"=>$ev['data'],
                                        "txt"=>$ev['ora_i'].' - '.$ev['ora_f']
                                    );
                                    
                                    $this->calendario->insertGroupInGrid('presenza',$ev['data'],$ev['data'],$tempry,array());

                                }
                            }

                            //sposta
                            if ( array_key_exists('sposta',$eva) ) {
                                foreach ($eva['sposta'] as $ev) {

                                    //il controllo dovrebbe già funzionare in INTERVALLO
                                    //$esclusioni=$this->getPresenzaZero('actual',$reparto,$coll,$ev['data'],$ev['data']);
                                    
                                    $tempry=array(
                                        "reparto"=>$reparto,
                                        "coll"=>$coll,
                                        "evento"=>"sposta",
                                        "ora_i"=>$ev['ora_i'],
                                        "ora_f"=>$ev['ora_f'],
                                        "ID"=>$ev['ID'],
                                        "giorno"=>$ev['data'],
                                        "sub_a"=>$ev['sub_a'],
                                        "txt"=>$ev['ora_i'].'-'.$ev['ora_f'].'<span style="margin-left:2px;font-size:0.8em;">('.$ev['sub_a'].')</span>',
                                        "dat_conferma"=>"x"
                                    );
                                    
                                    $this->calendario->insertGroupInGrid('presenza',$ev['data'],$ev['data'],$tempry,array());
                                }
                            }

                        }
                    }

                    //#########################################

                    echo '<div class="tpRepDivBodyContainer" style="">';

                        echo '<div class="tpRepDivBody">';

                            echo '<div class="tpRepDivTitle" style="text-align:center;">';
                                echo '<div>'.$this->tpoReparti[$reparto]['descrizione'].'</div>';
                            echo '</div>'; 

                            foreach ($r as $ID_coll=>$cl) {

                                $tempintest=true;
                                $tempsost=false;

                                echo '<div class="tpRepDivElem" style="';
                                    //if ($ID_coll==$this->param['tpo_coll']) echo 'background-color:bisque;';
                                echo '">';

                                    foreach ($cl as $c) {

                                        if ($c['flag_sostituzione']) $tempsost=true;

                                        if ($tempintest) {
                                            echo '<div class="tpoCollIntest tpoCollIntestx1" idcoll="'.$ID_coll.'" style="position:relative;cursor:pointer;';
                                                if ($ID_coll==$this->param['tpo_coll']) echo 'background-color:bisque;';
                                            echo '" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tempoSetColl(\''.$ID_coll.'\');" >';
                                                //if ($c['cod_operaio']!='') echo '('.$c['cod_operaio'].') ';
                                                echo '<div style="display:inline-block;width:16%;font-size:smaller;">('.$ID_coll.') </div>';
                                                echo '<div style="display:inline-block;width:84%;font-weight:bold;">'.$c['cognome'].' '.$c['nome'].'</div>';
                                            echo '</div>';

                                            $tempintest=false;
                                        }

                                        echo '<div class="tpoCollIntest tpoCollIntestx2" idcoll="'.$ID_coll.'" style="cursor:pointer;';
                                            if ($ID_coll==$this->param['tpo_coll']) echo 'background-color:bisque;';
                                        echo '" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tempoSetColl(\''.$ID_coll.'\');" >';
                                            echo '<div style="display:inline-block;width:9%;font-size:0.85em;">';
                                                echo $c['gruppo'];
                                            echo '</div>';
                                            echo '<div style="display:inline-block;width:91%;font-size:smaller;text-align:center;">';
                                                echo '<div style="display:inline-block;width:40%;">';
                                                    echo ($c['data_i']<$this->info['int_i'])?mainFunc::gab_todata($this->info['int_i']):mainFunc::gab_todata($c['data_i']);
                                                echo '</div>';
                                                echo '<div style="display:inline-block;width:10%;">';
                                                    echo '<img style="width:80%;height: 10px;opacity:0.5;" src="http://'.SADDR.'/nebula/apps/tempo/img/blackarrowR.png" />';
                                                echo '</div>';
                                                echo '<div style="display:inline-block;width:40%;">';
                                                    echo ($c['data_f']>$this->info['int_f'])?mainFunc::gab_todata($this->info['int_f']):mainFunc::gab_todata($c['data_f']);
                                                echo '</div>';
                                            echo '</div>';
                                        echo '</div>';

                                    }

                                    echo '<div class="tpoCollIntest" id="tempo_widget_coll_presenza" idcoll="'.$ID_coll.'" style="cursor:pointer;';
                                        if ($ID_coll==$this->param['tpo_coll']) echo 'background-color:bisque;';
                                    echo '" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.tempoSetColl(\''.$ID_coll.'\');" >';

                                        $tarrRif=$this->tpoIntervallo->getCollTot('subs',$reparto,$ID_coll);
                                        if ($tarrRif) {
                                            $tarr=$tarrRif->getPresenza();
                                        }
                                        else $tarr=false;

                                        echo '<div style="display:inline-block;width:50%;" >';
                                            echo '<div style="display:inline-block;width:30%;font-weight:bold;font-size:smaller;text-align:center;';
                                                if ($tempsost) echo 'background-color:yellow;';
                                            echo '" >';
                                                echo '<img style="width:15px;height:15px;" src="http://'.SADDR.'/nebula/apps/tempo/img/calendar.png">'; 
                                            echo '</div>';
                                            echo '<div style="display:inline-block;width:70%;" >';
                                                if ($tarr) {
                                                    echo mainFunc::gab_mintostring($tarr['nominale']);
                                                }
                                            echo '</div>';
                                        echo '</div>';

                                        echo '<div style="display:inline-block;width:50%;" >';
                                            echo '<div style="display:inline-block;width:30%;font-weight:bold;font-size:smaller;text-align:center;" >';
                                                echo '<img style="width:15px;height:15px;" src="http://'.SADDR.'/nebula/apps/tempo/img/work2.png">'; 
                                            echo '</div>';
                                            echo '<div style="display:inline-block;width:70%;" >';
                                                if ($tarr) {
                                                    echo mainFunc::gab_mintostring($tarr['actual']);
                                                }
                                            echo '</div>';
                                        echo '</div>';

                                    echo '</div>';

                                    $this->schemi->drawCollSk($ID_coll);

                                echo '</div>';

                            }

                        echo '</div>';

                    echo '</div>';
                }   
            
            echo '</div>';

        echo '</div>';


        echo '<div class="tpRight">';

            $this->giorni=$this->calendario->getGiorni();
            
            $divo=new Divo('tempo','8%','92%',true);

            $divo->setBk($this->info['color']);

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

            $txt="";

            $tempindex=-1;

            if ($this->info['tabPresenza']) {

                $tempindex++;

                ob_start();
                    $this->drawCalendario('presenza');
                $txt=ob_get_clean();

                $divo->add_div('Presenza','black',0,"",$txt,($this->param['tpo_divo']=='presenza'?1:0),$css);

                echo '<input id="tpo_mainDivo_Presenza" type="hidden" value="'.$tempindex.'" data-tag="presenza" />';
            }

            $txt="";
            if ($this->info['badge']) {

                $tempindex++;

                $this->alan=new nebulaAlan($this->param['tpo_macroreparto'],'tpo',$this,$this->galileo);

                $this->alan->importa();

                ob_start();
                    $this->drawBadge();
                $txt=ob_get_clean();

                $divo->add_div('Badge','black',1,"R",$txt,($this->param['tpo_divo']=='badge'?1:0),$css);

                echo '<input id="tpo_mainDivo_Badge" type="hidden" value="'.$tempindex.'" data-tag="badge" />';
            }

            $txt="";
            if ($this->info['tabSchemi']) {

                $tempindex++;

                $txt=$this->drawSchemi();
                $divo->add_div('Schemi','black',0,"",$txt,($this->param['tpo_divo']=='schemi'?1:0),$css);

                echo '<input id="tpo_mainDivo_Schemi" type="hidden" value="'.$tempindex.'" data-tag="schemi" />';
            }

            $txt="";
            if ($this->info['tabAgenda']) {

                $tempindex++;

                ob_start();
                    $this->drawAgenda();
                $txt=ob_get_clean();
                $divo->add_div('Agenda','black',0,"",$txt,($this->param['tpo_divo']=='agenda'?1:0),$css);

                echo '<input id="tpo_mainDivo_Agenda" type="hidden" value="'.$tempindex.'" data-tag="agenda" />';
            }

            $txt="";
            if ($this->info['tabAlert']) {

                $tempindex++;

                ob_start();
                    $this->drawAlert();
                $txt=ob_get_clean();
                $divo->add_div('Alerts','black',1,($this->alerts['conteggio']==0?'V':'R'),$txt,($this->param['tpo_divo']=='alert'?1:0),$css);

                echo '<input id="tpo_mainDivo_Alert" type="hidden" value="'.$tempindex.'" data-tag="alert" />';
            }

            //brogliaccio qui non è contemplato
            //if ($this->info['tabBrogliaccio']) $divo->add_div('Brogliaccio','black',0,"",$txt,0,$css);
            unset($txt);

            $divo->build();

            $divo->draw();

            echo '<script type="text/javascript">';
                echo <<<JS
                    window._tempo_divo.postSel=function() {
                        $('#ribbon_tpo_divo').val($('input[id^="tpo_mainDivo_"][value="'+this.def_sel+'"]').data('tag'));

                        if (window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].schemiEdit) {
                            window._nebulaApp.ribbonExecute();
                        }
                    }
JS;
            echo '</script>';

            //echo json_encode($tpLista);
            //echo json_encode($this->tpoIntervallo->getLog());

        echo '</div>';

    }

    function drawCalHead() {

        echo '<table class="tpo_cal_table">';

            echo '<tr>';

                for ($i=0;$i<=6;$i++) {
                    $c=($i==0)?'red':'black';
                    echo '<th style="width:'.round(100/7,2).'%;border:1px solid '.$c.';color:'.$c.';">';                    
                        echo mainFunc::gab_weektotag($i);
                    echo '</th>';
                }

            echo '</tr>';

        echo '</table>';

    }

    function drawColor($t) {

        $a=array(
            "ck"=>"",
            "bk"=>""
        );
        
        $a['ck']=($t['wd']==0 || $t['festa']==1)?'red':'black';
        if ($t['chiusura']==1) $a['ck']='darkviolet';
        $a['bk']=($this->info['actualMese']!=substr($t['tag'],4,2))?'#dddddd;':'#ffffff';
        
        return $a;
    }

    function drawCalendario($contenuto) {

        echo '<div class="tpo_cal_div_container" >';

            echo '<div style="width:97%;height:6%;">';

                $this->drawCalHead();

            echo '</div>';

            echo '<div style="width:100%;height:93%;overflow:scroll;">';
                
                echo '<div style="width:97%;">';

                    echo '<table class="tpo_cal_table" style="margin-bottom:10px;" >';

                        foreach ($this->giorni as $sett=>$s) {
                            
                            echo '<tr>';

                                //{"tag":"20210430","wd":5,"festa":0,"chiusura":0,"chi":[],"testo":""},{"tag":"20210501","wd":6,"festa":1,"chiusura":0,"chi":[],"testo":"Festa Lavoratori"}
                                foreach ($s as $t) {

                                    $cc=$this->drawColor($t);
                                    
                                    echo '<td style="width:'.round(100/7,2).'%;border:1px solid '.$cc['ck'].';background-color:'.$cc['bk'].';height:120px;overflow:hidden;vertical-align:top;padding:3px;" >';

                                        echo '<div class="tpo_cal_day_title" style="color:'.$cc['ck'].';border-color:'.$cc['ck'].';text-align:right;">';
                                            echo '<span class="';
                                                if ($this->info['actualMese']==substr($t['tag'],4,2) && $t['festa']!=1) echo 'tpoGoodDay';
                                                else echo 'tpoBadDay';
                                            echo '" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.selDay(\''.$t['tag'].'\')">'.substr($t['tag'],6,2).'</span>';

                                            if ($t['festa']==1 || $t['chiusura']==1) {
                                                echo '<div style="position:absolute;left:2px;top:2px;font-size: 0.8em;line-height: 16px;vertical-align: middle;">';
                                                    echo substr(html_entity_decode($t['testo']),0,20);
                                                echo '</div>';
                                            }

                                        echo '</div>';
                                        
                                        //se è un giorno del mese
                                        if ($this->info['actualMese']==substr($t['tag'],4,2)) {
                                        
                                            if (array_key_exists($contenuto,$t['griglie'])) {

                                                ksort($t['griglie'][$contenuto]);

                                                switch ($contenuto) {

                                                    case 'presenza':
                                                        $this->drawGrigliaPresenza($t['tag'],$t['griglie'][$contenuto]);
                                                    break;
                                                }
                                            }
                                        }

                                    echo '</td>';

                                    if ($this->info['actualMese']==substr($t['tag'],4,2)) {

                                        foreach ($this->collaboratori[$this->param['tpo_reparto']] as $collID=>$c) {
                                            $this->totCollDayTurni[$collID][$t['tag']]=$this->tpoIntervallo->getTurnoCollDay($this->param['tpo_reparto'],$collID,$t['tag']);
                                        }
                                    }

                                }
                            
                            echo '</tr>';
                        }

                    echo '</table>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div>';
            //echo json_encode($this->giorni);
            //echo json_encode($this->tpoIntervallo->getLog());
        echo '</div>';

        echo '<input id="tpoTurnoCollDay" type="hidden" data-arr="" />';

        echo '<script type="text/javascript">';
            echo 'var temp='.json_encode($this->totCollDayTurni).';';
            echo '$("#tpoTurnoCollDay").data("arr",temp);';
            //echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.grigliaCollDaySkema='
        echo '</script>';

        /*echo '<div>';
            echo json_encode($this->eventi);
        echo '</div>';*/

        /*
        echo '<div>';
            echo json_encode($this->tpoIntervallo->getLog());
        echo '</div>';
        echo json_encode($this->tpoIntervallo->getTurnoCollDay('VWS','132','20210608'));*/
        
        
    }

    function drawGrigliaPresenza($tag,$arr) {

        $pos=-1;

        foreach ($arr as $kg=>$g) {

            $pos++;

            //riempi eventuali posti vuoti della griglia
            while ($pos<$kg) {
                echo '<div class="tpo_griglia_presenza_elem"></div>';
                $pos++;
            }

            $this->drawEventTile($g,$tag);

            if ($this->info['badge']) {

                if ($g['evento']!='sposta') {

                    $temptipo=($g['evento']=='mark')?'mark':substr($g['evento'],0,-1);

                    $this->badgeEvents[$g['coll']][$tag][$temptipo][]=$g;
                }
            }
        }                               
    }

    function drawEventTile($g,$tag) {

        echo '<div class="tpo_griglia_presenza_elem tpo_griglia_presenza_'.$g['evento'].'" style="position:relative;';
            echo 'background-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eventi/'.$g['evento'].'.png);';
        echo '">';

            $temptxt=$this->collaboratori[$g['reparto']][$g['coll']][0]['cognome'].' '.$this->collaboratori[$g['reparto']][$g['coll']][0]['nome'];

            echo '<div style="';

                    if ($g['evento']=='mark') {
                        //se la presenza actual == 0 copri con la cover
                        $tp=$this->getPresenzaZero("actual",$g['reparto'],$g['coll'],$tag,$tag);
                        if (count($tp)>0 || $g['flag_cnc']) {
                            echo 'background-color:#dddddd;opacity:0.7;';
                        }
                        elseif ($g['flag_agg']) {
                            echo 'background-color:#ffffae;opacity:0.7;';
                        }
                    }

                echo '">';

                echo '<div style="font-weight:bold;">'.substr($temptxt,0,14).'</div>';

                switch($g['evento']) {

                    case 'mark':
                        echo '<div>'.$g['skema'].' ('.$g['blocco'].')</div>';
                    break;

                    case 'periodoF':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'periodoM':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'periodoC':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'permessoP':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'permessoS':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'extraE':
                        echo '<div>'.$g['txt'].'</div>';
                    break;

                    case 'sposta':
                        echo '<div style="font-size:smaller;">'.$g['txt'].'</div>';
                    break;

                }

            echo '</div>'; 

            echo '<div class="tpo_griglia_presenza_cover" idcoll="'.$g['coll'].'" ';
                switch($g['evento']) {
                    case 'periodoF':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="periodo" tpotipo="F" tpodatai="'.$g['data_i'].'" tpodataf="'.$g['data_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'periodoM':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="periodo" tpotipo="M" tpodatai="'.$g['data_i'].'" tpodataf="'.$g['data_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'periodoC':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="periodo" tpotipo="C" tpodatai="'.$g['data_i'].'" tpodataf="'.$g['data_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'permessoP':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="permesso" tpotipo="P" giorno="'.$g['giorno'].'" orai="'.$g['ora_i'].'" oraf="'.$g['ora_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'permessoS':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="permesso" tpotipo="S" giorno="'.$g['giorno'].'" orai="'.$g['ora_i'].'" oraf="'.$g['ora_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'extraE':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="extra" tpotipo="E" giorno="'.$g['giorno'].'" orai="'.$g['ora_i'].'" oraf="'.$g['ora_f'].'" tpoconferma="'.$g['dat_conferma'].'"';
                    break;
                    case 'sposta':
                        echo 'tpoid="'.$g['ID'].'" tpoevento="sposta" giorno="'.$g['giorno'].'" orai="'.$g['ora_i'].'" oraf="'.$g['ora_f'].'" suba="'.$g['sub_a'].'"';
                    break;
                }

                if($this->info['autorizza']) echo ' onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.toggleHeadEvent(\'\',this);"';
            echo '>';

                 if ($g['dat_conferma']=="") echo '<img style="position:relative;width:100%;height:100%;opacity:0.2;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/zebra.png" />';
                
            echo '</div>';

            /*if ($g['evento']=='mark') {
                //se la presenza actual == 0 copri con la cover
                $tp=$this->getPresenzaZero("actual",$g['reparto'],$g['coll'],$tag,$tag);
                if (count($tp)>0) {
                    echo '<div style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:#777777;opacity:0.3;z-index:3;"></div>';
                }
                elseif ($g['flag_agg']) {
                    echo '<div style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:yellow;opacity:0.3;z-index:3;"></div>';
                }
            }*/

        echo '</div>';
    }

    function drawAgenda() {

        echo '<div class="tpo_cal_div_container" >';

            echo '<div style="width:100%;height:99%;overflow:scroll;">';

                $wh=new nebulaWHole($this->param['tpo_reparto'],$this->galileo);

                $all=array();

                foreach ($this->giorni as $sett=>$s) {

                    foreach ($s as $t) {
                        if( $this->info['actualMese']!=substr($t['tag'],4,2) )continue;

                        if (!$dts=$this->tpoIntervallo->getDayTotSub($t['tag'])) continue;
                        
                        $a=array(
                            "inizio"=>$t['tag'],
                            "fine"=>$t['tag']
                        );

                        $wh->build($a);

                        $all[$t['tag']]=array(
                            "reparto"=>$this->param['tpo_reparto'],
                            "dms"=>$wh->getTodayDms($t['tag']),
                            "subtot"=>false
                        );

                        $tempry=array(
                            "titolo"=>mainFunc::gab_weektotag(date('w',mainFunc::gab_tots($t['tag']))).' - '.mainFunc::gab_todata($t['tag']),
                            "range"=>$this->tpoIntervallo->getGlobalTrim()
                        );

                        echo '<div style="margin-top:10px;margin-bottom:5px;">';
                            echo $dts->drawProprietario($tempry);
                        echo '</div>';

                        $all[$t['tag']]['subtot']=$dts->getSubtot();
                    }
                }

                echo '<div style="margin-top:25px;text-align:center;" >';
                    echo '<div>';
                        echo '<button id="tempo_allineadms_button" style="width:300px;" data-info="'.base64_encode(json_encode($all)).'" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.allineaDms(this);" >Allinea DMS</button>';
                    echo '</div>';
                    /*echo '<div>';
                        echo json_encode($all);
                    echo '</div>';*/
                echo '</div>';

            echo '</div>';

        echo '</div>';

        /*$tempry=array();
            foreach ($this->tpoIntervallo->getGrigliaDayTotSub() as $rt=>$r) {
                $tempry[$rt]=$r->getTl();
            }*/           
    }

    function drawSchemi() {

        /* $this->tpoIntervallo->getTl() [reparto][collaboratore][tag]
        "600":{"start":600,"end":615,"tag":"10:00","point":1,"half":1,"flag":true,"subs":{"nominale":{"flag":true,"qta":1},"actual":{"flag":true,"qta":1},"eventi":[],
        "schemi":{"ST_PV2IA":"11"},
        "agenda":{"DIAVWS":{"flag":true,"qta":1},"MECVWS":{"flag":false,"qta":0},"CARPU":{"flag":false,"qta":0},"GOMPU":{"flag":false,"qta":0},"REVPU":{"flag":false,"qta":0}}}},
        */

        /*$this->schemi->exportSchemi('A')
        "ST_PV4EC":{"codice":"ST_PV4EC","reparto":"VWS","titolo":"vwtec 2021","turnazione":7,"flag_festivi":0,"flag_turno":0,"on_flag":0,"mark":0,"exclusive":0,"overall":"COVID",
        "griglia":"{\"11\":{\"turno\":\"COV_3PV\",\"next\":\"12\",\"agenda\":{\"MECVWS\":\"100\"},\"ricric\":\"0\"},\"12\":{\"turno\":\"COV_3PV\",\"next\":\"13\",\"agenda\":{\"MECVWS\":\"100\"},\"ricric\":\"0\"},\"13\":{\"turno\":\"VW421\",\"next\":\"11\",\"agenda\":{\"MECVWS\":\"100\"},\"ricric\":\"0\"}}",
        "data_i":"20210301","blocco_inizio":"11","posizione":1,"colore":"#3DB300"},
        */

        ob_start();

        echo '<div class="tpo_cal_div_container" >';

            /*echo '<div style="width:97%;height:6%;">';

                $this->drawCalHead();

            echo '</div>';*/

            echo '<div style="width:100%;height:99%;overflow:scroll;">';
                
                echo '<div style="width:97%;">';

                    foreach ($this->giorni as $sett=>$s) {

                        $this->drawCalHead();

                        echo '<table class="tpo_cal_table" style="margin-bottom:10px;border-collapse:collapse;" >';

                            echo '<tr>';

                                //{"tag":"20210430","wd":5,"festa":0,"chiusura":0,"chi":[],"testo":""},{"tag":"20210501","wd":6,"festa":1,"chiusura":0,"chi":[],"testo":"Festa Lavoratori"}
                                foreach ($s as $t) {

                                    $cc=$this->drawColor($t);

                                    //echo '<td style="width:'.round(100/7,2).'%;border-top:1px solid '.$cc['ck'].';border-left:1px solid '.$cc['ck'].';border-right:1px solid '.$cc['ck'].';border-bottom:1px solid '.$cc['bk'].';background-color:'.$cc['bk'].';height:15px;vertical-align:top;padding:3px;" >';
                                    //echo '<td style="width:'.round(100/7,2).'%;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid '.$cc['bk'].';background-color:'.$cc['bk'].';height:15px;vertical-align:top;padding:3px;" >';
                                    echo '<td style="width:'.round(100/7,2).'%;border:1px solid '.$cc['ck'].';background-color:'.$cc['bk'].';height:15px;vertical-align:top;padding:3px;" >';

                                        echo '<div class="tpo_cal_day_title" style="color:'.$cc['ck'].';border-color:'.$cc['ck'].';text-align:right;">';
                                            echo substr($t['tag'],6,2);

                                            if ($t['festa']==1 || $t['chiusura']==1) {
                                                echo '<div style="position:absolute;left:2px;top:2px;font-size: 0.8em;line-height: 16px;vertical-align: middle;">';
                                                    echo substr(html_entity_decode($t['testo']),0,20);
                                                echo '</div>';
                                            }

                                        echo '</div>';

                                    echo '</td>';

                                }
                            
                            echo '</tr>';

                            foreach ($this->schemi->exportSchemi('A') as $skema=>$k) {

                                echo '<tr>';

                                    foreach ($s as $t) {

                                        $cc=$this->drawColor($t);

                                        //echo '<td style="width:'.round(100/7,2).'%;border-left:1px solid '.$cc['ck'].';border-right:1px solid '.$cc['ck'].';border-bottom:1px solid '.$cc['bk'].';background-color:'.$cc['bk'].';height:20px;vertical-align:top;padding:3px;" >';
                                        //echo '<td style="width:'.round(100/7,2).'%;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid '.$cc['bk'].';background-color:'.$cc['bk'].';height:20px;vertical-align:top;padding:3px;" >';
                                        echo '<td style="width:'.round(100/7,2).'%;border-bottom:1px solid '.$cc['bk'].';background-color:'.$cc['bk'].';height:20px;vertical-align:top;padding:3px;" >';
                                        
                                            if ( $this->info['actualMese']==substr($t['tag'],4,2) && $t['festa']==0) {

                                                if ($k['exclusive']==1) {
                                                    $exc=$this->tpoIntervallo->getExclusive($skema,$t['tag']);
                                                }
                                                else $exc="";

                                                $onclick="window._nebulaApp_".$this->param['nebulaFunzione']['nome'].".tpoChangeSkema(this);";
                                                
                                                $this->schemi->drawSkDay('A',$skema,$t['tag'],$t['wd'],$exc,$onclick);
                                            }

                                        echo '</td>';

                                    }

                                echo '</tr>';
                                
                            }

                        echo '</table>';

                    }

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<input id="tpoSchemiData" type="hidden" data-arr="" value="" />';

        echo '<script type="text/javascript">';
            echo 'var temp='.json_encode($this->tpoIntervallo->getGrigliaCollDaySkema($this->param['tpo_reparto'])).';';
            echo '$("#tpoSchemiData").data("arr",temp);';
            //echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.grigliaCollDaySkema='
        echo '</script>';

        /*
        echo '<div class="tpo_cal_div_container" >';

            echo '<div style="width:100%;height:99%;overflow:scroll;">';

                foreach ($this->tpoIntervallo->getGrigliaDaySub() as $reparto=>$r) {

                    if ($reparto!=$this->info['actualReparto']) continue;

                    //foreach ($r as $coll=>$c) {
                        foreach ($r as $tag=>$t) {
                            //if ($coll==130) {
                                echo json_encode($t->getTl());
                            //}
                        }
                    //}
                }

            echo '</div>';

        echo '</div>';
        */

        return ob_get_clean();
    }

    function drawBadge() {

        $divo2=new Divo('badge','6%','94%',true);

        $divo2->setBk('#cccccc');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"0.7em",
            "margin-left"=>"2px",
            "margin-top"=>"8px"
        );

        $css2=array(
            "width"=>"10px",
            "height"=>"10px",
            "top"=>"50%",
            "transform"=>"translate(0%,-110%)",
            "right"=>"2px"
        );

        $divo2->setChkimgCss($css2);

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/alan.js" ></script>';

        echo '<div class="tpo_cal_div_container" >';

            $badgeIndex=0;

            foreach ($this->collaboratori as $reparto=>$r) {
                foreach ($r as $collID=>$cl) {
                    foreach ($cl as $c) {

                        //$c['badgeIndex']=$badgeIndex;

                        $txt="";

                        ob_start();
                            $this->drawBadgeColl($c);
                        $txt=ob_get_clean();

                        //questa è una informazione che serve specificatamente a TEMPO e non può essere generale per ALAN
                        $txt.='<input id="tpo_alan_collBadgeIndex_'.$collID.'" type="hidden" value="'.$badgeIndex.'" data-idcoll="'.$collID.'" />'; 

                        //$txt.='<div>'.json_encode($this->collaboratori).'</div>';
                        $divo2->add_div($c['ID_coll'].' '.substr($c['cognome'],0,3),'black',1,"R",$txt,($this->param['tpo_coll']==$collID)?1:0,$css);

                        $badgeIndex++;

                        //collaboratori si basa sull'appartenenza del dipendente ad un gruppo
                        //a noi interessa solo la prima ricorrenza
                        break;
                    }
                }
            }

            $divo2->build();
            $divo2->draw();

        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->collaboratori);
        echo '</div>';*/

        echo '<script type="text/javascript">';

            echo <<<JS
                if (window._badge_divo) {
                    
                    window._badge_divo.postSel=function() {

                        if ($('#ribbon_tpo_divo').val()=='badge') {
                            var coll=$('input[id^="tpo_alan_collBadgeIndex_"][value="'+this.def_sel+'"]').data('idcoll');
                            window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].tempoSetColl(coll);
                        }
                    };
                }
JS;

            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.setAlan();';
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.alanCheck();';
        echo '</script>';

    }

    function drawBadgeColl($c) {

        //$this->alan->setCollaboratore($c);
        $this->alan->setCollaboratore($c,$this->totCollDayTurni[$c['ID_coll']]);

        $this->alan->leggi($this->info['int_i'],$this->info['int_f']);
       
        /*if (isset($this->totCollDayTurni[$c['ID_coll']])) {
            //$this->alan->draw($this->totCollDayTurni[$c['ID_coll']]);
            $this->alan->draw();
        }
        //else $this->alan->draw(array());*/
        
        $this->alan->build();
        $this->alan->draw();
    }

    function drawBadgeEvents($coll,$tag) {

        if (!isset($this->badgeEvents[$coll][$tag])) return;

        if (array_key_exists('mark',$this->badgeEvents[$coll][$tag])) {

            foreach ($this->badgeEvents[$coll][$tag]['mark'] as $g) {

                echo '<div class="tpo_griglia_badge_event" style="display:inline-block;margin-left:3px;"> ';
                    $this->drawEventTile($g,$tag);
                echo '</div>';
            }

        }

        if (array_key_exists('periodo',$this->badgeEvents[$coll][$tag])) {

            foreach ($this->badgeEvents[$coll][$tag]['periodo'] as $g) {

                echo '<div class="tpo_griglia_badge_event" style="display:inline-block;margin-left:3px;"> ';
                    $this->drawEventTile($g,$tag);
                echo '</div>';
            }

        }

        if (array_key_exists('permesso',$this->badgeEvents[$coll][$tag])) {

            foreach ($this->badgeEvents[$coll][$tag]['permesso'] as $g) {

                echo '<div class="tpo_griglia_badge_event" style="display:inline-block;margin-left:3px;"> ';
                    $this->drawEventTile($g,$tag);
                echo '</div>';
            }

        }

        if (array_key_exists('extra',$this->badgeEvents[$coll][$tag])) {

            foreach ($this->badgeEvents[$coll][$tag]['extra'] as $g) {

                echo '<div class="tpo_griglia_badge_event" style="display:inline-block;margin-left:3px;"> ';
                    $this->drawEventTile($g,$tag);
                echo '</div>';
            }

        }

    }

    function drawAlertTitle($cc) {
        echo '<div style="font-weight:bold;border-top:1px solid #cccccc;margin-top:5px;">';
            echo $cc['cognome'].' '.$cc['nome'];
        echo '</div>';
    }

    function drawAlert() {

        $this->alerts['conteggio']=0;

        //echo '<div style="width:100%;height:99%;overflow:scroll;">';

            foreach ($this->collaboratori[$this->param['tpo_reparto']] as $IDcoll=>$c) {

                $title=false;

                foreach ($c as $kc=>$cc) {

                    if (array_key_exists($IDcoll,$this->alerts['periodi'])) {
                        foreach ($this->alerts['periodi'][$IDcoll] as $evID=>$e) {
                            if (!$title) {
                                $this->drawAlertTitle($cc);
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
                                        echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data_i'],0,6).'01\',\''.$IDcoll.'\');"/>';
                                    echo '</div>';
                                echo '</div>';

                            echo '</div>';

                            $this->alerts['conteggio']++;
                        }
                    }

                    if (array_key_exists($IDcoll,$this->alerts['permessi'])) {
                        foreach ($this->alerts['permessi'][$IDcoll] as $evID=>$e) {
                            if (!$title) {
                                $this->drawAlertTitle($cc);
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
                                        echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data'],0,6).'01\',\''.$IDcoll.'\');"/>';
                                    echo '</div>';
                                echo '</div>';

                            echo '</div>';

                            $this->alerts['conteggio']++;
                        }
                    }

                    if (array_key_exists($IDcoll,$this->alerts['extra'])) {
                        foreach ($this->alerts['extra'][$IDcoll] as $evID=>$e) {
                            if (!$title) {
                                $this->drawAlertTitle($cc);
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
                                        echo '<img style="width:25px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/eye.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.linkAlerts(\''.substr($e['data'],0,6).'01\',\''.$IDcoll.'\');"/>';
                                    echo '</div>';
                                echo '</div>';

                            echo '</div>';

                            $this->alerts['conteggio']++;
                        }
                    }

                    break;
                }
            }

        //echo '</div>';
    }

}
?>