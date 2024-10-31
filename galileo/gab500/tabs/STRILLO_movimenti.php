<?php

class strillo_movimenti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.STRILLO_movimenti";
        $this->selectMap=array(
            "ID",
            "rif_dms",
            "dms",
            "d_fatt",
            "cassa",
            "chiusura",
            "utente",
            "d_reg",
            "rettifica",
            "d_rett",
            "reparto",
            "num_fatt",
            "intest_ragsoc",
            "desc_movimento"
        );

        $this->increment="ID";

        $this->default=array(
            "rif_dms"=>"",
            "dms"=>"",
            "d_fatt"=>"",
            "cassa"=>"",
            "chiusura"=>"",
            "utente"=>"",
            "d_reg"=>"",
            "rettifica"=>"",
            "d_rett"=>"",
            "reparto"=>"",
            "num_fatt"=>"",
            "intest_ragsoc"=>"",
            "desc_movimento"=>""
        );

        $this->checkMap=array(
            "rif_dms"=>array("NOTNULL"),
            "dms"=>array("NOTNULL"),
            "d_fatt"=>array("NOTNULL"),
            "cassa"=>array("NOTNULL"),
            "chiusura"=>array("NOTNULL"),
            "utente"=>array("NOTNULL"),
            "d_reg"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>