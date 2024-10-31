<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class ribbonForm extends chekko {

    function __construct($tag) {

        parent::__construct($tag);
        
    }

    //###################################################
    //le funzioni $this->nebulaXXXXXXX() vengono caricate come CLOSURE dalle classi della funzione
    //attraverso la funzione set_closure() di chekko
    //###################################################

    function draw_css() {
        $this->nebulaCss();
    }

    function draw_js() {
        $this->nebulaJs();
    }

    function draw() {
        $this->nebulaDraw();
    }

}

?>