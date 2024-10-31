<?php

class qcheck_auth extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_auth";
        $this->selectMap=array(
            "controllo",
            "gruppo",
            "auth"
        );
        
    }

    function evaluate($tipo){
    }
}

?>