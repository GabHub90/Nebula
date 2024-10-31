
function workshopCode(funzione) {

    this.funzione=funzione;

    this.mark=Date.now();

    this.wspRefresh=function() {
        window._nebulaApp.ribbonExecute();
    }

    this.openTecnico=function(id,timb) {

        $('#ribbon_wsp_tecnico').val(id);
        $('#ribbon_wsp_timb').val(timb);

        window._nebulaApp.ribbonExecute();
    }

    this.closeTecnico=function(id) {

        $('#ribbon_wsp_tecnico').val('');
        $('#ribbon_wsp_timb').val('');

        window._nebulaApp.ribbonExecute();
    }

    this.openTimbratura=function(tipo) {

        $('#ribbon_wsp_timb').val(tipo);

        window._nebulaApp.ribbonExecute();   
    }

    this.closeTimbratura=function() {
        
        $('#ribbon_wsp_timb').val('');

        window._nebulaApp.ribbonExecute();   
    }

    this.start=function(coll) {

    }

    this.selectActualOdl=function() {

        $('#wsp_timbratura_nextlam').val('');
        $('#wsp_timbratura_nextDiv').html('');
        $('#wsp_timbratura_nextDiv').hide();

        $('#wsp_timbratura_actualDiv').show();

    }

    this.selectNextOdl=function(officina,ID_coll) {

        var rif=$('#wsp_timbratura_nextlam').val();

        if (!rif || rif=="") return;

        var param={
            "wsp_officina":officina,
            "rif":rif,
            "dms":$('#wsp_timbratura_dms').val(),
            "ID_coll":ID_coll
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/next_odl.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#wsp_timbratura_nextDiv').html(ret);
                $('#wsp_timbratura_nextDiv').show();
                $('#wsp_timbratura_actualDiv').hide();
                
            }
        });
    }

    this.restartMarcatura=function(dms,ID) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "dms":dms,
            "ID":ID
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/restart_marc.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._nebulaApp.ribbonExecute();
                
            }
        });

    }

    this.fineMarcatura=function(dms,ID) {

        if (!confirm("Vuoi chiudere la marcatura?")) return;

        var param={
            "dms":dms,
            "ID":ID
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/fine_marc.php',
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

    //this.inizioMarcatura=function(dms,IDcoll,odl,lam,anno,id_cliente,tipo_doc,data_doc,num_doc) {
    this.inizioMarcatura=function(dms,IDcoll,odl,lam) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "dms":dms,
            "IDcoll":IDcoll,
            "odl":odl,
            "lam":lam,
            "statoLamentato":$('#wsp_timbratura_lastlam').val()
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/inizio_marc.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);   
            }
        });

    }

    this.chiudiMarcatura=function(param) {
        //è come FINE ma ammette più parametri

        //alert(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/fine_marc.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);  
            }
        });
    }

    this.special_CHI=function(IDcoll,checkID,officina) {

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_CHI.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    if (confirm("Vuoi ALLINEARE la marcatura alle: "+obj.o_fine+" ?")) {
                        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].chiudiMarcatura(obj);
                    }
                }
                else {
                    alert('Non è possibile ALLINEARE la marcatura');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_EXT=function(IDcoll,checkID,officina) {

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_EXT.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    if (confirm("Vuoi CHIUDERE la marcatura alle: "+obj.o_fine+" ?")) {
                        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].chiudiMarcatura(obj);
                    }
                }
                else {
                    alert('Non è possibile CHIUDERE la marcatura');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_ATT=function(IDcoll,checkID,officina) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_ATT.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);
                }
                else {
                    alert('Non è possibile APRIRE la marcatura in attesa');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_SER=function(IDcoll,checkID,officina) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_SER.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);
                }
                else {
                    alert('Non è possibile APRIRE la marcatura in servizio');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_PUL=function(IDcoll,checkID,officina) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_PUL.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);
                }
                else {
                    alert('Non è possibile APRIRE la marcatura in pulizia');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_PRV=function(IDcoll,checkID,officina,rif) {

        //cerca di evitare doppi click
        if ((Date.now()-this.mark)<2000) return;

        var param={
            "IDcoll":IDcoll,
            "statoLamentato":$('#wsp_timbratura_lastlam').val(),
            "checkID":checkID,
            "wsp_officina":officina,
            "rifOdlPRV":rif
        }

        if (!param.statoLamentato || param.statoLamentato=="") {
            alert ("Specificare stato attuale !!");
            return;
        }

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].mark=Date.now();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_PRV.php',
            "async": false,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);
                }
                else {
                    alert('Non è possibile APRIRE la marcatura in prova');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    this.special_ANT=function(IDcoll,checkID,officina) {

        if (!confirm("Vuoi chiudere ANTICIPATAMENTE la marcatura?")) return;

        var param={
            "IDcoll":IDcoll,
            "checkID":checkID,
            "wsp_officina":officina
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/special_ANT.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj && obj.result=='OK') {
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeTimbratura(obj);
                }
                else {
                    alert('Non è possibile CHIUDERE anticipatamente la marcatura');
                    window._nebulaApp.ribbonExecute();
                }
                
            }
        });

    }

    //======================================================

   
}