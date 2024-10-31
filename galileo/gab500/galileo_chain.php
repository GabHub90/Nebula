<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/CHAIN.php');

class galileoChain extends galileoOps {

    function __construct() {

        $this->tabelle['CHAIN']=new nebula_Chain();
    }

} 


?>