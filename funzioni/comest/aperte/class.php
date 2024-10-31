<?php

class comest extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Comest';

        $this->suffix=array('RAM','CO','INF');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='INF';

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
        $this->cls['nebulaCss']=function() {
        };

        $this->cls['nebulaJs']=function() {
        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:10%;text-align:center;vertical-align:top;">';
                    echo '<img style="width:50px;height:50px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/comest.png" />';
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;vertical-align:top;">';

                    echo '<div style="font-weight:bold;font-size:1.5em;" >';
                        echo 'Commesse Aperte';
                    echo '</div>';
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:40%;;vertical-align:top;">';

                    echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                        //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                        echo '<div class="divButton" style="width:80px;margin-top:13px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_comest_commessa" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_commessa']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_commessa" />';
            echo '<input id="ribbon_comest_tt" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_tt']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_tt" />';
            
            $this->draw_js_base();
        }; 
    }

}
?>