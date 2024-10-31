
function ermesCode(funzione) {

    this.funzione=funzione;

    this.reparti;
    this.marcorep;

    this.sede="";
    this.mrep="";
    this.rep="";

    this.loadReparti=function(str) {
        this.reparti=$.parseJSON(atob(str));
    }

    this.loadMacrorep=function(str) {
        this.macrorep=$.parseJSON(atob(str));
    }

    this.loadDefColl=function(str) {
        var temp=$.parseJSON(atob(str));

        if (temp!=='undefined') {
            this.sede=(temp.sede?temp.sede:'');
            this.mrep=(temp.macrorep?temp.macrorep:'');
            this.rep=(temp.reparto?temp.reparto:'');
        }
    }

    this.initPanorama=function() {

        var flag=false;
        
        $('#ermes_sede').append(new Option('Tutte',''));

        for (var x in this.reparti) {
            $('#ermes_sede').append(new Option(x,x));
            if (x==this.sede) {
                $('#ermes_sede option[value="'+x+'"]').prop("selected",true);
                flag=true;
            }
        }

        this.changeSede(flag?this.sede:'');
    }

    this.changeSede=function(val) {

        $("#ermes_macrorep").empty();

        $('#ermes_macrorep').append(new Option('Tutti',''));

        var flag=false;

        //val = indice sede
        if (val=='') {
            for (var x in this.macrorep) {
                $('#ermes_macrorep').append(new Option(x+' - '+this.macrorep[x],x));
                if (x==this.mrep) {
                    $('#ermes_macrorep option[value="'+x+'"]').prop("selected",true);
                    flag=true;
                }
            }
        }
        else {
            for (var x in this.reparti[val]) {
                $('#ermes_macrorep').append(new Option(x+' - '+this.macrorep[x],x));
                if (x==this.mrep) {
                    $('#ermes_macrorep option[value="'+x+'"]').prop("selected",true);
                    flag=true;
                }
            }
        }

        this.sede=val;

        //se cambio sede automaticamente i macroreparti diventano "tutti"
        this.changeMacrorep(flag?this.mrep:'');
    }

    this.changeMacrorep=function(val) {

        $("#ermes_reparto").empty();

        $('#ermes_reparto').append(new Option('Tutti',''));

        //val = indice macroreparto
        if (val=='') {
            if (this.sede!="") {
                for (var x in this.reparti[this.sede]) {
                    for (var y in this.reparti[this.sede][x]) {
                        $('#ermes_reparto').append(new Option(y+' - '+this.reparti[this.sede][x][y],y));
                    }
                }
            }
            else {
                for (var z in this.reparti) {
                    for (var x in this.reparti[z]) {
                        for (var y in this.reparti[z][x]) {
                            $('#ermes_reparto').append(new Option(y+' - '+this.reparti[z][x][y],y));
                        }
                    }
                }
            }
        }
        else {
            if (this.sede!="") {
                for (var y in this.reparti[this.sede][val]) {
                    $('#ermes_reparto').append(new Option(y+' - '+this.reparti[this.sede][val][y],y));
                }
            }
            else {
                for (var z in this.reparti) {
                    for (var x in this.reparti[z]) {
                        if (x!=val) continue;
                        for (var y in this.reparti[z][x]) {
                            $('#ermes_reparto').append(new Option(y+' - '+this.reparti[z][x][y],y));
                        }
                    }
                }
            }
        }

        if (this.reparto!="") {
            $('#ermes_reparto option[value="'+this.rep+'"]').prop("selected",true);
        }
        
        this.mrep=val;
    }

    this.loadPanorama=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "sede":$('#ermes_sede').val(),
            "mrep":$('#ermes_macrorep').val(),
            "reparto":$('#ermes_reparto').val(),
            "flagGestito":($('#ermes_flag_gestito').prop('checked'))?1:0,
            "contesto":contesto
        }

        //console.log(JSON.stringify(param));

        $('#ermes_panorama_body').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_panorama.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_panorama_body').html(ret);
                
            }
        });

    }

    this.loadGestione=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "coll":$('#ermes_coll').val(),
            "contesto":contesto
        }

        $('#ermes_gestione_body').html(window._nebulaMain.setWaiter());

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_gestione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                $('#ermes_gestione_body').html(ret);
                
            }
        });

    }

    this.loadMiei=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "contesto":contesto,
            "gestione":$('#ermes_miei_gestione').val(),
            "tipo":$('#ermes_miei_stato').val(),
            "da":$('#ermes_miei_da').val(),
            "a":$('#ermes_miei_a').val()
        }

        if (param.da==="undefined" || param.a==="undefined") {
            alert('Errore date');
            return;
        }

        if (param.da=="" || param.a=="" || param.a<param.da) {
            alert('Errore date');
            return;
        }

        $('#ermes_gestione_body').html(window._nebulaMain.setWaiter());

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_miei.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                $('#ermes_miei_body').html(ret);
                
            }
        });

    }

    this.newTicket=function(id,caller) {

        $('#ermes_util_body').html(window._nebulaMain.setWaiter());

        $('#ermes_main').hide();
        $('#ermes_util').show();

        param={
            'id':id,
            'logged':window._nebulaMain.getMainLogged(),
            'padre':0,
            'caller':caller
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_ticket.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var res=$.parseJSON(ret);
                
                //$('#tml_search_main').html(ret);
                $('#ermes_util_body').html(atob(res.txt));
                
            }
        });
    }

    this.apriTicket=function(id,caller) {

        if (id=='0') {
            alert('Utente non abilitato per questo Ticket.');
            return;
        }

        $('#ermes_util_body').html(window._nebulaMain.setWaiter());

        $('#ermes_main').hide();
        $('#ermes_util').show();

        param={
            'id':id,
            'logged':window._nebulaMain.getMainLogged(),
            'caller':caller
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_ticket.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                var res=$.parseJSON(ret);

                //console.log(window._nebulaMain.b64DecodeUnicode(res.log));

                $('#ermes_util_body').html(window._nebulaMain.b64DecodeUnicode(res.txt));

                /*if (res.stato==1) {
                    $('#ermes_util_body').html(window._nebulaMain.b64DecodeUnicode(res.txt));
                }
                else {
                    $('#ermes_util_body').html('Utente non abilitato a vedere il Ticket.');
                }*/ 
            }
        });
    }

    this.chiudiTicket=function(logged) {

        //alert(logged);

        if (window.hasOwnProperty('_ermesTicket')) {
            window._ermesTicket.unchain(logged);
        }

        $('#ermes_util_body').html('');

        $('#ermes_main').show();
        $('#ermes_util').hide();

        clearTimeout(window._ermesTicket.ermesTO);

    } 

    this.newMonoTicket=function(caller,reparto,categoria) {

        $('#ermes_util_body').html(window._nebulaMain.setWaiter());

        $('#ermes_main').hide();
        $('#ermes_util').show();

        param={
            'logged':window._nebulaMain.getMainLogged(),
            'padre':0,
            'caller':caller,
            'reparto':reparto,
            'categoria':categoria
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/new_mono_ticket.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var res=$.parseJSON(ret);
                
                //$('#tml_search_main').html(ret);
                $('#ermes_util_body').html(atob(res.txt));
                
            }
        });
    }

    //////////////////////////////////////////////////////////////////////////////

    this.monoPanorama=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "monoReparto":$('#ermes_mono_reparto').val(),
            "monoCategoria":$('#ermes_mono_categoria').val(),
            "stato":$('#ermes_mono_panorama_stato').val(),
            "testo":$('#ermes_mono_panorama_testo').val(),
            "contesto":contesto
        }

        console.log(JSON.stringify(param));

        $('#ermes_mono_panorama_body').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/mono_load_panorama.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_mono_panorama_body').html(ret);
                
            }
        });

    }

    //MONOPANORAMA
    /*this.monoPanoramaRic=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "monoReparto":$('#ermes_mono_reparto').val(),
            "monoCategoria":$('#ermes_mono_categoria').val(),
            "stato":$('#ermes_mono_panorama_stato').val(),
            "testo":$('#ermes_mono_panorama_testo').val(),
            "contesto":contesto
        }

        //console.log(JSON.stringify(param));

        $('#ermes_mono_panorama_body').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/mono_load_panorama.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_mono_panorama_body').html(ret);
                
            }
        });
    }*/

    this.monoGestione=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "monoReparto":$('#ermes_mono_reparto').val(),
            "monoCategoria":$('#ermes_mono_categoria').val(),
            "flagGestito":1,
            "contesto":contesto
        }

        //console.log(JSON.stringify(param));

        $('#ermes_mono_gestione_body').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/mono_load_panorama.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_mono_gestione_body').html(ret);
                
            }
        });

    }

    this.monoMiei=function(contesto) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "contesto":contesto,
            "gestione":$('#ermes_mono_miei_gestione').val(),
            "tipo":$('#ermes_mono_miei_stato').val(),
            "monoReparto":$('#ermes_mono_reparto').val(),
            "monoCategoria":$('#ermes_mono_categoria').val(),
            "testo":$('#ermes_mono_miei_testo').val()
        }

        //console.log(JSON.stringify(param));

        $('#ermes_mono_miei_body').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/mono_load_miei.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_mono_miei_body').html(ret);
                
            }
        });

    }

}