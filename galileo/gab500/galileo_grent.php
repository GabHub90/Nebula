<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/GRENT_veicoli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GRENT_fasce.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GRENT_franchigie.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GRENT_pratiche.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/GRENT_reset.php');

class galileoGrent extends galileoOps {

    function __construct() {

        $this->tabelle['GRENT_veicoli']=new grent_veicoli();
        $this->tabelle['GRENT_fasce']=new grent_fasce();
        $this->tabelle['GRENT_franchigie']=new grent_franchigie();
        $this->tabelle['GRENT_pratiche']=new grent_pratiche();
        $this->tabelle['GRENT_reset']=new grent_reset();
    }

} 


?>