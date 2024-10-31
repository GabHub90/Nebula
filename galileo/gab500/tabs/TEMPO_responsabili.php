<?php

class tempo_responsabili extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_responsabili";
        $this->selectMap=array(
            "gruppo",
            "tag",
            "stato",
            "tabPresenza",
            "tabSchemi",
            "tabAgenda",
            "tabBrogliaccio",
            "tabBadge",
            "evPeriodo",
            "evPermesso",
            "evExtra",
            "evSposta",
            "autorizza",
            "cancella",
            "vincola_utente",
            "sostituzioni"
        );
        
    }
    
    function evaluate($tipo){
    }
}

?>