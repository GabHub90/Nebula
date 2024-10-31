
function qCheckCode(funzione) {

    this.funzione=funzione;

    this.navigator=function(index) {
       
        if ($('#ribbon_qc_check').val()=="") return;

        $('#ribbon_qc_openType').val(index);
        //window._nebulaApp.ribbonExecute(this.funzione);

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.navigatorCheck=function(controllo) {
        $('#ribbon_qc_check').val(controllo);
        //window._nebulaApp.ribbonExecute(this.funzione);

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.openForm=function(rif) {
        //il riferimento è ID_CONTROLLO:VERSIONE:MODULO

        var args={"qc_form":rif};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.openNew=function() {

        this.azzeraForm();

        window._js_chk_qc_new.js_chk();

        $('#qc_stat_div').hide();
        $('#qc_new_div').show();
    }

    this.closeNew=function() {
        $('#qc_new_div').hide();
        $('#qc_stat_div').show();   
    }

    this.azzeraForm=function() {
        //#########################
        //azzerare il form
        $('#qc_new_form_riferimento').val('');
        $('#qc_new_form_chiave').html('');
        $('#qc_new_form_intestazione').html('');
        $('select[js_chk_qc_new_tipo^="op"]').html('');

        $('input[js_chk_qc_new_tipo="chiave"]').val("");
        $('input[js_chk_qc_new_tipo="intestazione"]').val("");
        //#########################
    }

    this.forzaChiusura=function(controllo) {

        if (!confirm('il controllo NON è completo ma sarà CHIUSO !!!')) return;

        var param={"controllo":controllo};

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/qcheck/core/forza_chiusura.php',
            "async": true,
            "cache": false,
            "data": { "param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.elimina=function(controllo) {

        if (!confirm('il controllo sarà ELIMINATO per SEMPRE !!!')) return;

        var param={"controllo":controllo};

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/qcheck/core/elimina.php',
            "async": true,
            "cache": false,
            "data": { "param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.filtra=function(tipo) {
        //tipo= aperto/chiuso

        var chiave=$('#qc_filter_'+tipo).val();

        if (!chiave || chiave=="") return;

        $('div[id^="qc_elemento_lista_"][qc_lista_tipo="'+tipo+'"]').hide();

        $('#qc_elemento_lista_'+chiave).show();

    }

    this.annullaFiltra=function(tipo) {
        //tipo= aperto/chiuso
        $('div[id^="qc_elemento_lista_"][qc_lista_tipo="'+tipo+'"]').show();
        $('#qc_filter_'+tipo).val("");
    }

    this.view=function(controllo,modulo) {

        var param={
            "controllo":controllo,
            "modulo":modulo
        };

        //alert(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/qcheck/core/view.php',
            "async": true,
            "cache": false,
            "data": { "param": param},
            "type": "POST",
            "success": function(ret) {
                $('#qcStoricoView').html(ret);
                $('#qcStoricoElenco').hide();
                $('#qcStoricoViewer').show();
            }
        });
    }

    this.closeView=function() {

        $('#qcStoricoViewer').hide();
        $('#qcStoricoElenco').show();
    }

    this.pdfView=function(chiave) {

        var param= {
            "txt":$('#qcStoricoView').html()
        }

        var req = new XMLHttpRequest();
        req.open("POST", 'http://'+location.host+'/nebula/core/html2pdf/export.php', true);
        req.responseType = "blob";
        req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

        req.onload = function (event) {
            var blob = req.response;
            console.log(blob.size);
            //console.log(req.response);
            var link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download="prova.pdf";
            link.click();
          };
        
        req.send(JSON.stringify(param));

        /*$.ajax({
            "url": 'http://'+location.host+'/nebula/core/html2pdf/export.php',
            "async": true,
            "cache": false,
            "data": { "param": param},
            "dataType": "native",
            "xhrFields": {
                responseType: 'blob'
              },
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
                var blob=new Blob([ret],{type: "application/x-pdf"});
                console.log(blob);
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download="prova.pdf";
                link.click();
            }
        });*/
       
    }

}