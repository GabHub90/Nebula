<?php

class  nebulaCentavos extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Centavos';

        $this->suffix=array('TDD');
        
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
        };

        $this->cls['nebulaDraw']=function() {

            //echo "<div>Questa pagina sarà l'home page dell'utente tecnico di officina officina (RT,TEC)</div>";
            //echo "<div>Da qui si avrà accesso alle funzioni di pianificazione ed avanzamento del flusso di lavoro</div>";

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;">';

                echo '<div>';
                    echo '<label>Reparto:</label>';
                echo '</div>';
                
                echo '<div>';
                    echo '<div>';
                        echo '<select id="'.$this->form_tag.'_reparto" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="reparto" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvChangeRep(this.value);" ';
                            if ($this->mappa['reparto']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            foreach ($this->mappa['reparto']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ( array_key_exists('disabled',$t) ) {
                                        echo ($t['disabled']?' disabled="disabled" ':'');
                                    }
                                    if ($k==$this->mappa['reparto']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['reparto'].' - '.$t['descrizione'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';   
                    echo '<div id="js_chk_'.$this->form_tag.'_error_reparto" class="js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';

                echo '<input id="ribbon_ctv_openType" type="'.$this->mappa['ctv_openType']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['ctv_openType']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="ctv_openType" />';
                echo '<input id="ribbon_ctv_panorama" type="'.$this->mappa['ctv_panorama']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['ctv_panorama']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="ctv_panorama" />';
                echo '<input id="ribbon_ctv_variante" type="'.$this->mappa['ctv_variante']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['ctv_variante']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="ctv_variante" />';
                echo '<input id="ribbon_ctv_sezione" type="'.$this->mappa['ctv_sezione']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['ctv_sezione']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="ctv_sezione" />';
                echo '<input id="ribbon_ctv_linkoll" type="'.$this->mappa['ctv_linkoll']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['ctv_linkoll']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="ctv_linkoll" />';

            echo '</div>';

            $this->draw_js_base();
        }; 
    }

}
?>