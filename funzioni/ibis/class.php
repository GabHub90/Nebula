<?php

class ibis extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='IBIS';

        $this->suffix=array('RC');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='RC';

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

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:25%;text-align:right;vertical-align:top;">';

                    echo '<div>'; 
                        echo '<select id="'.$this->form_tag.'_macroreparto" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="macroreparto" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['macroreparto']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            foreach ($this->mappa['macroreparto']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ($k==$this->mappa['macroreparto']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t.'</option>';
                            }
                        echo '</select>';
                    echo '</div>';
                            
                    echo '<div id="js_chk_'.$this->form_tag.'_error_officina" class="chekko_error js_chk_'.$this->form_tag.'_error"></div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="position:relative;width:80px;top:2px;left:10px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////

            $this->draw_js_base();
        }; 
    }

}
?>