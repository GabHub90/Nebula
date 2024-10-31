<?php

class quartet_subrep extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_subrep";
        $this->selectMap=array(
            "subrep",
            "reparto",
            "macroreparto",
            "descrizione",
            "stato",
            "concerto",
            "off_concerto",
            "infinity",
            "off_infinity"
        );
        
    }

    function evaluate($tipo){
    }
}

?>