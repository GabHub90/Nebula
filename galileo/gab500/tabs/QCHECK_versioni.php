<?php

class qcheck_versioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_versioni";
        $this->selectMap=array(
            "controllo",
            "versione",
            "descrizione",
            "moduli",
            "auth",
            "peso"
        );
        
    }

    function evaluate($tipo){
    }
}

?>