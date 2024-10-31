<?php

class  nebulaTempo extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Tempo';

        $this->suffix=array('RS','TDD');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='TDD';

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {
        };

        $this->cls['nebulaJs']=function() {

            echo 'window._js_chk_'.$this->form_tag.'.setMacrorep=function() {';
            
                echo "$('#ribbon_tpo_reparto').val('');";
                //echo "window._js_chk_".$this->form_tag.".js_chk();";
                echo "window._nebulaApp.ribbonExecute();";
        
            echo '};';
        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;height:70px;">';

                echo '<div>';
                    echo '<label>Macroreparto:</label>';
                echo '</div>';
                
                echo '<div>';
                    echo '<div>';
                        echo '<select id="ribbon_tpan_macroreparto" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="macroreparto" onchange="window._js_chk_'.$this->form_tag.'.setMacrorep();" ';
                            if ($this->mappa['macroreparto']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            foreach ($this->mappa['macroreparto']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ( array_key_exists('disabled',$t) ) {
                                        echo ($t['disabled']?' disabled="disabled" ':'');
                                    }
                                    if ($k==$this->mappa['macroreparto']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['tipo'].' - '.$t['descrizione'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';   
                    echo '<div id="js_chk_'.$this->form_tag.'_error_macroreparto" class="chekko_error js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;height:70px;vertical-align:top;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;top:50%;margin-top:-20px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';

                //////////////////////////////////////////////////
                echo '<input id="ribbon_tpan_today" type="'.$this->mappa['today']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['today']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="today" />';
                echo '<input id="ribbon_tpan_reparto" type="'.$this->mappa['reparto']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['reparto']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="reparto" />';
                echo '<input id="ribbon_tpan_coll" type="'.$this->mappa['coll']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['coll']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="coll" />';

            echo '</div>';

            $this->draw_js_base();
        }; 
    }

}
?>