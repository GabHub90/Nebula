
function comestCode(funzione) {

    this.funzione=funzione;

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.setCommessa=function(commessa) {

        $('input[js_chk_home_tipo="comest_commessa"]').val(commessa);

        $('#ribbon_comest_commessa').val('');
        $('#ribbon_comest_telaio').val('');
        $('#ribbon_comest_targa').val('');
        $('#ribbon_comest_desc').val('');
        $('#ribbon_comest_dms').val('');
        $('#ribbon_comest_odl').val('');

        window._nebulaApp.ribbonExecute();
    }

    this.setVeicolo=function(obj) {

        var info=$.parseJSON(atob($(obj).data('info')));

        if (!info) {
            alert('Errore passaggio informazioni!');
            return;
        }

        $('#ribbon_comest_telaio').val(info.telaio);
        $('#ribbon_comest_targa').val(info.targa);
        $('#ribbon_comest_desc').val(info.descrizione);
        $('#ribbon_comest_dms').val(info.dms);
        $('#ribbon_comest_odl').val(info.odl);

        window._nebulaApp.ribbonExecute();
    }

    this.closeCommessa=function() {

        $('#ribbon_comest_commessa').val('');
        $('#ribbon_comest_telaio').val('');
        $('#ribbon_comest_targa').val('');
        $('#ribbon_comest_desc').val('');
        $('#ribbon_comest_dms').val('');
        $('#ribbon_comest_odl').val('');

        $('#nebulaFunctionBody_home').html('');

    }

    this.selectCommessaFromLista=function(indice) {

        var param={
            'commessa':indice
        }

        $('#comest_liste').hide();
        $('#comest_liste_commessa').css('display','block');
        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/select_commessa.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });
    }

    this.allineaChiuse=function() {

        var param={}

        $('#comest_liste').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/allinea_chiuse.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window._nebulaApp.ribbonExecute();
            }
        });
    }
    
}