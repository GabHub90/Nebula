<?php

class infinity_articoli extends galileoTab {

    function __construct() {

        $this->tabName="dba.articoli";

        $this->selectMap=array(         
            "precodice",
            "articolo",
            "categoria_sconto",
            "descrizione",
            "unita_misura",
            "codice_iva",
            "famiglia",
            "nomen_intra",
            "peso"
        );
        
    }

    function evaluate($tipo){
    }
}

?>