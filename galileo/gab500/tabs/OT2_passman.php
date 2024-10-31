<?php

class galileo_ot2_passman extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_passman";

        $this->selectMap=array(
            "telaio",
            "indice",
            "obj",
            "note"
        );

        $this->default=array(
            "telaio"=>"",
            "indice"=>"",
            "obj"=>"",
            "note"=>""
        );

        $this->checkMap=array(
            "telaio"=>array("NOTNULL"),
            "indice"=>array("NOTNULL"),
            "obj"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            $this->actual['indice']="###(SELECT isnull(max(indice),0)+1 FROM ".$this->tabName." WHERE telaio='".$this->actual['telaio']."')";
        }
    }
}

?>