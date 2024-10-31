function nebulaGdm() {

    this.veicolo={};

    this.materiali={};

    this.nuova=false;

    this.app='main';

    this.ambito="";

    this.tempProprietario="";

    this.loadMateriali=function(m) {
        this.materiali=m;
    }

    this.loadVei=function(v) {
        this.veicolo=v;
    }

    this.refresh=function() {
        //REFRESH: occorre considerare che GDM può essere aperto in una finestra e non avere accesso al ribbon
        //questo modifica il metodo da chiamare per il REFRESH
        //NON sarà "window._nebulaApp.ribbonExecute();"
        //ma il metodo dell'applicazione che farà da viewer alternativo di GDM

        if (window._nebulaGdm.app=='main') window._nebulaApp.ribbonExecute();
        if (window._nebulaGdm.app=='odl') window._nebulaOdl.apriGDM();
        if (window._nebulaGdm.app=='workwshop') window._nebulaWS.getGDM(window._nebulaGdm.veicolo.telaio);
    }

    this.openUtil=function() {
        $('#gdm_gestione_util').show();
        $('#gdm_gestione_main').hide();
    }

    this.closeUtil=function() {
        $('#gdm_gestione_util_body').html('');
        $('#gdm_gestione_util').hide();
        $('#gdm_gestione_main').show();
    }

    this.refreshRichiesta=function(id,ambito) {

        this.ambito=ambito;

        var param={
            "idRi":id,
            "ambito":ambito
        }

        //alert(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/actual_richiesta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
 
                $('#gdm_actual_richiesta_'+window._nebulaGdm.ambito).html(ret);   
            }
        });
    }

    this.refreshRichiestaButton=function() {
        var id=$('#gdm_actual_richiesta_id').val();
        var ambito=$('#gdm_actual_richiesta_ambito').val();

        $('#gdm_actual_richiesta_'+ambito).html(''); 

        this.refreshRichiesta(id,ambito);
    }


    this.selectMateriale=function(id) {

        $('.gdmOperazioneDiv').css('background-color','transparent');

        $('.gdmOperazione_'+id).css('background-color','bisque');
    }

    this.editNote=function(id) {
        
        var txt=prompt('Modifica nota materiale '+id);
        txt=txt.trim();

        if (txt==='undefined') return;

        if (txt=="") {
            if (!confirm('La nota sarà cancellata: Confermi?')) return;
        }

        var param={
            "txt":txt,
            "id":id
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/edit_annotazione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                window._nebulaGdm.refresh();    
            }
        });


    }

    ////////////////////////////////////////////////////

    this.resetOp=function() {

        $('div[id^="gdm_busy"]').each(function() {
            if ($(this).data('busy')=='True') $(this).show();
            else $(this).hide();
        });

        $('div[id^="gdm_buttons"]').each(function() {
            if ($(this).data('busy')=='True') $(this).hide();
            else $(this).show();
        });

        //se non è possibile usarlo non esiste nemmeno
        $('#gdm_scambio').show();
    }


    this.updateNew=function() {

        $('#gdm_new_richiesta').html('');

        //scrivendo il div devono essere modificati i bottoni dei materiali ed il pulsante scambia
        //resetta
        this.resetOp();

        //se lines==0 nascondi il div "gdm_new_richiesta"
        if (this.nuova.lines==0) {
            this.nuova=false;
            $('#gdm_new_richiesta').hide();
            return;
        }

        //altrimenti scrivi il div e rendilo visibile
        var txt="";

        //il DIV può avere due titoli
        txt+='<div style="font-weight:bold;width:100%;text-align:center;">';
            txt+=(this.nuova.aperta)?'Aggiunta alla richiesta attuale':'Nuova richiesta';
        txt+='</div>';

        if (!this.nuova.aperta) {
            txt+='<div style="margin-top:5px;margin-bottom:5px;">';
                txt+='<span>Prenotazione:</span>';
                txt+='<input style="margin-left:5px;" type="date" value="'+window._nebulaMain.data_db_to_form(this.nuova.d)+'" onchange="window._nebulaGdm.editDataNew(this.value);" />';
            txt+='</div>';
        }

        var ambito=['Edit','Deposito','Vettura','Cliente'];

        for (var z in ambito) {

            if ( this.nuova.operazioni.hasOwnProperty(ambito[z]) ) {

                for (var x in this.nuova.operazioni[ambito[z]]) {

                    var idMat=this.nuova.operazioni[ambito[z]][x].id;

                    $('#gdm_busy_'+idMat).show();
                    $('#gdm_buttons_'+idMat).hide();

                    if (this.nuova.operazioni[ambito[z]][x].origine=='Deposito' || this.nuova.operazioni[ambito[z]][x].origine=='Vettura') $('#gdm_scambio').hide();

                    txt+='<div style="position:relative;font-size:0.9em;height:15px;">';
                        txt+='<div style="position:relative;display:inline-block;width:12%;vertical-align:top;font-size:0.9em;" >('+idMat+')</div>';
                        txt+='<div style="position:relative;display:inline-block;width:18%;vertical-align:top;" >'+(this.materiali[idMat].tipologia=='Pneumatici'?this.materiali[idMat].compoGomme:this.materiali[idMat].nome)+'</div>';
                        txt+='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >'+(this.materiali[idMat].tipologia=='Pneumatici'?this.materiali[idMat].tipoGomme:this.materiali[idMat].descrizione)+'</div>';
                        txt+='<div style="position:relative;display:inline-block;width:16%;vertical-align:top;" >'+this.nuova.operazioni[ambito[z]][x].origine+'</div>';
                        txt+='<div style="position:relative;display:inline-block;width:6%;vertical-align:top;text-align:center;height:100%;" >';
                            txt+='<img style="position:relative;width:10px;height:6px;top:55%;transform:translate(0px,-50%);" src="http://'+location.host+'/nebula/main/img/blackarrowR.png" />';
                        txt+='</div>';
                        txt+='<div style="position:relative;display:inline-block;width:16%;vertical-align:top;" >';
                            txt+=this.nuova.operazioni[ambito[z]][x].destinazione.substr(0,8);
                        txt+='</div>';
                        txt+='<div style="position:relative;display:inline-block;width:4%;vertical-align:top;" >';
                            txt+='<img style="position:relative;width:12px;height:12px;margin-left:5px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/gdm/img/chiudi.png" onclick="window._nebulaGdm.delNew(\''+idMat+'\',\''+this.nuova.operazioni[ambito[z]][x].destinazione+'\');" />';
                        txt+='</div>';
                    txt+='</div>';
                }
            }
        }

        txt+='<div style="margin-top:10px;width:100%;text-align:center;" >';
            txt+='<button style="background-color:burlywood;"';
                if (this.nuova.aperta) txt+=' onclick="window._nebulaGdm.confirmInsertNew();"';
                else  txt+=' onclick="window._nebulaGdm.confirmNew();"';
            txt+=' >'+(this.nuova.aperta?'Aggiungi':'Crea nuova Richiesta')+'</button>';
        txt+='</div>';

        $('#gdm_new_richiesta').html(txt);
        $('#gdm_new_richiesta').show();

    }

    this.addNew=function(id,origine,destinazione) {

        if (!this.materiali.hasOwnProperty(id)) {
            alert('materiale inesistente');
            return;
        }

        if (!this.nuova) {
            this.nuova={
                'd':window._nebulaMain.phpDate('Ymd'),
                'lines':0,
                'operazioni':{},
                'aperta':$('#gdm_richiesta_aperta').val()
            }
        }

        if ( !this.nuova.operazioni.hasOwnProperty(destinazione) ) this.nuova.operazioni[destinazione]=[];

        var obj={'id':id,'origine':origine,'destinazione':destinazione};

        this.nuova.operazioni[destinazione].push(obj);
        this.nuova.lines++

        this.updateNew();
    }

    this.scambio=function(dep,vet) {
        //in sostanza esegue due "addNew"
        this.addNew(dep,'Deposito','Vettura');
        this.addNew(vet,'Vettura','Deposito');
    }

    this.delNew=function(id,destinazione) {

        var temp=[];

        for (var x in this.nuova.operazioni[destinazione]) {

            if (this.nuova.operazioni[destinazione][x].id=id) continue;

            temp.push(this.nuova.operazioni[destinazione][x]);
        }

        this.nuova.operazioni[destinazione]=temp;
        this.nuova.lines--;

        this.updateNew();
    }

    this.editDataNew=function(d) {
        this.nuova.d=window._nebulaMain.data_form_to_db(d);
    }

    this.delRichiesta=function() {

        if (!confirm('Vuoi davvero cancellare la richiesta attiva?')) return;

        var aperta=$('#gdm_richiesta_aperta').val();

        if (!aperta) return;

        var param={
            "idRi":aperta
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/del_richiesta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                window._nebulaGdm.refresh();    
            }
        });

    }

    this.confirmNew=function() {

        if (!this.nuova) return;

        if (!this.nuova.aperta) {
            if (!this.nuova.d || this.nuova.d=="") {
                alert ('Data non corretta.');
                return;
            }
        }

        this.nuova.veicolo=this.veicolo;

        //console.log(JSON.stringify(this.nuova));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/confirm_new.php',
            "async": true,
            "cache": false,
            "data": {"param": this.nuova},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                window._nebulaGdm.refresh();    
            }
        });

    }

    //#######################################
    this.edit=function(id) {

        $('#gdm_gestione_util_body').html(window._nebulaMain.setWaiter());
        this.openUtil();

        var param={
            "id":id
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/edit_materiale.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                $('#gdm_gestione_util_body').html(atob(ret));
            }
        });
    }
    //#######################################

    this.chiudiRichiestaActual=function(ambito) {

        var param={
            "form":window._ckmf.salva(),
            "richiesta":$('#gdm_actual_richiesta_id').val(),
            "ambito":ambito
        }

        if (!param.form) {
            alert('Correggi i moduli !!!');
            return;
        }

        var flagSmaltite=0;

        if (ambito=='gdmr') {

            for (var x in param.form) {

                var countSmaltite=0;
                var totgomme=4;

                param.form[x].expo.smaltite=0;

                if (param.form[x].expo.usuraASx !== "undefined") {
                    if (parseInt(param.form[x].expo.usuraASx)<0) {
                        if (parseInt(param.form[x].expo.usuraASx)==-1) countSmaltite++;
                        param.form[x].expo.smaltite++;
                    }
                }
                else totgomme--;
                if (param.form[x].expo.usuraADx !== "undefined") {
                    if (parseInt(param.form[x].expo.usuraADx)<0) {
                        if (parseInt(param.form[x].expo.usuraADx)==-1) countSmaltite++;
                        param.form[x].expo.smaltite++;
                    }
                }
                else totgomme--;
                if (param.form[x].expo.usuraPSx !== "undefined") {
                    if (parseInt(param.form[x].expo.usuraPSx)<0) {
                        if (parseInt(param.form[x].expo.usuraPSx)==-1) countSmaltite++;
                        param.form[x].expo.smaltite++;
                    }
                }
                else totgomme--;
                if (param.form[x].expo.usuraPDx !== "undefined") {
                    if (parseInt(param.form[x].expo.usuraPDx)<0) {
                        if (parseInt(param.form[x].expo.usuraPDx)==-1) countSmaltite++;
                        param.form[x].expo.smaltite++;
                    }
                }
                else totgomme--;

                if (countSmaltite==totgomme) {
                    alert("Se le gomme vengono smaltite TUTTE, cambiare la destinazione.");
                    return;
                }

                if (countSmaltite>0) flagSmaltite++;
            }
        }

        if (flagSmaltite>0) {
            if (!confirm("Ci sono gomme SMALTITE, confermi?")) return;
        }

        console.log(JSON.stringify(param));

        if (!confirm("Confermi l'invio del modulo?")) return;

        param.smaltite=flagSmaltite;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/conferma_richiesta_actual.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });
    }

    //################################################################################################

    this.creaMateriale=function(proprietario) {
        
        //console.log(JSON.stringify(this.veicolo));

        $('#gdm_gestione_util_body').html(window._nebulaMain.setWaiter());

        //this.openUtil();

        var param={
            "proprietario":proprietario
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/crea_materiale.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                $('#gdm_gestione_util_body').html(atob(ret));
                window._nebulaGdm.openUtil();
            }
        });

    }

    this.confermaCrea=function() {

        var param={
            "form":window._ckmf.salva(),
            "telaio":this.veicolo.telaio.trim(),
            "veicolo":this.veicolo,
            "tipologia":$('#gdm_nuovo_form_tipologia').val()
        }

        //console.log(JSON.stringify(param));

        if (param.telaio==='undeifned' || param.telaio=='') {
            alert('Telaio non specificato!!');
            return;
        }

        if (!param.form) {
            alert('Correggi i moduli !!!');
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/conferma_crea.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });

    }

    this.confermaEdit=function(id) {

        var param={
            "form":window._ckmf.salva(),
            "telaio":this.veicolo.telaio.trim(),
            "veicolo":this.veicolo,
            "id":id
        }

        //console.log(JSON.stringify(param));

        if (id==='undeifned' || id=='') {
            alert('ID non specificato!!');
            return;
        }

        if (!param.form) {
            alert('Correggi i moduli !!!');
            return;
        }

        if (!confirm("Il materiale verrà modificato.")) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/conferma_edit.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });

    }

    this.confermaFake=function() {

        //si da per scontato che siano GOMME
        var param={
            "form":{
                "tipoGomme":$('#gdmForm_0_tipoGomme').val(),
                "compoGomme":$('#gdmForm_0_compoGomme').val(),
                "annotazioni":$('#gdmForm_0_annotazioni').val(),
                "destinazione":$('#gdmForm_0_destinazione').val()
            },
            "telaio":this.veicolo.telaio.trim(),
            "veicolo":this.veicolo,
            "tipologia":$('#gdm_nuovo_form_tipologia').val()
        }

        //console.log(JSON.stringify(param));

        if (param.telaio==='undeifned' || param.telaio=='') {
            alert('Telaio non specificato!!');
            return;
        }

        if (param.form.tipoGomme=='' || param.form.compoGomme=='' || param.form.tipoGomme==='undefined' || param.form.compoGomme==='undefined') {
            alert('È necessario specificare Tipo e Composizione!!!');
            return;
        }

        if (!confirm("Confermi l'inserimento del materiale provvisorio?")) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/conferma_fake.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });
    }

    this.annullaMateriale=function(id) {

        var param={
            "id":id
        }

        if (!confirm("Il materiale sarà ANNULLATO definitivamente (operazione non recuperabile)!!!")) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/annulla_materiale.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });
    }

    this.cambiaLocazione=function(id) {

        var param={
            "id":id,
            "locazione":"",
            "veicolo":this.veicolo
        }

        param.locazione=prompt('Nuova locazione:').trim();

        if(param.locazione==='undefined' || param.locazione=='') {
            alert ('Locazione non valida');
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/cambia_locazione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaApp.ribbonExecute();
                window._nebulaGdm.refresh(); 
            }
        });

    }

    ////////////////////////////////////////////////////////////////////////////////////
    this.stampaEtichetta=function(id) {

        var param={
            "id":id,
            "cliente":this.veicolo.nomeCliente,
            "targa":this.veicolo.targa
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/stampa_etichetta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf;charset=UTF-8");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }

    this.stampaEtichettaRichiesta=function(id,obj) {

        var temp=$.parseJSON(atob($(obj).data('info')));

        var param={
            "id":id,
            "cliente":temp.nomeCliente,
            "targa":temp.targa
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/stampa_etichetta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf;charset=UTF-8");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }

    this.stampaRichiesta=function(id) {

        var param={
            "id":id
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/stampa_richiesta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf;charset=UTF-8");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }


}