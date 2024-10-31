<?php

include_once($_SERVER['DOCUMENT_ROOT']."/nebula/apps/sthor/classi/gommaio.php");

class sthorApp extends appBaseClass {

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/sthor/';
       
        $this->loadParams($param);

    }

    function initClass() {
        return ' sthorCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        $gommaio=new Gommaio('sthor');

       echo '<div style="width:500px;height:600px;border:2px solid black;padding:4px;box-sizing:border-box;" >';

        $gommaio->gommaioInit();

        $gommaio->draw();

       echo '</div>';

    }

}
?>