function nebulaComest() {

    this.edit=false;
    this.length=0;

    this.fornitori={};

    this.commessa={
        "rif":"",
        "versione":"",
        "targa":"",
        "telaio":"",
        "descrizione":"",
        "dms":"",
        "odl":"",
        "fornitore":{},
        "d_apertura":"",
        "utente_apertura":"",
        "d_annullo":"",
        "utente_annullo":"",
        "controllo":"",
        "utente_controllo":"",
        "d_controllo":""
    }

    this.actualRevisione=0;

    this.revisioni={};

    this.danni={};

    this.operazioni={};

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.setFornitori=function(obj) {
        //this.fornitori=$.parseJSON(atob(obj));
        //console.log(obj);
        //console.log(window._nebulaMain.base64ToUtf8(obj));
        this.fornitori=$.parseJSON(window._nebulaMain.base64ToUtf8(obj));
    }

    this.setCommessa=function(obj) {
        this.commessa=$.parseJSON(atob(obj));
    }

    this.setRevisioni=function(obj) {
        this.revisioni=$.parseJSON(atob(obj));
    }

    this.setDanni=function(obj) {
        this.danni=$.parseJSON(atob(obj));
    }

    this.setOperazioni=function(obj) {
        this.operazioni=$.parseJSON(atob(obj));
    }

    this.editFornitore=function(id) {

        if (id==0) {
            $('#comest_indirizzo').html('');
            $('#comest_mail').html('');
            $('#comest_tel1').html('');
            $('#comest_tel2').html('');
            this.commessa.fornitore={};
        }
        else {
            $('#comest_indirizzo').html(this.fornitori[id].indirizzo);
            $('#comest_mail').html(this.fornitori[id].mail);
            $('#comest_tel1').html(this.fornitori[id].tel1+(this.fornitori[id].nota1!=""?' ('+this.fornitori[id].nota1+')':''));
            $('#comest_tel2').html(this.fornitori[id].tel2+(this.fornitori[id].nota2!=""?' ('+this.fornitori[id].nota2+')':''));
            this.commessa.fornitore=this.fornitori[id];
        }
    }

    this.apriCommessa=function() {

        //AJAX - scrittura DB commessa e Revisione
        //PHP apertura comest con l'ID della commessa
        //SUCCED - scrittura della commessa nel DIV "nebula_comest_body" (senza utilizzare comest_class)

        var param=this.commessa;
        param.utente=window._nebulaMain.getMainLogged();

        //console.log(JSON.stringify(param));

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/open_new.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });
    }

    this.drawBody=function() {

        this.edit=false;
        this.length=0;
        this.actualRevisione=0;

        for (var x in this.revisioni) {

            this.actualRevisione=x;
        }

        for (var x in this.revisioni) {

            this.drawRevisione(x);
        }

        if (!this.edit) {
            $('#comest_revisione_'+this.actualRevisione).append(this.drawControllo());
        }
    }

    this.drawRevisione=function(x) {

        var index='#comest_revisione_'+x;

        $(index).html('');

        this.refreshDanni();

        this.edit=(this.revisioni[x].d_chiusura=="")?true:false;

        //$('#comest_revisione_'+x).html('Revisione '+x);
        /*
        $this->revisioni[1]=array(
            "d_creazione"=>'20230316',
            "d_chiusura"=>'',
            "righe"=>array(),
            "preventivo"=>0,
            "riconsegna"=>"",
            "nota"=>"",
            "check"=>array()
        );
        */

        //se la revisione non è stata ancora confermata ma è in via di definizione
        if (this.edit) {
            $(index).append(this.newLine());
        }

        for (var y in this.revisioni[x].righe) {

            if (this.edit) this.length++;

            var txt='<div style="width:95%;margin-top:5px;border:1px solid #777777;padding:6px;box-sizing:border-box;background-color: white;background-color: #e5e5f7;opacity: 0.8;background-size: 10px 10px;background-image: repeating-linear-gradient(45deg, #ffffff 0, #ffffff 1px, #e5e5f7 0, #e5e5f7 50%);" >';
                    txt+='<div style="position:relative;margin-top:5px;">';    
                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-size:1.1em;font-weight:bold;font-size:1.2em;">'+this.revisioni[x].righe[y].titolo+'</div>';
                        if (this.edit) {
                            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;">';
                                txt+='<select id="comest_operazione_'+y+'" style="font-size:1.2em;" onchange="window._nebulaComest.changeOperazione('+y+',this.value);">';
                                    txt+='<option value="99" >Generico</option>';
                                    for (var k in this.operazioni) {
                                        txt+='<option value="'+k+'" '+(this.operazioni[k].default==1 && this.revisioni[x].righe[y].descrizione==""?'selected':'')+'>'+this.operazioni[k].tag+'</option>';

                                        if (this.operazioni[k].default==1 && this.revisioni[x].righe[y].descrizione=="") this.revisioni[x].righe[y].descrizione=this.operazioni[k].tag;
                                    }
                                txt+='</select>';
                            txt+='</div>';

                            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:right;">';
                                txt+='<img style="width:25px;height:25px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/trash.png" onclick="window._nebulaComest.cancellaLavorazione(\''+y+'\');" />';
                            txt+='</div>';
                        } 
                    txt+='</div>';

                    txt+='<div id="comest_div_'+y+'" style="position:relative;margin-top:10px;border:2px solid transparent;box-sizing:border-box;padding:4px;">';
                        if (this.edit) {
                            txt+='<input id="comest_descrizione_'+y+'" data-index="'+y+'" type="text" style="width:90%;font-size:1.2em;" value="'+this.revisioni[x].righe[y].descrizione+'" onchange="window._nebulaComest.evalRevisione();" />';
                        }
                        else {
                            txt+=this.revisioni[x].righe[y].descrizione;
                        }
                    txt+='</div>';

            txt+='</div>';

            if (this.danni[this.revisioni[x].righe[y].zona]) {
                this.danni[this.revisioni[x].righe[y].zona].set=1;
            }

            $(index).append(txt);

        }

        //actualRevisione è sempre l'ultima
        //this.actualRevisione=x;

        $(index).append(this.drawPreventivo(x));

        this.updateDanni();

        if (this.edit) {
            this.evalRevisione();
        }
    }

    this.refreshDanni=function() {
        for (var x in this.danni) {
            this.danni[x].set=0;
        }
    }

    this.updateDanni=function() {

        for (var x in this.danni) {

            var obj=$('#comest_spot_'+x);

            if (this.danni[x].set==0) {
                temp=$(obj).data('defcolor');
            }
            else {
                temp=$(obj).data('color'); 
            }

            if (temp !== null) {
                $(obj).css('background-color',temp.slice(0,-1));
            }
        }

    }

    this.evalRevisione=function() {

        var error=0;

        var revisione=this.revisioni[this.actualRevisione];

        $('div[id^="comest_div_"').css('border-color','transparent');

        $('input[id^="comest_descrizione_"]').each(function() {
            if ($(this).val().trim()=='') {
                $('#comest_div_'+$(this).data('index')).css('border-color','red');
                revisione.righe[$(this).data('index')].descrizione='';
                error++;
            }
            else {
                revisione.righe[$(this).data('index')].descrizione=$(this).val().trim();
            }
        });

        if (!this.evalPreventivo()) error++;

        return (error==0)?true:false;
    }

    this.evalPreventivo=function() {

        error=0;

        var valore=$('#comest_preventivo_valore').val().replace(/\D/g,'');
        $('#comest_preventivo_valore').val(valore);
        valore=parseInt(valore);
        $('#comest_preventivo_valore').css('background-color','white');

        var riconsegna=$('#comest_preventivo_riconsegna').val();
        $('#comest_preventivo_riconsegna').css('background-color','white');

        if (!valore || valore==0) {
            $('#comest_preventivo_valore').css('background-color','#FF000055');
            this.revisioni[this.actualRevisione].preventivo=0;
            error++;
        }
        else this.revisioni[this.actualRevisione].preventivo=valore;

        if (!riconsegna || riconsegna=="") {
            $('#comest_preventivo_riconsegna').css('background-color','#FF000055');
            this.revisioni[this.actualRevisione].riconsegna="";
            error++;
        }
        else this.revisioni[this.actualRevisione].riconsegna=window._nebulaMain.data_form_to_db(riconsegna);

        return (error==0)?true:false;
    }

    this.newLine=function() {

        var txt='<hr style="width:80%;height:5px;margin-top: 15px;margin-bottom: 15px;" >';

        txt+='<div style="width:95%;margin-top:5px;margin-bottom:20px;border:2px solid green;padding:6px;box-sizing:border-box;background-color: #00ff0044;" >';

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:35%;" >';
                txt+='<select id="comest_newline_select" style="width:95%;font-size:1.2em;" onchange="window._nebulaComest.changeNewlineOption(this.value);" >';
                    txt+='<option value="999" >Parte interessata... (testo libero)</option>';
                    for (var x in this.danni) {
                        txt+='<option value="'+x+'">'+this.danni[x].tag+'</option>';
                    }
                txt+='</select>';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:55%;" >';
                txt+='<input id="comest_newline_input" style="width:95%;font-size:1.2em;" type="text" maxlength="50" />';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';
                txt+='<img style="width:25px;height:25px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/add.png" onclick="window._nebulaComest.nuovaLavorazione();" />';
            txt+='</div>';

        txt+='</div>';

        return txt;

    }

    this.drawPreventivo=function(x) {

        var txt='<hr style="width:80%;height:5px;margin-top: 15px;margin-bottom: 15px;" >';

        txt+='<div style="width:95%;margin-top:5px;border:2px solid #e98100;padding:6px;box-sizing:border-box;background-color: #e9810033;" >';

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:70%;" >';
                txt+='<div style="width:95%;" >';
                    txt+='<div style="position:relative;font-weight:bold;font-size:0.9em;" >Nota:</div>';
                    txt+='<div style="position:relative;font-size:1.2em;">';
                        if (this.edit) {
                            txt+='<div style="position:relative;">';
                                if (x==this.actualRevisione) {
                                    txt+='<input id="comest_preventivo_nota" type="text" style="width:100%;font-size:1.2em;" value="'+this.revisioni[x].nota+'" onchange="window._nebulaComest.changeNota();" />';
                                }
                            txt+='</div>';
                            txt+='<div style="position:relative;margin-top:10px;text-align:right;">';
                                
                                //if (this.actualRevisione==1) {
                                    txt+='<img style="width:30px;height:30px;margin-right:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/save.png" onclick="window._nebulaComest.save();"/>';
                                //}

                                if (this.commessa.d_annullo=="" && this.actualRevisione>1 && x==this.actualRevisione) {
                                    txt+='<img style="position:absolute;width:30px;height:30px;top:0px;left:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/trash.png" onclick="window._nebulaComest.annullaRevisione();"/>';
                                }

                            txt+='</div>';
                        }
                        else {
                            txt+='<div style="position:relative;">';
                                if (this.commessa.d_annullo=="" && this.commessa.d_controllo=="" && x==this.actualRevisione) {
                                    txt+='<input id="comest_preventivo_nota" type="text" style="width:100%;font-size:1.2em;" value="'+this.revisioni[x].nota+'" />';
                                }
                                else {
                                    txt+=this.revisioni[x].nota;
                                }
                            txt+='</div>';

                            if (this.commessa.d_annullo=="" && this.commessa.d_controllo=="" && x==this.actualRevisione) {
                                txt+='<div style="position:relative;margin-top:10px;text-align:left;">';
                                    txt+='<img style="width:30px;height:30px;margin-left:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/add.png" onclick="window._nebulaComest.nuovaRevisione();"/>';

                                    if (x==this.actualRevisione) {
                                        txt+='<img style="position:absolute;width:30px;height:30px;top:0px;right:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/nota.png" onclick="window._nebulaComest.salvaNota();"/>';
                                    }
                                txt+='</div>';
                            }
                        }
                    txt+='</div>';
                txt+='</div>';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;" >';
                txt+='<div style="position:relative;font-weight:bold;font-size:0.9em;" >Preventivo:</div>';
                txt+='<div style="position:relative;font-size:1.2em;">';
                    if (this.edit) {
                        txt+='<input id="comest_preventivo_valore" type="text" style="width:50%;font-size:1.2em;font-weight:bold;text-align:center;" value="'+(this.revisioni[x].preventivo!=0?this.revisioni[x].preventivo:'')+'" onchange="window._nebulaComest.evalPreventivo();" />';
                        txt+='<button style="margin-left:10px;" onclick="window._nebulaComest.conferma();">Conferma</button>';
                    }
                    else txt+=this.revisioni[x].preventivo+' € - '+this.revisioni[x].utente_chiusura+'<span style="margin-left:5px;font-size:0.9em;">'+window._nebulaMain.data_db_to_ita(this.revisioni[x].d_chiusura)+'</span>';
                txt+='</div>';
                txt+='<div style="position:relative;">';
                    
                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:left;" >';
                        txt+='<div style="position:relative;font-weight:bold;font-size:0.9em;" >Riconsegna:</div>';
                        txt+='<div style="position:relative;font-size:1.2em;">';
                            if (this.edit) {
                                txt+='<input id="comest_preventivo_riconsegna" type="date" style="width:80%;font-size:0.8em;" value="'+window._nebulaMain.data_db_to_form(this.revisioni[x].riconsegna)+'" onchange="window._nebulaComest.evalPreventivo();" />';
                            }
                            else txt+=window._nebulaMain.data_db_to_ita(this.revisioni[x].riconsegna);
                        txt+='</div>';
                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;" >';
                        if (this.commessa.d_annullo=="" && x==this.actualRevisione) {

                            if (this.revisioni[x].d_chiusura=="") {
                                txt+='<img style="position:relative;width:40px;height:40px;margin-right:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/bozza.png" onclick="window._nebulaComest.bozza();"/>';
                            }
                            else {
                                var loggedMail=window._nebulaMain.contesto.mail;
                                var fornitMail=(this.commessa.fornitore.mail)?this.commessa.fornitore.mail:'';

                                if (loggedMail && loggedMail!="" && fornitMail && fornitMail!="") {
                                    txt+='<img style="position:relative;width:40px;height:40px;margin-right:20px;cursor:pointer;" data-logged="'+loggedMail+'" data-fornit="'+fornitMail+'" src="http://'+location.host+'/nebula/main/img/mail.png" onclick="window._nebulaComest.sendMail(this);"/>';   
                                }

                                txt+='<img style="position:relative;width:40px;height:40px;margin-right:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/comest/img/print.png" onclick="window._nebulaComest.stampa();"/>';
                            }     
                        }
                    txt+='</div>';

                txt+='</div>';
            txt+='</div>';

        txt+='</div>';

        /*txt+='<script type="text/javascript">';
            txt+=
        txt+='</script>';*/

        return txt;
        
    }

    this.drawControllo=function() {

        var txt='<hr style="width:80%;height:5px;margin-top: 15px;margin-bottom: 15px;" >';

        if (this.commessa.d_annullo!="") {
            txt+='<div style="position:relative;width:100%;margin-top:10px;margin-bottom:10px;color:red;font-weight:bold;text-align:center;">Commessa ANNULLATA </div>';
            return txt;
        } 

        var temp=$.parseJSON(this.commessa.controllo);

        txt+='<div style="width:95%;margin-top:5px;border:2px solid #ff468b;padding:6px;box-sizing:border-box;background-color: #e9006133;font-size:1.1em;" >';

            if (temp) {
                for (var x in temp) {
                    txt+='<div style="position:relative;" >';

                        txt+='<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >'+temp[x].titolo+'</div>';

                        for (var y in temp[x].opzioni) {
                            txt+='<div style="position:relative;display:inline-block;width:10%;vertical-align:top;" >';
                                txt+='<input name="comest_check_'+x+'" type="radio" value="'+temp[x].opzioni[y]+'"';
                                    if (temp[x].opzioni[y]==temp[x].valore) txt+=' checked';
                                    if (this.commessa.d_controllo!="") txt+=' disabled';
                                txt+='/>';
                                txt+='<span style="margin-left:5px;" >'+temp[x].opzioni[y]+'</span>';
                            txt+='</div>';
                        } 

                    txt+='</div>';
                }
            }

            txt+='<div style="width:95%;margin-top:5px;text-align:right;">';
                if (this.commessa.d_controllo=="") {
                    txt+='<button onclick="window._nebulaComest.chiudiCommessa();" >Chiudi Commessa</button>';
                }
                else {
                    txt+='Data: '+window._nebulaMain.data_db_to_ita(this.commessa.d_controllo)+' Utente: '+this.commessa.utente_controllo;
                }
            txt+='</div>';

        txt+='</div>';

        return txt;

    }

    this.clickSpot=function(index) {
        //$('#comest_newline_select option').attr('selected',false);
        $('#comest_newline_select option[value="'+index+'"]').prop('selected',true);
        this.changeNewlineOption(index);
    }

    this.changeNewlineOption=function(index) {
        //alert(this.danni[index]);
        if (this.danni[index]) {
            $('#comest_newline_input').val(this.danni[index].tag);
            $('#comest_newline_input').prop('disabled',true);
        }
        else {
            $('#comest_newline_input').val('');
            $('#comest_newline_input').prop('disabled',false);
        }
    }

    this.changeOperazione=function(riga,index) {

        if (this.operazioni[index]) {
            $('#comest_descrizione_'+riga).val(this.operazioni[index].txt);
        }
        else $('#comest_descrizione_'+riga).val('');
    }

    this.changeNota=function() {
        this.revisioni[this.actualRevisione].nota=$('#comest_preventivo_nota').val();
    }

    //////////////////////////////////////////////////////////////////////////////////7

    this.nuovaLavorazione=function() {

        var txt=$('#comest_newline_input').val().trim();
        var zona=$('#comest_newline_select').val();

        if (txt=='') return;

        //AJAX - setWeiter - crea elemento DB (ritorna array revisione aggiornato) - ridisegna revisione
        /*TEST
            var a={
                "ID":1,
                "zona":zona,
                "titolo":txt,
                "operazione":0,
                "descrizione":""
            }
        */
        //ENDTEST

        this.evalRevisione();

        var param={
            "commessa":this.commessa,
            "revisione":this.revisioni[this.actualRevisione],
            "rif":this.commessa.rif,
            "rev":this.actualRevisione,
            "zona":zona,
            "titolo":txt
        }

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/new_lavorazione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.cancellaLavorazione=function(index) {

        if (!confirm('Vuoi cancellare la lavorazione: '+this.revisioni[this.actualRevisione].righe[index].titolo+' ?')) return;

        this.evalRevisione();

        var param={
            "commessa":this.commessa,
            "revisione":this.revisioni[this.actualRevisione],
            "rif":this.commessa.rif,
            "rev":this.actualRevisione,
            "riga":index
        }

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/del_lavorazione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.conferma=function() {

        if (!this.commessa.fornitore.ragsoc || this.commessa.fornitore.ragsoc === 'undefined' || this.commessa.fornitore.ragsoc=="") {
            alert('Manca il Fornitore!!!!');
            return;
        }

        if (this.length==0) {
            alert('Non ci sono lavorazioni!!!!');
            return;
        }

        if (!this.evalRevisione()) {
            alert('Ci sono errori nella commessa!!!');
            return;
        }

        if (!confirm('Vuoi confermare la Commessa?')) return;

        var param={
            "commessa":this.commessa,
            "revisione":this.revisioni[this.actualRevisione],
            "utente":window._nebulaMain.getMainLogged()
        }

        //console.log(JSON.stringify(param));

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/conferma.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.save=function() {

        /*if(!this.evalRevisione()) {
            alert('Ci sono errori nella commessa!!!');
            return;
        }*/

        this.evalRevisione();

        var param={
            "commessa":this.commessa,
            "revisione":this.revisioni[this.actualRevisione]
        }

        //console.log(JSON.stringify(param));

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/save.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.nuovaRevisione=function() {

        if (!confirm("Verrà creata una nuova revisione della commessa.")) return;

        var param={
            "commessa":this.commessa.rif,
            "revisione":this.actualRevisione,
            "utente":window._nebulaMain.getMainLogged()
        }

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/new_revisione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.annullaRevisione=function() {

        if (!confirm("Vuoi cancellare la revisione attuale? L'operazione NON è reversibile !!!")) return;

        var param={
            'commessa':this.commessa.rif,
            'revisione':this.actualRevisione
        }

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/annulla_revisione.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });
    }

    this.salvaNota=function() {

        var param={
            'nota':$('#comest_preventivo_nota').val(),
            'commessa':this.commessa.rif,
            'revisione':this.actualRevisione
        }

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/edit_nota.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.chiudiCommessa=function() {

        var temp=$.parseJSON(this.commessa.controllo);

        var error=0;

        //console.log(JSON.stringify(temp));

        if (temp) {
            for (var x in temp) {
                var v=$('input[name="comest_check_'+x+'"]:checked').val();
                if (v && v!='') {
                    temp[x].valore=v;
                    //alert(v);
                }
                else error++;
            }
        }

        //console.log(JSON.stringify(temp));

        if (error>0) {
            alert('Compila il Form di controllo...');
            return;
        }

        this.commessa.controllo=JSON.stringify(temp);

        if (!confirm('Vuoi CHIUDERE la Commessa?')) return;

        var param={
            "commessa":this.commessa,
            "utente":window._nebulaMain.getMainLogged()
        }

        //console.log(JSON.stringify(param));

        $('#nebula_comest_main').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/chiudi.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.annullaCommessa=function() {

        //se la commessa è aperta (una revisione è stata confermata viene ANNULLATA altrimenti CANCELLATA in toto)
        var param={
            "commessa":this.commessa.rif,
            "utente":window._nebulaMain.getMainLogged()
        }

        if (this.commessa.d_apertura=="") {
            if (!confirm('La richiesta verrà definitivamente CANCELLATA !!!')) return;
            param.operazione='cancella';
        }
        else {
            if (!confirm('La richiesta verrà archiviata come annullata.')) return;
            param.operazione='annulla';
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/annulla_commessa.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                $('#nebula_comest_main').html(ret);
            }
        });

    }

    this.stampa=function() {

        var param={
            "commessa":this.commessa.rif
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/stampa.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                //var obj=$.parseJSON(ret);
                //obj.pdf=obj.pdf.replace('\\','');

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

    this.bozza=function() {

        var param={
            "commessa":this.commessa.rif
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/bozza.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                //var obj=$.parseJSON(ret);
                //obj.pdf=obj.pdf.replace('\\','');

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

    this.sendMail=function(obj) {

        var param={
            "commessa":this.commessa.rif,
            "logged":$(obj).data('logged'),
            "fornit":$(obj).data('fornit')
        }

        if (!confirm('Invio mail a '+param.fornit+' da '+param.logged)) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/mail.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                alert(ret);
            }
        });

    }

}