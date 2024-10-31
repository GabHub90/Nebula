<?php

class infinity_off_marcature_int extends galileoTab {

    function __construct() {

        $this->tabName="dba.off_marcature_int";

        $this->selectMap=array(
            "id",
            "matricola",
            "cod_spesa",
            "data_inizio",
            "ora_inizio",
            "data_fine",
            "ora_fine",
            "tempo_calcolato",
            "note"
        );

        $this->default=array(
            "matricola"=>"",
            "cod_spesa"=>"",
            "cod_marca"=>"",
            "data_inizio"=>date('Ymd'),
            "ora_inizio"=>date("H:i:s"),
            "data_fine"=>"NULL",
            "ora_fine"=>"NULL",
            "tempo_calcolato"=>'0.00',
            "costo_orario"=>"",
            "id_qualifica"=>"",
            "id_squadra"=>"",
            "note"=>"",
            "id_utente"=>'57',
            "data_modifica"=>date("Y-m-d H:i:s")
        );

        $this->checkMap=array(
            "matricola"=>array('NOTNULL'),
            "cod_spesa"=>array('NOTNULL'),
            "cod_marca"=>array('NOTNULL'),
            "data_inizio"=>array('NOTNULL'),
            "ora_inizio"=>array('NOTNULL'),
            "tempo_calcolato"=>array('NOTNULL'),
            "costo_orario"=>array('NOTNULL'),
            "id_qualifica"=>array('NOTNULL'),
            "id_squadra"=>array('NOTNULL'),
            "id_utente"=>array('NOTNULL'),
            "data_modifica"=>array('NOTNULL')
        );  
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            $this->actual['cod_marca']="###(SELECT cod_marca FROM dba.o_carico WHERE codice='".$this->actual['cod_spesa']."')";
            $this->actual['costo_orario']="###(SELECT costo_orario FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
            $this->actual['id_qualifica']="###(SELECT qualifica FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
            $this->actual['id_squadra']="###(SELECT id_squadra FROM dba.o_operai WHERE matricola='".$this->actual['matricola']."')";
        }
    }


}

?>