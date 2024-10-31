<?php

class alan_parametri extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.ALAN_parametri";
        $this->selectMap=array(
            "parametro",
            "valore"
        );

        //si considera di dover fare SOLO SELECT ed UPDATE

        $this->default=array(
            "valore"=>""
        );

        $this->checkMap=array(
            "valore"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>