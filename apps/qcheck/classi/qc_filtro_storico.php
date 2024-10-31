<?php

class qcFiltroStorico extends chekko {

    function __construct($tag) {

        parent::__construct($tag);

        $this->chk_fields=array(
            "reparto"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "esecutore"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "operatore"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "modulo"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "variante"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "chiave"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "da"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "a"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "controllo"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );
        
        $this->tipi=array(
            "reparto"=>"none",
            "esecutore"=>"none",
            "operatore"=>"none",
            "modulo"=>"none",
            "variante"=>"none",
            "chiave"=>"none",
            "da"=>"data",
            "a"=>"data",
            "controllo"=>"none"
        );

        $this->expo=array(
            "reparto"=>"",
            "esecutore"=>"",
            "operatore"=>"",
            "modulo"=>"",
            "variante"=>"",
            "chiave"=>"",
            "data_i"=>"",
            "data_f"=>"",
            "controllo"=>""
        );

        $this->conv=array(
            "reparto"=>"reparto",
            "esecutore"=>"esecutore",
            "operatore"=>"operatore",
            "modulo"=>"modulo",
            "variante"=>"variante",
            "chiave"=>"chiave",
            "data_i"=>"da",
            "data_f"=>"a",
            "controllo"=>"controllo"
        );
        
    }

    function draw_js(){

        echo 'window._js_chk_'.$this->form_tag.'.scrivi_proprietario=function() {';
        echo <<<JS
            //alert(JSON.stringify(this.expo));
            
            var param=this.expo;

            $.ajax({
                "url": 'http://'+location.host+'/nebula/apps/qcheck/core/storico_lines.php',
                "async": true,
                "cache": false,
                "data": { "param": param},
                "type": "POST",
                "success": function(ret) {
                    //console.log(ret);
                    $('#qcStoricoLines').html(ret);
                }
            });
JS;
        echo '};';

    }

    function draw_css() {

    }

    function draw() {

        $this->draw_js_base();

    }

}


?>