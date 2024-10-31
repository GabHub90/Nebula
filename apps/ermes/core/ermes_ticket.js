function ermesTicket(cat) {

    this.categorie=$.parseJSON(atob(cat));

    this.rep="";
    this.cat="";

    this.actualID="";

    //riferimento oggetto da cui viene aperto il ticket
    this.caller="";
    

    this.resetTime=function() {
        if (this.ermesTO!=='undefined') {
            clearTimeout(this.ermesTO);
        }
        // 5 minuti = 300000
        this.ermesTO = setTimeout(function(){
            window[window._ermesTicket.caller].chiudiTicket(window._nebulaMain.getMainLogged());
        }, 150000);
    }

    this.resetTime();

    this.chgReparto=function(rep) {

        $('#ermes_ticket_form_icons').html('');
        $('#ermes_ticket_form_head').html('');
        $('#ermes_ticket_util_cerca').hide();
        var first=true;

        this.rep=rep;

        if (this.rep=='') return;

        var mono=$('#ermes_ticket_form_reparto').data('mono');

        var txt='<div style="position:relative;margin-top:10px;">';

            txt+='<div style="position:relative;display:inline-block;width:60%;vertical-align:top;">';
                txt+='<div class="ermes_ticket_form_lable">';
                    txt+='<lable>Categoria:</lable>';
                txt+='</div>';
                txt+='<div>';
                    txt+='<select id="ermes_ticket_form_categoria" class="ermesTicketSelect" style="width:95%;" onchange="window._ermesTicket.chgCategoria(this.value);">';
                        if(mono=="") txt+='<option value="">Seleziona una categoria...</option>';
                        if(this.categorie.hasOwnProperty(rep)) {
                            for (var x in this.categorie[rep]) {

                                if (this.categorie[rep][x].enabled==0) continue;

                                if (mono=='' || mono==x) {

                                    txt+='<option value="'+x+'">'+this.categorie[rep][x]['titolo']+'</option>';

                                    if (first) {
                                        let temp='<img class="ermes_ticket_form_icon" src="http://'+location.host+'/nebula/apps/ermes/img/'+this.categorie[rep][x]['icon3']+'" />';
                                        temp+='<img class="ermes_ticket_form_icon" src="http://'+location.host+'/nebula/apps/ermes/img/'+this.categorie[rep][x]['icon1']+'" />';
                                        temp+='<img class="ermes_ticket_form_icon" src="http://'+location.host+'/nebula/apps/ermes/img/'+this.categorie[rep][x]['icon2']+'" />';

                                        $('#ermes_ticket_form_icons').html(temp);

                                        first=false;
                                    }
                                }
                            }
                        }
                    txt+='</select>';    
                txt+='</div>';                                                   
            txt+='</div>';
        txt+='</div>';
        

        txt+='<div id="ermes_ticket_form_opt" style="position:relative;margin-top:10px;height:100px;">';
        txt+='</div>';

        $('#ermes_ticket_form_head').html(txt);

        $('#ermes_ticket_form_button').hide();

        if (mono!='') this.chgCategoria(mono);
    }

    this.chgCategoria=function(cat) {

        $('#ermes_ticket_form_opt').html('');
        $('#ermes_ticket_util_cerca').hide();

        this.cat=cat;

        if (this.cat=='') return;

        txt='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;text-align:center;">';

            if (this.categorie[this.rep][this.cat].urgenza==1) {
                txt+='<div class="ermes_ticket_form_lable">';
                    txt+='<lable>Urgente</lable>';
                txt+='</div>';
                txt+='<div>';
                    txt+='<input id="ermes_ticket_form_urgenza" type="checkbox"value="1" />';
                txt+='</div>';
            }
            else {
                txt+='<input id="ermes_ticket_form_urgenza" type="hidden" value="0" />';
            }
        
        txt+='</div>';

        txt+='<div style="position:relative;display:inline-block;width:30%;vertical-align:top;text-align:center;">';

            txt+=this.writeDest(this.rep,this.cat);

        txt+='</div>';

        txt+='<div style="position:relative;display:inline-block;width:50%;vertical-align:top;text-align:center;">';

            txt+='<div class="ermes_ticket_form_lable">';
                txt+='<lable>Cliente</lable>';
            txt+='</div>';

            txt+='<div>';

                if (this.categorie[this.rep][this.cat].mittente=='logged') {
                    txt+=$('#ermes_ticket_form_logged').val();
                    //txt+='<input id="ermes_ticket_form_ragsoc" type="hidden" value="'+$('#ermes_ticket_form_logged').val()+'" />';
                    txt+='<input id="global_linker_input" type="hidden" value="'+$('#ermes_ticket_form_logged').val()+'" />';
                }
                else {
                    txt+='<div style="text-align:left;">';
                        //txt+='<input id="ermes_ticket_form_ragsoc" type="text" style="width:92%;text-align:center;background-color: thistle;font-weight:bold;" onkeyup="window._ermesTicket.check();" />';
                        txt+='<input id="global_linker_input" type="text" style="width:92%;text-align:center;background-color: thistle;font-weight:bold;" onkeyup="window._ermesTicket.check();" />';
                    txt+='</div>';
                    txt+='<div style="position:relative;margin-top:5px;text-align:left;">';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:left;" >';
                            txt+='<b>Tel:</b>';
                        txt+='</div>';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:77%;text-align:left;" >';
                            txt+='<input id="ermes_ticket_form_tel" type="text" style="width:100%;" />';
                            //txt+='<button style="margin-left:8px;" >Cerca</button>';
                        txt+='</div>';
                    txt+='</div>';
                    txt+='<div style="position:relative;margin-top:5px;text-align:left;">';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:left;" >';
                            txt+='<b>Mail:</b>';
                        txt+='</div>';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:77%;text-align:left;" >';
                            txt+='<input id="ermes_ticket_form_mail" type="text" style="width:100%;" />';
                        txt+='</div>';
                    txt+='</div>';
                    txt+='<div style="position:relative;margin-top:5px;text-align:left;">';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:left;" >';
                            txt+='<b>Auto:</b>';
                        txt+='</div>';
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:77%;text-align:left;" >';
                            txt+='<input id="ermes_ticket_form_vei" type="text" style="width:100%;" />';
                        txt+='</div>';
                    txt+='</div>';

                    $('#ermes_ticket_util_cerca').show();
                }

            txt+='</div>';

        txt+='</div>';

        $('#ermes_ticket_form_opt').html(txt);

        this.check();

    }

    this.writeDest=function(rep,cat) {
        //in caso di REPARTO e MACROREPARTO in realtà i valori non vengono utilizzati

        txt="";

        txt+='<div id="ermes_ticket_form_lable_dest" class="ermes_ticket_form_lable" >';
            if (this.categorie[rep][cat].box=='utente') {
                txt+=window._nebulaMain.setWaiter();
            }
            else txt+='<lable>Destinatario</lable>';
        txt+='</div>';

        txt+='<div>';
            txt+='<select id="ermes_ticket_form_dest" class="ermesTicketSelect" style="width:85%;font-size:0.9em;" >';
                if (this.categorie[rep][cat].box=='macrorep') {
                    txt+='<option value="mrep">Macroreparto</option>';
                }
                else if (this.categorie[rep][cat].box=='reparto') {
                    txt+='<option value="rep">Reparto</option>';
                }
            txt+='</select>'

            if (this.categorie[rep][cat].box=='utente') {
                txt+='<script type="text/javascript">';
                    txt+='window._ermesTicket.loadColl("'+rep+'");';
                txt+='</script>';
            }
        txt+='</div>';

        return txt;
    }

    this.loadColl=function(reparto) {

        $("#ermes_ticket_form_lable_dest").html('<lable>Destinatario</lable>');

        var param={
            "reparto":reparto
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/load_coll.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#ermes_ticket_form_dest').html(ret);
            }
        });


    }

    this.check=function() {

        $('#ermes_ticket_form_error').html('');

        var res={
            msg:$('#ermes_ticket_form_messaggio').val().trim(),
            cli:$('#global_linker_input').val().trim(),
            msglen:0,
            clilen:0
        }

        res.msglen=res.msg.length;
        res.clilen=res.cli.length;

        //console.log(JSON.stringify(res));

        var error="";
        if (res.msglen<5) error+='- Messaggio troppo corto -';
        if (res.msglen>500) error+='- Messaggio troppo lungo -';
        if (res.clilen<3) error+='- Cliente troppo corto -';

        if (error=="") {
            $('#ermes_ticket_form_button').show();
            return {"res":res,"ok":true};
        }
        else {
            $('#ermes_ticket_form_button').hide();
            $('#ermes_ticket_form_error').html(error);
            return {"ok":false};
        }
    }

    this.checkChat=function() {

        this.resetTime();

        $('#chat_form_error').html('');

        var res={
            msg:$('#chat_form_messaggio').val().trim(),
            msglen:0
        }

        res.msglen=res.msg.length;

        //console.log(JSON.stringify(res));

        var error="";
        if (res.msglen<5) error+='- Messaggio troppo corto -';
        if (res.msglen>500) error+='- Messaggio troppo lungo -';

        if (error=="") {
            $('#chat_form_button').show();
            return {"res":res,"ok":true};
        }
        else {
            $('#chat_form_button').hide();
            $('#chat_form_error').html(error);
            return {"ok":false};
        }
    }

    this.getUrgenza=function() {

        if ($('#ermes_ticket_form_urgenza').val()==0) return 0;
        else return ($('#ermes_ticket_form_urgenza').prop('checked')?1:0);
    }

    this.confirm=function(caller) {

        chk=this.check();

        if (!chk.ok) return;

        if (!confirm("Confermi l'invio del Ticket?")) return;

        var mit={
            "ragsoc":chk.res.cli,
            "tel":($('#ermes_ticket_form_tel').val()==='undefined')?'':$('#ermes_ticket_form_tel').val(),
            "mail":($('#ermes_ticket_form_mail').val()==='undefined')?'':$('#ermes_ticket_form_mail').val(),
            "vei":($('#ermes_ticket_form_vei').val()==='undefined')?'':$('#ermes_ticket_form_vei').val()
        }

        var tempG=$('#ermes_ticket_form_dest').val();

        param={
            "caller":caller,
            "categoria":this.cat,
            "reparto":this.rep,
            "des_reparto":$('#ermes_ticket_form_reparto option:selected').data('des'),
            "creatore":$('#ermes_ticket_form_logged').val(),
            "gestore":(tempG=='rep' || tempG=="mrep")?'':tempG,
            "mittente":JSON.stringify(mit),
            "urgenza":this.getUrgenza(),
            "scadenza":this.categorie[this.rep][this.cat].scadenza,
            "padre":$('#ermes_ticket_form_padre').val(),
            "nota":$('#ermes_ticket_form_nota').val(),
            "msg":chk.res.msg
        };

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/write_new.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#ermes_util_body').html(ret);
                
            }
        });
    }

    this.confirmChat=function() {

        chk=this.checkChat();

        if (!chk.ok) return;

        if (!confirm("Confermi l'invio?")) return;

        param={
            "info":$('#chat_form_messaggio').data('info'),
            "msg":chk.res.msg
        };

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/write_bubble.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                var res=$.parseJSON(ret);

                if (res.ok==0) alert(res.txt);

                //alert(window._ermesTicket.caller);

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);
                
            }
        });

    }

    this.execGlobalLinker=function(obj) {
        if (obj.ragsoc_util!="") {
            $('#global_linker_input').val(obj.ragsoc_util);
            $('#ermes_ticket_form_tel').val(obj.tel1_util+' - '+obj.tel2_util);
            $('#ermes_ticket_form_mail').val(obj.mail_util);
        }

        else if (obj.ragsoc_intest!="") {
            $('#global_linker_input').val(obj.ragsoc_intest);
            $('#ermes_ticket_form_tel').val(obj.tel1_intest+'  - '+obj.tel2_intest);
            $('#ermes_ticket_form_mail').val(obj.mail_intest);
        }

        $('#ermes_ticket_form_vei').val(obj.targa+' - '+obj.telaio+' - '+obj.des_veicolo)
    }

    this.unchain=function(logged) {

        var param={
            "app":"ermes",
            "chiave":this.actualID,
            "utente":logged
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chain/unchain.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                window._ermesTicket.actualID="";    
            }
        });
    }

    this.cambiaStato=function(stato) {

        var param={
            "ID":this.actualID,
            "stato":stato
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/cambia_stato.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });
    }

    this.concludi=function(utente) {

        if (!confirm('Vuoi davvero CHIUDERE il ticket (operazione non annullabile)?')) return;

        var param={
            "ID":this.actualID,
            "utente":utente
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/concludi_ticket.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });

    }

    this.chiudiUtil=function() {

        $('#ermes_ticket_body_util_main').html('');
        $('#ermes_ticket_body_util').hide();
        $('#ermes_ticket_body_main').show();
    }

    this.openUtil=function() {

        $('#ermes_ticket_body_util').show();
        $('#ermes_ticket_body_main').hide();
    }

    this.setInoltra=function(logged) {

        var txt='<div style="position:relative;font-weight:bold;font-size:1.3em;" >Inoltra Ticket:</div>';

        txt+='<div style="position:relative;margin-top:10px;">';

            txt+='<div style="position:relative;display:inline-block;width:30%;vertical-align:top;">';
                txt+='<div class="ermes_ticket_form_lable">';
                    txt+='<lable>Reparto:</lable>';
                txt+='</div>';
                txt+='<div>';
                    txt+='<select id="ermes_ticket_util_form_reparto" class="ermesTicketSelect" style="width:95%;" onchange="window._ermesTicket.chgRepartoInoltra(this.value);">';
                        txt+='<option value="">Seleziona un reparto...</option>';
                        
                        for (var x in this.categorie) {

                            txt+='<option value="'+x+'">'+x+'</option>';

                        }

                    txt+='</select>';    
                txt+='</div>';                                                   
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:10px;">';

            txt+='<div style="position:relative;display:inline-block;width:40%;vertical-align:top;">';
                txt+='<div class="ermes_ticket_form_lable">';
                    txt+='<lable>Categoria:</lable>';
                txt+='</div>';
                txt+='<div>';
                    txt+='<select id="ermes_ticket_util_form_categoria" class="ermesTicketSelect" style="width:95%;" onchange="window._ermesTicket.chgCategoriaInoltra();">';
                    txt+='</select>';    
                txt+='</div>';                                                   
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:10px;">';

            txt+='<div id="ermes_ticket_util_form_gestore_div" style="position:relative;display:inline-block;width:40%;vertical-align:top;">';                                             
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:20px;">';
            txt+='<button onclick="window._ermesTicket.eseguiInoltra(\''+logged+'\')">Inoltra</button>';
        txt+='</div>';

        $('#ermes_ticket_body_util_main').html(txt);

        this.openUtil();

    }

    this.chgRepartoInoltra=function(rep) {

        $('#ermes_ticket_util_form_categoria').html('');

        var txt="";

        if(this.categorie.hasOwnProperty(rep)) {
            for (var x in this.categorie[rep]) {

                if (this.categorie[rep][x].enabled==0) continue;

                txt+='<option value="'+x+'">'+this.categorie[rep][x]['titolo']+'</option>';
            }
        }

        $('#ermes_ticket_util_form_categoria').html(txt);

        this.chgCategoriaInoltra();
    }

    this.chgCategoriaInoltra=function() {

        var rep=$('#ermes_ticket_util_form_reparto').val();
        var cat=$('#ermes_ticket_util_form_categoria').val();

        $('#ermes_ticket_util_form_gestore_div').html((this.writeDest(rep,cat)));
    }

    this.eseguiInoltra=function(logged) {

        var param={
            "ID":this.actualID,
            "logged":logged,
            "reparto":$('#ermes_ticket_util_form_reparto').val(),
            "categoria":$('#ermes_ticket_util_form_categoria').val(),
            "gestore":$('#ermes_ticket_form_dest').val()
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/concludi_inoltra.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });

    }

    this.setForzaGestione=function(reparto,logged) {

        var txt='<div style="position:relative;font-weight:bold;font-size:1.3em;" >Forza Gestione:</div>';

        txt+='<div id="ermes_ticket_form_lable_dest" class="ermes_ticket_form_lable" style="margin-top:15px;" >';
            txt+=window._nebulaMain.setWaiter();
        txt+='</div>';

        txt+='<div>';
            txt+='<select id="ermes_ticket_form_dest" class="ermesTicketSelect" style="width:50%;font-size:1.2em;" >';
            txt+='</select>'

            txt+='<script type="text/javascript">';
                txt+='window._ermesTicket.loadColl("'+reparto+'");';
            txt+='</script>';
        txt+='</div>';

        txt+='<div style="position:relative;margin-top:20px;">';
            txt+='<button onclick="window._ermesTicket.eseguiForzaGestione(\''+logged+'\')">Forza</button>';
        txt+='</div>';

        $('#ermes_ticket_body_util_main').html(txt);

        this.openUtil();

    }

    this.eseguiForzaGestione=function(logged) {

        var param={
            "ID":this.actualID,
            "logged":logged,
            "gestore":$('#ermes_ticket_form_dest').val()
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/concludi_forza_gestione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });

    }

    //viene chiamato dall'oggetto DUDU
    this.addDudu=function(txt) {
        //alert(txt);
        var param={
            "ticket":this.actualID,
            "txt":txt
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/new_dudu.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });

    }

    this.setScadenza=function() {

        var param={
            "ticket":this.actualID,
            "scadenza":$('#ermesTicket_set_scadenza').val()
        }

        if (param.scadenza==='undefined' || param.scadenza=='') return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/ermes/core/set_scadenza.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                window[window._ermesTicket.caller].apriTicket(window._ermesTicket.actualID,window._ermesTicket.caller);   
            }
        });

    }

}