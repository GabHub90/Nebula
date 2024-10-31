<?php

class nebulaPreventivo {

    //contiene i riferimenti (HEADER) del preventivo
    protected $actual=false;
    protected $lista=array();
    protected $pacchetti=array();

    protected $veicolo=false;
    protected $galileo;

    function __construct($galileo) {

        $this->galileo=$galileo;
        
    }

    function setHeader($arr) {

        //#############
        //VALIDAZIONE DELL'HEADER
        //#############

        $this->actual=array(
            "ID"=>"",
            "telaio"=>"",
            "targa"=>"",
            "ragsoc"=>"",
            "dms"=>"",
            "id_veicolo"=>"",
            "operatore"=>""
        );

        foreach ($this->actual as $k=>$v) {
            if (array_key_exists($k,$arr)) $this->actual[$k]=$arr[$k];
        }

        //inizializzazione dell'oggetto VEICOLO

        return true;
    }

    function readModuli() {

        if (!$this->actual) return false;

        //in base all'inizializzazione o meno del VEICOLO
        //lettura dei PACCHETTI
        //lettura della LISTA dei preventivi per questi riferimenti
    }

    function nuovoPreventivo($arr) {
        //apertura di un nuovo preventivo

        if (!$this->setHeader($arr)) return false;

        //apre il preventivo su DB e carica l'ID su actual
    }

}

?>