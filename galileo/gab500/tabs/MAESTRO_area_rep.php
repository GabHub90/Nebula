<?php

class maestro_area_rep extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_area_rep";
        $this->selectMap=array(
            "ID",
            "area",
            "reparto",
            "off_concerto",
            "mag_concerto"
        );
        
    }

    function evaluate($tipo){
    }
}

?>