<?php

class grent extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Grent';

        $this->suffix=array('VEN');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='VEN';

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
                    echo '<img style="width:40px;height:35px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/grent.png" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:30%;top:-5px;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Tipo:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<div>';
                            echo '<select id="'.$this->form_tag.'_tipoRent" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="tipoRent" onchange="" ';
                                if ($this->mappa['tipoRent']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                foreach ($this->mappa['tipoRent']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ($k==$this->mappa['tipoRent']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$k.' - '.$t.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';   
                        echo '<div id="js_chk_'.$this->form_tag.'_error_tipoRent" class="js_chk_'.$this->form_tag.'_error" style="font-size:0.9em;font-weight:bold;color:red;" ></div>';
                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;text-align:right;vertical-align:top;margin-top:6px;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            $this->draw_js_base();
        }; 
    }

}
?>