<?php

class gdm extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='GDM';

        $this->suffix=array('home','gdmp','gdms','gdmr');

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
        $this->cls['nebulaCss']=function() {
            echo '<style>.js_chk_'.$this->form_tag.'_error {';
            echo 'color:red;';
            echo 'font-weight:bold;';
            echo '}</style>';
        };

        $this->cls['nebulaJs']=function() {

            echo 'window._js_chk_'.$this->form_tag.'.kind_tt=function(val,id) {';
            echo <<<JS
                //se TRUE significa che non va bene
                //alert(val);
                if (val.length<3) return true;             
                return false;
            };
JS;

        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                if ($this->form_tag=='home') { 

                    echo '<div style="display:inline-block;width:30%;vertical-align:top;">';

                        echo '<div>';
                            echo '<label>Ricerca (più di 2 caratteri richiesti):</label>';
                        echo '</div>';
                        
                        echo '<div>';
                            echo '<input id="'.$this->form_tag.'_gdm_tt" type="text" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="gdm_tt" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                                if ($this->mappa['gdm_tt']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '/>';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_gdm_tt" class="js_chk_'.$this->form_tag.'_error"></div>';
                        
                    echo '</div>';

                    echo '<div style="display:inline-block;width:30%;;vertical-align:top;">';

                        echo '<div>';
                            echo '<label>Dms ricerca:</label>';
                        echo '</div>';
                        
                        echo '<div>';
                            echo '<select id="'.$this->form_tag.'_gdm_dmstt" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="gdm_dmstt" ';
                                if ($this->mappa['gdm_dmstt']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                foreach ($this->mappa['gdm_dmstt']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ($k==$this->mappa['gdm_dmstt']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$t.'</option>';
                                }

                            echo '</select>';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_gdm_dmstt" class="js_chk_'.$this->form_tag.'_error"></div>';
                    
                    echo '</div>';
                }

                echo '<div style="display:inline-block;width:30%;;vertical-align:top;">';

                    echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                        //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                        echo '<div class="divButton" style="width:80px;margin-top:13px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_gdm_telaio" type="'.$this->mappa['gdm_telaio']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_telaio']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_telaio" />';
            echo '<input id="ribbon_gdm_dms" type="'.$this->mappa['gdm_dms']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_dms']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_dms" />';
            echo '<input id="ribbon_gdm_pratica" type="'.$this->mappa['gdm_pratica']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_pratica']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_pratica" />';
            echo '<input id="ribbon_gdm_ambito" type="'.$this->mappa['gdm_ambito']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_ambito']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_ambito" />';
            if (isset($this->mappa['gdm_divo'])) {
                echo '<input id="ribbon_gdm_divo" type="'.$this->mappa['gdm_divo']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_divo']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_divo" />';
            }

            if ($this->form_tag!='home') {
                echo '<input id="ribbon_gdm_tt" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_tt']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_tt" />';
                echo '<input id="ribbon_gdm_dmstt" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['gdm_dmstt']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="gdm_dmstt" />';
            } 

            
            $this->draw_js_base();
        }; 
    }

}
?>