<?php

class infinity_ubicazioni extends galileoTab {

    function __construct() {

        $this->tabName="dba.UBICAZIONI";

        $this->selectMap=array(         
            "ubicazione",
            "descrizione",
            "indirizzo",
            "localita",
            "cap",
            "telefono1",
            "telefono2",
            "provincia",
            "tipo_ubicazione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>