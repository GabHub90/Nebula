<?php

class maestro_collaboratori extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_collaboratori";
        $this->selectMap=array(
            "ID",
            "nome",
            "cognome",
            "concerto",
            "stato",
            "cod_operaio",
            "tel_interno",
            "IDDIP",
            "IDMAT",
            "cod_operaio_infinity",
            "pw",
            "cellulare",
            "mail"
        );
        
    }

    function evaluate($tipo){
    }
}

?>