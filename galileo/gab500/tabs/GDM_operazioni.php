<?php

class gdm_operazioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GDM_operazioni";

        $this->selectMap=array(
            "id",
            "idRi",
            "idMat",
            "destinazione",
            "statoOp",
            "origine",
            "storico",
            "dataOperazione"
        );

        //autoincrement
        //$this->increment="id";

        $this->default=array(
            "idRi"=>"",
            "idMat"=>"",
            "destinazione"=>"",
            "statoOp"=>"",
            "origine"=>"",
            "storico"=>"",
            "dataOperazione"=>""
        );

        $this->checkMap=array(
            "idRi"=>array("NOTNULL"),
            "idMat"=>array("NOTNULL"),
            "destinazione"=>array("NOTNULL"),
            "statoOp"=>array("NOTNULL"),
            "origine"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>