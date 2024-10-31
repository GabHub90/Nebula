<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class gdmPneuForm extends chekko {

    protected $operazione="";

    function __construct($tag,$op) {

        parent::__construct($tag);

        $this->operazione=$op;
    }

    function draw() {

        echo '<div style="position:relative;display:inline-block;height:100%;width:10%;background-color:transparent;vertical-align:top;" >';
            echo '<div style="position:relative;width:100%;height:45%;"></div>';
            echo '<div style="position:relative;width:100%;height:9%;text-align:center;">';
                echo '<img id="'.$this->form_tag.'_arrowU_A" style="width:25px;height:30px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowU.png" onclick="window._js_chk_'.$this->form_tag.'.copyV(\'AS\');"/>';
                echo '<img id="'.$this->form_tag.'_arrowD_A" style="width:25px;height:30px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowD.png" onclick="window._js_chk_'.$this->form_tag.'.copyV(\'AD\');"/>';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;height:100%;width:35%;background-color:transparent;vertical-align:top;" >';
            echo '<div style="position:relative;width:100%;height:9%;"></div>';

            echo '<div style="position:relative;width:100%;height:36%;background-color:white;">';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_misuraADX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;" >';
                    echo '<input id="'.$this->form_tag.'_misuraADX" type="text" style="';
                        foreach ($this->mappa['misuraADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['misuraADX']['prop']['placeholder'].'" value="'.$this->mappa['misuraADX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="misuraADX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_marcaADX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_marcaADX" type="text" style="';
                        foreach ($this->mappa['marcaADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['marcaADX']['prop']['placeholder'].'" value="'.$this->mappa['marcaADX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="marcaADX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_dotADX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_dotADX" type="text" style="';
                        foreach ($this->mappa['dotADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" maxlength="4" placeholder="'.$this->mappa['dotADX']['prop']['placeholder'].'" value="'.$this->mappa['dotADX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="dotADX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_usuraADX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<select id="'.$this->form_tag.'_usuraADX" style="';
                        foreach ($this->mappa['usuraADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="usuraADX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();">';

                        echo '<option value=""></option>';

                        foreach ($this->mappa['usuraADX']['prop']['options'] as $v=>$t) {
                            echo '<option value="'.$v.'"' ;
                                if ($v==$this->mappa['usuraADX']['prop']['default']) echo ' selected';
                            echo ' >'.$t.'</option>';
                        }

                    echo '</select>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;height:9%;"></div>';

                echo '<div style="position:relative;width:100%;height:36%;background-color:white;">';

                    echo '<div id="js_chk_'.$this->form_tag.'_elem_misuraASX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;" >';
                        echo '<input id="'.$this->form_tag.'_misuraASX" type="text" style="';
                            foreach ($this->mappa['misuraADX']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" placeholder="'.$this->mappa['misuraASX']['prop']['placeholder'].'" value="'.$this->mappa['misuraASX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="misuraASX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_elem_marcaASX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                        echo '<input id="'.$this->form_tag.'_marcaASX" type="text" style="';
                            foreach ($this->mappa['marcaASX']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" placeholder="'.$this->mappa['marcaASX']['prop']['placeholder'].'" value="'.$this->mappa['marcaASX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="marcaASX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_elem_dotASX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                        echo '<input id="'.$this->form_tag.'_dotASX" type="text" style="';
                            foreach ($this->mappa['dotASX']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" maxlength="4" placeholder="'.$this->mappa['dotASX']['prop']['placeholder'].'" value="'.$this->mappa['dotASX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="dotASX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                    echo '</div>';

                    echo '<div id="js_chk_'.$this->form_tag.'_elem_usuraASX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                        echo '<select id="'.$this->form_tag.'_usuraASX" style="';
                            foreach ($this->mappa['usuraASX']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="usuraASX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'A\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" >';

                            echo '<option value=""></option>';

                            foreach ($this->mappa['usuraASX']['prop']['options'] as $v=>$t) {
                                echo '<option value="'.$v.'"' ;
                                    if ($v==$this->mappa['usuraASX']['prop']['default']) echo ' selected';
                                echo ' >'.$t.'</option>';
                            }

                        echo '</select>';
                    echo '</div>';

                echo '</div>';

            echo '<div style="position:relative;width:100%;height:10%;"></div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;height:100%;width:10%;background-color:transparent;vertical-align:top;" >';
            echo '<div style="position:relative;width:100%;height:45%;"></div>';
            echo '<div style="position:relative;width:100%;height:9%;text-align:center;">';
                echo '<img id="'.$this->form_tag.'_arrowAP" style="width:30px;height:25px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" onclick="window._js_chk_'.$this->form_tag.'.copyH(\'AP\');"/>';
                echo '<img id="'.$this->form_tag.'_arrowPA" style="width:30px;height:25px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowL.png" onclick="window._js_chk_'.$this->form_tag.'.copyH(\'PA\');"/>';
            echo '</div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;height:100%;width:35%;background-color:#transparent;vertical-align:top;" >';

            echo '<div style="position:relative;width:100%;height:9%;"></div>';

            echo '<div style="position:relative;width:100%;height:36%;background-color:white;">';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_misuraPDX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;" >';
                    echo '<input id="'.$this->form_tag.'_misuraPDX" type="text" style="';
                        foreach ($this->mappa['misuraADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['misuraPDX']['prop']['placeholder'].'" value="'.$this->mappa['misuraPDX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="misuraPDX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_marcaPDX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_marcaPDX" type="text" style="';
                        foreach ($this->mappa['marcaPDX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['marcaPDX']['prop']['placeholder'].'" value="'.$this->mappa['marcaPDX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="marcaPDX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_dotPDX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_dotPDX" type="text" style="';
                        foreach ($this->mappa['dotPDX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" maxlength="4" placeholder="'.$this->mappa['dotPDX']['prop']['placeholder'].'" value="'.$this->mappa['dotPDX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="dotPDX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_usuraPDX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<select id="'.$this->form_tag.'_usuraPDX" style="';
                        foreach ($this->mappa['usuraPDX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="usuraPDX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'D\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" >';

                        echo '<option value=""></option>';

                        foreach ($this->mappa['usuraPDX']['prop']['options'] as $v=>$t) {
                            echo '<option value="'.$v.'"' ;
                                if ($v==$this->mappa['usuraPDX']['prop']['default']) echo ' selected';
                            echo ' >'.$t.'</option>';
                        }

                    echo '</select>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;height:9%;"></div>';

            echo '<div style="position:relative;width:100%;height:36%;background-color:white;">';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_misuraPSX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;" >';
                    echo '<input id="'.$this->form_tag.'_misuraPSX" type="text" style="';
                        foreach ($this->mappa['misuraADX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['misuraPSX']['prop']['placeholder'].'" value="'.$this->mappa['misuraPSX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="misuraPSX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_marcaPSX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_marcaPSX" type="text" style="';
                        foreach ($this->mappa['marcaPSX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" placeholder="'.$this->mappa['marcaPSX']['prop']['placeholder'].'" value="'.$this->mappa['marcaPSX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="marcaPSX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_dotPSX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<input id="'.$this->form_tag.'_dotPSX" type="text" style="';
                        foreach ($this->mappa['dotPSX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" maxlength="4" placeholder="'.$this->mappa['dotPSX']['prop']['placeholder'].'" value="'.$this->mappa['dotPSX']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="dotPSX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$this->form_tag.'_elem_usuraPSX" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;" >';
                    echo '<select id="'.$this->form_tag.'_usuraPSX" style="';
                        foreach ($this->mappa['usuraPSX']['css'] as $k=>$c) {
                            echo $k.':'.$c;
                        }
                    echo '" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="usuraPSX" onfocus="window._js_chk_'.$this->form_tag.'.setFocus(\'P\',\'S\');" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" >';

                        echo '<option value=""></option>';

                        foreach ($this->mappa['usuraPSX']['prop']['options'] as $v=>$t) {
                            echo '<option value="'.$v.'"' ;
                                if ($v==$this->mappa['usuraPSX']['prop']['default']) echo ' selected';
                            echo ' >'.$t.'</option>';
                        }

                    echo '</select>';
                    
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;height:10%;"></div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;height:100%;width:10%;background-color:transparent;vertical-align:top;" >';
            echo '<div style="position:relative;width:100%;height:45%;"></div>';
            echo '<div style="position:relative;width:100%;height:9%;text-align:center;">';
                echo '<img id="'.$this->form_tag.'_arrowU_P" style="width:25px;height:30px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowU.png" onclick="window._js_chk_'.$this->form_tag.'.copyV(\'PS\');"/>';
                echo '<img id="'.$this->form_tag.'_arrowD_P" style="width:25px;height:30px;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowD.png" onclick="window._js_chk_'.$this->form_tag.'.copyV(\'PD\');"/>';
            echo '</div>';
        echo '</div>';

        $this->draw_js_base();
    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.pattern_dot=/^[0-9]{4}$/;';
        echo 'window._js_chk_'.$this->form_tag.'.pattern_misura=/^[1-3][0-9]5\/[1-9](0|5)\s?Z?R(1|2)[0-9]\s?[1-9][0-9]{1,2}(Q|R|S|T|H|V|Z|W|Y)$/;';

        echo 'window._js_chk_'.$this->form_tag.'.kind_dot=function(val,id) {';
            echo 'return !(this.pattern_dot.test(val));';
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.kind_misura=function(val,id) {';
            echo 'val = val.toUpperCase();';
            echo 'this.chg_val(id, val);';
            echo 'return !(this.pattern_misura.test(val));';
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.delAnnotazione=function(id) {';
            echo '$("#'.$this->form_tag.'_annotazioni").val("");';
        echo '};';

        if ($this->operazione!=0) {

            echo 'window._js_chk_'.$this->form_tag.'.pre_check=function() {';

                //echo 'alert(window._imageSelect_op_'.$this->operazione.'.returnValue());';

                echo '$("#'.$this->form_tag.'_destinazione").val(window._imageSelect_op_'.$this->operazione.'.returnValue());';

            echo '};';
        }

        echo 'window._js_chk_'.$this->form_tag.'.setFocus=function(zona,lato) {';

            echo '$("img[id^=\''.$this->form_tag.'_arrow\']").hide();';

            echo 'if(zona=="A") {';
                echo '$("#'.$this->form_tag.'_arrowAP").show();';
                echo 'if(lato=="S") $("#'.$this->form_tag.'_arrowU_A").show();';
                echo 'if(lato=="D") $("#'.$this->form_tag.'_arrowD_A").show();';
            echo'}';

            echo 'if(zona=="P") {';
                echo '$("#'.$this->form_tag.'_arrowPA").show();';
                echo 'if(lato=="S") $("#'.$this->form_tag.'_arrowU_P").show();';
                echo 'if(lato=="D") $("#'.$this->form_tag.'_arrowD_P").show();';
            echo '}';

        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.copyH=function(verso) {';

            echo 'if(verso=="AP") {';
                echo '$("#'.$this->form_tag.'_misuraPDX").val( $("#'.$this->form_tag.'_misuraADX").val() );';
                echo '$("#'.$this->form_tag.'_marcaPDX").val( $("#'.$this->form_tag.'_marcaADX").val() );';
                echo '$("#'.$this->form_tag.'_dotPDX").val( $("#'.$this->form_tag.'_dotADX").val() );';
                echo '$("#'.$this->form_tag.'_usuraPDX").val( $("#'.$this->form_tag.'_usuraADX").val() );';

                echo '$("#'.$this->form_tag.'_misuraPSX").val( $("#'.$this->form_tag.'_misuraASX").val() );';
                echo '$("#'.$this->form_tag.'_marcaPSX").val( $("#'.$this->form_tag.'_marcaASX").val() );';
                echo '$("#'.$this->form_tag.'_dotPSX").val( $("#'.$this->form_tag.'_dotASX").val() );';
                echo '$("#'.$this->form_tag.'_usuraPSX").val( $("#'.$this->form_tag.'_usuraASX").val() );';
            echo '}';

            echo 'if(verso=="PA") {';
                echo '$("#'.$this->form_tag.'_misuraADX").val( $("#'.$this->form_tag.'_misuraPDX").val() );';
                echo '$("#'.$this->form_tag.'_marcaADX").val( $("#'.$this->form_tag.'_marcaPDX").val() );';
                echo '$("#'.$this->form_tag.'_dotADX").val( $("#'.$this->form_tag.'_dotPDX").val() );';
                echo '$("#'.$this->form_tag.'_usuraADX").val( $("#'.$this->form_tag.'_usuraPDX").val() );';

                echo '$("#'.$this->form_tag.'_misuraASX").val( $("#'.$this->form_tag.'_misuraPSX").val() );';
                echo '$("#'.$this->form_tag.'_marcaASX").val( $("#'.$this->form_tag.'_marcaPSX").val() );';
                echo '$("#'.$this->form_tag.'_dotASX").val( $("#'.$this->form_tag.'_dotPSX").val() );';
                echo '$("#'.$this->form_tag.'_usuraASX").val( $("#'.$this->form_tag.'_usuraPSX").val() );';
            echo '}';

        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.copyV=function(verso) {';

            echo 'if(verso=="AD") {';
                echo '$("#'.$this->form_tag.'_misuraASX").val( $("#'.$this->form_tag.'_misuraADX").val() );';
                echo '$("#'.$this->form_tag.'_marcaASX").val( $("#'.$this->form_tag.'_marcaADX").val() );';
                echo '$("#'.$this->form_tag.'_dotASX").val( $("#'.$this->form_tag.'_dotADX").val() );';
                echo '$("#'.$this->form_tag.'_usuraASX").val( $("#'.$this->form_tag.'_usuraADX").val() );';
            echo '}';

            echo 'if(verso=="AS") {';
                echo '$("#'.$this->form_tag.'_misuraADX").val( $("#'.$this->form_tag.'_misuraASX").val() );';
                echo '$("#'.$this->form_tag.'_marcaADX").val( $("#'.$this->form_tag.'_marcaASX").val() );';
                echo '$("#'.$this->form_tag.'_dotADX").val( $("#'.$this->form_tag.'_dotASX").val() );';
                echo '$("#'.$this->form_tag.'_usuraADX").val( $("#'.$this->form_tag.'_usuraASX").val() );';
            echo '}';

            echo 'if(verso=="PD") {';
                echo '$("#'.$this->form_tag.'_misuraPSX").val( $("#'.$this->form_tag.'_misuraPDX").val() );';
                echo '$("#'.$this->form_tag.'_marcaPSX").val( $("#'.$this->form_tag.'_marcaPDX").val() );';
                echo '$("#'.$this->form_tag.'_dotPSX").val( $("#'.$this->form_tag.'_dotPDX").val() );';
                echo '$("#'.$this->form_tag.'_usuraPSX").val( $("#'.$this->form_tag.'_usuraPDX").val() );';
            echo '}';

            echo 'if(verso=="PS") {';
                echo '$("#'.$this->form_tag.'_misuraPDX").val( $("#'.$this->form_tag.'_misuraPSX").val() );';
                echo '$("#'.$this->form_tag.'_marcaPDX").val( $("#'.$this->form_tag.'_marcaPSX").val() );';
                echo '$("#'.$this->form_tag.'_dotPDX").val( $("#'.$this->form_tag.'_dotPSX").val() );';
                echo '$("#'.$this->form_tag.'_usuraPDX").val( $("#'.$this->form_tag.'_usuraPSX").val() );';
            echo '}';

        echo '};';
    }


    function draw_css() {}

}