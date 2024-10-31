<?php

class infinity_tipiveicolotempario extends galileoTab {

    function __construct() {

        $this->tabName="dba.TIPI_VEICOLO_TEMPARIO";

        $this->selectMap=array(
            "codice",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>