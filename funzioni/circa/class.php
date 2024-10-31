<?php

class  nebulaCirca extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Circa';

        $this->suffix=array('RC','RS','RT','ASS');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='RC';

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {

            echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/funzioni/circa/main.css?v='.time().'"); </style>';

        };

        $this->cls['nebulaJs']=function() {

        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:8%;position:relative;vertical-align:top;">';
                    echo '<img style="width:45px;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/circa.png" />';
                echo '</div>';

                /*echo '<div style="display:inline-block;width:5%;position:relative;vertical-align:top;text-align:center;">';
                    echo '<img style="width:30px;margin-top:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/cerca.png" />';
                echo '</div>';*/

                /*echo '<div style="display:inline-block;width:20%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Officina(dms):</label>';
                    echo '</div>';

                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;" >';
                            echo '<select id="'.$this->form_tag.'_officina" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="officina" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                                if ($this->mappa['officina']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                echo '<option value="banco" data-officinaconcerto="" >Generico</option>';

                                foreach ($this->mappa['officina']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ( array_key_exists('disabled',$t) ) {
                                            echo ($t['disabled']?' disabled="disabled" ':'');
                                        }
                                        if ($k==$this->mappa['officina']['prop']['default']) echo 'selected="selected" ';
                                    echo ' data-officinaconcerto="'.$t['concerto'].'" >'.$t['reparto'].' - '.$t['descrizione'].'</option>';
                                }
                            echo '</select>';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_officina" class="js_chk_'.$this->form_tag.'_error"></div>';
                    
                    echo '</div>';

                echo '</div>';*/

                echo '<div style="display:inline-block;width:20%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Targa/Telaio:</label>';
                    echo '</div>';
                    
                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;">';
                            echo '<input id="'.$this->form_tag.'cir_tt" type="'.$this->mappa['cir_tt']['prop']['tipo'].'" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['cir_tt']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="cir_tt" onkeydown="if(event.keyCode==13) window._nebulaApp.ribbonExecute();" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_cir_tt" class="js_chk_'.$this->form_tag.'_error"></div>';

                    echo '</div>'; 
                    
                echo '</div>';
                
                echo '<div style="display:inline-block;width:30%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Ragione Sociale:</label>';
                    echo '</div>';
                    
                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;">';
                            echo '<input id="'.$this->form_tag.'cir_ragsoc" type="'.$this->mappa['cir_ragsoc']['prop']['tipo'].'" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['cir_ragsoc']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="cir_ragsoc" onkeydown="if(event.keyCode==13) window._nebulaApp.ribbonExecute();" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_cir_ragsoc" class="js_chk_'.$this->form_tag.'_error"></div>';

                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;margin-top:15px;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            /////////////////////////////////////////////////////////////////////////////////
            echo '<input id="'.$this->form_tag.'_cir_officina" type="hidden" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="officina" value="'.$this->mappa['officina']['prop']['default'].'" />';
    
            $this->draw_js_base();
        }; 
    }

}
?>