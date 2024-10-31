
function ideskCode(funzione) {

    this.funzione=funzione;

    this.lockOdl=false;

    this.update=function() {

        $('#ribbon_idk_visuale').val($('input[name="idk_inofficina_tipolista"]:checked').val());
        $('#ribbon_idk_rc').val($('#idk_inofficina_rc').val());

        //alert( $('#ribbon_idk_visuale').val());

        window._nebulaApp.ribbonExecute();
    }

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.inarrivo=function(reparto) {

        var da=$('#idk_inarrivo_data_da').val();
        var a=$('#idk_inarrivo_data_a').val();

        if (!da || !a || da=='' || a=='') return;

        if (a<da) return;

        $('#idk_inarrivo').html(this.setWaiter());

        var param={
            "reparto":reparto,
            "da":da,
            "a":a,
            "rc":$('#ribbon_idk_rc').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/idesk/core/inarrivo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#idk_inarrivo').html(ret);     
            }
        });
    }

    this.inofficina=function(reparto,cliente) {

        $('#idk_inofficina').html(this.setWaiter());
        $('#idk_sospesi').html(this.setWaiter());
        $('#idk_esterni').html(this.setWaiter());
        $('#idk_ricambi').html(this.setWaiter());
        $('#idk_pronto').html(this.setWaiter());
        $('#idk_timeline').html(this.setWaiter());

        var param={
            "reparto":reparto,
            "cliente":cliente,
            "visuale":$('#ribbon_idk_visuale').val(),
            "rc":$('#ribbon_idk_rc').val(),
            "marca":$('#ribbon_idk_marca').val(),
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/idesk/core/inofficina.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                var obj=$.parseJSON(ret);

                if (obj) {
                    var t=atob(obj.sospesi);
                    $('#idk_sospesi').html(t);
                    if (t!="") window._idktop_divo.setStato(1,'R');
                    else window._idktop_divo.setStato(1,'Y');

                    t=atob(obj.esterni);
                    $('#idk_esterni').html(t);
                    if (t!="") window._idktop_divo.setStato(2,'R');
                    else window._idktop_divo.setStato(2,'Y');

                    t=atob(obj.ricambi);
                    $('#idk_ricambi').html(t);
                    if (t!="") window._idktop_divo.setStato(3,'R');
                    else window._idktop_divo.setStato(3,'Y');

                    t=atob(obj.pronto);
                    $('#idk_pronto').html(t);
                    if (t!="") window._idktop_divo.setStato(4,'R');
                    else window._idktop_divo.setStato(4,'Y');

                    $('#idk_inofficina').html(atob(obj.inofficina));
                    $('#idk_timeline').html(atob(obj.timeline));
                }
            }
        });
    }

    this.filterMarca=function() {

        var marca=$('#idk_filter_marca').val();

        if (marca=='') {
            $('div[id^="nebula_pratica_"]').show();
        }

        else {
            $('div[id^="nebula_pratica_"]').hide();
            $('div[id^="nebula_pratica_"][data-marca="'+marca+'"]').show();
        }

        $('#ribbon_idk_marca').val(marca);

    }

    this.filterCliente=function() {

        $('#ribbon_idk_cliente').val($('#idk_filter_cliente').val());

        window._nebulaApp.ribbonExecute();
    }

    this.openOdl=function(rif,dms) {

        if (this.lockOdl) return;

        var arr={
            'rif':rif,
            'dms':dms,
            'lista':'pre'
        }

        window._nebulaApp.setArgs(arr);

        var param=window._nebulaApp.collectParams();

        //console.log(JSON.stringify(param));

        $('#avalon_odielle').html(this.setWaiter());
        window._idesk_divo.selTab(1);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/open_odl.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#avalon_odielle').html(ret);

                window._idesk_divo.setStato(1,'V');

                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=true;

            }
        });

    }

    this.openCommessa=function(rif,dms) {

        if (this.lockOdl) return;

        var arr={
            'rif':rif,
            'dms':dms,
            'lista':'cli'
        }

        window._nebulaApp.setArgs(arr);

        var param=window._nebulaApp.collectParams();

        //console.log(JSON.stringify(param));

        $('#avalon_odielle').html(this.setWaiter());
        window._idesk_divo.selTab(1);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/open_odl.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#avalon_odielle').html(ret);

                window._idesk_divo.setStato(1,'V');

                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=true;

            }
        });

    }

    //di fatto sovrascrive la funzione di code.js di STORICO
    this.setAmbito=function(ambito) {

        $('#avalon_sto_ambito').val(ambito);

        var tt=$('#avalon_storico_refresh_tt').val();
        var km=$('#avalon_storico_refresh_km').val();
        this.loadStorico(tt,km);
    }

    this.loadStorico=function(tt,km) {

        var param=window._nebulaApp.collectParams();

        param.ribbon.sto_tt=tt;
        //non è nel ribbon ma in testa alle icone laterali dell'odl
        param.ribbon.sto_ambito=$('#avalon_sto_ambito').val();
        param.ribbon.sto_km=km;

        //console.log(JSON.stringify(param));

        $('#avalon_storico').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/load_storico.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#avalon_storico').html(ret);
                window._idesk_divo.setStato(2,'V');

            }
        });

    }

    this.closeOdl=function() {

        delete window._nebulaOdl;

        $('#avalon_odielle').html('<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>');
        $('#avalon_storico').html('<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>');

        window._idesk_divo.setStato(1,'Y');
        window._idesk_divo.setStato(2,'Y');
        window._idesk_divo.selTab(0);

        //window._idesktop.selTab(window._idesktop_divo.getSel());

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=false;

    }

    this.chiudiUtility=function() {

        $('#idesk_utilityDivBody').html('');
        $('#idesk_utilityDiv_head').html('');
        $('#idesk_utilityDiv').hide();
        $('#idesk_main_monitor').show();
    }

    this.search=function(reparto) {

        var src=$('#idk_search').val().trim();

        if (!src || src=="" || src.length<3) return;

        var c=$('#idk_filter_cliente').val();

        if (!c || c==='undefined') c="cliente";

        var param={
            "cliente":c,
            "reparto":reparto,
            "search":src
        }

        $('#idesk_utilityDivBody').html(this.setWaiter());
        $('#idesk_utilityDiv_head').html('<div style="text-align:center;font-size:1.1em;">Ricerca</div>');
        $('#idesk_utilityDiv').show();
        $('#idesk_main_monitor').hide();  

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/idesk/core/search.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#idesk_utilityDivBody').html(ret);
                
            }
        });
    }

    this.cercaNotifica=function(txt) {

        var rep=$('#idk_search').data('reparto');
        $('#idk_search').val(txt);
        this.search(rep);
    }

    ////////////////////////////////////////////////////////////////////7
    //COMEST dentro all'odl
    this.selectCommessaFromLista=function(indice) {

        //alert(indice);

        var param={
            "commessa":indice
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/select_commessa.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaOdlContainerDiv_utility_body').html('<div id="nebula_comest_main" style="width:100%;height:100%;">'+ret+'</div>');

                window._nebulaOdl.apriUtility();

                window._nebulaOdl.chiudiMain2();
            }
        });
    }

}