<?php

class galileo_ot2_criterimod extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_criteri_mod";
        $this->selectMap=array(
            "marca",
            "modello",
            "edit"
        );

        $this->default=array(
            "marca"=>"",
            "modello"=>"",
            "edit"=>""
        );

        $this->checkMap=array(
            "marca"=>array("NOTNULL"),
            "modello"=>array("NOTNULL"),
            "edit"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>