<?php

require_once(DROOT.'/nebula/apps/grent/classi/grent_veicoli.php');

class grentApp extends appBaseClass {

    protected $veicoli;

    protected $gruppo="";
    protected $responsabili=array();

    protected $log=array();

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/grent/';

        $this->param['tipoRent']="";

        $this->loadParams($param);
        
        if (!$this->param['tipoRent'] || $this->param['tipoRent']=="") die('Tipo noleggio non definito!!!');

        $this->veicoli= new grentVeicoli($this->param['tipoRent'],$this->id->getLogged(),$this->galileo);
        $this->veicoli->build();

        if (isset($this->id)) {

            $this->gruppo=$this->id->getGruppo('',array('V','A','S','D'));

            //TEST
            $this->responsabili=array(
                "RV"=>array(
                    "manage"=>true,
                    "reset"=>true
                ),
                "TDD"=>array(
                    "manage"=>true,
                    "reset"=>true
                ),
                "ITR"=>array(
                   "manage"=>true,
                   "reset"=>true
                )
            );
            //END TEST

            if (array_key_exists($this->gruppo,$this->responsabili)) {
                $this->veicoli->setAuth($this->responsabili[$this->gruppo]);
            }
        }
    }

    function initClass() {
        return ' grentCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function customDraw() {

        /*echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/core/storico.js?v='.time().'"></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaStorico=new nebulaStorico();';
        echo '</script>';*/

        echo '<div style="position:relative;display:inline-block;height:100%;width:40%;vertical-align:top;" >';

            echo '<div style="position:relative;height:5%;font-size:1.3em;font-weight:bold;text-align:center;">';
                echo '<div style="position:relative;margin-top:5px;" >';
                    if (array_key_exists($this->gruppo,$this->responsabili)) {
                        if ($this->responsabili[$this->gruppo]['manage']) {
                            echo '<img style="position:absolute;left:10px;top:5px;width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/add.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.addVei(\''.$this->param['tipoRent'].'\');" />';
                        }
                    }
                    echo 'Veicoli';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;height:95%;width:100%;overflow:scroll;overflow-x:hidden;" >';

                $this->veicoli->drawLista();

            echo '</div>';

        echo '</div>';

        echo '<div id="grent_main" style="position:relative;display:inline-block;height:100%;width:60%;vertical-align:top;box-sizing:border-box;padding:15px;" >';
            
        echo '</div>';

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/core/grent_form.js?v='.time().'"></script>';

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/grent/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

}

?>