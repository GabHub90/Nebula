<?php

class infinity_mdmcli_inc_operai extends galileoTab {

    function __construct() {

        $this->tabName="dba.mdm_cli_inc_operai";

        $this->selectMap=array(
            "id_ordine",
            "anno",
            "id_cliente",
            "data_doc",
            "tipo_doc",
            "numero_doc",
            "id_riga",
            "id_inconveniente",
            "matricola",
            "data_inizio",
            "ora_inizio",
            "data_fine",
            "ora_fine",
            "tempo_calcolato",
            "causale",
            "note"
        );

        $this->default=array(
            "anno"=>"",
            "id_cliente"=>"",
            "data_doc"=>"",
            "tipo_doc"=>"",
            "numero_doc"=>"",
            "id_riga"=>"",
            "id_inconveniente"=>"NULL",
            "matricola"=>"",
            "data_inizio"=>date('Ymd'),
            "ora_inizio"=>date("H:i:s"),
            "data_fine"=>"NULL",
            "ora_fine"=>"NULL",
            "tempo_calcolato"=>'0.00',
            "causale"=>"NULL",
            "tempo_fatturato_man"=>'0.00',
            "tempo_fatturato"=>'0.00',
            "id_utente"=>'57',
            "data_modifica"=>date("Y-m-d H:i:s"),
            "costo_orario"=>"",
            "id_qualifica"=>"",
            "id_squadra"=>"",
            "note"=>"",
            "flag_ins"=>"M"
        );

        $this->checkMap=array(
            "anno"=>array('NOTNULL'),
            "id_cliente"=>array('NOTNULL'),
            "data_doc"=>array('NOTNULL'),
            "tipo_doc"=>array('NOTNULL'),
            "numero_doc"=>array('NOTNULL'),
            "id_riga"=>array('NOTNULL'),
            "matricola"=>array('NOTNULL'),
            "data_inizio"=>array('NOTNULL'),
            "ora_inizio"=>array('NOTNULL'),
            "tempo_calcolato"=>array('NOTNULL'),
            "tempo_fatturato_man"=>array('NOTNULL'),
            "tempo_fatturato"=>array('NOTNULL'),
            "id_utente"=>array('NOTNULL'),
            "data_modifica"=>array('NOTNULL'),
            "costo_orario"=>array('NOTNULL'),
            "id_qualifica"=>array('NOTNULL'),
            "id_squadra"=>array('NOTNULL'),
            "flag_ins"=>array('NOTNULL')
        );  
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            $this->actual['costo_orario']="###(SELECT costo_orario FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
            $this->actual['id_qualifica']="###(SELECT qualifica FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
            $this->actual['id_squadra']="###(SELECT id_squadra FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
        }
    }
}

?>