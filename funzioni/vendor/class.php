<?php

class vendor extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Vendor';

        $this->suffix=array('RV');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='RV';

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

            //echo "<div>Questa pagina sarà l'home page dell'utente tecnico di officina officina (RT,TEC)</div>";
            //echo "<div>Da qui si avrà accesso alle funzioni di pianificazione ed avanzamento del flusso di lavoro</div>";

            /*echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;">';

                echo '<div>';
                    echo '<label>Magazzino:</label>';
                echo '</div>';
                
                echo '<div>';
                    echo '<div>';
                        echo '<select id="'.$this->form_tag.'_magazzino" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="magazzino" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['magazzino']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            foreach ($this->mappa['magazzino']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ( array_key_exists('disabled',$t) ) {
                                        echo ($t['disabled']?' disabled="disabled" ':'');
                                    }
                                    if ($k==$this->mappa['magazzino']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['reparto'].' - '.$t['descrizione'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';   
                    echo '<div id="js_chk_'.$this->form_tag.'_error_magazzino" class="js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_tpo_macroreparto" type="'.$this->mappa['tpo_macroreparto']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_macroreparto']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_macroreparto" />';
            echo '<input id="ribbon_tpo_today" type="'.$this->mappa['tpo_today']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_today']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_today" />';
            echo '<input id="ribbon_tpo_coll" type="'.$this->mappa['tpo_coll']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_coll']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_coll" />';
            */
           
            $this->draw_js_base();
        }; 
    }

}
?>