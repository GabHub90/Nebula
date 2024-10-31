<?php

class qcheck_moduli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_moduli";
        $this->selectMap=array(
            "ID",
            "titolo",
            "varianti",
            "auth"
        );
        
    }

    function evaluate($tipo){
    }
}

?>