<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');

class ctChekkoGradi extends chekko {

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
                var ret={};

                for (var i=0;i<=4;i++) {

                    var arr={};
                    
                    $('input[id^="'+tag+'_gradi_'+i+'"]').each(function(){
                        var id=$(this).data("id");
                        var val=parseInt($(this).val());
                        if (!val || val=="") val=200;
                        if (val>100) {
                            $('#js_chk_'+tag+'_elem_gradi_'+id+'_'+i).css('border-color','red');
                            window['_js_chk_'+tag].chk=1;
                        }
                        arr[id]=val;
                    });

                    ret[i]=arr;
                }

                $("input[js_chk_"+tag+"_tipo='gradi']").data("txt",JSON.stringify(ret));

                window._ctv_ckMulti.setChg(true);

JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.collect_data_proprietario=function() {';
            echo <<<JS
                this.expo['gradi']=""+$('input[js_chk_'+this.form_tag+'_tipo="gradi"]').data("txt");
                /*alert(this.expo['gradi']);*/
JS;
        echo '};';

    }
    
}