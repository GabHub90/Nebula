<?php

class infinity_mdmcli_lav extends galileoTab {

    function __construct() {

        $this->tabName="dba.mdm_cli_lav";

        $this->selectMap=array(
            
        );

        $this->default=array(
            "anno"=>"",
            "id_cliente"=>"",
            "data_doc"=>"",
            "tipo_doc"=>"",
            "numero_doc"=>"",
            "id_riga"=>"",
            "matricola_operaio"=>'NULL'
        );

        $this->checkMap=array(
            "anno"=>array('NOTNULL'),
            "id_cliente"=>array('NOTNULL'),
            "data_doc"=>array('NOTNULL'),
            "tipo_doc"=>array('NOTNULL'),
            "numero_doc"=>array('NOTNULL'),
            "id_riga"=>array('NOTNULL')
        );
        
    }

    function evaluate($tipo){
    }
}

?>