<?php

class  nebulaWorkshop extends basilare {

    static $wspIdRif;

    static $wspAuth=array();

    function __construct($params,$galileo) {

        parent::__construct($params,$galileo);

        nebulaWorkshop::$wspIdRif=$this->id;

        $this->appTag='Workshop';

        $this->suffix=array('RT','RS','RC','ASS');

        //TEST
        nebulaWorkshop::$wspAuth=array(
            "RIT"=>array(
                "ITR"=>"generale"
            ),
            "VWS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "CAR"=>array(
                "RT"=>"tutto",
                "LAM"=>"personale",
                "VER"=>"personale",
                "vRT"=>"personale"
            ),
            "AUS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "PAS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "POS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "UPM"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RC"=>"generale"
            ),
            "PNP"=>array(
                "PRT"=>"generale",
                "PR"=>"personale"
            ),
            "PCS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "ACS"=>array(
                "RT"=>"tutto",
                "TEC"=>"personale",
                "RS"=>"generale",
                "RC"=>"generale",
                "ASS"=>"generale"
            ),
            "PRP"=>array(
                "TR"=>"generale"
            )
        );
        //END TEST
        
    }

    function setClass() {
        //contiene la logica con la quale viene scelto il file di inizializzazione della classe

        $val='RT';

        $this->classe=$val;
        $this->loadClass($val);
    }

    function setRibbonFunctions() {
        //inizializza le funzioni (closure) di chekko (ribbonForm)
        $this->cls['nebulaCss']=function() {
        };

        $this->cls['nebulaJs']=function() {

            echo 'window._js_chk_'.$this->form_tag.'.wspReset=function() {';

                echo <<<JS
                $('input[js_chk_'+this.form_tag+'_tipo="wsp_tecnico"]').val('');
                $('input[js_chk_'+this.form_tag+'_tipo="wsp_timb"]').val('');

                window._nebulaApp.ribbonExecute();
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.wspChangeVisuale=function(valore) {';

                echo <<<JS
                //var visuale=$('input[name="'+this.form_tag+'_visuale"]:checked').val();
                $('input[js_chk_'+this.form_tag+'_tipo="visuale"]').val(valore);
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.wspChangeRep=function(obj) {';

                echo <<<JS
                var visuale=$(obj).data('visuale');
                $('input[name="'+this.form_tag+'_visuale"]').css('disabled',true);

                if (visuale=='generale' || visuale=='tutto' ) {
                    $('input[id="'+this.form_tag+'_visuale_generale"]').css('disabled',false);
                    $('input[id="'+this.form_tag+'_visuale_generale"]').prop('checked',true);
                }
                if (visuale=='personale' || visuale=='tutto' ) {
                    $('input[id="'+this.form_tag+'_visuale_personale"]').css('disabled',false);
                    if (visuale=='personale') {
                        $('input[id="'+this.form_tag+'_visuale_personale"]').prop('checked',true);
                    }
                }

                this.js_chk();
JS;

            echo '};';
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
                            echo '<select id="'.$this->form_tag.'_officina" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="officina" onchange="window._js_chk_'.$this->form_tag.'.wspChangeRep(this);" ';
                                if ($this->mappa['officina']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';

                                $visuale="";

                                //verificare se esistono delle configurazioni super per l'utente
                                $tx=nebulaWorkshop::$wspIdRif->getGruppoRep('',array('A','D'));
                                if ($tx) {
                                    foreach ($tx as $tarx) {
                                        if (isset(nebulaWorkshop::$wspAuth[$tarx['reparto']][$tarx['gruppo']])) {
                                            if ($visuale=='') $visuale=nebulaWorkshop::$wspAuth[$tarx['reparto']][$tarx['gruppo']];
                                            elseif ($visuale!='all') $visuale=nebulaWorkshop::$wspAuth[$tarx['reparto']][$tarx['gruppo']];
                                        }
                                    }
                                }

                                foreach ($this->mappa['officina']['prop']['options'] as $k=>$t) {

                                    echo '<option value="'.$k.'" ';
                                        if ( array_key_exists('disabled',$t) ) {
                                            echo ($t['disabled']?' disabled="disabled" ':'');
                                        }

                                        $tempVis=($visuale!='')?$visuale:'';

                                        //se non c'è nessuna impostazione SUPER
                                        if ($tempVis=="") {

                                            $tx=nebulaWorkshop::$wspIdRif->getGruppoRep($k,array());

                                            if ($tx) {
                                                foreach ($tx as $tarx) {
                                                    //se il reparto-gruppo esiste tra quelli configurati per l'applicazione
                                                    if (isset(nebulaWorkshop::$wspAuth[$tarx['reparto']][$tarx['gruppo']])) {
                                                        $tempVis=nebulaWorkshop::$wspAuth[$tarx['reparto']][$tarx['gruppo']];
                                                    }
                                                }
                                            }
                                        }

                                        echo ' data-visuale="'.$tempVis.'" ';

                                        if ($k==$this->mappa['officina']['prop']['default']) {
                                            echo 'selected="selected" ';
                                            if($visuale=='') $visuale=$tempVis;
                                        }     

                                        
                                    echo '>'.$t['reparto'].' - '.$t['descrizione'].'</option>';
                                }
                            echo '</select>';
                        echo '</div>';   
                        echo '<div id="js_chk_'.$this->form_tag.'_error_officina" class="chekko_error js_chk_'.$this->form_tag.'_error"></div>';
                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:30%;">';

                    echo '<div display="vertical-align:top;" >';
                        echo '<label>Visualizzazione:</label>';
                    echo '</div>';
                    
                    echo '<div style="height:16px;">';

                        //if ($visuale=='tutto' || $visuale=='generale') {
                            echo '<input id="'.$this->form_tag.'_visuale_generale" name="'.$this->form_tag.'_visuale" type="radio" style="margin-left:10px;" value="generale" ';
                                //se dal ribbon viene ereditato il valore "generale" oppure l'utente ha l'abilitazione "generale"
                                if ($this->mappa['visuale']['prop']['default']=='generale' || $visuale=='generale' || $visuale=='tutto') echo 'checked';
                                if ($visuale!='generale' && $visuale!='tutto') echo 'disabled';
                            echo ' onclick="window._js_chk_'.$this->form_tag.'.wspChangeVisuale(this.value);" />';
                            echo '<span style="margin-left:5px;" >Generale</span>';
                        //}

                        //if ($visuale=='tutto' || $visuale=='personale') {
                            echo '<input id="'.$this->form_tag.'_visuale_personale" name="'.$this->form_tag.'_visuale" type="radio" style="margin-left:10px;" value="personale" ';
                                if ($this->mappa['visuale']['prop']['default']=='personale' || $visuale=='personale') echo 'checked';
                                if ($visuale!='personale' && $visuale!='tutto') echo 'disabled';
                            echo ' onclick="window._js_chk_'.$this->form_tag.'.wspChangeVisuale(this.value);" />';
                            echo '<span style="margin-left:5px;" >Personale</span>';
                        //}

                            echo '<input type="hidden" value="'.$visuale.'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="visuale" />';

                    echo '</div>'; 
                    
                echo '</div>';

                echo '<div style="display:inline-block;width:10%;text-align:right;vertical-align:top;">';
                    //echo '<div class="divButton" style="width:80px;" onclick="window._nebulaApp.ribbonExecute(\''.$this->form_tag.'\');">carica</div>';
                    echo '<div class="divButton" style="position:absolute;width:80px;top:50%;transform:translate(0%,-50%);" onclick="window._js_chk_'.$this->form_tag.'.wspReset();" >carica</div>';
                echo '</div>';
            
            echo '</div>';

            //////////////////////////////////////////////////
            echo '<input id="ribbon_wsp_tecnico" type="'.$this->mappa['wsp_tecnico']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['wsp_tecnico']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="wsp_tecnico" />';
            echo '<input id="ribbon_wsp_timb" type="'.$this->mappa['wsp_timb']['prop']['tipo'].'" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['wsp_timb']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="wsp_timb" />';
            //echo  $this->mappa['officina']['prop']['default'];
            $this->draw_js_base();
        }; 
    }

}
?>