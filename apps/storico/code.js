
function storicoCode(funzione) {

    this.funzione=funzione;

    this.marca="";
    this.modello="";
    this.telaio="";
    this.ambito="";

    /*this.base={};
    this.actual={};
    this.actualElem=0;*/



    this.nebulaAppSetup=function() {

        //############ ???????????? #################

        this.marca=$('#storico_marca_hidden').val();
        this.modello=$('#storico_modello_hidden').val();
        this.telaio=$('#storico_telaio_hidden').val();
        this.ambito=$('#storico_ambito_hidden').val();

        //############ ???????????? #################
    }

    this.setAmbito=function(ambito) {

        //se c'è il ribbon
        if (document.getElementById("ribbon_sto_ambito")) {
            $('#ribbon_sto_ambito').val(ambito);
            window._nebulaApp.ribbonExecute();
        }

        //se non c'è il ribbon significa che storico è dentro ad una finestra come nel caso degli odl

    }

    /*this.setOggettiPassman=function(base,actual) {

        this.base= $.parseJSON(atob(base));
        this.actual= $.parseJSON(atob(actual));

        for (var x in this.actual) {
            this.actualElem++;
        }
    }

    this.closeStoricoUtil=function() {

        $('#storicoUtilDivBody').html('');

        $('#storicoUtilDiv').hide();
        $('#storicoElencoPratiche').show();

        window._storico_divo.selTab(0);

    }

    this.openPassman=function(p) {
        //p è il JSON del passaggio manuale in base 64

        //per sicurezza
        if (this.telaio=="") return;

        var passaggio=false;

        if (p) {
            passaggio=$.parseJSON(atob(p));
        }

        console.log(JSON.stringify(passaggio));

        $('#storicoUtilDivBody').html('');

        $('#storicoUtilDiv').show();
        $('#storicoElencoPratiche').hide();

        window._storico_divo.selTab(0);

        //////////////////////////////////////
        
        //var txt='<div>'+JSON.stringify(this.base)+'</div><div>'+JSON.stringify(this.actual)+'</div>';

        var txt='<div style="font-size:1.3em;position:relative;" >';
            txt+='<b>Passaggio Manuale '+(passaggio?'('+passaggio.indice+')':"")+'</b>';
            if (passaggio){
                txt+='<img style="position:relative;margin-left:20px;width:20px;height:20px;" src="http://'+location.host+'/nebula/apps/storico/img/trash.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].deletePassman();"/>';
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
                    txt+='<button style="width:95%;text-align:center;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].collectPassman();">';
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

        $('#storicoUtilDivBody').html(txt);

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

                window._nebulaApp.ribbonExecute();
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

                window._nebulaApp.ribbonExecute();
            }
        });
    }

    //////////////////////////////////////////////////////////////

    this.calcolaPrevisione=function() {

        var delta = document.getElementById("sto_prev_delta");

        var param={
            "km":parseInt($('#sto_prev_actualKm').val()),
            "cons":$('#sto_prev_actualCons').val(),
            "delta":(delta)?delta.value:delta,
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
                    window._nebulaApp_storico.drawPrevisione(obj);
                }
                else alert('errore');
                
            }
        });

    }

    this.drawPrevisione=function(obj) {

        var delta=[
            {'delta':6,'txt':'6 mesi - standard'},
            {'delta':3,'txt':'3 mesi - alto percorrente'},
            {'delta':1,'txt':'1 mese - altissimo percorrente'}   
        ]

        $('#sto_prev_mese').html(window._nebulaMain.number_format(obj.mese,0,'','.'));

        var txt='<div style="position:relative;width:100%;height:10%;margin-top:1%;" >';
            txt+='<span style="font-weight:bold;margin-right:10px;" >Proiezione scadenze:</span>';
            txt+='<select id="sto_prev_delta" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].calcolaPrevisione();" >';
                for (var x in delta) {
                    txt+='<option value="'+delta[x].delta+'" ';
                        if (delta[x].delta==obj.delta) txt+='selected';
                    txt+=' >'+delta[x].txt+'</option>';
                }
            txt+='</select>';
        txt+='</div>';
        
        txt+='<div style="position:relative;width:100%;height:89%;overflow:scroll;overflow-x:hidden;" >';
            
            if (obj.interventi) {

                txt+='<div style="color:blueviolet;">Prossimi interventi di manutenzione in base alla percorrenza attuale (salvo diversa segnalazione della vettura):</div>';

                for (var x in obj.interventi) {

                    txt+='<div style="position:relative;width:95%;margin-top:10px;border-top:1px solid #bbbbbb;border-bottom:1px solid #bbbbbb;" >';

                        txt+='<div style="font-weight:bold;">'+obj.interventi[x].d_intervento+' a km: '+obj.interventi[x].km+'</div>';

                        for (var y in this.base) {

                            if (y in obj.interventi[x].oggetti) {
                                txt+='<div style="position:relative;width:100%;height:20px;';
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
                                txt+='</div>';
                            }
                        }

                    txt+='</div>';

                }
            }
        txt+='</div>';

        $('#sto_prev_div').html(txt);

    }*/

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

    /*this.setPiano=function() {

        var param={
            'marca':$('#storico_marca_hidden').val(),
            'modello':$('#storico_modello_hidden').val(),
            'telaio':$('#storico_telaio_hidden').val(),
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

               $('#sto_rightDiv').html(ret);
                
            }
        });

    }*/


}