<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');

class ctChekkoModulo extends chekko {

    function __construct($tag) {
        
        parent::__construct($tag);
    }

    function draw(){

    }

    function draw_css() {

    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.chkTipo=function(valore) {';

            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS
            var div='funzione';
            if (valore=='griglia') div="griglia";

            this.chg_radio_std('tipo',valore);

            $('input[id^="'+tag+'_funzione"]').prop("disabled",false);
            if (valore=="lineare") {
                $('#'+tag+'_funzione_fattore').val(1);
                $('#'+tag+'_funzione_fattore').prop("disabled",true);
            }

            window['_js_chk_'+tag].js_chk();

            $('div[id^="ctv_div_tipo_'+tag+'"]').css('display','none');
            $('#ctv_div_tipo_'+tag+'_'+div).css('display','inline-block');

JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.post_check=function() {';

            echo 'var tag="'.$this->form_tag.'";';
            echo <<<JS

                var tipo=$('input[js_chk_'+tag+'_tipo="tipo"').val();

                var ret={};

                $('input[id^="'+tag+'_griglia_R"]').each(function(){
                    var arr=[];
                    var id=$(this).data("id");

                    //il primo campo non esiste da imputare
                    if (id=="0") arr.push(0);
                    else {
                        var temp=$(this).val();
                        var val=0;
                        if (temp=="" || temp!=0) {
                            val=parseFloat(temp);
                            if (!val) {
                                val="";
                                $('#js_chk_'+tag+'_elem_griglia_R_'+id).css('border-color','red');
                                if (tipo=="griglia") window['_js_chk_'+tag].chk=1;
                            }
                        }
                        arr.push(val);
                    }

                    var v=$('#'+tag+'_griglia_V_'+id);
                    var temp=v.val();
                    var val=0;
                    if (temp=="" || temp!=0) {
                        val=parseFloat(temp);
                        if (!val) {
                            val="";
                            $('#js_chk_'+tag+'_elem_griglia_V_'+id).css('border-color','red');
                            if (tipo=="griglia") window['_js_chk_'+tag].chk=1;
                        }
                    }
                    arr.push(val);

                    ret[id]=arr;
                });

                $("input[js_chk_"+tag+"_tipo='griglia']").data("txt",JSON.stringify(ret));

                //////////////////////////////////

                var arr={};
                $('input[id^="'+tag+'_funzione"]').each(function(){
                    var id=$(this).data("id");
                    var temp=$(this).val();
                    var val=0;
                    if (temp=="" || temp!=0) {
                        val=parseFloat(temp);
                        if (!val) {
                            val="";
                            $('#js_chk_'+tag+'_elem_funzione_'+id).css('border-color','red');
                            if (tipo!="griglia") window['_js_chk_'+tag].chk=1;
                        }
                    }
                    arr[id]=val;
                });

                if (arr.max<=arr.min) {
                    $('#js_chk_'+tag+'_elem_funzione_max').css('border-color','red');
                    $('#js_chk_'+tag+'_elem_funzione_min').css('border-color','red');
                    if (tipo!="griglia") window['_js_chk_'+tag].chk=1;
                }

                $("input[js_chk_"+tag+"_tipo='funzione']").data("txt",JSON.stringify(arr));

                window._ctv_ckMulti.setChg(true);

JS;
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.collect_data_proprietario=function() {';
            echo <<<JS
                this.expo['funzione']=$('input[js_chk_'+this.form_tag+'_tipo="funzione"]').data("txt");
                this.expo['griglia']=$('input[js_chk_'+this.form_tag+'_tipo="griglia"]').data("txt");
JS;
        echo '};';

    }
    
}