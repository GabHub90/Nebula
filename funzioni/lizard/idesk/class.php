<?php

class lizard extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Lizard';

        $this->suffix=array('RC');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='RC';

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

                echo '<div style="display:inline-block;width:10%;text-align:center;">';
                    echo '<img style="width:100px;height:50px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/lizard/img/lizard.png" />';
                echo '</div>';

                echo '<div style="display:inline-block;width:40%;text-align:left;vertical-align:top;">';
                    echo '<div style="position:relative;display:inline-block;font-weight:bold;font-size:1em;width:25%;vertical-align:top;">Leads:</div>';
                    echo '<div style="position:relative;display:inline-block;width:75%;vertical-align:top;">';

                        echo '<div>';
                            echo '<select id="'.$this->form_tag.'_liz_function" style="font-size:1.4em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="liz_function">';
                                foreach ($this->mappa['liz_function']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ($k==$this->mappa['liz_function']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$t.'</option>';
                                }
                            echo '</select>';

                            //echo json_encode($this->mappa);

                        echo '</div>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_liz_function" class="js_chk_'.$this->form_tag.'_error"></div>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;text-align:right;vertical-align:top;margin-top:6px;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            /*echo '<input id="ribbon_tpo_macroreparto" type="'.$this->mappa['tpo_macroreparto']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_macroreparto']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_macroreparto" />';
            echo '<input id="ribbon_tpo_today" type="'.$this->mappa['tpo_today']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_today']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_today" />';
            echo '<input id="ribbon_tpo_coll" type="'.$this->mappa['tpo_coll']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['tpo_coll']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="tpo_coll" />';
            */
            
            $this->draw_js_base();
        }; 
    }

}
?>