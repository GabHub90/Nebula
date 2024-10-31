<?php

class calendario_feste extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CALENDARIO_feste2";
        $this->selectMap=array(
            "ID",
            "giorno",
            "mese",
            "nome",
            "attivo",
            "reparto",
            "anno_i",
            "anno_f"
        );
        
    }

    function evaluate($tipo){
    }
}

?>