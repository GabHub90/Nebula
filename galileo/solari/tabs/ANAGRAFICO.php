<?php

class solari_anagrafico extends galileoTab {

    function __construct() {

        $this->tabName="[dbstart].[dbo].ANAGRAFICO";
        $this->selectMap=array(
            "ID",
            "MATRICOLA",
            "COGNOME",
            "NOME",
            "CODFISC"
        );
        
    }

    function evaluate($tipo){
    }
}

?>