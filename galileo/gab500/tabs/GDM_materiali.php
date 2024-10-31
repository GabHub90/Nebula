<?php

class gdm_materiali extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GDM_materiali";

        $this->selectMap=array(
            "id",
            "tipologia",
            "locazione",
            "annotazioni",
            "nome",
            "descrizione",
            "dotASx",
            "dotADx",
            "dotPSx",
            "dotPDx",
            "marcaASx",
            "marcaADx",
            "marcaPSx",
            "marcaPDx",
            "usuraASx",
            "usuraADx",
            "usuraPSx",
            "usuraPDx",
            "dimeASx",
            "dimeADx",
            "dimePSx",
            "dimePDx",
            "compoGomme",
            "tipoGomme",
            "colore",
            "idTelaio",
            "proprietario",
            "isBusy",
            "isAnnullato",
            "isAnnullabile",
            "isFull",
            "dataCreazione"
        );

        //$this->increment="id";

        $this->default=array(
            "id"=>"",
            "tipologia"=>"",
            "locazione"=>"",
            "annotazioni"=>"",
            "nome"=>"",
            "descrizione"=>"",
            "dotASx"=>"",
            "dotADx"=>"",
            "dotPSx"=>"",
            "dotPDx"=>"",
            "marcaASx"=>"",
            "marcaADx"=>"",
            "marcaPSx"=>"",
            "marcaPDx"=>"",
            "usuraASx"=>"",
            "usuraADx"=>"",
            "usuraPSx"=>"",
            "usuraPDx"=>"",
            "dimeASx"=>"",
            "dimeADx"=>"",
            "dimePSx"=>"",
            "dimePDx"=>"",
            "compoGomme"=>"",
            "tipoGomme"=>"",
            "colore"=>"",
            "idTelaio"=>"",
            "proprietario"=>"",
            "isBusy"=>"False",
            "isAnnullato"=>"False",
            "isAnnullabile"=>"False",
            "isFull"=>"",
            "dataCreazione"=>""
        );

        /*$this->checkMap=array(
            "tipologia"=>array("NOTNULL"),
            "locazione"=>array("NOTNULL"),
            "dotASx"=>array("NOTNULL"),
            "dotADx"=>array("NOTNULL"),
            "dotPSx"=>array("NOTNULL"),
            "dotPDx"=>array("NOTNULL"),
            "marcaASx"=>array("NOTNULL"),
            "marcaADx"=>array("NOTNULL"),
            "marcaPSx"=>array("NOTNULL"),
            "marcaPDx"=>array("NOTNULL"),
            "usuraASx"=>array("NOTNULL"),
            "usuraADx"=>array("NOTNULL"),
            "usuraPSx"=>array("NOTNULL"),
            "usuraPDx"=>array("NOTNULL"),
            "dimeASx"=>array("NOTNULL"),
            "dimeADx"=>array("NOTNULL"),
            "dimePSx"=>array("NOTNULL"),
            "dimePDx"=>array("NOTNULL"),
            "compoGomme"=>array("NOTNULL"),
            "tipoGomme"=>array("NOTNULL"),
            "idTelaio"=>array("NOTNULL"),
            "proprietario"=>array("NOTNULL"),
            "dataCreazione"=>array("NOTNULL")
        );*/

        $this->checkMap=array(
            "id"=>array("NOTNULL"),
            "tipologia"=>array("NOTNULL"),
            "idTelaio"=>array("NOTNULL"),
            "proprietario"=>array("NOTNULL"),
            "dataCreazione"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>