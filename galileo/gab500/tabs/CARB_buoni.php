<?php

class carb_buoni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CARB_buoni";

        $this->selectMap=array(
            "ID",
            "veicolo",
            "importo",
            "reparto",
            "id_rich",
            "id_esec",
            "d_creazione",
            "d_stampa",
            "d_ris",
            "d_verifica",
            "stato",
            "nota",
            "gestione",
            "causale",
            "pieno",
            "mov_open",
            "id_ris",
            "flag_ris",
            "nota_ris",
            "d_annullo",
            "id_annullo",
            "nota_annullo",
            "verifica",
            "autz",
            "nota_forges",
            "id_forges",
            "tipo_carb",
            "telaio",
            "targa",
            "des_veicolo",
            "dms"
        );

        $this->increment="ID";

        $this->default=array(
            "veicolo"=>"",
            "importo"=>"0.00",
            "reparto"=>"",
            "id_rich"=>"",
            "id_esec"=>"",
            "d_creazione"=>"",
            "d_stampa"=>"",
            "d_ris"=>"",
            "d_verifica"=>"",
            "stato"=>"creato",
            "nota"=>"",
            "gestione"=>"",
            "causale"=>"",
            "pieno"=>"0",
            "mov_open"=>"1",
            "id_ris"=>"",
            "flag_ris"=>"",
            "nota_ris"=>"",
            "d_annullo"=>"",
            "id_annullo"=>"",
            "nota_annullo"=>"",
            "verifica"=>"0",
            "autz"=>"0",
            "nota_forges"=>"",
            "id_forges"=>"",
            "tipo_carb"=>"",
            "telaio"=>"",
            "targa"=>"",
            "des_veicolo"=>"",
            "dms"=>""
        );

        $this->checkMap=array(
            "importo"=>array("NOTNULL"),
            "reparto"=>array("NOTNULL"),
            "id_rich"=>array("NOTNULL"),
            "id_esec"=>array("NOTNULL"),
            "d_creazione"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "causale"=>array("NOTNULL"),
            "tipo_carb"=>array("NOTNULL"),
            "telaio"=>array("NOTNULL"),
            "dms"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>