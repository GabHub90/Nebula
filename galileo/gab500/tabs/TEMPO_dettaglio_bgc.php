<?php

class tempo_dettaglio_bgc extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_dettaglio_bgc";
        
        $this->selectMap=array(
            "reparto",
            "coll",
            "tag",
            "obj"
        );

        $this->default=array(
            "reparto"=>"",
            "coll"=>"",
            "tag"=>"",
            "obj"=>"{}"
        );

        $this->checkMap=array(
            "reparto"=>array("NOTNULL"),
            "coll"=>array("NOTNULL"),
            "tag"=>array("NOTNULL"),
            "obj"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>