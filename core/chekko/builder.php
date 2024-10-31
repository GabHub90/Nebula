<?php

class ckBuilder {

    protected $form_tag="";

    protected $mappa=array();
    protected $struttura=array();
    
    //se TRUE disabilita i BUTTON
    protected $flagNoButton=false;

    //tiene il conto degli elemnti che hanno richiesto PREFIX - numero
    protected $elem_counter=0;

    function __construct($form_tag,$mappa,$struttura) {
        
        $this->form_tag=$form_tag;
        $this->mappa=$mappa;
        $this->struttura=$struttura;
    }

    function setMap($map) {
        $this->mappa=$map;
    }

    function setNobutton($v) {
        $this->flagNoButton=$v;
    }

    function view($risposte) {
        //scrive una versione del form riassuntiva

        foreach ($this->struttura['body'] as $sezione=>$s) {

            echo '<div>';

                //BLOCCHI
                foreach ($s['blocchi'] as $blocco=>$b) {

                    echo '<div style="margin-top:10px;">';

                        //ELEMENTI
                        foreach ($b['elementi'] as $elemento=>$e) {

                            if ($e['map']!="") $m=$this->mappa[$e['map']];
                            else $m=false;

                            //se non c'è alcun elemento da visualizzare e sia label che sub sono vuoti allora salta
                            if ($e['label']=="" && $e['sub']=="") {
                                if (!$m) continue;
                                if ($m['prop']['input']=='textarea' && $risposte[$e['map']]=="") continue;
                            }

                            echo '<div>';                        

                                $label=$this->buildLabel($e['prefix'],$e['label']);

                                if ($e['label']!="") {
                                    echo '<div style="font-weight:bold;font-size:12pt;" >'.$label.'</div>';
                                }

                                if ($e['sub']!="") {
                                    echo '<div style="font-size:10pt;">'.$e['sub'].'</div>';
                                }

                                if ($m) {

                                    echo '<div style="position:relative;margin-top:5px;width:98%;left:1%;">';
                                        /* {
                                        "prop": {
                                            "input": "textarea",
                                            "tipo": "",
                                            "maxlenght": "",
                                            "options": [],
                                            "rows": "2",
                                            "default": "",
                                            "placeholder": "note",
                                            "disabled": false
                                        },
                                        "css": []
                                        }*/

                                        /*{
                                            "prop": {
                                                "input": "radio",
                                                "tipo": "3bottom",
                                                "maxlenght": "",
                                                "options": {
                                                "1": "OK",
                                                "0": "KO",
                                                "2": "Non Rilevato"
                                                },
                                                "rows": "",
                                                "default": "1",
                                                "placeholder": "",
                                                "disabled": false
                                            },
                                            "css": {
                                                "width": "20px;",
                                                "height": "20px;"
                                            }
                                        }*/

                                        if ($m['prop']['input']=='textarea') {
                                            echo '<div>'.$risposte[$e['map']].'</div>';
                                        }
                                        elseif($m['prop']['input']=='radio' || $m['prop']['input']=='select' || $m['prop']['input']=='switch') {
                                            echo '<div> - '.$m['prop']['options'][$risposte[$e['map']]].'</div>';
                                        }
                                    echo '</div>';                                       

                                }

                            echo '</div>';

                        }

                    echo '</div>';
                }
            
            echo '</div>';

        }

    }

