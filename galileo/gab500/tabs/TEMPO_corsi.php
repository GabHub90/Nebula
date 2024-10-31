<?php

class tempo_corsi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_corsi";
        $this->selectMap=array(
            "ID_corso",
            "collaboratore",
            "sigla",
            "nota",
            "data_i",
            "data_f",
            "localita",
            "stato"
        );
        
    }

    function evaluate($tipo){
    }
}

?>