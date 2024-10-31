<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/carb/classi/buono.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/carb/classi/wormhole.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

class carbApp extends appBaseClass {

    protected $elenco=array(
        "creato"=>array(),
        "dacompletare"=>array(),
        "daris"=>array()
    );

    protected $causali=array();

    protected $responsabili=array();
    protected $utente=false;

    protected $wh;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/carb/';

        $this->param['carb_reparto']="";
       
        $this->loadParams($param);

        if (!$this->param['carb_reparto'] || $this->param['carb_reparto']=="") die ('Reparto non definito!!!');

        $this->wh=new carbWH($this->param['carb_reparto'],$this->galileo);

        $a=array(
            "inizio"=>date('Ymd'),
            "fine"=>date('Ymd')
        );

        $this->wh->build($a);

        $this->galileo->executeSelect('carb','CARB_causali',"","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('carb');
            while ($row=$this->galileo->getFetch('carb',$fid)) {   
                $this->causali[$row['codice']]=$row;
            }
        }   

        $this->galileo->executeSelect('carb','CARB_buoni',"mov_open!=0","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('carb');
            while ($row=$this->galileo->getFetch('carb',$fid)) {
                if (array_key_exists($row['stato'],$this->elenco)) {
                    $this->elenco[$row['stato']][$row['ID']]=new carbBuono($this->galileo);
                    $this->elenco[$row['stato']][$row['ID']]->init($row);
                    $this->elenco[$row['stato']][$row['ID']]->setUtente($this->id->getCollID());
                    $this->elenco[$row['stato']][$row['ID']]->loadCausali($this->causali);
                }
            }
        }

        $this->galileo->executeGeneric('carb','getResponsabili',array(),'');

        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('carb');

            while($row=$this->galileo->getFetch('carb',$fid)) {
                $this->responsabili[$row['ID']]=$row;
            }
        }

        $this->utente=$this->id->getCollID();

    }

    function initClass() {
        return ' carbCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';
        //echo '<div>'.json_encode($this->elenco).'</div>';

        $divo=new Divo('carb','5%','94%',true);

        $divo->setBk('#e7a5f1');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.1em",
            "margin-left"=>"4px",
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

        echo '<div style="position:relative;display:inline-block;height:100%;width:50%;vertical-align:top;padding:3px;box-sizing:border-box;" >';

            ob_start();

                echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;">';
                    foreach ($this->elenco['creato'] as $id=>$o) {
                        $o->drawHead();
                    }
                echo '</div>';

            $divo->add_div('Creati','black',1,(count($this->elenco['creato'])>0?'R':'Y'),ob_get_clean(),0,$css);

            ob_start();
                
                echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;">';
                    foreach ($this->elenco['dacompletare'] as $id=>$o) {
                        $o->drawHead();
                    }
                echo '</div>';

            $divo->add_div('Da Completare','black',1,(count($this->elenco['dacompletare'])>0?'R':'Y'),ob_get_clean(),0,$css);

            ob_start();

                echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;">';
                    foreach ($this->elenco['daris'] as $id=>$o) {
                        $o->drawHead();
                    }
                echo '</div>';

            $divo->add_div('Da Risarcire','black',1,(count($this->elenco['daris'])>0?'R':'Y'),ob_get_clean(),0,$css);

            if ($this->utente) {
                if ($this->responsabili[$this->utente]['annulla']=='1') {

                    ob_start();

                    $this->drawStrumenti();

                    $divo->add_div('Strumenti','#0f45e1',0,'',ob_get_clean(),0,$css);
                }
            }

            $divo->build();

            $divo->draw();

        echo '</div>';

        ////////////////////////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;height:100%;width:50%;vertical-align:top;padding:3px;box-sizing:border-box;" >';

            echo '<div style="position:relative;height:5%;" >';
                
                echo '<div style="position:relative;display:inline-block;vertical-align:top;text-align:right;width:90%;">'; 
                    echo '<img style="position:relative;width:35px;height:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].buonoNew(\''.$this->param['carb_reparto'].'\',\''.$this->wh->getTodayDms(date('Ymd')).'\',\''.$this->id->getCollID().'\');" />';
                echo '</div>';

            echo '</div>';

            echo '<div id="carb_mainDiv" style="position:relative;width:100%;height:95%;display:none;" >';
                
                echo '<div style="position:relative;height:5%;" >';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;">'; 
                        echo '<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeMain();">Annulla Modifica Buono</button>';
                    echo '</div>';

                echo '</div>';

                echo '<div id="carb_mainDiv_body" style="position:relative;width:100%;height:95%;" ></div>';

            echo '</div>';

        echo '</div>';

    }

    function drawStrumenti() {

        echo '<div style="position:relative;margin-top:20px;" >';
            echo '<img style="width:30px;height:30px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/trash.png" />';
            echo '<span style="margin-left:10px;font-weight:bold;" >Annullamento Buono:</span>';
        echo '</div>';

        echo '<div style="position:relative;margin-top:20px;height:50px;border-bottom:2px solid black;" >';
            echo '<span>Numero:</span>';
            echo '<input id="carb_strumenti_annullo" style="margin-left:10px;width:250px;text-align:center;font-weight:bold;font-size:1.2em;" type="text" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].carbCercaAnnulla(\''.$this->utente.'\');" />';
            echo '<button style="margin-left:20px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].carbCercaAnnulla(\''.$this->utente.'\');" >Cerca</button>';
        echo '</div>';
    }

}
?>