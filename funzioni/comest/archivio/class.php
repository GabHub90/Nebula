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

                echo '<div style="display:inline-block;width:5%;text-align:center;vertical-align:top;">';
                    echo '<img style="width:50px;height:50px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/comest.png" />';
                echo '</div>';

                /*echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="font-weight:bold;font-size:1.5em;" >';
                        echo 'Archivio';
                    echo '</div>';
                    
                echo '</div>';*/

                echo '<div style="display:inline-block;width:20%;;vertical-align:top;">';

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

                echo '<div style="display:inline-block;width:25%;;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Fornitore:</label>';
                    echo '</div>';
            
                    echo '<div>';
                        echo '<select id="'.$this->form_tag.'_fornitore" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_fornitore" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['comest_fornitore']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';
                            echo '<option value="" >Tutti...</option>';
                            foreach ($this->mappa['comest_fornitore']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.base64_encode('"ID":"'.$t['ID'].'"').'" ';
                                    if ( array_key_exists('disabled',$t) ) {
                                        echo ($t['disabled']?' disabled="disabled" ':'');
                                    }
                                    //if ($k==$this->mappa['officina']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['ragsoc'].'</option>';
                            }
                        echo '</select>';
                            
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_fornitore" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:13%;;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Apertura:</label>';
                    echo '</div>';
            
                    echo '<div>';
                        echo '<input id="'.$this->form_tag.'_comest_da" type="date" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_da" ';
                            if ($this->mappa['comest_da']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '/>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_da" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:13%;;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Chiusura:</label>';
                    echo '</div>';
            
                    echo '<div>';
                        echo '<input id="'.$this->form_tag.'_comest_a" type="date" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="comest_a" ';
                            if ($this->mappa['comest_a']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '/>';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_error_comest_a" class="js_chk_'.$this->form_tag.'_error" style="color:red;font-weight: bold;font-size: 0.9em;" ></div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:5%;;vertical-align:top;">';

                    echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                        //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                        echo '<div class="divButton" style="width:80px;margin-top:13px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_comest_commessa" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['comest_commessa']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="comest_commessa" />';
            
            $this->draw_js_base();
        }; 
    }

}
?>