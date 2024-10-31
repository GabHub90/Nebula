<?php

class maestro_reparti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_reparti";
        $this->selectMap=array(
            "tag",
            "tipo",
            "concerto",
            "infinity",
            "descrizione",
            "sede",
            "virtuale",
            "fore"
        );
        
    }

    function evaluate($tipo){
    }
}

?>