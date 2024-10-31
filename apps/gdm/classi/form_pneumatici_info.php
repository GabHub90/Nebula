<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class gdmPneuFormInfo extends chekko {

    protected $operazione="";

    function __construct($tag,$op) {

        parent::__construct($tag);

        $this->operazione=$op;
    }

    function draw() {

        $this->draw_js_base();
    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.delAnnotazione=function(id) {';
            echo '$("#'.$this->form_tag.'_annotazioni").val("");';
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.pre_check=function() {';

            echo '$("#'.$this->form_tag.'_destinazione").val(window._imageSelect_op_'.$this->operazione.'.returnValue());';

        echo '};';
    }


    function draw_css() {}

}