<?php

class  scontrillo extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Scontrillo';

        $this->suffix=array('INF');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='INF';

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {

            echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/funzioni/cassa/main.css?v='.time().'"); </style>';

        };

        $this->cls['nebulaJs']=function() {

        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:8%;position:relative;vertical-align:top;">';
                    echo '<img style="width:55px;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/scontrillo/img/scontrillo.png" />';
                echo '</div>';

                echo '<div style="display:inline-block;width:25%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Cassa:</label>';
                    echo '</div>';

                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;" >';
                            echo '<select id="'.$this->form_tag.'_strillo_cassa" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="strillo_cassa" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                                if ($this->mappa['strillo_cassa']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                echo '<option value="">Seleziona una cassa...</option>';

                                foreach ($this->mappa['strillo_cassa']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ( array_key_exists('disabled',$t) ) {
                                            echo ($t['disabled']?' disabled="disabled" ':'');
                                        }
                                        if ($k==$this->mappa['strillo_cassa']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$t['tag'].'</option>';
                                }
                            echo '</select>';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_strillo_cassa" class="js_chk_'.$this->form_tag.'_error"></div>';
                    
                    echo '</div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;margin-top:15px;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            /////////////////////////////////////////////////////////////////////////////////
            //echo '<input id="'.$this->form_tag.'_cir_officina" type="hidden" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="officina" value="'.$this->mappa['officina']['prop']['default'].'" />';
    
            $this->draw_js_base();
        }; 
    }

}
?>