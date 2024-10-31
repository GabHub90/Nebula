<?php

class qCheck extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Qcheck';

        $this->suffix=array('RC','RT','RS','ASS');
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe
        //19.02.2021 essendo le differenze minime tra utenti viene tutto svolto da RT
        $val='RT';

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {
        };

        $this->cls['nebulaJs']=function() {

            echo 'window._js_chk_'.$this->form_tag.'.preCheck=function() {';
                echo <<<JS
                $('#ribbon_qc_check').val('');
                window._nebulaApp.ribbonExecute();
JS;
            echo '};';
            
        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:30%;height:70px;">';

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
                    echo '<div id="js_chk_'.$this->form_tag.'_error_officina" class="chekko_error js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;height:70px;vertical-align:top;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;top:50%;margin-top:-20px;" onclick="window._js_chk_'.$this->form_tag.'.preCheck();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_qc_openType" type="'.$this->mappa['qc_openType']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['qc_openType']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="qc_openType" />';
            echo '<input id="ribbon_qc_today" type="'.$this->mappa['qc_today']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['qc_today']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="qc_today" />';
            echo '<input id="ribbon_qc_check" type="'.$this->mappa['qc_check']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['qc_check']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="qc_check" />';
            
            /*
            echo '<div>';
                echo json_encode($this->mappa);
            echo '</div>';
            */

            $this->draw_js_base();
        }; 
    }

}
?>