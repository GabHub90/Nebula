<?php

include('classi/struttura.php');
include('classi/link.php');
include('classi/analisi.php');

class centavosApp extends appBaseClass {

    protected $viewer=false;
    
    protected $panorami=array();

    //oggetto struttura
    protected $struttura;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/centavos/';

        $this->param['ctv_reparto']="";
        $this->param['ctv_panorama']="";
        $this->param['ctv_variante']="";
        $this->param['ctv_sezione']="";
        $this->param['ctv_linkoll']="";
        $this->param['ctv_logged']="";
        $this->param['ctv_openType']="struttura";

        $this->loadParams($param);

        $wclause="reparto='".$this->param['ctv_reparto']."' AND stato!='annullato'";
        $orderby="data_i DESC";
        $this->galileo->executeSelect('centavos','CENTAVOS_piani',$wclause,$orderby);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->panorami[$row['ID']]=$row;
            }
        }

        /*TEST
        $a=array(
            "1"=>array(
                "reparto"=>"VWS",
                "descrizione"=>"Incentivazione 2019",
                "data_i"=>"20190101",
                "data_f"=>"20191231"
            ),
            "2"=>array(
                "reparto"=>"VWS",
                "descrizione"=>"Incentivazione 2020",
                "data_i"=>"20200101",
                "data_f"=>"20210630"
            ),
            "3"=>array(
                "reparto"=>"VWS",
                "descrizione"=>"Incentivazione 2021",
                "data_i"=>"20210701",
                "data_f"=>"20211231"
            )
        );*/
        //END TEST

        //$this->panorami=$a;

        if ($this->param['ctv_panorama']!="" && isset($this->panorami[$this->param['ctv_panorama']])) {
            $this->struttura=new centaStruttura($this->panorami[$this->param['ctv_panorama']],$galileo);
        }

    }

    function initClass() {
        return ' centavosCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function drawViewer($logged) {
        $this->viewer=$logged;
        $this->draw();
    }

    function customDraw() {

        if ($this->viewer) {
            $this->customViewer();
            return;
        }

        echo '<div style="height:12%;">';

            echo '<div style="display:inline-block;width:30%;vertical-align:top;box-sizing: border-box;padding: 10px;">';
                
                echo '<div style="margin-left:10px;">';

                    echo '<div style="font-weight:bold;font-size:0.9em;">Panorama:</div>';

                    echo '<select id="ctv_panorama_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvChangePanorama(this.value);" ';
                    echo '>';
                        echo '<option value="">Seleziona un panorama...</option>';
                        foreach ($this->panorami as $id=>$p) {
                            echo '<option value="'.$id.'" ';
                                if ($id==$this->param['ctv_panorama']) echo 'selected="selected"';
                            echo '>'.$p['descrizione'].($p['stato']=='provvisorio'?' (p)':'').'</option>';
                        }
                    echo '</select>';

                echo '</div>';

            echo '</div>';

            echo '<div style="display:inline-block;width:6%;vertical-align:top;box-sizing: border-box;padding: 10px;text-align:center;">';
                if ($this->param['ctv_panorama']!="" && $this->param['ctv_openType']=='link') {
                    echo '<img style="width:50px;height:50px;position:relative;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/copia.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCopiaPiano();" />';
                }
            echo '</div>';

            echo '<div style="display:inline-block;width:27%;vertical-align:top;box-sizing: border-box;padding: 10px;">';
                
                if ($this->param['ctv_panorama']!="" && $this->param['ctv_openType']=='struttura') {

                    echo '<div style="">';

                        echo '<div style="font-weight:bold;font-size:0.9em;">Variante:</div>';

                        echo '<select id="ctv_variante_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvChangeVariante(this.value);" ';
                        echo '>';
                            echo '<option value="">Seleziona una variante...</option>';
                            $this->struttura->drawSelectVar($this->param['ctv_variante']);
                        echo '</select>';

                    echo '</div>';

                }

            echo '</div>';

            echo '<div style="display:inline-block;width:35%;height:100%">';

                echo '<button style="position:relative;top:50%;transform: translateY(-30%);font-size:1.3em;margin-left:20px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvNavigator(\'analisi\');" >Analisi</button>';
                echo '<button style="position:relative;top:50%;transform: translateY(-30%);font-size:1.3em;margin-left:30px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvNavigator(\'struttura\');" >Struttura</button>';
                echo '<button style="position:relative;top:50%;transform: translateY(-30%);font-size:1.3em;margin-left:30px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvNavigator(\'link\');" >Link</button>';
                echo '<button style="position:relative;top:50%;transform: translateY(-30%);font-size:1.3em;margin-left:30px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvNavigator(\'ext\');" >Dati</button>';
                        
            echo '</div>';

        echo '</div>';

        if ($this->param['ctv_variante']=="" && $this->param['ctv_openType']=='struttura') return;

        echo '<div class="ctv_main_div">';

            switch ($this->param['ctv_openType']) {

                case 'analisi':
                    $this->drawAnalisi();
                break;

                case 'struttura':
                    $this->drawStruttura();
                break;

                case 'link':
                    $this->drawLink();
                break;

                case 'ext':
                    $this->drawExt();
                break;
            }

        echo '</div>';

    }

    function drawAnalisi() {

        if (!is_a($this->struttura,'centaStruttura')) return;

        $an=new centavosAnalisi($this->panorami[$this->param['ctv_panorama']],$this->galileo);

        echo '<div class="ctv_left_div" style="background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/background.png\');">';

            echo '<div style="height:11%;">';
                echo $this->struttura->drawHead('analisi');
            echo '</div>';

            echo '<div style="font-size:1.3em;height:5%;">Periodi:</div>';

            echo '<div style="height:84%;overflow:scroll;overflow-x:hidden;">';
                $an->draw($this->viewer);
            echo '</div>';

        echo '</div>';

        echo '<div id="ctv_right_div" class="ctv_right_div" style="" >';
            
        echo '</div>';

    }

    function drawStruttura() {

        if (!is_a($this->struttura,'centaStruttura')) return;

        echo '<div class="ctv_left_div" style="">';
           
            echo '<div style="height:12%;">';
                echo $this->struttura->drawHead('struttura');
            echo '</div>';

            echo '<div style="position:relative;font-size:1.3em;height:5%;">';
                echo 'Sezioni:';
                echo '<img style="width:20px;height:20px;position:relative;margin-left:15px;top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAddSezione();" />';
            echo '</div>';

            echo '<div style="height:83%;overflow:scroll;">';

                echo '<div style="width:90%;">'; 
                    echo $this->struttura->drawStructBody($this->param['ctv_variante'],'struttura');
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div class="ctv_right_div" style="" >';

            if ($this->param['ctv_sezione']!="") {
                echo $this->struttura->drawStructSection($this->param['ctv_sezione']);
            }
            
        echo '</div>';

    }

    function drawLink() {

        if (!is_a($this->struttura,'centaStruttura')) return;

        $lista=array();
        $link=array();

        //echo $this->param['ctv_reparto'].' '.$this->panorami[$this->param['ctv_panorama']]['data_i'].' '.$this->panorami[$this->param['ctv_panorama']]['data_f'];

        $a=array(
            "reparti"=>"'".$this->param['ctv_reparto']."'",
            "data_i"=>$this->panorami[$this->param['ctv_panorama']]['data_i'],
            "data_f"=>$this->panorami[$this->param['ctv_panorama']]['data_f'],
            "piano"=>$this->param['ctv_panorama'],
            "coll"=>""
        );

        $this->galileo->executeGeneric('centavos','getCollaboratori',$a,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                //i link potrebbero essere più di uno per periodo

                $row['nome']=iconv("ISO-8859-1", "UTF-8", $row['nome']);
                $row['cognome']=iconv("ISO-8859-1", "UTF-8", $row['cognome']);
                
                $lista[$row['ID_coll']]=$row;
                if ($row['ID_link']!=0) $link[$row['ID_coll']][$row['ID_link']]=$row;
            }
        }
        
        echo '<div class="ctv_left_div" style="">';

            echo '<div style="height:11%;">';
                echo $this->struttura->drawHead('link');
            echo '</div>';

            echo '<div style="font-size:1.3em;height:5%;">';
                echo '<span>Collaboratori:</span>';
                echo '<button style="margin-left:50px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvConsolidaLink(\''.$this->param['ctv_panorama'].'\');" >Consolida</button>';
            echo '</div>';

            echo '<div style="height:84%;overflow:scroll;">';

                foreach ($lista as $idcoll=>$c) {

                    echo '<div style="border:1px solid black;margin-top:3px;margin-bottom:3px;width:90%;min-height:40px;cursor:pointer;';
                        if ($idcoll==$this->param['ctv_linkoll']) echo 'background-color:#ffd2a4;';
                    echo '" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvOpenLink(\''.$idcoll.'\');" >';

                        echo '<div style="position:relative;">';
                            //if ($c['cod_operaio']!='') echo '('.$c['cod_operaio'].') ';
                            echo '<div style="display:inline-block;width:16%;font-size:smaller;">('.$idcoll.') </div>';
                            echo '<div style="display:inline-block;width:84%;font-weight:bold;">'.$c['cognome'].' '.$c['nome'].'</div>';
                        echo '</div>';

                        if (array_key_exists($idcoll,$link)) {

                            foreach($link[$idcoll] as $idlink=>$l) {

                                echo '<div style="margin-top:2px;" >';

                                    echo '<div style="display:inline-block;width:25%;font-size:smaller;text-align:center;font-weight:bold;">';
                                        echo $l['variante'];
                                    echo '</div>';

                                    echo '<div style="display:inline-block;width:75%;font-size:smaller;text-align:center;">';
                                        echo '<div style="display:inline-block;width:46%;">';
                                            echo mainFunc::gab_todata($l['dlink_i']);
                                        echo '</div>';
                                        echo '<div style="display:inline-block;width:8%;">';
                                            echo '<img style="width:80%;height: 10px;opacity:0.5;" src="http://'.SADDR.'/nebula/apps/tempo/img/blackarrowR.png" />';
                                        echo '</div>';
                                        echo '<div style="display:inline-block;width:46%;">';
                                            echo mainFunc::gab_todata($l['dlink_f']);
                                        echo '</div>';
                                    echo '</div>';

                                echo '</div>';

                            }
                        }

                    echo '</div>';

                }

            echo '</div>';

        echo '</div>';

        echo '<div class="ctv_right_div" style="" >';

            if ($this->param['ctv_linkoll']!="") {

                $link=new centavosLink($this->panorami[$this->param['ctv_panorama']],$this->param['ctv_linkoll'],$this->galileo);

                $link->draw();
            }

            //echo json_encode($this->galileo->getLog('query'));
            //echo json_encode($link);
            
        echo '</div>';
    }

    function drawExt() {

        if (!is_a($this->struttura,'centaStruttura')) return;

        echo '<div class="ctv_left_div" style="">';

            echo '<div style="height:12%;">';
                echo $this->struttura->drawHead('ext');
            echo '</div>';

            echo '<div style="font-size:1.3em;height:5%;text-align:center;">Dati</div>';

            echo '<div style="height:83%;overflow:scroll;overflow-x:hidden;">';

                echo $this->struttura->drawDatiExt();

            echo '</div>';

        echo '</div>';

        echo '<div id="ctv_right_div" class="ctv_right_div" style="" >';
            
        echo '</div>';

    }

    function customViewer() {

        //LOGGED è scritta in $this->viewer

        echo '<div style="height:12%;">';

            echo '<div style="display:inline-block;width:35%;vertical-align:top;box-sizing: border-box;padding: 10px;">';
                
                echo '<div style="margin-left:20px;">';

                    echo '<div style="font-weight:bold;font-size:0.9em;">Panorama:</div>';

                    echo '<select id="ctv_panorama_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.ctvChangePanorama(this.value);" ';
                    echo '>';
                        echo '<option value="">Seleziona un panorama...</option>';
                        foreach ($this->panorami as $id=>$p) {
                            echo '<option value="'.$id.'" ';
                                if ($id==$this->param['ctv_panorama']) echo 'selected="selected"';
                            echo '>'.$p['descrizione'].($p['stato']=='provvisorio'?' (p)':'').'</option>';
                        }
                    echo '</select>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div class="ctv_main_div">';

            $this->drawAnalisi();

        echo '</div>';


    }

}
?>