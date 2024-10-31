<?php

class galileo_ot2_ambiti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_ambiti";
        $this->selectMap=array(
            "ambito",
            "pos"
        );
    }

    function evaluate($tipo){
    }
}

?>