    function draw() {

        //28.02.2021 sposatto in draw_CSS_base
        //echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chekko/style.css?v='.time().'"); </style>';

        /*
        echo '<div>';
            echo json_encode($this->struttura);
        echo '</div>';
        */

        $h=100;

        if ($this->struttura['head']['flag']) {
            $h-=$this->struttura['head']['height'];

            echo '<div id="js_chk_'.$this->form_tag.'_head" class="chekko_head" style="height:'.$this->struttura['head']['height'].'%;';
                foreach ($this->struttura['head']['css'] as $k=>$v) {
                    echo $k.':'.$v;
                }
            echo '"></div>';

        }

        echo '<div style="width:100%;height:'.$h.'%;overflow-y:scroll;">';

            echo '<div style="width:97%;">';

                foreach ($this->struttura['body'] as $sezione=>$s) {

                    echo '<div class="';
                        echo ($s['class']==""?"chekko_sezione":$s['class']);
                    echo '" style="';
                        foreach ($s['css'] as $k=>$v) {
                            echo $k.':'.$v;
                        }
                    echo '">';

                    //BLOCCHI
                    foreach ($s['blocchi'] as $blocco=>$b) {

                        echo '<div class="';
                            echo ($b['class']==""?"chekko_blocco":$b['class']);
                        echo '" style="';
                            foreach ($b['css'] as $k=>$v) {
                                echo $k.':'.$v;
                            }
                        echo '">';

                        //ELEMENTI
                        foreach ($b['elementi'] as $elemento=>$e) {

                            if ($e['map']!="") $m=$this->mappa[$e['map']];
                            else $m=false;

                            echo '<div ';
                                if ($m) echo 'id="js_chk_'.$this->form_tag.'_elem_'.$e['map'].'" '; 
                            echo 'class="chekko_elem" style="';
                                foreach ($e['css'] as $k=>$v) {
                                    echo $k.':'.$v;
                                }
                            echo '">';                        

                                $label=$this->buildLabel($e['prefix'],$e['label']);

                                echo '<div class="chekko_label">'.$label.'</div>';

                                echo '<div class="chekko_sub">'.$e['sub'].'</div>';

                                if ($e['extra']!="") $this->drawExtra($e);

                                elseif ($m) {

                                    echo '<div>';   
                                        //if ($m['prop']['input']!="label") $this->drawKind($e['map'],$m);
                                        $this->drawKind($e['map'],$m);
                                    echo '</div>';

                                    if ($m['prop']['tipo']!="info") {
                                        echo '<div id="js_chk_'.$this->form_tag.'_error_'.$e['map'].'" class="chekko_error js_chk_'.$this->form_tag.'_error" style="font-size:11pt;" ></div>';
                                    }
                                }

                            echo '</div>';

                        }

                        echo '</div>';
                    }

                    echo '</div>';
                }
            
            echo '</div>';
        
        echo '</div>'; 

    }

    function buildLabel($prefix,$label) {

        $l=$label;

        switch($prefix) {

            case 'numero':
                $this->elem_counter++;
                $l=$this->elem_counter.' - '.$l;
            break;

        }

        return $l;

    }

    function drawKind($index,$m) {

        /*
        "qc1"=>array(
            "prop"=>array(
                "input"=>"radio",
                "tipo"=>"3bottom",
                "maxlenght"=>"",
                "options"=>array(
                    "1"=>"OK",
                    "0"=>"KO",
                    "2"=>"Non Rilevato"
                ),
                "rows"=>"",
                "default"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        )
        */

        switch($m['prop']['input']) {

            case "input":
                $this->drawInput($index,$m);
            break;
            case "radio":
                $this->drawRadio($index,$m);
            break;
            case "textarea":
                $this->drawTextarea($index,$m);
            break;
            case "switch":
                $this->drawSwitch($index,$m);
            break;
            case "select":
                $this->drawSelect($index,$m);
            break;
        }
    }

    function drawExtra($e) {

        switch($e['extra']) {

            case "label":
            break;
            case "button":
                if (!$this->flagNoButton) $this->drawButton($e);
            break;
            case "app":
                $this->drawApp($e);
            break;
        }

    }

    function drawInput($index,$m) {

        $type=($m['prop']['tipo']=='info')?'hidden':$m['prop']['tipo'];

        echo '<input id="'.$this->form_tag.'_'.$index.'" type="'.$type.'" ';
            if ($m['prop']['maxlenght']!="") echo 'maxlenght="'.$m['prop']['maxlenght'].'"';
        echo ' placeholder="'.$m['prop']['placeholder'].'" style="';
            foreach ($m['css'] as $kcss=>$vcss) {
                echo $kcss.':'.$vcss;
            }
        echo '" value="';
            echo $m['prop']['default'];
        echo '" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="'.$index.'" onchange="_js_chk_'.$this->form_tag.'.js_chk();" ';
            if ($m['prop']['disabled']) echo 'disabled="disabled"';
        echo '/>';

    }

