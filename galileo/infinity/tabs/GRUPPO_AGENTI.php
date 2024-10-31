<?php

class infinity_gruppoagenti extends galileoTab {

    function __construct() {

        $this->tabName="dba.gruppo_agenti";

        $this->selectMap=array(         
            "id_gruppo_agenti",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>