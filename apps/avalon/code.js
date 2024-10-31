
function avalonCode(funzione) {

    this.funzione=funzione;

    this.lockOdl=false;

    this.nebulaAppSetup=function() {

        var d=$("#ribbon_avl_setday").val();
        this.setDay(d);
    };

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.setDay=function(day) {

        window._nebulaMain.checkTime();

        if (day=="") return;

        this.chiudiSearch();

        $("#ribbon_avl_setday").val(day);

        var contesto=window._nebulaMain.getContesto();
        var temp=contesto['mainApp'].split(':');

        var param={
            "reparto":$('#avalon_officina').val(),
            "inizio":day,
            "fine":day,
            "odlFlag":(temp[0]=='isla')?1:0
        }

        //alert(JSON.stringify(temp));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/set_day.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#nebula_avalon_right').html(ret);
            }
        });

    }

    this.loadDay=function(flag) {

        var d=$('#ribbon_avl_setday').val();

        if (!d || d=='') return;

        var param={
            "reparto":$('#avalon_officina').val(),
            "officina":$('#avalon_officina option:selected').data('officinaconcerto'),
            "inizio":d,
            "fine":d,
            "odlFlag":flag
        }

        //console.log(JSON.stringify(param));

        //AJAX prenotazioni
        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/load_pren.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#avalon_right_pren').html(ret);
            }
        });

        //AJAX lavorazioni
        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/load_lav.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#avalon_right_lav').html(ret);
            }
        });
    }

    this.graffaPdf=function() {

        var rifs=[];

        $('input[id^="avalonPrenCheckbox"]:checked').each(function() {

            var t={
                "rif":$(this).data('rif'),
                "dms":$(this).data('dms')
            }

            rifs.push(t);
        });

        if (rifs.length==0) return;

        var param={
            "reparto":$('#avalon_officina').val(),
            "rifs":rifs
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/graffa_pdf.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });

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

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/open_odl.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#avalon_odielle').html(ret);

                window._avalon_divo.setStato(1,'V');
                window._avalon_divo.selTab(1);

                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=true;

            }
        });

    }

    this.closeOdl=function() {

        delete window._nebulaOdl;

        $('#avalon_odielle').html('<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>');
        $('#avalon_storico').html('<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>');

        window._avalon_divo.setStato(1,'Y');
        window._avalon_divo.setStato(2,'Y');
        window._avalon_divo.selTab(0);

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=false;

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
                window._avalon_divo.setStato(2,'V');

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

    this.openChime=function(reparto,pren,day,dms) {

        $("#avalon_agenda").hide();
        $("#avalon_chime_utilityDiv").show();

        $("#avalon_chime_utilityDivBody").html(this.setWaiter);

        var param={
            "reparto":reparto,
            "pren":pren,
            "day":day,
            "pratica":"",
            "dms":dms
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chime/loader.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $("#avalon_chime_utilityDivBody").html(ret);

            }
        });
    }

    this.closeChime=function() {

        $("#avalon_agenda").show();
        $("#avalon_chime_utilityDiv").hide();
        $("#avalon_chime_utilityDivBody").html("");
    }

    this.search=function(reparto) {

        var src=$('#avalon_agenda_search').val().trim();
        var inizio=window._calnav_avalon.getToday();

        if (!src || src=="" || src.length<3) return;
        if (!inizio || inizio=="" || src.length<3) return;

        var param={
            "inizio":inizio,
            "reparto":reparto,
            "search":src
        }

        $('#nebula_avalon_search_body').html(this.setWaiter());
        $('#nebula_avalon_search').css('display','inline-block');
        $('#nebula_avalon_right').css('display','none');

        console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/search.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#nebula_avalon_search_body').html(ret);
                
            }
        });
    }

    this.chiudiSearch=function() {

        $('#nebula_avalon_search_body').html('');
        $('#nebula_avalon_right').css('display','inline-block');
        $('#nebula_avalon_search').css('display','none'); 

    }

}
