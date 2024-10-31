<?php

class worksetApp extends appBaseClass {


    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/workshop/';

        $this->param['wst_reparto']="";

        $this->loadParams($param);
   
    }

    function initClass() {
        return ' workshopCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        echo '<iframe style="width:100%;height:100%" frameBorder="0" src="http://'.SADDR.'/nebula/frames/graffa/index.php?reparto='.$this->param['wst_reparto'].'"></iframe>';

    }

}
?>