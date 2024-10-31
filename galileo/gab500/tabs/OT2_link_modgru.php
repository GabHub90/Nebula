<?php

class galileo_ot2_linkmodgru extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_link_modgru";

        $this->selectMap=array(
            "marca",
            "modello",
            "gruppo",
            "indice"
        );

        $this->default=array(
            "marca"=>"",
            "modello"=>"",
            "gruppo"=>"",
            "indice"=>""
        );

        $this->checkMap=array(
            "marca"=>array("NOTNULL"),
            "modello"=>array("NOTNULL"),
            "gruppo"=>array("NOTNULL"),
            "indice"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>