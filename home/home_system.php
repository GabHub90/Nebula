<?php

class homeSystem extends nebulaSystem {

    function __construct($tag,$contesto,$versione,$galileo) {

        parent::__construct($tag,$contesto,$versione,$galileo);

        if ($this->sistema=='home') $this->menuTag='Overview';
        else $this->menuTag=$this->sistema;

        $this->menuTagColor="white";

        //$this->setBorderibbon('http://'.SADDR.'/nebula/main/img/bordo_div2.png');
        $this->setRibbonclass('nebulaNoBorderRibbon');
    }
}

?>