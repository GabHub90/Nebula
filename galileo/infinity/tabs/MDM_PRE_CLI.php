<?php

class infinity_mdmprecli extends galileoTab {

    function __construct() {

        $this->tabName="dba.mdm_pre_cli";

        $this->selectMap=array(         
            "anno",
            "id_cliente",
            "data_doc",
            "tipo_doc",
            "numero_doc",
            "id_riga",
            "deposito",
            "tipo_riga",
            "precodice",
            "articolo",
            "unita_misura",
            "codice_iva",
            "descr_articolo",
            "quantita",
            "cod_sconto_prod",
            "vu_lordo",
            "sconti_riga1",
            "sconti_riga2",
            "sconti_riga3",
            "vu_netto",
            "tipo_evasione",
            "perciva",
            "id_utente",
            "codice_carico",
            "famiglia"
        );
        
    }

    function evaluate($tipo){
    }
}

?>