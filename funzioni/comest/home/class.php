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

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Comm.Esterna:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<input id="'.$this->form_tag.'_comest_commessa" type="text" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_commessa" ';
                            if ($this->mappa['comest_commessa']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '/>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_commessa" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:20%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Targa/Telaio:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<input id="'.$this->form_tag.'_comest_tt" type="text" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_tt" ';
                            if ($this->mappa['comest_tt']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '/>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_tt" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Odl:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<input id="'.$this->form_tag.'_comest_odltt" type="text" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_odltt" ';
                            if ($this->mappa['comest_odltt']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '/>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_odltt" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:12%;;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Dms ricerca:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<select id="'.$this->form_tag.'_comest_dmstt" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_dmstt" ';
                            if ($this->mappa['comest_dmstt']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';

                            foreach ($this->mappa['comest_dmstt']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ($k==$this->mappa['comest_dmstt']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t.'</option>';
                            }

                        echo '</select>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_dmstt" class="js_chk_'.$this->form_tag.'_error"></div>';
                
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;;vertical-align:top;">';

                    echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                        //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                        echo '<div class="divButton" style="width:80px;margin-top:13px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_comest_telaio" type="'.$this->mappa['comest_telaio']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_telaio']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_telaio" />';
            echo '<input id="ribbon_comest_targa" type="'.$this->mappa['comest_targa']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_targa']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_targa" />';
            echo '<input id="ribbon_comest_desc" type="'.$this->mappa['comest_desc']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_desc']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_desc" />';
            echo '<input id="ribbon_comest_dms" type="'.$this->mappa['comest_dms']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_dms']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_dms" />';
            echo '<input id="ribbon_comest_odl" type="'.$this->mappa['comest_odl']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_odl']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_odl" />';
            
            $this->draw_js_base();
        }; 
    }

}
?>