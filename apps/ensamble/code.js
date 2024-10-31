
function ensambleCode(funzione) {

    this.funzione=funzione;

    this.selectReparto=function(reparto) {

        $('#ribbon_ens_reparto').val(reparto);

        window._nebulaApp.ribbonExecute();
    }

    this.backToOverview=function() {

        $('#ribbon_ens_reparto').val("");
        $('#ribbon_ens_today').val("");

        window._nebulaApp.ribbonExecute();
    }

    this.add=function() {

        var param={
            "today":window._calnav_ensamble.getToday(),
            "reparto":$('#ribbon_ens_reparto').val()
        }

        $('#ensRepartoInfoMain').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ensamble/core/add_div.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('#ensRepartoInfoMain').html(ret);
            }
        });

    }

    this.edit=function(coll,panorama) {

        var param={
            "reparto":$('#ribbon_ens_reparto').val(),
            "coll":coll,
            "panorama":panorama,
            "d":window._calnav_ensamble.getToday()
        }

        $('#ensRepartoInfoMain').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ensamble/core/edit_div.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('#ensRepartoInfoMain').html(ret);
            }
        });

    }

    this.confirmAdd=function() {

        var param={
            "gruppo":$('#addcoll_gruppo').val(),
            "collaboratore":$('#addcoll_coll').val(),
            "data_i":$('#addcoll_today').val()
        }

        if (!param.gruppo || !param.collaboratore || !param.data_i) return;
        if (param.gruppo=='' || param.collaboratore=='' || param.data_i=='') return;

        //console.log(JSON.stringify(param));

        if (!confirm('Vuoi inserire il collaboratore '+param.collaboratore+' a partire dalla data: '+window._nebulaMain.data_db_to_ita(param.data_i))) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ensamble/core/add_confirm.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
                window._nebulaApp.ribbonExecute();
            }
        });
    }

    this.confirmEdit=function(coll,idgruppo,panorama) {

        var param={
            "id_gruppo":idgruppo,
            "coll":coll,
            "panorama":panorama,
            "data_f":window._calnav_ensamble.getToday()
        }

        //console.log(JSON.stringify(param));

        if (!param.id_gruppo || !param.coll || !param.data_f) return;
        if (param.id_gruppo=='' || param.coll=='' || param.data_f=='') return;

        if (!confirm('Il collaboratore '+param.coll+' verrà escluso dal reparto a partire dalla data: '+window._nebulaMain.data_db_to_ita(param.data_f)+'. Verranno inoltre chiusi tutti gli schemi di appartenenza al reparto nella stessa data.')) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ensamble/core/edit_confirm.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
                window._nebulaApp.ribbonExecute();
            }
        });
    }

}