    function drawRadio($index,$m) {

        if ($m['prop']['tipo']=="3bottom") {

            foreach ($m['prop']['options'] as $k=>$v) {

                echo '<div style="display:inline-block;width:33.3%;text-align:center;">';

                    echo '<div>';
                        echo '<input name="'.$this->form_tag.'_'.$index.'" type="radio" style="';
                            foreach ($m['css'] as $kcss=>$vcss) {
                                echo $kcss.':'.$vcss;
                            }
                        echo '" value="'.$k.'" onclick="window._js_chk_'.$this->form_tag.'.chg_radio_std(\''.$index.'\',this.value);" ';
                            if ($m['prop']['default']!="" && $m['prop']['default']==$k) echo 'checked="checked" ';
                            if ($m['prop']['disabled']) echo ' disabled="disabled"';
                        echo ' />';
                    echo '</div>';

                    echo '<div style="color:'.( ($k=='1')?"green":($k=='0'?"red":"darkgray") ).';">'.$v.'</div>';

                echo '</div>';
            }

            echo '<input id="'.$this->form_tag.'_'.$index.'" type="hidden" value="'.$m['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="'.$index.'" />';
    
        }

    }

    function drawSwitch($index,$m) {

        //versione orizzontale
        if ($m['prop']['tipo']=="H") {

            $pos=0;

            foreach ($m['prop']['options'] as $k=>$v) { 
                
                $pos++;
                
                echo '<div style="display:inline-block;width:50%;height:40px;">';

                    echo '<div id="sw_'.$this->form_tag.'_'.$index.'_'.$k.'" style="position:relative;left:20%;width:60%;height:100%;text-align:center;font-weight:bold;line-height:40px;cursor:pointer;';
                        if ($pos==1) {
                            //#b5f7b5
                            echo 'border:2px solid green;color:green;background-color:'.($m['prop']['default']==$k?'#cfd2cf':'transparent').';';
                        }
                        if ($pos==2) {
                            //#f5b6b6
                            echo 'border:2px solid red;color:red;background-color:'.($m['prop']['default']==$k?'#cfd2cf':'transparent').';';
                        }
                    echo '" data-value="'.$k.'" onclick="window._js_chk_'.$this->form_tag.'.chg_switch_std(\''.$index.'\',\''.$k.'\');">'.$v.'</div>';

                echo '</div>';

            }

            echo '<input id="'.$this->form_tag.'_'.$index.'" type="hidden" value="'.$m['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="'.$index.'" />';
        }

    }

    function drawTextarea($index,$m) {

        echo '<textarea id="'.$this->form_tag.'_'.$index.'" placeholder="'.$m['prop']['placeholder'].'",rows="'.$m['prop']['rows'].'" class="chekko_input js_chk_'.$this->form_tag.'" style="resize: none;';
            foreach ($m['css'] as $kcss=>$vcss) {
                echo $kcss.':'.$vcss;
            }
            echo '" js_chk_'.$this->form_tag.'_tipo="'.$index.'" onchange="_js_chk_'.$this->form_tag.'.js_chk();" ';
            if ($m['prop']['disabled']) echo 'disabled="disabled"';
        echo '>';

            echo $m['prop']['default'];

        echo '</textarea>';

    }

    function drawSelect($index,$m) {

        echo '<select id="'.$this->form_tag.'_'.$index.'" style="';
            foreach ($m['css'] as $kcss=>$vcss) {
                echo $kcss.':'.$vcss;
            }
            echo '" js_chk_'.$this->form_tag.'_tipo="'.$index.'" onchange="_js_chk_'.$this->form_tag.'.js_chk();" ';
            if ($m['prop']['disabled']) echo 'disabled="disabled"';
        echo '>';

            foreach ($m['prop']['options'] as $k=>$t) {
                echo '<option value="'.$k.'" ';
                    if ($t['disabled']) echo ' disabled';
                    if ($k==$m['prop']['default']) echo ' selected';
                echo '>'.$t['testo'].'</option>';
            }
        
        echo '</select>';

    }

    function drawButton($e) {

        echo '<button id="'.$e['ID'].'" style="';
            foreach ($e['extraCSS'] as $kcss=>$vcss) {
                echo $kcss.':'.$vcss;
            }
        echo '" onclick="" ';
            if ($e['disabled']) echo 'disabled="disabled"';
        echo '>'.$e['testo'].'</button>';

    }

    function drawApp($e) {

        echo '<img id="'.$e['ID'].'" style="';
            foreach ($e['extraCSS'] as $kcss=>$vcss) {
                echo $kcss.':'.$vcss;
            }
        echo '" onclick="window._js_chk_'.$this->form_tag.'.execApp(\''.$e['extraCLICK'].'\',\''.$e['extraPARAM'].'\');" ';
            if ($e['disabled']) echo 'disabled="disabled"';
        echo ' src="'.$e['extraSRC'].'" />';
    }

}
?>