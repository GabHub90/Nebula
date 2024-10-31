<?php

class ticket extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='ticket';

        $this->suffix=array('GEN');
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        $val='GEN';

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
                    echo '<img style="width:50px;height:50px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/img/ermes.png" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:30%;top:-5px;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Categoria:</label>';
                    echo '</div>';
                    
                    echo '<div>';
                        echo '<div>';
                            echo '<select id="'.$this->form_tag.'_categoria" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="categoria" onchange="window._nebulaApp.ribbonExecute();" ';
                                if ($this->mappa['categoria']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                foreach ($this->mappa['categoria']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$t['reparto'].':'.$k.':'.$t['tipo'].'" ';
                                        if ( array_key_exists('disabled',$t) ) {
                                            echo ($t['disabled']?' disabled="disabled" ':'');
                                        }
                                        if ($k==$this->mappa['categoria']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$t['reparto'].' - '.$t['titolo'].'</option>';
                                }
                            echo '</select>';
                        echo '</div>';   
                        echo '<div id="js_chk_'.$this->form_tag.'_error_carb_reparto" class="js_chk_'.$this->form_tag.'_error" style="font-size:0.9em;font-weight:bold;color:red;" ></div>';
                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;text-align:right;vertical-align:top;margin-top:6px;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            

            $this->draw_js_base();
        }; 
    }

}
?>