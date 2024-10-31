<?php

class fidel_tipi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.FIDEL_tipi";
        $this->selectMap=array(
            "tag",
            "titolo",
            "stato"
        );
        
    }

    function evaluate($tipo){
    }
}

?>