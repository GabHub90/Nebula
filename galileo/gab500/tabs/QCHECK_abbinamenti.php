<?php

class qcheck_abbinamenti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_abbinamenti";
        $this->selectMap=array(
            "ID",
            "reparto",
            "controllo",
            "versione",
            "auth",
            "data_i",
            "data_f",
            "titolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>