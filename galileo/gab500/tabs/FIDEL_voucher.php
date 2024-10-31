<?php

class fidel_voucher extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.FIDEL_voucher";

        $this->selectMap=array(
            "ID",
            "tag",
            "dms",
            "utente",
            "creazione",
            "template",
            "stato",
            "chiusura",
            "utente_chiusura",
            "dms_chiusura",
            "odl_chiusura",
            "titolo",
            "offerta",
            "nota",
            "scadenza",
            "ben1",
            "ben2"
        );

        $this->increment="ID";

        $this->default=array(
            "tag"=>"",
            "dms"=>"",
            "utente"=>"",
            "creazione"=>"",
            "template"=>"",
            "stato"=>"",
            "chiusura"=>"",
            "utente_chiusura"=>"",
            "dms_chiusura"=>"",
            "odl_chiusura"=>"",
            "titolo"=>"",
            "offerta"=>"",
            "nota"=>"",
            "scadenza"=>"",
            "ben1"=>"",
            "ben2"=>""
        );
        
        $this->checkMap=array(
            "tag"=>array("NOTNULL"),
            "utente"=>array("NOTNULL"),
            "creazione"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "titolo"=>array("NOTNULL"),
            "offerta"=>array("NOTNULL"),
            "scadenza"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>