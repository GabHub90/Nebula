<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class grentResetForm extends chekko {

    function __construct($tag) {

        parent::__construct($tag);
        
    }

    function draw_css() {

        echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/style.css?v='.time().'"); </style>';

    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.kind_kmreset=function(val,id) {';
            echo <<<JS
                var actual=$('#'+id).data('check');
                if (val<actual) return true;
                else return false;
JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.kind_valf=function(val,id) {';
            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS
                var vali=$('input[js_chk_'+tag+'_tipo="valore_i"]').val();
                if (val>vali) return true;
                else return false;
JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.pm=function(tipo,op) {';
            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS
                var val=$('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val();
                
                if (tipo=='coeff') {
                    if (op=='p') val=parseFloat(val)+0.1;
                    if (op=='m') val=parseFloat(val)-0.1;
                    if (val<1) val=1;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val.toFixed(1));
                }
                else if (tipo=='coeff_km') {
                    if (op=='p') val=parseFloat(val)+0.1;
                    if (op=='m') val=parseFloat(val)-0.1;
                    if (val>1) val=1;
                    if (val<0) val=0;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val.toFixed(1));
                }
                if (tipo=='incent') {
                    if (op=='p') val=parseInt(val)+1;
                    if (op=='m') val=parseInt(val)-1;
                    if (val<0) val=0;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val);
                }
                if (tipo=='cop_fisso') {
                    if (op=='p') val=parseFloat(val)+0.1;
                    if (op=='m') val=parseFloat(val)-0.1;
                    if (val>1) val=1;
                    if (val<0) val=0;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val.toFixed(1));
                }
                if (tipo=='sva_km') {
                    if (op=='p') val=parseFloat(val)+5000;
                    if (op=='m') val=parseFloat(val)-5000;
                    if (val<5000) val=5000;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val);
                }
                if (tipo=='sva_tempo') {
                    if (op=='p') val=parseFloat(val)+1;
                    if (op=='m') val=parseFloat(val)-1;
                    if (val<1) val=1;
                    $('input[js_chk_'+tag+'_tipo="'+tipo+'"]').val(val);
                }
JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.calcolaSva=function() {';
            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS
                var vali=$('input[js_chk_'+tag+'_tipo="valore_i"]').val();
                var valf=$('input[js_chk_'+tag+'_tipo="valore_f"]').val();
                var sva=parseInt(vali)-parseInt(valf);
                if (isNaN(sva)) sva=-1;
                $('input[js_chk_'+tag+'_tipo="sva"]').val((sva<0)?"":sva);

                this.js_chk();
JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.post_check=function() {';
            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS
                var fascia=$('select[js_chk_'+tag+'_tipo="fascia"]').val();
                var fr=$('select[js_chk_'+tag+'_tipo="franchigia"]').val();

                if (fascia!="") {
                    var obj=$.parseJSON(atob($('select[js_chk_'+tag+'_tipo="fascia"] option:selected').data('info')));
                    if (obj) {
                        $('#grent_reset_txt_kasko').html(obj.kasko);
                        $('#grent_reset_txt_manut').html(obj.manut);
                        $('#grent_reset_txt_gomme').html(obj.gomme);
                        $('#grent_reset_txt_ipt').html(obj.ipt);
                        $('#grent_reset_txt_bollo').html(obj.bollo);
                    }
                }

                if (fr!="") {
                    var obj=$.parseJSON(atob($('select[js_chk_'+tag+'_tipo="franchigia"] option:selected').data('info')));
                    if (obj) {
                        $('#grent_reset_txt_frimp').html(obj.importo);
                        $('#grent_reset_txt_frperc').html(obj.perc);
                        $('#grent_reset_txt_frlim').html(obj.limite);
                        $('#grent_reset_txt_frrimp').html(obj.flag_importo);
                        $('#grent_reset_txt_frrperc').html(obj.flag_perc);
                        $('#grent_reset_txt_frrlim').html(obj.flag_limite);
                        $('#grent_reset_txt_frmax').html(obj.calc_max);
                        $('#grent_reset_txt_frmin').html(obj.calc_min);
                        $('#grent_reset_txt_frk').html(parseFloat(obj.calc_indice).toFixed(1));
                    }
                }
JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.scrivi_proprietario=function() {';
            echo <<<JS

                this.expo['incent']=this.expo['incent']/100;

                //console.log(JSON.stringify(this.expo));

                if (!confirm('Vuoi salvare il RESET con '+this.expo.km_reset+' km (operazipne non annullabile)?')) return;

                $.ajax({
                    "url": 'http://'+location.host+'/nebula/apps/grent/core/newreset.php',
                    "async": true,
                    "cache": false,
                    "data": {"param": this.expo},
                    "type": "POST",
                    "success": function(ret) {

                        //console.log(ret);

                        window._nebulaApp.ribbonExecute();
                    }
                });
JS;
        echo '};';

    }

    function draw() {

        echo '<div style="position:relative;" class="grent_reset_form" >';

            echo '<div style="font-weight:bold;color:blue;">Impostare SEMPRE una svalutazione basata su 30.000 km e 12 mesi</div>';

            echo '<div style="position:relative;height:58px;">';

                echo '<input id="'.$this->form_tag.'_grent_id" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['grent_id']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="grent_id" />';
                echo '<input id="'.$this->form_tag.'_rif_vei" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['rif_vei']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="rif_vei" />';
                echo '<input id="'.$this->form_tag.'_dms" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['dms']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="dms" />';

                echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Chiusura</label>';
                    echo '</div>';
                    
                    echo '<div style="text-align:center;">';
                        echo '<input id="'.$this->form_tag.'_chiusura" type="checkbox" '.($this->mappa['chiusura']['prop']['default']==1?'checked':"").' onclick="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            echo ($this->mappa['chiusura']['prop']['disabled'])?'disabled':"";
                        echo ' />';
                        echo '<input id="'.$this->form_tag.'_chiusura" type="hidden" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['chiusura']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="chiusura" />';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_chiusura" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 
                        
                echo '</div>';

                echo '<div style="display:inline-block;width:20%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Km attuali:</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_km_reset" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" data-check="'.$this->mappa['km_reset']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="km_reset" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_km_reset" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 
                        
                echo '</div>';

                echo '<div style="display:inline-block;width:25%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Valore Iniziale:</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_valore_i" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['valore_i']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="valore_i" onchange="window._js_chk_'.$this->form_tag.'.calcolaSva();" />';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_valore_i" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 
                        
                echo '</div>';

                echo '<div style="display:inline-block;width:25%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Valore Finale:</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_valore_f" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['valore_f']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="valore_f" onchange="window._js_chk_'.$this->form_tag.'.calcolaSva();" />';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_valore_f" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 
                        
                echo '</div>';

                echo '<div style="display:inline-block;width:20%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label>Svalutazione:</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_sva" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" value="" js_chk_'.$this->form_tag.'_tipo="sva" disabled/>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_sva" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 
                        
                echo '</div>';

            echo '</div>';

            ////////////////////////////////////////////////////////

            echo '<div style="position:relative;margin-top:5px;height:48px;">';

                echo '<div style="display:inline-block;width:18%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Lim.Km</label>';
                    echo '</div>';

                    echo '<div style="text-align:center;">';
                        echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'sva_km\',\'m\');">-</span>';
                        echo '<input id="'.$this->form_tag.'_sva_km" style="width:70px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sva_km']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sva_km" disabled/>';
                        echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'sva_km\',\'p\');">+</span>';
                    echo '</div>';
                    echo '<div id="js_chk_'.$this->form_tag.'_error_sva_km" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    
                    /*echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_sva_km" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sva_km']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sva_km" onchange="window._js_chk_'.$this->form_tag.'.js_chk();"/>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_sva_km" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>';*/

                echo '</div>';
                
                echo '<div style="display:inline-block;width:18%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Lim.Mesi</label>';
                    echo '</div>';

                    echo '<div style="text-align:center;">';
                        echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'sva_tempo\',\'m\');">-</span>';
                        echo '<input id="'.$this->form_tag.'_sva_tempo" style="width:70px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sva_tempo']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sva_tempo" disabled/>';
                        echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'sva_tempo\',\'p\');">+</span>';
                    echo '</div>';
                    echo '<div id="js_chk_'.$this->form_tag.'_error_sva_tempo" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    
                    /*echo '<div style="">';
                        echo '<input id="'.$this->form_tag.'_sva_tempo" style="width:90%;text-align:center;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['sva_tempo']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="sva_tempo" onchange="window._js_chk_'.$this->form_tag.'.js_chk();"/>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_sva_tempo" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>';*/

                echo '</div>';

                echo '<div style="display:inline-block;width:15%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>K</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div style="text-align:center;">';
                            echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'coeff\',\'m\');">-</span>';
                            echo '<input id="'.$this->form_tag.'_coeff" style="width:40px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['coeff']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="coeff" disabled/>';
                            echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'coeff\',\'p\');">+</span>';
                        echo '</div>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_coeff" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:15%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>K_km</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div style="text-align:center;">';
                            echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'coeff_km\',\'m\');">-</span>';
                            echo '<input id="'.$this->form_tag.'_coeff_km" style="width:40px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['coeff_km']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="coeff_km" disabled/>';
                            echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'coeff_km\',\'p\');">+</span>';
                        echo '</div>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_coeff_km" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:15%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Inc. %</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div style="text-align:center;">';
                            echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'incent\',\'m\');">-</span>';
                            echo '<input id="'.$this->form_tag.'_incent" style="width:40px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['incent']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="incent" disabled/>';
                            echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'incent\',\'p\');">+</span>';
                        echo '</div>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_incent" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:17%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Cop.Fisso %</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div style="text-align:center;">';
                            echo '<span style="cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'cop_fisso\',\'m\');">-</span>';
                            echo '<input id="'.$this->form_tag.'_cop_fisso" style="width:40px;text-align:center;margin-left:10px;" type="text" class="js_chk_'.$this->form_tag.'" value="'.$this->mappa['cop_fisso']['prop']['default'].'" js_chk_'.$this->form_tag.'_tipo="cop_fisso" disabled/>';
                            echo '<span style="margin-left:10px;cursor:pointer;" onclick="window._js_chk_'.$this->form_tag.'.pm(\'cop_fisso\',\'p\');">+</span>';
                        echo '</div>';
                        echo '<div id="js_chk_'.$this->form_tag.'_error_cop_fisso" class="js_chk_'.$this->form_tag.'_error chekko_error"></div>';
                    echo '</div>'; 

                echo '</div>';

            echo '</div>';

            ////////////////////////////////////////////////////////

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div style="display:inline-block;width:40%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Fascia</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                       
                        echo '<select id="'.$this->form_tag.'_fascia" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="fascia" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                                if ($this->mappa['fascia']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';
                                foreach ($this->mappa['fascia']['prop']['options'] as $k=>$v) {
                                    echo '<option value="'.$k.'" ';
                                        echo 'data-info="'.base64_encode(json_encode($v)).'" ';
                                        if ($k==$this->mappa['fascia']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$k.' - '.$v['testo'].'</option>';
                                }
                        echo '</select>';
                       
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>KASKO</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_kasko" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Manut.</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_manut" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Gomme</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_gomme" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>IPT</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_ipt" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Bollo</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_bollo" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

            echo '</div>';

            ////////////////////////////////////////////////////////

            echo '<div style="position:relative;margin-top:15px;">';

                echo '<div style="display:inline-block;width:8%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Franch.</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                       
                        echo '<select id="'.$this->form_tag.'_franchigia" style="width:95%;font-size:1em;text-align:center;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="franchigia" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                                if ($this->mappa['franchigia']['prop']['disabled']) echo 'disabled="disabled"';
                            echo '>';
                                foreach ($this->mappa['franchigia']['prop']['options'] as $k=>$v) {
                                    echo '<option value="'.$k.'" ';
                                        echo 'data-info="'.base64_encode(json_encode($v)).'" ';
                                        if ($k==$this->mappa['franchigia']['prop']['default']) echo 'selected="selected" ';
                                    echo '>'.$k.'</option>';
                                }
                        echo '</select>';
                       
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Importo</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frimp" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:8%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>%</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frperc"style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Limite</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frlim" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Rid.Imp.</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frrimp" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:8%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Rid.%</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frrperc" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:12%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Rid.Lim.</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frrlim" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Max</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frmax" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>Min</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frmin" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

                echo '<div style="display:inline-block;width:10%;vertical-align:top;">';

                    echo '<div style="text-align:center;">';
                        echo '<label>K</label>';
                    echo '</div>';
                    
                    echo '<div style="">';
                        echo '<div id="grent_reset_txt_frk" style="text-align:center;">';
                        echo '</div>';
                    echo '</div>'; 

                echo '</div>';

            echo '</div>';

            //////////////////////////////////////////////////

            echo '<div style="position:relative;margin-top:10px;text-align:center;">';
                echo '<button onclick="window._js_chk_'.$this->form_tag.'.scrivi();"" >Crea il nuovo Reset</button>';
            echo '</div>';

        echo '</div>';

        $this->draw_js_base();

    }

}

?>