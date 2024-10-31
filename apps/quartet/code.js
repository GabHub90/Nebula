
function quartetCode(funzione) {

    this.funzione=funzione;

    this.lock=false;

    //data di riferimento
    this.rif="";

    this.collSkemi={};

    this.edit={
        "coll":"",
        "arr":{}
    }

    this.quartetSetRif=function(d) {
        this.rif=d;
    }

    this.quartetChangeRep=function(reparto) {

        if (reparto=="") return;

        $('#ribbon_qt_reparto').val(reparto);

        window._nebulaApp.ribbonExecute();
    }

    this.loadCollSkemi=function(arr) {
        this.collSkemi=arr;
    }

    this.quartetSelColl=function(IDcoll) {

        if (this.lock) return;

        if (IDcoll==this.edit.coll) {
            this.quartetConfirmColl();
            return;
        }

        this.quartetUpdateColl(IDcoll);
    }

    this.quartetUpdateColl=function(IDcoll) {

        /*"11":{
            "AU1220S":{"panorama":55,"collaboratore":11,"skema":"AU1220S","turno":"11","data_i":"20201201","data_f":"21001231","posizione":1,"colore":"#3DB300"},
            "SB_PATEC":{"panorama":55,"collaboratore":11,"skema":"SB_PATEC","turno":"12","data_i":"20200701","data_f":"21001231","posizione":2,"colore":"#FB0300"}
        }*/

        //opacizza tutti gli schemi
        $('.schemiGrigliaCover').css('background-color','#ffffffcc');
        //schiarisci tutti i nomi
        $('div[id^="quartetCollDiv_"]').css('background-color','transparent');
        //resetta le immagini dei nomi
        if (this.edit.coll!=IDcoll) {
            $('img[id^="quartetCollImg_"]').prop('src','http://'+location.host+'/nebula/apps/ensamble/img/edit.png');
        }

        //console.log(JSON.stringify(this.collSkemi[IDcoll]));

        if (this.edit.coll!=IDcoll) {

            this.edit.coll=IDcoll;

            if (IDcoll in this.collSkemi) {
                Object.assign(this.edit.arr, this.collSkemi[IDcoll]);
            }
            else this.edit.arr={};

            $('#quartetCollDiv_'+IDcoll).css('background-color','darkgray');
            $('#quartetCollImg_'+IDcoll).prop('src','http://'+location.host+'/nebula/apps/quartet/img/ok.png');
        }

        for (var x in this.edit.arr) {

            var obj=this.edit.arr[x];
            //alert('.schemiGrigliaCover[data-panorama="'+t.panorama+'"][data-codice="'+x+'"][data-turno="'+t.turno+'"]');

            $('.schemiGrigliaCover[data-panorama="'+obj.panorama+'"][data-codice="'+x+'"][data-blocco="'+obj.turno+'"]').each(function() {
                var color='#fbff006b';
                if (obj.edit && obj.edit.op=='delete') color='#8888886b';
                $(this).css('background-color',color);
            });

            //se è switch occorre scurire il turno originale
            if (obj.edit && obj.edit.op=='switch') {
                $('.schemiGrigliaCover[data-panorama="'+obj.panorama+'"][data-codice="'+x+'"][data-blocco="'+obj.edit.turnoBAK+'"]').css('background-color','#8888886b');
            }
            
        }
    }

    this.quartetConfirmColl=function() {

        //console.log(JSON.stringify(this.edit.arr));

        //#######################
        //verifica esistenza modifiche
        var ed=0;
        var txt="";
        for (var x in this.edit.arr) {
            if ('edit' in this.edit.arr[x]) {
                ed++;
                if (this.edit.arr[x].edit.op=='insert') txt+='ABILITARE '+x+' ('+this.edit.arr[x].turno+') dal '+window._nebulaMain.data_db_to_ita(this.edit.arr[x].data_i)+'\n';
                if (this.edit.arr[x].edit.op=='delete') txt+='ANNULLARE '+x+' ('+this.edit.arr[x].turno+') dal '+window._nebulaMain.data_db_to_ita(this.edit.arr[x].data_f)+'\n';
                if (this.edit.arr[x].edit.op=='switch') txt+='SPOSTARE '+x+' ('+this.edit.arr[x].edit.turnoBAK+') a ('+this.edit.arr[x].turno+') dal '+window._nebulaMain.data_db_to_ita(this.edit.arr[x].data_i)+'\n';
            }
        }

        //nel caso chiedere conferma con un RECAP
        if (ed>0) {
            if ( confirm('CONFERMA:\n'+txt) ) {
                //aggiornare il DB e ricaricare la pagina
                $.ajax({
                    "url": 'http://'+location.host+'/nebula/apps/quartet/core/update_collsk.php',
                    "async": true,
                    "cache": false,
                    "data": {"param": this.edit.arr},
                    "type": "POST",
                    "success": function(ret) {
                        //console.log(ret);

                        var r=$.parseJSON(ret);

                        if (r && r.alert!="") {
                            alert(r.alert);
                        }
        
                        window._nebulaApp.ribbonExecute();
                    }
                });
            }
        }
          
        //#######################

        //se non ci sono modifiche:

        //schiarisci tutti gli schemi
        $('.schemiGrigliaCover').css('background-color','transparent');
        //schiarisci tutti i nomi
        $('div[id^="quartetCollDiv_"]').css('background-color','transparent');
        //resetta le immagini dei nomi
        $('img[id^="quartetCollImg_"]').prop('src','http://'+location.host+'/nebula/apps/ensamble/img/edit.png');

        this.edit.coll='';
        this.edit.arr={}
        
    }

    this.quartetCheckSkema=function(obj) {

        if (this.edit.coll=="" || this.rif=="") return;

        var panorama=$(obj).data('panorama');
        var codice=$(obj).data('codice');
        var blocco=$(obj).data('blocco');

        //alert(panorama+' '+codice+' '+blocco);

        if ( !(codice in this.edit.arr) ) {

            //var d=$('#ribbon_qt_date').val();
            if ( confirm("Vuoi ABILITARE lo schema "+codice+" ("+blocco+") per il collaboratore "+this.edit.coll+" a partire dal "+window._nebulaMain.data_db_to_ita(this.rif)+' ?') ) {
                this.edit.arr[codice]={
                    "panorama":panorama,
                    "skema":codice,
                    "turno":blocco,
                    "collaboratore":this.edit.coll,
                    "data_i":this.rif,
                    "data_f":'21001231',
                    "edit":{
                        'op':'insert'
                    }
                }
            }
        }

        else if (codice in this.edit.arr) {

            if (!this.edit.arr[codice].edit) {

                if (this.edit.arr[codice].turno==blocco) {

                    //var d=$('#ribbon_qt_date').val();
                    if ( confirm("Vuoi ESCLUDERE lo schema "+codice+" ("+blocco+") per il collaboratore "+this.edit.coll+" a partire dal "+window._nebulaMain.data_db_to_ita(this.rif)+' (se si attiva un altro blocco l\'esclusione sarà anticipata al giorno precddente)?') ) {
                        this.edit.arr[codice].edit={'op':'delete'};
                        this.edit.arr[codice].data_f=this.rif;
                    }
                }
            }

            else if (this.edit.arr[codice].edit.op=='delete') {
                if (this.edit.arr[codice].turno!=blocco) {
                    var d=$('#ribbon_qt_date').val();
                    if ( confirm("Vuoi ABILITARE lo schema "+codice+" ("+blocco+") per il collaboratore "+this.edit.coll+" a partire dal "+window._nebulaMain.data_db_to_ita(this.rif)+' ?') ) {
                        this.edit.arr[codice].edit={
                            'op':'switch',
                            'turnoBAK':this.edit.arr[codice].turno,
                            'dataBAK':this.edit.arr[codice].data_i,
                        }
                        this.edit.arr[codice].turno=blocco;
                        this.edit.arr[codice].data_i=this.rif;
                    }
                }
                else {
                    this.edit.arr[codice].edit=false;
                }
            }

            else if (this.edit.arr[codice].edit.op=='insert') {
                delete this.edit.arr[codice];
            }

            else if (this.edit.arr[codice].edit.op=='switch') {
                if (this.edit.arr[codice].turno==blocco) {
                    this.edit.arr[codice].turno=this.edit.arr[codice].edit.turnoBAK;
                    this.edit.arr[codice].data_i=this.edit.arr[codice].edit.dataBAK;
                    this.edit.arr[codice].edit.op='delete';
                }
            }
        }

        this.quartetUpdateColl(this.edit.coll);
    }

    this.quartetOpenEdit=function(tipo,today) {
        //alert(tipo+' '+today);

        window._nebulaMain.checkTime();

        param={
            "tipo":tipo,
            "today":today,
            "reparto":$('#qt_reparto_select').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/quartet/core/edit.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);

                $('#skemicontainer_'+param.tipo).html(ret);

                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lock=true;
            }
        });

    }

    this.quartetNewPanorami=function(today) {
        //questa funzione serve quando per il reparto NON è MAI stato definito un panorama
        //genera per il reparto attuale i panorami A e P
        //a partre dal mese attuale

        var param={
            "reparto":$('#ribbon_qt_reparto').val(),
            "am":today.substr(0,6),
            "oa":window._kassettone_turni.getVal()
        }

        //console.log(JSON.stringify(param));

        if ( !confirm('Vuoi creare un panorama a partire da '+param['am']+' ?') ) return;

        if (param.reparto && param.am && param.oa) {

            $.ajax({
                "url": 'http://'+location.host+'/nebula/apps/quartet/core/new_pano.php',
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

}
