<?php

class gdm extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='GDM';

        $this->suffix=array('isla');

        //#######################################################
        $this->contesto['ribbon']['gdm_ambito']=$this->ribbonID;
        //#######################################################
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe

        //#############################
        //È il nome della funzione, home,GDMP, GDMS
        $val=$this->ribbonID;
        //##########################################

        //di base viene impostato un uso RC
        //con gli ordini di lavoro che sono AFFIDATI a lui senza possibilità di vedere gli altri

        //Nel caso RS viene sbloccata la tendina di selezione RC a tutti.
        //e sbloccata anche la tendina tipo: (RC-RG).

        //Nel caso di macrogruppo RGV (Responsabile garanzia VGI) visualizzare ad uso RG

        //Nel caso reparto RIT (reparto IT) gruppo ITR (responsabile IT) equiparare ad RS

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {};

        $this->cls['nebulaJs']=function() {};

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;;vertical-align:top;">';

                    echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                        //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                        echo '<div class="divButton" style="width:80px;margin-top:13px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            
            echo '<input id="ribbon_gdm_ambito" type="'.$this->mappa['gdm_ambito']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_ambito']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_ambito" />';
            echo '<input id="ribbon_officina" type="'.$this->mappa['officina']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['officina']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="officina" />';

            
            $this->draw_js_base();
        }; 
    }

}
?>