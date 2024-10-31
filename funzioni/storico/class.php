<?php

class  nebulaStorico extends basilare {

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        $this->appTag='Storico';

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

            //echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/funzioni/storico/main.css?v='.time().'"); </style>';
        };

        $this->cls['nebulaJs']=function() {

            echo 'window._temprif="'.$this->form_tag.'";';

            echo 'window._js_chk_'.$this->form_tag.'.openGlobalLinker=function() {';

                echo 'var param={"contesto":"_js_chk_'.$this->form_tag.'"}';
            
                echo <<<JS

                $.ajax({
                    "url": 'http://'+location.host+'/nebula/core/veicolo/open_global_linker.php',
                    "async": true,
                    "cache": false,
                    "data": {"param": param},
                    "type": "POST",
                    "success": function(ret) {

                        $("#nebulaFunctionBody_"+window._temprif).html(ret);
                    }
                });
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.closeGlobalLinker=function() {';

                echo '$("#nebulaFunctionBody_'.$this->form_tag.'").html("");';

            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.execGlobalLinker=function(dms,telaio) {';

                echo '$("#'.$this->form_tag.'_sto_tt").val(telaio);';
                echo 'window._nebulaApp.ribbonExecute();';

            echo '};';

        };

        $this->cls['nebulaDraw']=function() {

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:8%;position:relative;vertical-align:top;">';
                    echo '<img style="width:45px;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/funzioni/storico/img/storico.png" />';
                echo '</div>';

                echo '<div style="display:inline-block;width:5%;position:relative;vertical-align:top;text-align:center;">';
                    echo '<img style="width:30px;margin-top:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/cerca.png" onclick="window._js_chk_'.$this->form_tag.'.openGlobalLinker();" />';
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Targa/Telaio:</label>';
                    echo '</div>';
                    
                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;">';
                            echo '<input id="'.$this->form_tag.'_sto_tt" type="'.$this->mappa['sto_tt']['prop']['tipo'].'" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sto_tt']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sto_tt" onkeydown="if(event.keyCode==13) window._nebulaApp.ribbonExecute();" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_sto_tt" class="js_chk_'.$this->form_tag.'_error"></div>';

                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:15%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Marca:</label>';
                    echo '</div>';
                    
                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;">';

                            echo '<select id="'.$this->form_tag.'_marca" style="width:90%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="sto_marca" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" >';
                            
                                echo '<option value="" >Marca...</option>';

                                foreach ($this->mappa['sto_marca']['prop']['options'] as $k=>$t) {
                                    echo '<option value="'.$k.'" ';
                                        if ($k==$this->mappa['sto_marca']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$k.' - '.$t.'</option>';
                                }
                            echo '</select>';

                        echo '</div>';   
                        echo '<div id="js_chk_'.$this->form_tag.'_error_sto_marca" class="js_chk_'.$this->form_tag.'_error"></div>';

                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:15%;position:relative;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Modello:</label>';
                    echo '</div>';
                    
                    echo '<div>';

                        echo '<div style="position:relative;vertical-align:top;">';
                            echo '<input id="'.$this->form_tag.'sto_modello" type="'.$this->mappa['sto_modello']['prop']['tipo'].'" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sto_modello']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sto_modello" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                        echo '</div>';

                        echo '<div id="js_chk_'.$this->form_tag.'_error_sto_modello" class="js_chk_'.$this->form_tag.'_error"></div>';

                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:15%;height:90%;text-align:right;vertical-align:top;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="width:80px;position:relative;top: 40%;transform: translate(0px, -50%)" onclick="window._nebulaApp.ribbonExecute();">carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_sto_ambito" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sto_ambito']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sto_ambito" />';
            echo '<input id="ribbon_officina" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['officina']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="officina" />';
    
            $this->draw_js_base();
        }; 
    }

}
?>