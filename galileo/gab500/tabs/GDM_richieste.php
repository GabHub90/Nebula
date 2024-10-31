<?php

class gdm_richieste extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GDM_richieste";

        $this->selectMap=array(
            "id",
            "statoRi",
            "dataRi",
            "idTelaio",
            "nomeCliente",
            "targa",
            "tipoVeicolo",
            "numPratica",
            "dms"
        );

        //$this->increment="id";

        $this->default=array(
            "id"=>"",
            "statoRi"=>"",
            "dataRi"=>"",
            "idTelaio"=>"",
            "nomeCliente"=>"",
            "targa"=>"",
            "tipoVeicolo"=>"",
            "numPratica"=>"",
            "dms"=>""
        );

        $this->checkMap=array(
            "id"=>array("NOTNULL"),
            "statoRi"=>array("NOTNULL"),
            "dataRi"=>array("NOTNULL"),
            "idTelaio"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>