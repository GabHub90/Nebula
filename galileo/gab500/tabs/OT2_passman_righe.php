<?php

class galileo_ot2_passmanrighe extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_passman_righe";

        $this->selectMap=array(
            "telaio",
            "indice",
            "tipo"
        );

        $this->default=array(
            "telaio"=>"",
            "indice"=>"",
            "tipo"=>""
        );

        $this->checkMap=array(
            "telaio"=>array("NOTNULL"),
            "indice"=>array("NOTNULL"),
            "tipo"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            if ($this->actual['indice']=="") {
                $this->actual['indice']="###(SELECT max(indice) FROM OT2_passman WHERE telaio='".$this->actual['telaio']."')";
            }
        }
    }
}

?>