<?php

class comest_revisioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_revisioni";

        $this->selectMap=array(
            "commessa",
            "revisione",
            "d_creazione",
            "utente_creazione",
            "d_chiusura",
            "utente_chiusura",
            "preventivo",
            "riconsegna",
            "nota"
        );

        $this->default=array(
            "commessa"=>"",
            "revisione"=>"",
            "d_creazione"=>"",
            "utente_creazione"=>"",
            "d_chiusura"=>"",
            "utente_chiusura"=>"",
            "preventivo"=>"0",
            "riconsegna"=>"",
            "nota"=>""
        );

        $this->checkMap=array(
            "commessa"=>array("NOTNULL"),
            "revisione"=>array("NOTNULL"),
            "d_creazione"=>array("NOTNULL"),
            "utente_creazione"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>