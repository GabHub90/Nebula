<?php

class comest_lavorazioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_lavorazioni";

        $this->selectMap=array(
            "commessa",
            "revisione",
            "ID",
            "zona",
            "titolo",
            "descrizione"
        );

        $this->default=array(
            "commessa"=>"",
            "revisione"=>"",
            "ID"=>"",
            "zona"=>"",
            "titolo"=>"",
            "descrizione"=>""
        );

        $this->checkMap=array(
            "commessa"=>array("NOTNULL"),
            "revisione"=>array("NOTNULL"),
            "ID"=>array("NOTNULL"),
            "titolo"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>