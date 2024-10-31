<?php

include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_GEN_ANAUTE.php');

class galileoConcertoUtenti extends galileoOps {

    function __construct() {

        $this->tabelle['TB_GEN_ANAUTE']=new concerto_tbgenanaute();
        
    }

} 


?>