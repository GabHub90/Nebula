function nebulaStorico () {

    this.base={};
    this.actual={};
    this.actualElem=0;

    this.ambito="main";

    this.telaio="";

    this.setOggettiPassman=function(base,actual) {

        this.base= $.parseJSON(atob(base));
        this.actual= $.parseJSON(atob(actual));

        for (var x in this.actual) {
            this.actualElem++;
        }
    }

    //in teoria non è più utilizzato
    this.refresh=function() {

        if (window._nebulaStorico.ambito=='main') window._nebulaApp.ribbonExecute();

        else if (window._nebulaStorico.ambito=='avalon') {

            var tt=$('#avalon_storico_refresh_tt').val();
            var km=$('#avalon_storico_refresh_km').val();

            window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadStorico(tt,km);
            //window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
        }
    }

    this.closeStoricoUtil=function() {

        $('#storico_utilityDivBody').html('');

        $('#storico_utilityDiv').hide();
        $('#storicoElencoPratiche').show();

        window._storico_divo.selTab(0);

    }

    this.openPassman=function(p) {
        //p è il JSON del passaggio manuale in base 64

        this.telaio=$('#storico_telaio_hidden').val();

        //alert(this.telaio);

        //per sicurezza
        if (!this.telaio || this.telaio=="") return;

        var passaggio=false;

        if (p) {
            passaggio=$.parseJSON(atob(p));
        }

        //console.log(JSON.stringify(passaggio));

        $('#storico_utilityDivBody').html('');

        $('#storico_utilityDiv').show();
        $('#storicoElencoPratiche').hide();

        window._storico_divo.selTab(0);

        //////////////////////////////////////
        
        //var txt='<div>'+JSON.stringify(this.base)+'</div><div>'+JSON.stringify(this.actual)+'</div>';

        var txt='<div style="font-size:1.3em;position:relative;" >';
            txt+='<b>Passaggio Manuale '+(passaggio?'('+passaggio.indice+')':"")+'</b>';
            if (passaggio){
                //txt+='<img style="position:relative;margin-left:20px;width:20px;height:20px;" src="http://'+location.host+'/nebula/apps/storico/img/trash.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].deletePassman();"/>';
                txt+='<img style="position:relative;margin-left:20px;width:20px;height:20px;" src="http://'+location.host+'/nebula/apps/storico/img/trash.png" onclick="window._nebulaStorico.deletePassman();"/>';
            }
        txt+='</div>';

        txt+='<div style="position:relative;width:100%;margin-top:10px;">';
            txt+='<input id="storicoPassmanIndice" type="hidden" value="'+(passaggio?passaggio.indice:"")+'" />';
            txt+='<div style="position:relative;display:inline-block;width:10%;vertical-align:top;font-weight:bold;">Data:</div>';
            txt+='<div style="position:relative;display:inline-block;width:40%;vertical-align:top;font-weight:bold;">';
                txt+='<input id="storicoPassmanData" style="width:75%;font-size:1.2em;" type="date" value="';
                    if (passaggio) {
                        txt+=window._nebulaMain.data_db_to_form(passaggio.data_fatt);
                    }
                    else {
                        txt+=window._nebulaMain.phpDate('Y-m-d',undefined);
                    }
                txt+='" />';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:5%;vertical-align:top;font-weight:bold;">Km:</div>';
            txt+='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;font-weight:bold;">';
                txt+='<input id="storicoPassmanKm" style="width:95%;font-size:1.2em;text-align:center;" type="text" value="';
                    if (passaggio) {
                        txt+=passaggio.km;
                    }
                txt+='" />';
            txt+='</div>';
        txt+='</div>';

        txt+='<div style="position:relative;width:100%;margin-top:10px;">';
             txt+='<div style="position:relative;display:inline-block;width:10%;vertical-align:top;font-weight:bold;">Note:</div>';
             txt+='<div style="position:relative;display:inline-block;width:68%;vertical-align:top;font-weight:bold;">';
                    txt+='<textarea id="storicoPassmanNote" rows="3" style="width:95%;resize:none;" >'+(passaggio?passaggio.note:"")+'</textarea>';
             txt+='</div>';
             txt+='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;font-weight:bold;">';
                    //txt+='<button style="width:95%;text-align:center;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].collectPassman();">';
                    txt+='<button style="width:95%;text-align:center;" onclick="window._nebulaStorico.collectPassman();">';
                        if (passaggio) txt+='modifica';
                        else txt+='crea';
                    txt+='</button>';
             txt+='</div>';
        txt+='</div>';

        var count=0;
        var col=Math.ceil(this.actualElem/3);
        if (col<1) col=1;

        txt+='<div style="position:relative;width:100%;margin-top:10px;">';

            txt+='<div style="position:relative;display:inline-block;width:32%;vertical-align:top;">'

                for (var x in this.base) {

                    if (x in this.actual) {

                        if (count==col) {
                            txt+='</div>';
                            txt+='<div style="position:relative;display:inline-block;width:32%;vertical-align:top;">';
                            count=0;
                        }

                        txt+='<div style="position:relative;width:90%;margin-top:10px;">';
                            txt+='<div style="position:relative;display:inline-block;width:10%;vertical-align:top;font-weight:bold;">';
                                txt+='<input id="storicoPassmanObj_'+x+'" type="checkbox" value="'+x+'" ';
                                    if (passaggio && passaggio.righe.includes(x)) txt+='checked';
                                txt+=' />';
                            txt+='</div>';
                            txt+='<div style="position:relative;display:inline-block;width:85%;vertical-align:top;">';
                                txt+='<div style="font-weight:bold;">'+x+'</div>';
                                txt+='<div style="">'+this.base[x].descrizione+'</div>';
                            txt+='</div>';
                        txt+='</div>';

                        count++
                    }
                }

            txt+='</div>';

        txt+='</div>';

        $('#storico_utilityDivBody').html(txt);

    }

    this.collectPassman=function() {

        var param={
            "telaio":this.telaio,
            "indice":$('#storicoPassmanIndice').val(),
            "km":parseInt($('#storicoPassmanKm').val()),
            "data":$('#storicoPassmanData').val(),
            "note":$('#storicoPassmanNote').val(),
            "righe":[]
        }

        //console.log(JSON.stringify(param));

        if (!param.telaio || param.telaio=="") {
            alert('errore Telaio');
            return;
        }

        if (!param.km || param.km=="" || param.km<10) {
            alert('errore Km');
            return;
        }

        if (!param.data || param.data=="") {
            alert('errore Data');
            return;
        }

        $('input[id^="storicoPassmanObj_"]:checked').each(function(){
            param.righe.push($(this).val());
        });

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/passman.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                //window._nebulaApp.ribbonExecute();
                //window._nebulaStorico.refresh();
                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });
    }

    this.deletePassman=function() {

        if (!confirm('Confermi la cancellazione del passaggio?')) return;

        var param={
            "telaio":this.telaio,
            "indice":$('#storicoPassmanIndice').val()
        }

        if (!param.telaio || param.telaio=="") {
            alert('errore Telaio');
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/passman_del.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //window._nebulaApp.ribbonExecute();
                //window._nebulaStorico.refresh();
                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });
    }

    //////////////////////////////////////////////////////////////

    this.calcolaPrevisione=function() {

        var delta = document.getElementById("sto_prev_delta");

        var param={
            "km":parseInt($('#sto_prev_actualKm').val()),
            "cons":$('#sto_prev_actualCons').val(),
            "delta":(delta==null)?'':$(delta).val(),
            "oggetti":this.actual,
            "eventi":$.parseJSON(atob($('#sto_prev_eventi').val()))
        }

        //console.log(JSON.stringify(param.eventi));

        if (!param.cons || param.cons=="" || !param.km || param.km<=1000) {
            alert('Errore parametri');
            return false;
        }

        param.cons=""+param.cons.substr(0,4)+param.cons.substr(5,2)+param.cons.substr(8,2);

        //console.log(JSON.stringify(param));

        $('#sto_prev_div').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif"/></div>');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/calcola_previsione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                var obj=$.parseJSON(ret);

                if (obj) {
                    //window._nebulaApp_storico.drawPrevisione(obj);
                    $('#storico_print_icon').show();
                    window._nebulaStorico.drawPrevisione(obj);
                }
                else alert('errore');
                
            }
        });

    }

    this.drawPrevisione=function(obj) {

        //riporta i valori della selezione dei piani a STD='no'
        this.resetPack();

        var delta=[
            {'delta':12,'txt':'12 mesi - extra'},
            {'delta':6,'txt':'6 mesi - standard'},
            {'delta':3,'txt':'3 mesi - alto percorrente'},
            {'delta':1,'txt':'1 mese - altissimo percorrente'}   
        ]

        $('#sto_prev_mese').html(window._nebulaMain.number_format(obj.mese,0,'','.'));

        var txt='<div style="position:relative;width:100%;height:10%;margin-top:1%;" >';
            txt+='<span style="font-weight:bold;margin-right:10px;" >Proiezione scadenze:</span>';
            txt+='<select id="sto_prev_delta" onchange="window._nebulaStorico.calcolaPrevisione();" >';
                for (var x in delta) {
                    txt+='<option value="'+delta[x].delta+'" ';
                        if (delta[x].delta==obj.delta) txt+='selected';
                    txt+=' >'+delta[x].txt+'</option>';
                }
            txt+='</select>';
        txt+='</div>';
        
        txt+='<div id="sto_prev_div_content" style="position:relative;width:100%;height:89%;overflow:scroll;overflow-x:hidden;" >';
            
            if (obj.interventi) {

                txt+='<div style="color:blueviolet;">Prossimi interventi di manutenzione in base alla percorrenza attuale (salvo diversa segnalazione della vettura):</div>';

                var first=true;

                for (var x in obj.interventi) {

                    txt+='<div style="position:relative;width:95%;margin-top:10px;border-top:1px solid #bbbbbb;border-bottom:1px solid #bbbbbb;" >';

                        txt+='<div style="font-weight:bold;">'+obj.interventi[x].d_intervento+' a km: '+obj.interventi[x].km;
                        if (obj.interventi[x].d_intervento==obj.interventi[x].oggi) txt+='<span style="color:red;"> - ADESSO</span>';
                        else if (first) txt+='<span style="color:#d415d4;"> --> successivo</span>';
                        txt+='</div>';

                        txt+='<table style="position:relative;width:100%;border-spacing: 0px 5px;border-color:transparent;" >';

                            for (var y in this.base) {

                                if (y in obj.interventi[x].oggetti) {

                                    //se è la prima ricorrenza setta di conseguenza i PACCHETTI
                                    if (first) {
                                        $('#storico_obj_sel_'+y).data('std','si');
                                    }

                                    txt+='<tr style="';
                                        if (this.actual[y].stat==1) txt+='color:brown;';
                                    txt+='">';
                                        txt+='<td style="width:128px;">'+(this.base[y].main==1?'(*) ':'')+y+'</td>';
                                        txt+='<td style="width:214px;">'+this.base[y].descrizione+'</td>';
                                        txt+='<td style="width:178px;font-size:0.9em;">';
                                            txt+='<div>(';
                                                txt+='mesi:'+obj.interventi[x].oggetti[y].dt;
                                                txt+=' - ';
                                                txt+='km:'+obj.interventi[x].oggetti[y].dkm;
                                            txt+=')</div>';
                                        txt+='</td>';

                                        txt+='<td style="width:192px;font-size:0.9em;">';
                                            if (this.actual[y].stat==1) {
                                                txt+="(Senza scadenza definita)";
                                            }
                                            else {
                                                txt+='<div>';
                                                    txt+=obj.interventi[x].oggetti[y].termine;
                                                txt+='</div>';
                                                if (obj.interventi[x].oggetti[y].ragdt!=-1) {
                                                    txt+='<div style="font-size:0.9em;">(';
                                                        txt+='mesi:'+obj.interventi[x].oggetti[y].ragdt;
                                                        txt+=' - ';
                                                        txt+='km:'+obj.interventi[x].oggetti[y].ragdkm;
                                                    txt+=')</div>';
                                                }
                                            }
                                        txt+='</td>';

                                    txt+='</tr>';

                                    /*txt+='<div style="position:relative;width:100%;height:20px;';
                                        if (this.actual[y].stat==1) txt+='color:brown;';
                                    txt+='">';
                                        txt+='<div style="position:relative;display:inline-block;width:18%;height:100%;">'+(this.base[y].main==1?'(*) ':'')+y+'</div>';
                                        txt+='<div style="position:relative;display:inline-block;width:30%;height:100%;">'+this.base[y].descrizione+'</div>';
                                        txt+='<div style="position:relative;display:inline-block;width:25%;height:100%;">( ';
                                            txt+='mesi:'+obj.interventi[x].oggetti[y].dt;
                                            txt+=' - ';
                                            txt+='km:'+obj.interventi[x].oggetti[y].dkm;
                                            //if (obj.interventi[x].oggetti[y].dt>obj.interventi[x].oggetti[y].tConf) txt+=' mesi:'+obj.interventi[x].oggetti[y].dt;
                                            //if (obj.interventi[x].oggetti[y].dkm>obj.interventi[x].oggetti[y].kmConf) txt+=' km:'+obj.interventi[x].oggetti[y].dkm;
                                        txt+=' )</div>';
                                        if (this.actual[y].stat==1) {
                                            txt+='<div style="position:relative;display:inline-block;width:27%;height:100%;font-size:0.9em;">(Senza scadenza definita)</div>';
                                        }
                                        else {
                                            txt+='<div style="position:relative;display:inline-block;width:27%;height:100%;font-size:0.9em;">'+obj.interventi[x].oggetti[y].termine+'</div>';
                                        }
                                    txt+='</div>';*/
                                }
                            }
                        txt+='</table>';

                    txt+='</div>';

                    first=false;
                }
            }
        txt+='</div>';

        $('#sto_prev_div').html(txt);

        this.drawPack();

    }

    this.printPrevisione=function() {

        var delta = document.getElementById("sto_prev_delta");

        var param={
            "km":parseInt($('#sto_prev_actualKm').val()),
            "cons":$('#sto_prev_actualCons').val(),
            "delta":(delta)?delta.value:delta,
            "oggetti":this.actual,
            "eventi":$.parseJSON(atob($('#sto_prev_eventi').val())),
            "veicolo":$('#storico_print_icon').data('vei'),
            "html":$('#sto_prev_div_content').html()
        }

        //console.log(JSON.stringify(param.eventi));

        if (!param.cons || param.cons=="" || !param.km || param.km<=1000) {
            alert('Errore parametri');
            return false;
        }

        param.cons=""+param.cons.substr(0,4)+param.cons.substr(5,2)+param.cons.substr(8,2);

        //console.log(JSON.stringify(param));

        $('#storico_print_icon').attr('src','http://'+location.host+'/nebula/main/img/busy.gif');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/print_previsione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#storico_print_icon').attr('src','http://'+location.host+'/nebula/apps/storico/img/print.png');
                
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

    /*this.selectPiano=function(piano) {

        var param={
            'marca':this.marca,
            'modello':this.modello,
            'telaio':this.telaio,
            'piano':piano
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/elenco_piani.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

               $('#sto_leftDiv').html(ret);
                
            }
        });

    }*/

    this.setPiano=function() {

        var param={
            'marca':$('#storico_marca_hidden').val(),
            'modello':$('#storico_modello_hidden').val(),
            'telaio':$('#storico_telaio_hidden').val(),
            'actual':($('#sto_actual_piano_hidden').val())?$('#sto_actual_piano_hidden').val():'',
            'piano':$('input[name="sto_gruppi_radio"]:checked').val()
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/edit_piano.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                $('#sto_rightDiv').html(ret);
                
            }
        });

    }

    this.setINS=function(rif,dms,str,mov) {

        if (!rif || rif=='' || !dms || dms=='' || !str || str=='' || !mov || mov=='') return;

        var param={
            'dms':dms,
            'rif':rif,
            'str':str,
            'new':mov
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/new_event_check.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
            
                //console.log(ret);
                //window._nebulaStorico.refresh();
                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });
    }

    this.delINS=function(rif,dms,str) {

        if (!rif || rif=='' || !dms || dms=='' || !str || str=='') return;

        var param={
            'dms':dms,
            'rif':rif,
            'str':str
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/del_event_check.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
            
                //console.log(ret);
                window._nebulaStorico.refresh();   
            }
        });
    }

    this.editEvento=function(oggetto,codice,tipo) {

        $('#storico_utilityDivBody').html('');

        $('#storico_utilityDiv').show();
        $('#storicoElencoPratiche').hide();

        var param={
            "marca":$('#storico_marca_hidden').val(),
            "oggetto":oggetto,
            "codice":codice,
            "tipo":tipo
        }

        if (!param.marca || param.marca=="" || !param.codice || param.codice=="" || !param.tipo || param.tipo=="") {
            $('#storico_utilityDivBody').html('errore parametri');
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/edit_evento.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
            
                $('#storico_utilityDivBody').html(ret);   
            }
        });

    }

    this.insertEvento=function(old) {

        var param={
            "oggetto":$('#storicoEditEvento_evento').val(),
            "codice":$('#storicoEditEvento_codice').val(),
            "tipo":$('#storicoEditEvento_tipo').val(),
            "qta":parseFloat($('#storicoEditEvento_qta').val().replace(',','.')),
            "chk":($('#storicoEditEvento_chk').prop('checked'))?'1':'0',
            "old":old?1:0
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/new_evento.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //console.log(ret);
                //window._nebulaStorico.refresh();
                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');  
            }
        });

    }

    this.deleteEvento=function() {

        if (!confirm("Vuoi procedere alla cancellazione?")) return;

        var param={
            "oggetto":$('#storicoEditEvento_evento').val(),
            "codice":$('#storicoEditEvento_codice').val(),
            "tipo":$('#storicoEditEvento_tipo').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/del_evento.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
            
                //window._nebulaStorico.refresh();
                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });

    }

    this.linkPiano=function() {

        var param={
            'marca':$('#storico_marca_hidden').val(),
            'modello':$('#storico_modello_hidden').val(),
            'piano':$('input[name="sto_gruppi_radio"]:checked').val()
        }

        //console.log(JSON.stringify(param));

        if (!param.marca || param.marca=="" || !param.modello || param.modello=="" || !param.piano || param.piano=="") {
            alert('Operazione non possibile');
            return;
        }

        if (!confirm("L'operazione avrà effetto su TUTTI i modelli: "+param.modello+". Proseguo?" )) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/link_piano.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                //$('#ribbon_sto_ambito').val('standard');
                //window._nebulaStorico.refresh();

                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });

    }

    this.switchEventoForm=function(oggetto) {

        if ($('#storicoGruppoForm_evento_'+oggetto).prop('checked')==true) {
            $('input[id^="storicoGruppoForm_campo_'+oggetto+'"]').attr('disabled',false);
            $('#storicoGruppoForm_evento_div_'+oggetto).css('background-color','transparent');
        }
        else {
            $('input[id^="storicoGruppoForm_campo_'+oggetto+'"]').attr('disabled',true);
            $('#storicoGruppoForm_evento_div_'+oggetto).css('background-color','#dddddd');
        }

    }

    this.switchEventoTipo=function(tipo,oggetto) {

        if ($('#storicoGruppo'+tipo+'_evento_'+oggetto).prop('checked')==true) {
            $('#storicoGruppo'+tipo+'_evento_div_'+oggetto).show();
        }
        else {
            $('#storicoGruppo'+tipo+'_evento_div_'+oggetto).hide();
        }

    }

    this.convalidaForm=function(indice) {

        if (!confirm('ATTENZIONE!! Le modifiche interesseranno tutti i modelli collegati a questo piano!!!')) return;

        var count=0;
        var error=0;
        var chk=/[^0-9]/;

        //{"ISPEZ":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":36,"first_km":0},"COLIO":{"dt":"12","mint":0,"maxt":0,"stet":0,"dkm":"15000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FANT":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"20000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FARIA":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"CAND":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"POLIV":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"120000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"DISTR":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"210000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"LIQFR":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":36,"first_km":0},"PROTM":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"30000","minkm":0,"maxkm":0,"stekm":0},"REVI":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":1,"topt":0,"topkm":0,"first_t":48,"first_km":0},"FRANT":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"50000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FRPOS":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"70000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"SPAZZ":{"dt":"36","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"BATT":{"dt":"60","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0}}
        var obj={};

        $('div[id^="storicoGruppoForm_campo_error_"]').html('');
        $('div[id^="storicoGruppoForm_campo_"]').css('background-color','white');

        $('input[id^="storicoGruppoForm_evento_"]').each(function(){

            if ($(this).prop('checked')==true) {

                var tag=$(this).val();
                count++;

                //alert(tag);

                obj[tag]={
                    "dt":$('#storicoGruppoForm_campo_'+tag+'_dt').val().trim(),
                    "mint":0,
                    "maxt":0,
                    "stet":0,
                    "dkm":$('#storicoGruppoForm_campo_'+tag+'_dkm').val().trim(),
                    "minkm":0,
                    "maxkm":0,
                    "stekm":0,
                    "pcx":$('#storicoGruppoForm_campo_'+tag+'_pcx').prop('checked')?1:0,
                    "topt":$('#storicoGruppoForm_campo_'+tag+'_topt').val().trim(),
                    "topkm":$('#storicoGruppoForm_campo_'+tag+'_topkm').val().trim(),
                    "first_t":$('#storicoGruppoForm_campo_'+tag+'_first_t').val().trim(),
                    "first_km":$('#storicoGruppoForm_campo_'+tag+'_first_km').val().trim()
                }

                if (chk.test(obj[tag].dt) || obj[tag].dt<0 || obj[tag].dt=='') {
                    //$('#storicoGruppoForm_campo_error_'+tag+'_dt').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_dt').css('background-color','#ff8c8c');
                    error++;
                }
                if (chk.test(obj[tag].dkm) || obj[tag].dkm<0 || obj[tag].dkm=='') {
                    //$('#storicoGruppoForm_campo_error_'+tag+'_dkm').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_dkm').css('background-color','#ff8c8c');
                    error++;
                }
                if (chk.test(obj[tag].topt) || obj[tag].topt<0 || obj[tag].topt=='') {
                    //$('#storicoGruppoForm_campo_error_'+tag+'_topt').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_topt').css('background-color','#ff8c8c');
                    error++;
                }
                if (chk.test(obj[tag].topkm) || obj[tag].topkm<0 || obj[tag].topkm=='') {
                    //$('#storicoGruppoForm_campo_error_'+tag+'_topkm').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_topkm').css('background-color','#ff8c8c');
                    error++;
                }
                if (chk.test(obj[tag].first_t) || obj[tag].first_t<0 || obj[tag].first_t=='') {
                    //$('#storicoGruppoForm_campo_error_'+tag+'_first_t').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_first_t').css('background-color','#ff8c8c');
                    error++;
                }
                if (chk.test(obj[tag].first_km) || obj[tag].first_km<0 || obj[tag].first_km=='') {
                    //$('#storicoGruppoForm_campo_error_first_'+tag+'_km').html('error');
                    $('#storicoGruppoForm_campo_'+tag+'_first_km').css('background-color','#ff8c8c');
                    error++;
                }
            }

        });

        if (error>0) {
            alert('Ci sono degli errori.');
            return;
        }

        if (count==0) {
            alert('NON ci sono elementi selezionati.');
            return;
        }

        var param={
            "indice":indice,
            "oggetti":obj
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/confirm_piano.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });

    }

    this.convalidaTipo=function(marca,modello,telaio,tx) {

        if (!confirm('Vuoi confermare le modifiche!!!')) return;

        var count=0;
        var tipo=tx;
        var error=0;
        var chk=/[^0-9]/;

        //{"ISPEZ":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":36,"first_km":0},"COLIO":{"dt":"12","mint":0,"maxt":0,"stet":0,"dkm":"15000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FANT":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"20000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FARIA":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"CAND":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"POLIV":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"120000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"DISTR":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"210000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"LIQFR":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":36,"first_km":0},"PROTM":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"30000","minkm":0,"maxkm":0,"stekm":0},"REVI":{"dt":"24","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":1,"topt":0,"topkm":0,"first_t":48,"first_km":0},"FRANT":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"50000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"FRPOS":{"dt":"0","mint":0,"maxt":0,"stet":0,"dkm":"70000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"SPAZZ":{"dt":"36","mint":0,"maxt":0,"stet":0,"dkm":"60000","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0},"BATT":{"dt":"60","mint":0,"maxt":0,"stet":0,"dkm":"0","minkm":0,"maxkm":0,"stekm":0,"pcx":0,"topt":0,"topkm":0,"first_t":0,"first_km":0}}
        var obj={};

        $('div[id^="storicoGruppo'+tipo+'_campo_error_"]').html('');
        $('div[id^="storicoGruppo'+tipo+'_campo_"]').css('background-color','white');

        $('input[id^="storicoGruppo'+tipo+'_evento_"]').each(function(){

            if ($(this).prop('checked')==true) {

                var tag=$(this).val();
                count++;

                //alert(tag);

                if ($('#storicoGruppo'+tipo+'_escludi_'+tag).prop('checked')) obj[tag]={'flag_mov':'del'};

                else {

                    obj[tag]={
                        "dt":$('#storicoGruppo'+tipo+'_campo_'+tag+'_dt').val().trim(),
                        "mint":0,
                        "maxt":0,
                        "stet":0,
                        "dkm":$('#storicoGruppo'+tipo+'_campo_'+tag+'_dkm').val().trim(),
                        "minkm":0,
                        "maxkm":0,
                        "stekm":0,
                        "pcx":$('#storicoGruppo'+tipo+'_campo_'+tag+'_pcx').prop('checked')?1:0,
                        "topt":$('#storicoGruppo'+tipo+'_campo_'+tag+'_topt').val().trim(),
                        "topkm":$('#storicoGruppo'+tipo+'_campo_'+tag+'_topkm').val().trim(),
                        "first_t":$('#storicoGruppo'+tipo+'_campo_'+tag+'_first_t').val().trim(),
                        "first_km":$('#storicoGruppo'+tipo+'_campo_'+tag+'_first_km').val().trim(),
                        "flag_mov":"ok"
                    }

                    if (chk.test(obj[tag].dt) || obj[tag].dt<0 || obj[tag].dt=='') {
                        //$('#storicoGruppoForm_campo_error_'+tag+'_dt').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_dt').css('background-color','#ff8c8c');
                        error++;
                    }
                    if (chk.test(obj[tag].dkm) || obj[tag].dkm<0 || obj[tag].dkm=='') {
                        //$('#storicoGruppoForm_campo_error_'+tag+'_dkm').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_dkm').css('background-color','#ff8c8c');
                        error++;
                    }
                    if (chk.test(obj[tag].topt) || obj[tag].topt<0 || obj[tag].topt=='') {
                        //$('#storicoGruppoForm_campo_error_'+tag+'_topt').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_topt').css('background-color','#ff8c8c');
                        error++;
                    }
                    if (chk.test(obj[tag].topkm) || obj[tag].topkm<0 || obj[tag].topkm=='') {
                        //$('#storicoGruppoForm_campo_error_'+tag+'_topkm').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_topkm').css('background-color','#ff8c8c');
                        error++;
                    }
                    if (chk.test(obj[tag].first_t) || obj[tag].first_t<0 || obj[tag].first_t=='') {
                        //$('#storicoGruppoForm_campo_error_'+tag+'_first_t').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_first_t').css('background-color','#ff8c8c');
                        error++;
                    }
                    if (chk.test(obj[tag].first_km) || obj[tag].first_km<0 || obj[tag].first_km=='') {
                        //$('#storicoGruppoForm_campo_error_first_'+tag+'_km').html('error');
                        $('#storicoGruppo'+tipo+'_campo_'+tag+'_first_km').css('background-color','#ff8c8c');
                        error++;
                    }
                }
            }

        });

        if (error>0) {
            alert('Ci sono degli errori.');
            return;
        }

        if (count==0) {
            if (!confirm("NON ci sono elementi selezionati. I parametri verranno azzerati...")) return;
        }

        var param={
            "tipo":tipo,
            "marca":marca,
            "modello":modello,
            "telaio":telaio,
            "oggetti":(count>0)?obj:""
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/confirm_tipo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });

    }

    this.openNuovo=function(copia,desc) {

        if (copia!="") $('#storico_gruppo_nuovo_titolo').html('Nuovo modello da: '+desc);
        else $('#storico_gruppo_nuovo_titolo').html('Nuovo modello:');

        $('#storico_gruppo_nuovo_copia').val(copia);
        $('#storico_gruppo_nuovo_suffix').val('');

        $('#storico_gruppo_nuovo').show();
        $('#storico_gruppo_main').hide();
    }

    this.closeNuovo=function() {
        $('#storico_gruppo_nuovo').hide();
        $('#storico_gruppo_main').show();
    }

    this.nuovoPiano=function() {

        var param={
            "copia":$('#storico_gruppo_nuovo_copia').val(),
            "marca":$('#storico_gruppo_nuovo_marca').val(),
            "alim":$('#storico_gruppo_nuovo_alim').val(),
            "traz":$('#storico_gruppo_nuovo_traz').val(),
            "cambio":$('#storico_gruppo_nuovo_cambio').val(),
            "manut":$('#storico_gruppo_nuovo_manut').val(),
            "suffix":$('#storico_gruppo_nuovo_suffix').val().trim(),
            "marcaDms":$('#storico_gruppo_nuovo_marcaDms').val()
        }

        param.chksum=param.marca+param.alim+param.traz+param.cambio+param.manut;

        //console.log(JSON.stringify(param));

        if (param.chksum.length!=7 || param.suffix=="" || param.marcaDms=="") {
            alert('errore parametri');
            return;
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/storico/core/nuovo_piano.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].setAmbito('standard');
            }
        });

    }

    ///////////////////////////////////////////////////////////////////////////////////////

    this.resetPack=function() {

        $('img[id^="storico_obj_sel_"]').data('std','no');
        $('div[id^="storico_pacchetto_"]').hide();

    }

    this.clickEvent=function(obj) {

        var man=$(obj).data('man');

        if (man=='no') $(obj).data('man','si');
        else $(obj).data('man','no');

        this.drawPack();
    }

    this.drawPack=function() {

        $('div[id^="storico_pacchetto_"]').hide();

        $('img[id^="storico_obj_sel_"]').each(function() {

            var codice=$(this).data('codice');
            var std=$(this).data('std');
            var man=$(this).data('man');

            if (std=='si') {
                if (man=='si') {
                    $(this).attr('src','http://'+location.host+'/nebula/apps/storico/img/esc.png');
                }
                else {
                    $(this).attr('src','http://'+location.host+'/nebula/apps/storico/img/sel.png');
                    $('#storico_pacchetto_'+codice).show();
                }
            }
            else {
                if (man=='si') {
                    $(this).attr('src','http://'+location.host+'/nebula/apps/storico/img/ins.png');
                    $('#storico_pacchetto_'+codice).show();
                }
                else $(this).attr('src','http://'+location.host+'/nebula/apps/storico/img/no.png');
            }

        });

    }

}