<?php

class grent_franchigie extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GRENT_franchigie";

        $this->selectMap=array(
            "fr_id",
            "importo",
            "perc",
            "limite",
            "flag_importo",
            "flag_perc",
            "flag_limite",
            "calc_max",
            "calc_min",
            "calc_indice"
        );

        $this->default=array(
        );

        $this->checkMap=array(
        );
        
    }

    function evaluate($tipo){
    }
}

?>