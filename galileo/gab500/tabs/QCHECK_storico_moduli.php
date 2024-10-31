<?php

class qcheck_storico_moduli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QCHECK_storico_moduli";
        $this->selectMap=array(
            "ID_controllo",
            "modulo",
            "variante",
            "esecutore",
            "operatore",
            "rif_operatore",
            "d_modulo",
            "risposte",
            "punteggio",
            "stato"
        );

        $this->default=array(
            "ID_controllo"=>"",
            "modulo"=>"",
            "variante"=>"",
            "esecutore"=>"",
            "operatore"=>"",
            "rif_operatore"=>"NULL",
            "d_modulo"=>"",
            "risposte"=>"",
            "punteggio"=>"",
            "stato"=>"aperto"
        );

        $this->checkMap=array(
            "ID_controllo"=>array("NOTNULL"),
            "modulo"=>array("NOTNULL"),
            "variante"=>array("NOTNULL"),
            "operatore"=>array("NOTNULL"),
            "stato"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo) {

        /* var abbinamento={
        "m1":"tecnici",
        "m2":"#m1",
        "m3":"rc"
        }*/

        //se l'operatore è impostato come riferimento all'esecutore di un altro modulo allora deve essere impostato anche rif_operatore
        if ( array_key_exists('operatore',$this->actual) ) {
            if (substr ($this->actual['operatore'],0,1)=='#') {
                $this->actual['rif_operatore']=substr($this->actual['operatore'],2);
            }
        }
    }
}

?>