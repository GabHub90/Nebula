<?php

class fidel_offerte extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.FIDEL_offerte";
        $this->selectMap=array(
            "ID",
            "data_i",
            "data_f",
            "tipo",
            "marca",
            "titolo",
            "durata",
            "offerta",
            "nota"
        );
        
    }

    function evaluate($tipo){
    }
}

?>