<?php

class galileo_ot2_eventi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_eventi";

        $this->selectMap=array(
            "oggetto",
            "codice",
            "tipo",
            "min_qta",
            "chk"
        );

        $this->default=array(
            "oggetto"=>"",
            "codice"=>"",
            "tipo"=>"",
            "min_qta"=>"1.00",
            "chk"=>"0"
        );

        $this->checkMap=array(
            "oggetto"=>array("NOTNULL"),
            "codice"=>array("NOTNULL"),
            "tipo"=>array("NOTNULL"),
            "min_qta"=>array("NOTNULL"),
            "chk"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>