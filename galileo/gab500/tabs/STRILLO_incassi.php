<?php

class strillo_incassi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.STRILLO_incassi";
        $this->selectMap=array(
            "movimento",
            "pos",
            "importo",
            "incasso"
        );

        $this->default=array(
            "movimento"=>"",
            "pos"=>"",
            "importo"=>"",
            "incasso"=>""
        );

        $this->checkMap=array(
            "movimento"=>array("NOTNULL"),
            "pos"=>array("NOTNULL"),
            "importo"=>array("NOTNULL"),
            "incasso"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>