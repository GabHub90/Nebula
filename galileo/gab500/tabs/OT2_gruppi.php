<?php

class galileo_ot2_gruppi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_gruppi";
        $this->selectMap=array(
            "codice",
            "indice",
            "descrizione",
            "oggetti",
            "marca"
        );

        $this->default=array(
            "codice"=>"",
            "indice"=>"",
            "descrizione"=>"",
            "oggetti"=>"",
            "marca"=>""
        );

        $this->checkMap=array(
            "codice"=>array("NOTNULL"),
            "indice"=>array("NOTNULL"),
            "descrizione"=>array("NOTNULL"),
            "oggetti"=>array("NOTNULL"),
            "marca"=>array("NOTNULL")
        );


    }

    function evaluate($tipo){
    }
}

?>