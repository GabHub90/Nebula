<?php

class quartet_turni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_turni";
        $this->selectMap=array(
            "codice",
            "wd",
            "orari"
        );

        $this->default=array(
            "codice"=>"",
            "wd"=>"",
            "orari"=>""
        );

        $this->checkMap=array(
            "codice"=>array("NOTNULL"),
            "wd"=>array("NOTNULL"),
            "orari"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>