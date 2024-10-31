<?php

require_once('builder.php');

abstract class chekko {

    protected $form_tag;

    //detrmina la scrittura della clausola STYLE
    protected $cssFlag=true;

    //campi record db del modulo (valori default) sostituito da MAPPA
    //protected $info=array();

    //impostazioni dei campi del form
    protected $chk_fields=array();

    //variabile di appoggio per passare i tipi nell'oggetto JS
    protected $tipi=array();

    //descrive la mappa dei campi del FORM
    /*
     "odl"=>array(
        "prop"=>array(
            "input"=>"input",
            "tipo"=>"text",
            "maxlenght"=>"8",
            "options"=>array(),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    )
    */
    protected $mappa=array();

    protected $expo=array();
    protected $conv=array();

    protected $score=array();
    
    //classe ckBuilder
    protected $builder;

    //definizione di nuovi metodi in maniera dinamica
    protected $methods = array();
    protected $closure = array();

    protected $log=array();
    
    function __construct($tag) {
        $this->form_tag=$tag;
    }

    function getLog() {
        return $this->log;
    }

    //definisce in maniera dinamica nuovi metodi
    //serve per scrivere il metodo DRAW in base alla versione del modulo
    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
    }

    function set_closure() {
        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class());
        }
    }

    function resetFields() {
        
        $this->chk_fields=array();
        $this->expo=array();
        $this->conv=array();
    }

    function add_fields($arr) {

        /*"rif"=>array(
			"js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
			"js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
		)*/

        foreach ($arr as $k=>$a) {
            $this->chk_fields[$k]=$a;
        }

        //non ho capito perché questo non funziona
        //$this->chk_fields=$arr;
    }

    /*sostituito da MAPPA
    function load_info($arr) {
        $this->info=$arr;
    }
    */

    function load_tipi($arr) {
        foreach ($arr as $k=>$a) {
            $this->tipi[$k]=$a;
        }

        //$this->tipi=$arr;
    }

    function load_mappa($arr) {
        foreach ($arr as $k=>$a) {
            $this->mappa[$k]=$a;
        }

        //$this->mappa=$arr;
    }

    function load_expo($arr) {
        foreach ($arr as $k=>$a) {
            $this->expo[$k]=$a;
        }

        //$this->expo=$arr;
    }

    function load_conv($arr) {
        foreach ($arr as $k=>$a) {
            $this->conv[$k]=$a;
        }

        //$this->conv=$arr;
    }

    function load_struttura($arr) {
        $this->builder=new ckBuilder($this->form_tag,$this->mappa,$arr);
    }

    function load_score($arr) {
        $this->score=$arr;
    }

    function setBuilderMap($map) {
        if ( !is_a($this->builder, 'ckBuilder') )return;
        $this->builder->setMap($map);
    }

    function setBuilderNoButton($v) {
        if ( !is_a($this->builder, 'ckBuilder') )return;
        $this->builder->setNobutton($v);
    }

    function load_closure($arr) {
        $this->closure=$arr;
    }

    function setCssflag($f) {
        $this->cssFlag=$f;
    }

    function draw_js_base() {

        echo '<script type="text/javascript">';

            echo 'window._js_chk_'.$this->form_tag.'=new chekko(\''.$this->form_tag.'\');';
            
			echo 'var t_fields='.json_encode($this->chk_fields).';';
            echo 'window._js_chk_'.$this->form_tag.'.load_fields(t_fields);';

            echo 'var t_fields='.json_encode($this->tipi).';';
            echo 'window._js_chk_'.$this->form_tag.'.load_tipi(t_fields);';

            echo 'var t_fields='.json_encode($this->expo).';';
            echo 'window._js_chk_'.$this->form_tag.'.load_expo(t_fields);';

            echo 'var t_fields='.json_encode($this->conv).';';
            echo 'window._js_chk_'.$this->form_tag.'.load_conv(t_fields);';

            if (count($this->score)>0) {
                echo 'var t_fields='.json_encode($this->score).';';
                echo 'window._js_chk_'.$this->form_tag.'.load_score(t_fields);';
            }
            
            $this->draw_js();

            //27.02.2021 modificato uso di VALIDATE ed EDIT
            //echo 'window._js_chk_'.$this->form_tag.'.contesto.fase="VALIDATE";';
            echo 'window._js_chk_'.$this->form_tag.'.js_chk();';
			
		echo '</script>';

        $this->draw_css_base();
    }

    function draw_css_base() {

        /*
        echo '$(".js_chk_'.$this->form_tag.'_error").css("font-weight","bold");';
		echo '$(".js_chk_'.$this->form_tag.'_error").css("text-align","left");';
		echo '$(".js_chk_'.$this->form_tag.'_error").css("position","relative");';
        echo '$(".js_chk_'.$this->form_tag.'_error").css("min-height","20px");';
        */

        if ($this->cssFlag) {
            echo '<style> @import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chekko/style.css?v='.time().'"); </style>';
        }

        $this->draw_css();
    }

    /*function init_switch($val) {

        $a=array('bk'=>'#dddddd','text'=>'?');

        if ($val=='S') $a=array('bk'=>'#64dc64','text'=>'SI');
        if ($val=='N') $a=array('bk'=>'#e62b2b','text'=>'NO');

        return $a;
    }*/

    //call draw_js_base prima di tutto (draw_js_base chiama anche CSS base)
    abstract function draw_js();

    abstract function draw_css();

    abstract function draw();

    //MODELLI DI MODULI

     /*function draw() {
        echo '<div style="min-height: 50px;margin-top:5px;" >';

            echo '<div style="display:inline-block;width:25%;">';

                echo '<div>';
                    echo '<label>Immatricolazione:</label>';
                echo '</div>';
// INPUT (tranne radio e checkbox)
// TEXTAREA
                echo '<div>';
                    echo '<input id="'.$this->form_tag.'_d_imm" type="date" class="js_chk_'.$this->form_tag.'" style="width: 95%;" value="'.GabFunc::gab_toinput($this->info['d_imm']).'" js_chk_'.$this->form_flag.'_tipo="d_imm" onchange="_js_chk_'.$this->form_tag.'.js_chk();" />';
                    echo '<div id="js_chk_'.$this->form_tag.'_error_d_imm" class="js_chk_'.$this->form_tag.'_error"></div>';
                echo '</div>';

            echo '</div>';

            echo '<div style="display:inline-block;width:75%;">';
                .............................
            echo '</div>';

        echo '</div>';
    }*/

