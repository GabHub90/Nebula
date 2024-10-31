<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divutil/divutil.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chime/chime_class.php');

require_once(DROOT.'/nebula/core/panorama/intervallo.php');
require_once(DROOT.'/nebula/core/panorama/schemi.php');

require_once(DROOT.'/nebula/apps/avalon/classi/wormhole.php');

class avalonApp extends appBaseClass {

    protected $infoIntervallo=array();
    protected $avlReparti=array();
    protected $subreps=array();
    protected $giorni=array();
    protected $occupazione=array();

    protected $avlCalnav;
    protected $avlIntervallo;
    //wormhole
    protected $wh;

    protected $flagedit=false;

    protected $actualDms="";

    function __construct($param,$flagEdit,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/avalon/';

        $this->flagEdit=$flagEdit;

        $this->param['officina']="";
        $this->param['avl_today']="";
        $this->param['avl_setday']="";

        $this->loadParams($param);

        if ($this->param['avl_today']=="") $this->param['avl_today']=date('Ymd');

        $rif=mainFunc::gab_tots($this->param['avl_today']);

        while (date('w',$rif)>0) {
            $rif=strtotime("-1 day",$rif);
        }

        $config=array(
            "index"=>"avalon",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>"d m Y",
            "m1"=>array("settimana","1"),
            "m2"=>array("settimana","2"),
            "m3"=>array("settimana","3"),
            "p1"=>array("settimana","1"),
            "p2"=>array("settimana","2"),
            "p3"=>array("settimana","3"),
            "div"=>false,
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>'white'
        );

        $this->avlCalnav=new calnav('W',date('Ymd',$rif),$config,$css,$this->galileo);

        $this->galileo->getReparti('S','');
        $fetID=$this->galileo->preFetchBase('reparti');

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->avlReparti[$row['reparto']]=$row;
        }

