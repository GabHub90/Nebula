<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class lizChekko extends chekko {

    function __construct($tag) {

        parent::__construct($tag);

        $this->setBuilderNoButton(false);
        
    }

    function draw() {

        echo '<div style="width:100%;height:18%;padding:3px;box-sizing:border-box;border-bottom:1px solid black;" >';
            $this->builder->draw();
            $this->draw_js_base();
        echo '</div>';
    
        echo '<div id="lizard_lista_div" style="width:100%;height:82%;padding:3px;box-sizing:border-box;" >';
        echo '</div>';
    }

    function draw_js() {

        echo <<<JS

        $('#liz_scrivi_button').click(function() {
            window._js_chk_lizForm.scrivi();
        });

        window._js_chk_lizForm.scrivi_proprietario=function() {
            //alert(JSON.stringify(this.expo));


            $('#lizard_lista_div').html('<div style="width:100%;text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

            $.ajax({
                "url": 'http://'+location.host+'/nebula/apps/lizard/lizard_exec.php',
                "async": true,
                "cache": false,
                "data": {"param": this.expo},
                "type": "POST",
                "success": function(ret) {
                    
                    $('#lizard_lista_div').html(ret);
                }
            });
        };

        window._js_chk_lizForm.app_Voucher=function(arr) {

            $('#lizard_lista_div').html('<div style="width:100%;text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

            //console.log(arr);

            $.ajax({
                "url": 'http://'+location.host+'/nebula/core/fidel/fidel_interface.php',
                "async": true,
                "cache": false,
                "data": {"param": arr},
                "type": "POST",
                "success": function(ret) {
                    
                    $('#lizard_lista_div').html(ret);
                }
            });
        };
JS;
    }

    function draw_css() {}

}