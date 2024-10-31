<?php

class comest_versioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_versioni";

        $this->selectMap=array(
            "versione",
            "inizio"
        );
        
    }

    function evaluate($tipo){
    }
}

?>