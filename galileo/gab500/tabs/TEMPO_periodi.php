<?php

class tempo_periodi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_periodi2";
        $this->selectMap=array(
            "ID",
            "coll",
            "tipo",
            "data_i",
            "data_f",
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
            "data_i"=>"",
            "data_f"=>"",
            "utente_inserimento"=>"NULL",
            "utente_modifica"=>"NULL",
            "utente_conferma"=>"NULL",
            "dat_inserimento"=>date('Ymd'),
            "dat_modifica"=>"NULL",
            "dat_conferma"=>"NULL"
        );

        $this->checkMap=array(
            "coll"=>array("NOTNULL"),
            "tipo"=>array("IN(F,M,C,)"),
            "data_i"=>array("NOTNULL"),
            "data_f"=>array("NOTNULL"),
        );
        
    }

    function evaluate($tipo){
    }
}

?>