// RADIO (con collettore per il valore)

    /*echo '<div style="display:inline-block;width:20%;">';

        echo '<div>';
            echo '<label>Km Garantiti:</label>';
        echo '</div>';

        echo '<div style="border:1px solid white;padding 3px;text-align:-webkit-center;width:70%;">';

            echo '<div style="width:100%;">';
                echo '<div style="display:inline-block;width:50%;text-align:-webkit-center;">';
                    echo '<input id="'.$this->form_tag.'_gar_km_S" name="'.$this->form_tag.'_gar_km" type="radio" style="width: 25%;" value="S" chk_group_'.$this->form_tag.'="gar_km" onclick="_js_chk_'.$this->form_tag.'.chg_radio_std(\'gar_km\',this.value);" ';
                        if ($this->info['gar_km']=='S') echo 'checked="checked"';
                    echo ' />';
                    echo '<span>Si</span>';
                echo '</div style="width:20%;">';
                echo '<div style="display:inline-block;width:50%;text-align:-webkit-center;">';
                    echo '<input id="'.$this->form_tag.'_gar_km_N" name="'.$this->form_tag.'_gar_km" type="radio" style="width: 25%;" value="N" chk_group_'.$this->form_tag.'="gar_km" onclick="_js_chk_'.$this->form_tag.'.chg_radio_std(\'gar_km\',this.value);" ';
                        if ($this->info['gar_km']=='N') echo 'checked="checked"';
                    echo ' />';
                    echo '<span>No</span>';
                echo '</div>';
            echo '</div>';
            echo '<div id="js_chk_'.$this->form_tag.'_error_gar_km" class="js_chk_'.$this->form_tag.'_error"></div>';

//COLLETTORE PER IL VALORE
            echo '<input id="'.$this->form_tag.'_gar_km" type="hidden" data-chk_group="gar_km" value="'.$this->info['gar_km'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="gar_km" />';
        echo '</div>';
    echo '</div>';
    */

//SELECT

    /*echo '<div style="display:inline-block;width:50%;">';

        echo '<div>';
            echo '<label>Tipo Iva:</label>';
        echo '</div>';

        echo '<div>';
            echo '<div>';
                $temp=array(
                    array("","Seleziona un tipo iva..."),
                    array("USIVA","Iva Esposta"),
                    array("USMAR","Iva a margine")
                );
                echo '<select id="'.$this->form_tag.'_tipo_iva" style="width:95%;font-size:1em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="tipo_iva" onchange="window._js_chk_'.$this->form_tag.'.js_chk();">';
                    foreach ($temp as $k=>$t) {
                        echo '<option value="'.$t[0].'" ';
                            if ($t[0]==$this->info['tipo_iva']) echo 'selected="selected" ';
                        echo '>'.$t[1].'</option>';
                    }
                echo '</select>';
            echo '</div>';   
            echo '<div id="js_chk_'.$this->form_tag.'_error_tipo_iva" class="js_chk_'.$this->form_tag.'_error"></div>';
        echo '</div>';

    echo '</div>';
    */

// SWITCH (con collettore per il valore)

    /*echo '<div style="display:inline-block;width:20%;">';

        echo '<div>';
            echo '<label>Km Garantiti:</label>';
        echo '</div>';

        echo '<div style="padding 3px;text-align:-webkit-center;width:70%;">';
            
            $tcss=$this->init_switch($this->info['gar_km']);

            echo '<div id="'.$this->form_tag.'_gar_km" style="width:100%;text-align:center;font-weight:bold;font-size:1em;color:black;background-color:'.$tcss['bk'].';" onclick="_js_chk_'.$this->form_tag.'.chg_switch_std(\'gar_km\');">';
                echo $tcss['text'];
            echo '</div>';
            echo '<div id="js_chk_'.$this->form_tag.'_error_gar_km" class="js_chk_'.$this->form_tag.'_error"></div>';

//COLLETTORE PER IL VALORE
            echo '<input id="'.$this->form_tag.'_gar_km" type="hidden" value="'.$this->info['gar_km'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="gar_km" />';
        echo '</div>';
    echo '</div>';
    */


}

?>