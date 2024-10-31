<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/scontrillo/classi/cassa.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_scontrillo.php');

class scontrilloApp extends appBaseClass {

    protected $cassa;

    protected $log=array();

    function __construct($param,$galileo) {

        $obj=new galileoScontrillo();
        $nebulaDefault['strillo']=array("gab500",$obj);

        $galileo->setFunzioniDefault($nebulaDefault);

        parent::__construct($galileo);

        $this->loc='/nebula/apps/scontrillo/';

        $this->param['strillo_cassa']="";
        
        $this->loadParams($param);

        //TEST
        $row=array(
            "ID"=>"C1",
            "nome"=>"Info VW",
            "data_i"=>"20240101",
            "data_f"=>"21001231",
            "reparti"=>'{"OFF":["VWS","UPM"],"MAG":["VGM"]}',
            "fondocassa"=>300
        );
        //END TEST

        $this->cassa=new strilloCassa($row,$this->galileo);

    }

    function initClass() {
        return ' scontrilloCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function customDraw() {

        $this->cassa->draw();

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/storico/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

}

?>