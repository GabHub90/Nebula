<?php

class galileo_ot2_oggettidefault extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_oggetti_default";
        $this->selectMap=array(
            "marca",
            "codice",
            "dt",
            "dkm",
            "mint",
            "maxt",
            "stet",
            "topt",
            "minkm",
            "maxkm",
            "stekm",
            "topkm",
            "pcx",
            "stato",
            "first_t",
            "first_km",
            "stat"
        );
    }

    function evaluate($tipo){
    }
}

?>