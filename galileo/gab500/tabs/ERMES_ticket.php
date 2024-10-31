<?php

class ermes_ticket extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.ERMES_ticket";

        $this->selectMap=array(
            "ID",
            "categoria",
            "reparto",
            "des_reparto",
            "creatore",
            "d_creazione",
            "d_chiusura",
            "mittente",
            "gestore",
            "urgenza",
            "stato",
            "scadenza",
            "padre",
            "react",
            "nota"
        );

        $this->default=array(
            "ID"=>"",
            "categoria"=>"",
            "reparto"=>"",
            "des_reparto"=>"",
            "creatore"=>"",
            "d_creazione"=>"",
            "d_chiusura"=>"",
            "mittente"=>'',
            "gestore"=>"",
            "urgenza"=>0,
            "stato"=>"attesa",
            "scadenza"=>"",
            "padre"=>0,
            "react"=>"",
            "nota"=>""
        );

        $this->checkMap=array(
            "ID"=>array("NOTNULL"),
            "categoria"=>array("NOTNULL"),
            "reparto"=>array("NOTNULL"),
            "creatore"=>array("NOTNULL"),
            "d_creazione"=>array("NOTNULL"),
            "mittente"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "scadenza"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>