<?php

class tempo_permessi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_permessi2";
        $this->selectMap=array(
            "ID",
            "coll",
            "data",
            "ora_i",
            "ora_f",
            "tipo",
            "utente_inserimento",
            "utente_modifica",
            "utente_conferma",
            "dat_inserimento",
            "dat_modifica",
            "dat_conferma"
        );

        $this->increment="ID";

        $this->default=array(
            "coll"=>"",
            "tipo"=>"",
            "data"=>"",
            "ora_i"=>"",
            "ora_f"=>"",
            "utente_inserimento"=>"NULL",
            "utente_modifica"=>"NULL",
            "utente_conferma"=>"NULL",
            "dat_inserimento"=>date('Ymd'),
            "dat_modifica"=>"NULL",
            "dat_conferma"=>"NULL"
        );

        $this->checkMap=array(
            "coll"=>array("NOTNULL"),
            "tipo"=>array("IN(P,S,)"),
            "data"=>array("NOTNULL"),
            "ora_i"=>array("NOTNULL"),
            "ora_f"=>array("NOTNULL"),
        );
        
    }

    function evaluate($tipo){
    }
}

?>