<?php

class iDesk extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='iDesk';

        $this->suffix=array('RS','RC','ASS','RIT');
        
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

            //echo "<div>Questa pagina sarà l'home page dell'utente tecnico di officina officina (RT,TEC)</div>";
            //echo "<div>Da qui si avrà accesso alle funzioni di pianificazione ed avanzamento del flusso di lavoro</div>";

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;">';

                echo '<div>';
                    echo '<label>Officina:</label>';
                echo '</div>';
                
                echo '<div>';
                    echo '<div>';
                        echo '<select id="'.$this->form_tag.'_officina" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="officina" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['officina']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            foreach ($this->mappa['officina']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ( array_key_exists('disabled',$t) ) {
                                        echo ($t['disabled']?' disabled="disabled" ':'');
                                    }
                                    if ($k==$this->mappa['officina']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['reparto'].' - '.$t['descrizione'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';   
                    echo '<div id="js_chk_'.$this->form_tag.'_error_officina" class="js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_idk_visuale" type="'.$this->mappa['idk_visuale']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_visuale']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_visuale" />';
            echo '<input id="ribbon_idk_rc" type="'.$this->mappa['idk_rc']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_rc']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_rc" />';
            echo '<input id="ribbon_idk_cliente" type="'.$this->mappa['idk_cliente']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_cliente']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_cliente" />';
            echo '<input id="ribbon_idk_marca" type="'.$this->mappa['idk_marca']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_marca']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_marca" />';
            echo '<input id="ribbon_idk_divo" type="'.$this->mappa['idk_divo']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_divo']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_divo" />';
            echo '<input id="ribbon_idk_desk" type="'.$this->mappa['idk_desk']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['idk_desk']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="idk_desk" />';

            $this->draw_js_base();
        }; 
    }

}
?>