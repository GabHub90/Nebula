<?php

class calendario_chiusure extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CALENDARIO_chiusure";
        $this->selectMap=array(
            "ID",
            "anno",
            "mese",
            "giorno",
            "nome",
            "tipo",
            "reparto",
            "ora_i",
            "ora_f",
            "rep_exc"
        );
        
    }

    function evaluate($tipo){
    }
}

?>