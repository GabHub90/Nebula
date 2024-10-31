function circaJS() {

    this.setRight=function(index) {

        $('div[id^="circa_right_"]').hide();
        $('#circa_right_'+index).show();
    }

    this.clearNewForm=function(index) {

        if (index=='ragsoc') $('#newcirca_ragsoc').val('');
        else if (index=='telaio') {
            $('#newcirca_telaio').val('');
            $('#newcirca_targa').val('');
        }
    }

    this.validateNew=function() {

        var param={
            "dms":$('#newcirca_dms').val(),
            "targa":$('#newcirca_targa').val(),
            "telaio":$('#newcirca_telaio').val(),
            "ragsoc":$('#newcirca_ragsoc').val(),
            "id_veicolo":$('#newcirca_idveicolo').val(),
            "id_anagra":$('#newcirca_idanagra').val()
        }

        if (param.dms=='generico') {
            param.targa='';
            param.id_veicolo='';
            param.id_anagra='';

            if (param.telaio=='' && param.ragsoc=='') {
                alert('Manca il TELAIO o la RAGIONE SOCIALE !!!');
                return;
            }
        }

        else {
            if (param.id_veicolo=='') {
                alert('Veicolo NON identificato correttamente !!!');
                return;
            }
        }

    }

    this.setHeader=function(obj) {

        var actual={
            "cir_actual":$(obj).data('info')
        }

        var temp=$.parseJSON(atob(actual.cir_actual));

        if (!temp) {
            alert('Dati corrotti');
            return;
        }

        window._nebulaApp.setArgs(actual);

        if (temp.telaio!="") {
            $('input[js_chk_circa_tipo="cir_tt"]').val(temp.telaio);
            $('input[js_chk_circa_tipo="cir_ragsoc"]').val('');
        }
        else if (temp.targa!="") {
            $('input[js_chk_circa_tipo="cir_tt"]').val(temp.targa);
            $('input[js_chk_circa_tipo="cir_ragsoc"]').val('');
        }
        else if (temp.ragsoc!="") {
            $('input[js_chk_circa_tipo="cir_ragsoc"]').val(temp.ragsoc);
            $('input[js_chk_circa_tipo="cir_tt"]').val('');
        }

        window._nebulaApp.ribbonExecute();

        /*var actual=$.parseJSON(atob($(obj).data('info')));

        if (!actual) return;

        //simula la raccolta dati di ribbonExecute
        var param={
            "contesto":window._nebulaMain.contesto,
            "ribbon":{"cir_actual":actual},
            "args":{},
            "nebulaFunzione":{'nome':window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].contesto.funzione}
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/circa/core/set_header.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#grent_main').html(ret);
            }
        });*/




    }


}