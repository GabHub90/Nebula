<?php

class ibis extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='IBIS';

        $this->suffix=array('AMM');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='AMM';

        //di base viene impostato un uso RC
        //con gli ordini di lavoro che sono AFFIDATI a lui senza possibilità di vedere gli altri

        //Nel caso reparto RIT (reparto IT) gruppo ITR (responsabile IT) equiparare ad RS

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {
        };

        $this->cls['nebulaJs']=function() {
        };

        $this->cls['nebulaDraw']=function() {

            //////////////////////////////////////////////////

            $this->draw_js_base();
        }; 
    }

}
?>