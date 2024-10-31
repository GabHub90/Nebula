
function gdmCode(funzione) {

    this.funzione=funzione;

    /*this.materiali={};

    this.nuova=false;

    this.loadMateriali=function(m) {
        this.materiali=m;
    }*/

    this.setVeicolo=function(dms,telaio) {

        if (!dms || dms=='' || !telaio || telaio=="") {
            alert('Vettura non selezionabile');
            return;
        }

        $('input[js_chk_'+this.funzione+'_tipo="gdm_telaio"]').val(telaio);
        $('input[js_chk_'+this.funzione+'_tipo="gdm_dms"]').val(dms);
        $('input[js_chk_'+this.funzione+'_tipo="gdm_tt"]').val('');

        window._nebulaApp.ribbonExecute();
    }

    this.setVeicoloRichiesta=function(telaio,dms) {

        if (dms!="") {
            $('input[js_chk_'+this.funzione+'_tipo="gdm_divo"]').val('1');
            this.setVeicolo(dms,telaio);
            return;
        }

        $('input[js_chk_'+this.funzione+'_tipo="gdm_telaio"]').val('');
        $('input[js_chk_'+this.funzione+'_tipo="gdm_dms"]').val('');
        $('input[js_chk_'+this.funzione+'_tipo="gdm_tt"]').val(telaio);
        $('select[js_chk_'+this.funzione+'_tipo="gdm_dmstt"] option:first').attr('selected',true);

        window._nebulaApp.ribbonExecute();
    }

    this.unsetVeicolo=function() {

        $('input[js_chk_'+this.funzione+'_tipo="gdm_telaio"]').val('');
        $('input[js_chk_'+this.funzione+'_tipo="gdm_dms"]').val('');
        $('input[js_chk_'+this.funzione+'_tipo="gdm_tt"]').val('');
        $('select[js_chk_'+this.funzione+'_tipo="gdm_dmstt"] option:first').attr('selected',true);

        window._nebulaApp.ribbonExecute();
    }

    /*this.selectMateriale=function(id) {

        $('.gdmOperazioneDiv').css('background-color','transparent');

        $('.gdmOperazione_'+id).css('background-color','bisque');
    }*/

    this.gestioneRichieste=function(telaio,ambito) {

        var param={
            "telaio":telaio,
            "gdm_ambito":ambito
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/core/gestione_richieste.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
               $('#gdm_gestione_richieste').html(ret);
                
            }
        });
    }

    ////////////////////////////////////////////////////
    /*nuova richiesta

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
                txt+='<input style="margin-left:5px;" type="date" value="'+window._nebulaMain.data_db_to_form(this.nuova.d)+'" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editDataNew(this.value);" />';
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
                            txt+='<img style="position:relative;width:12px;height:12px;margin-left:5px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/gdm/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].delNew(\''+idMat+'\',\''+this.nuova.operazioni[ambito[z]][x].destinazione+'\');" />';
                        txt+='</div>';
                    txt+='</div>';
                }
            }
        }

        txt+='<div style="margin-top:10px;width:100%;text-align:center;" >';
            txt+='<button style="background-color:burlywood;"';
                if (this.nuova.aperta) txt+=' onclick=""window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].confirmInsertNew();"';
                else  txt+=' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].confirmNew();"';
            txt+=' >'+(this.nuova.aperta?'Aggiungi':'Crea nuova Richiesta')+'</button>';
        txt+='</div>';

        $('#gdm_new_richiesta').html(txt);
        $('#gdm_new_richiesta').show();
        
        //L'esecuzione della richiesta richiamerà il metodo corretto e farà un refresh.

        //REFRESH: occorre considerare che GDM può essere aperto in una finestra e non avere accesso al ribbon
        //questo modifica il metodo da chiamare per il REFRESH
        //NON sarà "window._nebulaApp.ribbonExecute();"
        //ma il metodo dell'applicazione che farà da viewer alternativo di GDM
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
        this.addNew(dep,'Desposito','Vettura');
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

    this.confirmNew=function() {

    }

    this.confirmInsertNew=function() {
        
    }*/


}