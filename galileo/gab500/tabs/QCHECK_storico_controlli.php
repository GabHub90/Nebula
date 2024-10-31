<?php

class qcheck_storico_controlli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_storico_controlli";
        $this->selectMap=array(
            "ID",
            "controllo",
            "versione",
            "reparto",
            "d_controllo",
            "esecutore",
            "chiave",
            "intestazione",
            "stato",
            "ID_abbinamento"
        );

        $this->increment="ID";

        $this->default=array(
            "controllo"=>"",
            "versione"=>"",
            "reparto"=>"",
            "d_controllo"=>date('Ymd'),
            "esecutore"=>"",
            "chiave"=>"",
            "intestazione"=>"",
            "stato"=>"aperto",
            "ID_abbinamento"=>""
        );

        $this->checkMap=array(
            "controllo"=>array("NOTNULL"),
            "versione"=>array("NOTNULL"),
            "reparto"=>array("NOTNULL"),
            "d_controllo"=>array("NOTNULL"),
            "esecutore"=>array("NOTNULL"),
            "chiave"=>array("NOTNULL"),
            "intestazione"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "ID_abbinamento"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>