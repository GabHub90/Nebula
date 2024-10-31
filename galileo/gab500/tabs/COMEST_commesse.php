<?php

class comest_commesse extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_commesse";

        $this->selectMap=array(
            "rif",
            "versione",
            "targa",
            "telaio",
            "descrizione",
            "dms",
            "odl",
            "fornitore",
            "d_apertura",
            "utente_apertura",
            "d_annullo",
            "utente_annullo",
            "controllo",
            "utente_controllo",
            "d_controllo"
        );

        $this->increment="rif";

        $this->default=array(
            "versione"=>"",
            "targa"=>"",
            "telaio"=>"",
            "descrizione"=>"",
            "dms"=>"",
            "odl"=>"",
            "fornitore"=>"",
            "d_apertura"=>"",
            "utente_apertura"=>"",
            "d_annullo"=>"",
            "utente_annullo"=>"",
            "controllo"=>"",
            "utente_controllo"=>"",
            "d_controllo"=>""
        );

        $this->checkMap=array(
            "versione"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>