<?php

class storicoPratica {

    protected $info=array(
        "d_rif"=>"",
        "n_rif"=>"",
        "dms"=>"",
        "commesse"=>array(),
        "prenotazione"=>false,
        "preventivo"=>false
    );

    function __construct($arr) {
        
        foreach ($this->info as $k=>$v) {
            if (array_key_exists($k,$arr)) {
                $this->info[$k]=$arr[$k];
            }
        }
    }

    function add($c) {
        //aggiunge una commessa o la prenotazione

    }
    
}

?>