<?php

class maestro_app_livello extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_app_livello";
        $this->selectMap=array(
            "tag",
            "pos"
        );
        
    }

    function evaluate($tipo){
    }
}

?>