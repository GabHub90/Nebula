<?php

class comest_fornitori extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_fornitori";

        $this->selectMap=array(
            "ID",
            "ragsoc",
            "indirizzo",
            "mail",
            "tel1",
            "nota1",
            "tel2",
            "nota2"
        );
        
    }

    function evaluate($tipo){
    }
}

?>