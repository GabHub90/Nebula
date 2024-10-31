<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');

class ctChekkoSezione extends chekko {

    function __construct($tag) {
        
        parent::__construct($tag);

    }

    function draw(){

    }

    function draw_css() {

    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.post_check=function() {';
            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS

                var arr={};
                $('input[id^="'+tag+'_coeff"]').each(function(){
                    var v=$(this).val();
                    if ($(this).prop("checked")) arr[v]=true;
                    else arr[v]=false;
                });
                $("input[js_chk_"+tag+"_tipo='coefficienti']").data("txt",JSON.stringify(arr));

                /////////////////////////////////////////////

                arr={};
                $('input[id^="'+tag+'_range"]').each(function(){
                    var id=$(this).data("id");
                    var val=parseInt($(this).val());
                    if (!val || val=="") val=0;
                    if (val<100) {
                        $('#js_chk_'+tag+'_elem_range_'+id).css('border-color','red');
                        window['_js_chk_'+tag].chk=1;
                    }
                    arr[id]=val;
                });
                $("input[js_chk_"+tag+"_tipo='limite']").data("txt",JSON.stringify(arr));

                /////////////////////////////////////////////

                arr={};
                $('input[id^="'+tag+'_peso"]').each(function(){
                    var id=$(this).data("id");
                    var val=parseInt($(this).val());
                    if (!val || val=="") val=200;
                    if (val>100) {
                        $('#js_chk_'+tag+'_elem_peso_'+id).css('border-color','red');
                        window['_js_chk_'+tag].chk=1;
                    }
                    arr[id]=val;
                });
                $("input[js_chk_"+tag+"_tipo='peso']").data("txt",JSON.stringify(arr));

                window._ctv_ckMulti.setChg(true);

JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.collect_data_proprietario=function() {';
            echo <<<JS
                this.expo['coefficienti']=$('input[js_chk_'+this.form_tag+'_tipo="coefficienti"]').data("txt");
                this.expo['limite']=$('input[js_chk_'+this.form_tag+'_tipo="limite"]').data("txt");
                this.expo['peso']=$('input[js_chk_'+this.form_tag+'_tipo="peso"]').data("txt");
JS;
        echo '};';

    }
    
}