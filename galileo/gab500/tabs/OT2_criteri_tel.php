<?php

class galileo_ot2_criteritel extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_criteri_tel";
        $this->selectMap=array(
            "telaio",
            "edit"
        );

        $this->default=array(
            "telaio"=>"",
            "edit"=>""
        );

        $this->checkMap=array(
            "telaio"=>array("NOTNULL"),
            "edit"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>