        $this->galileo->getSubrepTable('concerto IS NOT NULL OR infinity IS NOT NULL');
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            if ($row['concerto']!='' || $row['concerto']!='null') {
                $this->subreps['concerto'][$row['concerto']]=$row['subrep'];
            }
            if ($row['infinity']!='' || $row['infinity']!='null') {
                $this->subreps['infinity'][$row['infinity']]=$row['subrep'];
            }
        }

        $this->infoIntervallo=array(
            "contesto"=>"reparto",
            "presenza"=>"totali",
            "badge"=>false,
            "schemi"=>false,
            "agenda"=>true,
            "brogliaccio"=>false,
            "intervallo"=>"libero",
            "data_i"=>date('Ymd',$rif),
            "data_f"=>date('Ymd',strtotime("+20 days",$rif)),
            "actualReparto"=>$this->param['officina']
        );

        $this->avlIntervallo=new quartetIntervallo($this->infoIntervallo,$this->avlReparti,$this->galileo);
        $this->avlIntervallo->calcola();
        $this->avlIntervallo->calcolaIntTot();

        $this->giorni=$this->avlIntervallo->getGrigliaCal();

        //////////////////////////////////////
        $this->wh=new avalonWHole($this->param['officina'],$this->galileo);

        ///////////////////
        //definisci DMS attuale per il reparto
        $a=array(
            "inizio"=>date('Ymd'),
            "fine"=>date('Ymd')
        );
        $this->wh->build($a);
        $this->actualDms=$this->wh->getTodayDms(date('Ymd'));
        ////////////////////////////////////////////

        $this->wh->getOccu($this->infoIntervallo['data_i'],$this->infoIntervallo['data_f']);
        $this->elaboraOccupazione();
    }

    function initClass() {
        return ' avalonCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function elaboraOccupazione() {

        foreach ($this->wh->exportMap() as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    //###########################################
                    //traduci $row['subrep'] in base al DMS nei subrep NEBULA
                    if ($m['dms']=='concerto') {
                        if (isset($this->subreps['concerto'][$row['subrep']])) {
                            $row['subrep']=$this->subreps['concerto'][$row['subrep']];
                        }
                        else {
                            $row['subrep']='';
                        }
                    }
                    //###########################################

                    if ('prog_spalm'==0) {
                        $d=substr($row['d_inc'],0,8);
                        if (isset($this->occupazione[$d][$row['subrep']])) {
                            $this->occupazione[$d][$row['subrep']]+=$row['ore'];
                        }
                        else {
                            $this->occupazione[$d][$row['subrep']]=$row['ore'];
                        }
                    }
                    else {

                        if ('prog_spalm'==1) {
                            $d=substr($row['d_inc'],0,8);
                            if (isset($this->occupazione[$d][$row['subrep']])) {
                                $this->occupazione[$d][$row['subrep']]+=$row['ore'];
                            }
                            else {
                                $this->occupazione[$d][$row['subrep']]=$row['ore'];
                            }
                        }

                        $d=substr($row['d_spalm'],0,8);
                        if (isset($this->occupazione[$d][$row['subrep']])) {
                            $this->occupazione[$d][$row['subrep']]+=$row['ore_spalm'];
                        }
                        else {
                            $this->occupazione[$d][$row['subrep']]=$row['ore_spalm'];
                        }
                    }
                }

            }
        }
    }

    function customDraw() {

        $divo=new Divo('avalon','5%','96%',true);

        $divo->setBk('#cccccc');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        $txt="";

        ob_start();
            $this->drawAgenda();
        $txt=ob_get_clean();

        $divo->add_div('Agenda','black',0,"",$txt,1,$css);

        if ($this->flagEdit) {

            $txt='<div id="avalon_odielle" style="width:100%;height:92%;" >';
                $txt.='<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>'; 
            $txt.='</div>';

            $divo->add_div('Odl','black',1,"Y",$txt,0,$css);

            $txt='<div id="avalon_storico" style="width:100%;height:92%;" >';
                $txt.='<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>'; 
            $txt.='</div>';

            $divo->add_div('Storico','black',1,"Y",$txt,0,$css);
        }

        unset($txt);

        $divo->build();

        $divo->draw();

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/code.js?v='.time().'"></script>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/classi/avalonOdl.js?v='.time().'"></script>';

        nebulaChime::drawJS();

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/avalon/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

    function drawAgenda() {

        echo '<div style="position:relative;margin-top:10px;width:100%;height:7%;">';

            echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >';
                $this->avlCalnav->draw();
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;" >';

                echo '<div style="margin-left:5%;margin-top:-7px;" >';
                
                    echo '<div style="font-size:0.9em;font-weight:bold;" >';
                        echo 'Cerca in avanti -> targa,telaio,intest. (più di 3 caratt.):';
                    echo '</div>';

                    echo '<div style="width:100%;" >';
                        echo '<input id="avalon_agenda_search" style="width:75%;font-size:1em;" type="text" onkeydown="if(event.keyCode==13) window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.search(\''.$this->param['officina'].'\');" />';
                        echo '<button style="margin-left:15px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.search(\''.$this->param['officina'].'\');" >cerca</button>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;" >';

                if ($this->flagEdit) {
                    echo '<img style="position:relative;width:35px;height:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openOdl(\'\',\''.$this->actualDms.'\');"/>';
                }

                echo '<img style="position:relative;width:35px;height:35px;margin-left:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/print.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.graffaPdf();" />';

            echo '</div>';

        echo '</div>';

        echo '<div style="width:100%;height:91%;">';

            echo '<div style="position:relative;display:inline-block;width:66%;height:100%;border-right:1px solid black;padding:3px;box-sizing:border-box;vertical-align:top;" >';

                echo '<div id="avalon_agenda" style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;">';

                    $index=mainFunc::gab_tots($this->infoIntervallo['data_i']);
                    $end=mainFunc::gab_tots($this->infoIntervallo['data_f']);

                    //$this->giorni
                    //"VWS":{"20210822":{"tag":"20210822","wd":"0","festa":0,"chiusura":0,"chi":[],"testo":"","chk":"OK"},

                    echo '<table style="position:relative;width:98%;"> ';

                        while ($index<=$end) {

                            if (date('w',$index)==0) {
                                echo '<tr>';
                            }

                            echo '<td style="width:14.29%;min-height:100px;border: 1px solid black;vertical-align:top;" >';

                                $tag=date('Ymd',$index);

                                $color="black";
                                if ($this->giorni[$this->param['officina']][$tag]['chiusura']==1) $color='darkviolet';
                                if ($this->giorni[$this->param['officina']][$tag]['festa']==1) $color='red';

                                echo '<div style="position:relative;text-align:center;font-size:0.8em;font-weight:bold;cursor:pointer;color:'.$color.';" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setDay(\''.$tag.'\');" >';
                                    echo substr(mainFunc::gab_weektotag(date('w',$index)),0,3).' '.substr($tag,6,2).'/'.substr($tag,4,2);
                                    if ($tag==date('Ymd')) {
                                        echo '<img style="position:absolute;width:10px;height:10px;right:3px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/dot.png" />';
                                    }
                                echo '</div>';

                                echo '<div style="text-align:center;font-size:0.7em;height:16px;color:'.$color.';" >';       
                                    echo $this->giorni[$this->param['officina']][$tag]['testo'];
                                echo '</div>';

                                if ($dts=$this->avlIntervallo->getDayTotSub($tag)) {

                                    //l'occupazione deve essere recuperata tramite WORMHOLE
                                    $dts->avalonGrid((isset($this->occupazione[$tag]))?$this->occupazione[$tag]:array());

                                }

                            echo '</td>';


                            if (date('w',$index)==6) {
                                echo '</tr>';
                            }

                            $index=strtotime("+1 day",$index);

                        }

                        $close=false;
                        while (date('w',$index)>0) {
                            $close=true;
                            echo '<td style="width:14.29%;" ></td>';
                            $index=strtotime("+1 day",$index);
                        }
                        if ($close) echo '</tr>';

                    echo '</table>';

                echo '</div>';

                $divu=new nebulaUtilityDiv('avalon_chime',"window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeChime()");
                $divu->draw();

            echo '</div>';

            

            echo '<div id="nebula_avalon_right" style="position:relative;display:inline-block;width:34%;height:100%;padding:3px;box-sizing:border-box;border-top:2px solid black;vertical-align:top;" >';

                /*echo '<div>';
                    //echo json_encode($this->galileo->getLog('query'));
                    echo json_encode($this->occupazione);
                echo '</div>';*/

            echo '</div>';

            echo '<div id="nebula_avalon_search" style="position:relative;display:none;width:34%;height:100%;padding:3px;box-sizing:border-box;border-top:2px solid black;vertical-align:top;" >';

                echo '<div style="width:100%;height:10%;text-align:left;font-size:1.2em;">';
                        echo "Ricerca:";
                        echo '<img style="position:absolute;width:25px;height:25px;right:20px;top:10px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/chiudi.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.chiudiSearch();" />';
                echo '</div>';

                echo '<div style="width:100%;height:90%;overflow:scroll;overflow-x:hidden;">';

                    echo '<div id="nebula_avalon_search_body" style="width:97%;" ></div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';
        
    }

}

?>