function panEdit(reparto,tipo) {

    this.tipoPanorama=tipo;

    this.schemi={};
    this.turni={};
    this.subrep={};

    //this.stato='copia';

    this.default={
        "codice":"",
        "reparto":reparto,
        "titolo":"",
        "turnazione":0,
        "flag_festivi":0,
        "flag_turno":0,
        "on_flag":0,
        "mark":0,
        "exclusive":0,
        "overall":"",
        "griglia":{},
        "elem":0,
        "busy":false,
        "data_i":"",
        "blocco_inizio":"",
        "stato":'copia'
    }

    this.actual={};
    Object.assign(this.actual,this.default);

    //creazione nuovo turno
    this.actualTurno={};
    this.tempTurno=[];
    this.flagNuovoTurno=false;

    this.loadSchemi=function(a) {
        this.schemi=a;
    }

    this.loadTurni=function(a) {
        this.turni=a;
    }

    this.loadSubrep=function(a) {
        this.subrep=a;
    }

    this.disableForm=function(val) {
        //se il form è da abilitare verificare se è editabile altrimenti setta come NON editabile
        if (!val && this.actual.stato=='copia') {
            if (this.actual.elem>0) val=true;
        }

        window._kassettone_turni.setEnable(!val);
        $('.panedit_form').prop('disabled',val);
    }

    this.change=function(index,val) {

        if (index=='data_i') val=window._nebulaMain.data_form_to_db(val);
        this.actual[index]=val;
    }

    this.selectSchema=function(codice) {

        if (codice in this.schemi) {
            
            Object.assign(this.actual,this.schemi[codice]);
            //this.actual=this.schemi[codice];

            if (typeof this.actual.griglia==='string') {
                eval("this.actual.griglia="+this.actual.griglia+';');
            }
        }
        else return;

        if (this.actual.elem==0) {
            this.actual.stato='salva';
        }
        else this.actual.stato='copia';

        $('#panedit_form_codice').val(this.actual.codice);
        $('#panedit_form_titolo').val(this.actual.titolo);

        if (this.actual.data_i=='') {
            this.actual.data_i=''+$('#panedit_form_pandatai').val()+'01';
        }

        $('#panedit_form_collectdiv').html(this.drawCollectDiv());

        var tt="";

        if (this.actual.stato=='copia') {
            tt='<button style="position:relative;font-size:1em;font-weight:bold;transform:translate(0%,20%);width:130px;text-align:center;" onclick="window._panedit.copia();" >Copia</button>';
        }
        if (this.actual.stato=='salva') {
            tt+='<button style="position:relative;font-size:1em;font-weight:bold;transform:translate(0%,20%);width:130px;text-align:center;margin-left:10px;" onclick="window._panedit.salvaSchema();" >Salva</button>';
        }

        $('#panedit_form_buttondiv').html(tt);

        //$('#panedit_form_turnoOverall').html(this.actual.overall);
        tt=(this.actual.overall=="")?'Turno':this.actual.overall;
        window._kassettone_turni.setTitle(tt,'turnoOverall');

        $('#panedit_form_turnoOverall_table').html(this.drawTurno(this.actual.overall,8));

        $('#panedit_options_tab').html(this.drawOptionsTab());

        $('#panedit_blocks_div').html(this.drawBlocks());

        setTimeout(function() {window._panedit.disableForm(false)},100);

    }

    this.setNew=function() {

        Object.assign(this.actual,this.default);
        //this.actual=this.default;
        this.actual.griglia={};

        this.actual.stato='crea';

        $('#panedit_form_codice').val('');
        $('#panedit_form_titolo').val('');

        window._kassettone_turni.setTitle('Turno','turnoOverall');

        //alert(window._nebulaMain.data_db_to_form(''+$('#panedit_form_pandatai').val()+'01'));
        this.actual.data_i=''+$('#panedit_form_pandatai').val()+'01';
        //$('#panedit_form_datai').val(window._nebulaMain.data_db_to_form(''+$('#panedit_form_pandatai').val()+'01'));

        $('#panedit_form_collectdiv').html(this.drawCollectDiv());
        $('#panedit_form_buttondiv').html('<button style="position:relative;font-size:1em;font-weight:bold;transform:translate(0%,20%);width:130px;text-align:center;" onclick="window._panedit.salvaSchema();" >Crea</button>');

        $('#panedit_form_turnoOverall').html('');
        $('#panedit_form_turnoOverall_table').html('');

        $('#panedit_options_tab').html(this.drawOptionsTab());

        $('#panedit_blocks_div').html(this.drawNewButton());

        setTimeout(function() {window._panedit.disableForm(false)},100);
        //window._kassettone_turni.setEnable(true);

    }

    this.drawCollectDiv=function() {

        //alert(JSON.stringify(this.actual));

        var txt='<span id="panedit_tag_datai" class="panedit_tag">Inizio:</span>';
        txt+='<input id="panedit_form_datai" type="date" style="width:150px;margin-left:3px;font-size:0.9em;" value="'+window._nebulaMain.data_db_to_form(this.actual.data_i)+'" onchange="window._panedit.change(\'data_i\',this.value);"/>';
        txt+='<span style="margin-left:10px;">Blocco:</span>';
        txt+='<select id="panedit_form_inizio" style="margin-left:3px;" >';
            for (var x in this.actual.griglia) {
                if (x==0) continue;
                txt+='<option value"'+x+'" ';
                    if (x==this.actual.blocco_inizio) txt+='selected';
                txt+='>'+x+'</option>';
            }
        txt+='</select>';

        if (!this.actual.busy) txt+='<img style="position:absolute;right:5px;top:50%;widht:22px;height:22px;margin-top:-12px;" src="http://'+location.host+'/nebula/core/panorama/img/collect.png" onclick="window._panedit.aggiungiSchema();" />';
        else if (this.tipoPanorama=='P') txt+='<img style="position:absolute;right:5px;top:50%;widht:22px;height:22px;margin-top:-10px;" src="http://'+location.host+'/nebula/core/panorama/img/edit.png" />';

        return txt;
    }

    this.drawOptionsTab=function() {

        var w='120px';

        var dis=(this.actual.stato=='copia')?'disabled':'';
        
        var txt='<table style="font-size:0.9em;text-align:center;">';

            txt+='<tr>';
                txt+='<th style="width:'+w+';">Turnazione</th>';
                txt+='<th style="width:'+w+';">No Cambio Festa</th>';
                txt+='<th style="width:'+w+';">No Cambio Chiu.</th>';
                txt+='<th style="width:'+w+';">Nuovo limite</th>';
                txt+='<th style="width:'+w+';">Segnaposto</th>';
                txt+='<th style="width:'+w+';">Esclusivo</th>';
            txt+='</tr>';

            txt+='<tr>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_turnazione" class="panedit_form" style="text-align:center;width:50%;" type="text" maxlenght="2" data-tipo="turnazione" value="'+this.actual.turnazione+'" '+dis+'/>';
                txt+='</td>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_festivi" class="panedit_form" type="checkbox" data-tipo="flag_festivi" '+(this.actual.flag_festivi==1?'checked':'')+' '+dis+'/>';
                txt+='</td>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_fturno" class="panedit_form" type="checkbox" data-tipo="flag_turno" '+(this.actual.flag_turno==1?'checked':'')+' '+dis+'/>';
                txt+='</td>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_flag" class="panedit_form" style="text-align:center;width:50%;" type="text" maxlenght="2" data-tipo="on_flag" value="'+this.actual.on_flag+'" '+dis+'/>';
                txt+='</td>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_mark" class="panedit_form" type="checkbox" data-tipo="mark" '+(this.actual.mark==1?'checked':'')+' '+dis+'/>';
                txt+='</td>';
                txt+='<td style="width:'+w+';">';
                    txt+='<input id="panedit_form_exclusive" class="panedit_form" type="checkbox" data-tipo="exclusive" '+(this.actual.exclusive==1?'checked':'')+' '+dis+'/>';
                txt+='</td>';
                
            txt+='</tr>';

        txt+='<table>';

        return txt;
    }

    this.drawTurno=function(codice,pt) {

        var w='14.2%';

        var txt='<table style="font-size:'+pt+'pt;text-align:center;width:100%;" >';

            txt+='<tr>';
                txt+='<th style="width:'+w+';">Dom</th>';
                txt+='<th style="width:'+w+';">Lun</th>';
                txt+='<th style="width:'+w+';">Mar</th>';
                txt+='<th style="width:'+w+';">Mer</th>';
                txt+='<th style="width:'+w+';">Gio</th>';
                txt+='<th style="width:'+w+';">Ven</th>';
                txt+='<th style="width:'+w+';">Sab</th>';
            txt+='</tr>';

            txt+='<tr>';
                for (var x in this.turni[codice]) {

                    eval("var obj="+this.turni[codice][x].orari+";");

                    txt+='<td style="width:'+w+';" >';

                        for (var y in obj) {

                            if (obj[y].i=='00:00' && obj[y].f=='00:00') break;

                            txt+='<div>';
                                txt+=obj[y].i+'-'+obj[y].f;
                            txt+='</div>';
                        }

                    txt+='</td>';
                }

            txt+='</tr>';

        txt+='</table>';

        return txt;
    }

    this.drawBlocks=function() {

        var txt="";

        //"griglia":{"11":{"turno":"SAB_STD","next":"12","ricric":"4"},"12":{"turno":"SAB_STD","next":"13","ricric":"4"},"13":{"turno":"SAB_STD","next":"11","ricric":"4"}}
        for (var x in this.actual.griglia) {
            //reso necessario per compatibilità
            if (x==0) continue;

            var temp=parseInt(x)+1;

            txt+='<div style="position:relative;width:95%;padding:5px;box-sizing:border-box;border-bottom:1px solid #777777;min-height:60px;height:fit-content;">';

                txt+='<div style="display:inline-block;width:26%;vertical-align:top;" >';

                    txt+='<div style="position:relative;width:100%;" >';

                        txt+='<div style="display:inline-block;width:45%;" >';
                            txt+='<span>'+x+'</span>';
                            txt+='<img style="width:10px;height:7px;margin-left:3px;" src="http://'+location.host+'/nebula/core/panorama/img/blackarrowR.png" />';
                            txt+='<select id="panedit_form_next_'+x+'" class="panedit_form" style="margin-left:3px;">';
                                for (var y in this.actual.griglia) {

                                    //reso necessario per compatibilità
                                    if (y==0) continue;

                                    txt+='<option value="'+y+'" ';
                                    //segna selected se l'impostazione è uguale all'elemento che si sta scrivendo
                                    if (this.actual.griglia[x].next==y) txt+='selected';
                                    else if (this.actual.griglia[x].next=="" || this.actual.griglia[x].next==0) {
                                        //se stiamo scrivendo il primo blocco ed x+1 NON esiste
                                        if ( (y==11 && !(temp in this.actual.griglia) ) || (y==temp) ) txt+='selected';
                                    }
                                    txt+=' >'+y+'</option>';
                                }
                            txt+='</select>';
                        txt+='</div>';

                        txt+='<div style="display:inline-block;width:55%;height:25px;line-height:25px;" >';
                            
                            var tt=(this.actual.griglia[x].turno!="")?this.actual.griglia[x].turno:'Turno';

                            txt+=window._kassettone_turni.drawButton(tt,'block_'+x);

                        txt+='</div>';

                    txt+='</div>';

                    //////////////////////
                    txt+='<div style="position:relative;width:100%;margin-top:12px;" >';

                        txt+='<div style="display:inline-block;width:20%;text-align:center;" >';
                            txt+='<img style="width:15px;height:15px;margin-left:3px;" src="http://'+location.host+'/nebula/core/panorama/img/X.png" />';
                        txt+='</div>';

                        txt+='<div style="display:inline-block;width:50%;" >';
                            txt+='<select id="panedit_form_sub_'+x+'" class="panedit_form" >';

                                var subrif="";
                                if ('agenda' in this.actual.griglia[x]) {
                                    for (var k in this.actual.griglia[x].agenda) {
                                        subrif=k;
                                    }
                                }

                                txt+='<option value="" ';
                                    if (this.actual.griglia[x].ricric=="" || this.actual.griglia[x].ricric==0 ) {
                                        if (subrif=="") txt+='selected';
                                    } 
                                txt+= ' >Nessuno</option>';

                                //alert(JSON.stringify(this.actual.griglia[x].agenda));

                                for (var z in this.subrep) {
                                    txt+='<option value="'+z+'" '; 
                                        if (z==subrif) txt+='selected';
                                    txt+= ' >'+z+'</option>'; 
                                }

                                txt+='<option value="ricric" ';
                                    if (this.actual.griglia[x].ricric!="" && this.actual.griglia[x].ricric>0) txt+='selected';
                                txt+= ' >Ricezioni</option>';

                            txt+='</select>';
                        txt+='</div>';

                        temp=[4,2,1];

                        txt+='<div style="display:inline-block;width:30%;" >';
                            txt+='<select id="panedit_form_ric_'+x+'" class="panedit_form" >';
                                for (var z in temp) {
                                    txt+='<option value="'+temp[z]+'" ';
                                        if (temp[z]==this.actual.griglia[x].ricric) txt+='selected';
                                    txt+= ' >'+temp[z]+'</option>'; 
                                }
                            txt+='</select>';
                        txt+='</div>';

                    txt+='</div>';
                    //////////////////////

                txt+='</div>';

                txt+='<div style="display:inline-block;width:74%;vertical-align:top;" >';
                    txt+=this.drawTurno(this.actual.griglia[x].turno,10);
                txt+='</div>';

            txt+='</div>';

        }

        txt+=this.drawNewButton();

        return txt;
    }

    this.drawNewButton=function() {

        txt='<div style="margin-top:5px;">';
            txt+='<button class="panedit_form" style="margin-left:10px;" onclick="window._panedit.addNewBlock();" disabled >Nuovo blocco</button>';
        txt+='</div>';

        return txt;
    }

    this.selectTurno=function(codice,mark) {

        //alert(mark);

        if (mark=='turnoOverall') {
            this.actual.overall=codice;
            //$('#panedit_form_turnoOverall').html(this.actual.overall);
            window._kassettone_turni.setTitle(codice,mark);
            $('#panedit_form_turnoOverall_table').html(this.drawTurno(this.actual.overall,8));
        }
        else {
            var a=mark.split('_');
            this.actual.griglia[a[1]].turno=codice;
            $('#panedit_blocks_div').html(this.drawBlocks());
            setTimeout(function() {window._panedit.disableForm(false)},100);
        }

    }

    this.addNewBlock=function() {

        var ac=10;

        for (var x in this.actual.griglia) {
            if (x==0) continue;
            ac=x;
        }

        ac++;

        this.actual.griglia[ac]={
            "turno":"",
            "next":"",
            "agenda":{},
            "ricric":""
        }

        $('#panedit_blocks_div').html(this.drawBlocks());
        $('#panedit_form_collectdiv').html(this.drawCollectDiv());
        setTimeout(function() {window._panedit.disableForm(false)},100);
    }

    this.copia=function() {

        this.actual.codice="";

        this.actual.stato='crea';

        $('#panedit_form_codice').val('');

        this.actual.busy=false;
        $('#panedit_form_collectdiv').html(this.drawCollectDiv());

        $('#panedit_form_buttondiv').html('<button style="position:relative;font-size:1em;font-weight:bold;transform:translate(0%,20%);width:130px;text-align:center;" onclick="window._panedit.salvaSchema();" >Crea</button>');

        setTimeout(function() {window._panedit.disableForm(false)},100);

    }

    this.setNewTurno=function() {

        $('div[id^="panedit_turni_turno_lista_div"]').css('background-color','white');
        $('#panedit_turni_main_div').html('');
        $('#panedit_turni_form_confirm').prop('disabled',false);
        $('#panedit_turni_interval_confirm').prop('disabled',false);

        $('#panedit_turni_form_codice').val('');
        $('#panedit_turni_form_codice').prop('disabled',false);

        this.actualTurno=[
            {"wd":0,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":1,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":2,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":3,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":4,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":5,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
            {"wd":6,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'},
        ];

        this.flagNuovoTurno=true;

        this.updateTurniTurnoInterval();

        this.drawTurnoDays();
    }

    this.selectTurniTurno=function(turno) {

        $('div[id^="panedit_turni_turno_lista_div"]').css('background-color','white');
        $('#panedit_turni_main_div').html('');
        $('#panedit_turni_form_confirm').prop('disabled',true);
        $('#panedit_turni_interval_confirm').prop('disabled',true);

        $('#panedit_turni_turno_lista_div_'+turno).css('background-color','wheat');

        $('#panedit_turni_form_codice').val(turno);
        $('#panedit_turni_form_codice').prop('disabled',true);

        this.flagNuovoTurno=false;

        this.tempTurno=[];

        this.updateTurniTurnoInterval();

        Object.assign(this.actualTurno,this.turni[turno]);

        this.drawTurnoDays();
    
    }

    this.drawTurnoDays=function() {

        var wd=[
            "Dom",
            "Lun",
            "Mar",
            "Mer",
            "Gio",
            "Ven",
            "Sab"
        ];

        var txt='<div style="position:relative;top:10px;left:10px;" >';

            for (var x in this.actualTurno) {

                txt+='<div style="position:relative;border:1px solid black;margin-top:5px;margin-bottom:5px;width:90%;height:30px;" >';

                    txt+='<div style="position:relative;display:inline-block;height:30px;line-height:30px;width:15%;font-weight:bold;text-align:left;vertical-align:top;" >';
                        txt+='<div style="display:inline-block;vertical-align:middle;">';
                            if (this.flagNuovoTurno) {
                                txt+='<input id="panedit_turni_form_day_'+x+'" type="checkbox" style="margin-left:5px;" value="'+x+'"/>';
                            }
                            txt+='<span style="margin-left:5px;" >'+wd[x]+'</span>';
                        txt+='</div>';
                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;height:30px;line-height:30px;width:75%;text-align:left;vertical-align:top;" >';

                        var temp='';
                        var obj=$.parseJSON(this.actualTurno[x].orari);

                        temp=this.turnoLine(obj);

                        txt+='<div style="display:inline-block;vertical-align:middle;">'+temp+'</div>';

                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;height:30px;line-height:30px;width:10%;text-align:center;vertical-align:top;" >';
                        if (this.flagNuovoTurno) {
                            txt+='<img style="width:20px;height:20px;position: relative;top:5px;cursor:pointer;" src="http://'+location.host+'/nebula/core/panorama/img/X.png" onclick="window._panedit.resetTurnoDay('+x+');" />';
                        }
                    txt+='</div>';

                txt+='</div>';

            }

        txt+='</div>';

        $('#panedit_turni_main_div').html(txt);

    }

    this.turnoLine=function(obj) {

        var temp='';

        for (var y in obj) {

            if (obj[y].i=='00:00' && obj[y].f=='00:00') continue;

            temp+=obj[y].i+' - '+obj[y].f+' / ';
            
        }

        var len = temp.length;
        temp = temp.substring(0,len-2);

        return temp;
    }

    this.updateTurniTurnoInterval=function() {

        txt="";

        for (var x in this.tempTurno) {

            txt+='<div style="width:100%;margin-top:5px;" >';
                txt+='<div style="position:relative;display:inline-block;width:80%;" >';
                    txt+=this.tempTurno[x].i+' - '+this.tempTurno[x].f;
                txt+='</div>';
                txt+='<div style="position:relative;display:inline-block;width:20%;" >';
                    txt+='<img style="width:15px;height:15px;position:relative;top:2px;cursor:pointer;" src="http://'+location.host+'/nebula/core/panorama/img/X.png" onclick="window._panedit.resetIntervalElem('+x+');" />';
                txt+='</div>';
            txt+='</div>';
        }

        $('#panedit_turni_show_interval').html(txt);
    }

    this.resetIntervalElem=function(x) {

        temp=[];

        for (var y in this.tempTurno) {
            if (x==y) continue;
            temp.push(this.tempTurno[y]);
        }

        //Object.assign(this.tempTurno,temp);
        this.tempTurno=temp;

        this.updateTurniTurnoInterval();
    }

    this.resetTurnoDay=function(x) {

        this.actualTurno[x]={"wd":x,"orari":'[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}]'};
        this.drawTurnoDays(true);
    }

    this.addTurnoInterval=function() {

        if (!this.flagNuovoTurno) return;

        var da=$('#panedit_turni_form_da').val();
        var a=$('#panedit_turni_form_a').val();

        if (da>=a) return;

        var x=0;
        var err=false;
        var riff='00:00';

        for (var y in this.tempTurno ) {

            //se il nuovo elemento comincia prima della fine di uno esistente
            if (da<=this.tempTurno[y].f) err=true;
            x++;
        }

        if (!err) {
            this.tempTurno[x]={"i":da,"f":a,"info":{"reg":0,"str":0,"day":0}};
        }

        this.updateTurniTurnoInterval();

    }

    this.assegnaInterval=function() {

        var temp=this.tempTurno;
        var actual=this.actualTurno;

        if (temp.lenght==0) return;

        $('input[id^="panedit_turni_form_day_"]').each(function(){

            if ($(this).prop('checked')) {
                actual[$(this).val()].orari=JSON.stringify(temp);
            }

        });

        this.drawTurnoDays();
    }

    this.confermaTurno=function() {

        var codice=$('#panedit_turni_form_codice').val();

        if (codice=='' || (codice in this.turni) ) {
            alert('codice non accettabile');
            return;
        }

        var err=true;

        if (!confirm('Verrà creato il turno !! ATTENZIONE: successivamente non sarà possibile modificarlo o cancellarlo.') ) return;

        //console.log(JSON.stringify(this.actualTurno));

        var param={
            "codice":codice,
            "turno":this.actualTurno
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/quartet/core/crea_turno.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);

                $('#qt_openedit_img').click();
            }
        });
    }

    ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////

    this.evaluate=function() {

        var error=false;

        //opzioni
        var temp="";
        temp=$('#panedit_form_turnazione').val();
        if (!temp || temp=="") temp=0;
        this.actual.turnazione=temp;

        this.actual.flag_festivi=($('#panedit_form_festivi').prop('checked'))?'1':'0';
        this.actual.flag_turno=($('#panedit_form_fturno').prop('checked'))?'1':'0';

        if (this.actual.flag_festivi==0 && this.actual.flag_turno==0) this.actual.on_flag=0;
        else {
            temp="";
            temp=$('#panedit_form_flag').val();
            if (!temp || temp=="") temp=0;
            this.actual.on_flag=temp;
        }

        this.actual.mark=($('#panedit_form_mark').prop('checked'))?'1':'0';
        this.actual.exclusive=($('#panedit_form_exclusive').prop('checked'))?'1':'0';

        if (isNaN(this.actual.turnazione)) {
            alert('Valore turnazione errato');
            return;
        }

        if (isNaN(this.actual.on_flag)) {
            alert('Valore limite errato');
            return;
        }

        var c=0;
        for (var x in this.actual.griglia) {
            c++;

            if (this.actual.turnazione==0) {
                this.actual.griglia[x].next=x;
            }
            else {
                this.actual.griglia[x].next=$('#panedit_form_next_'+x).val();
            }

            var t=$('#panedit_form_sub_'+x).val();

            //alert(x+' '+t);

            if (t=='ricric') {
                this.actual.griglia[x].agenda=[];
                this.actual.griglia[x].ricric=$('#panedit_form_ric_'+x).val();
            }
            else {
                this.actual.griglia[x].agenda={};
                //serve per i vecchi schemi che conservano il blocco 0
                if (t!="" && x!=0) {
                    this.actual.griglia[x].agenda[t]=100;
                }
                this.actual.griglia[x].ricric=0;
            }

            if (this.actual.griglia[x].turno=="") {
                alert('Turno blocco '+x+' non definito...!!!');
                return false;
            }
        }

        if (c==0) {
            alert('Lo schema non ha nessun blocco definito...!!!');
            return false;
        }

        $('.panedit_tag').css('color','black');

        if (this.actual.codice=='') {
            $('#panedit_tag_codice').css('color','red');
            error=true;
        }
        if (this.actual.titolo=='') {
            $('#panedit_tag_titolo').css('color','red');
            error=true;
        }
        if (this.actual.data_i=='') {
            $('#panedit_tag_datai').css('color','red');
            error=true;
        }
        if (this.actual.overall=='') {
            $('#panedit_tag_overall').css('color','red');
            error=true;
        }

        if (error) {
            alert('Verifica i dati colorati di rosso...');
            return false;
        }
        else {
            //correzione data
            //this.actual.data_i=window._nebulaMain.data_form_to_db(this.actual.data_i);
            ///////////////////////
            return true;
        }

    }

    this.salvaSchema=function() {

        if (!this.evaluate()) return;

        var param={};
        Object.assign(param,this.actual);

        param.griglia=JSON.stringify(param.griglia);

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/quartet/core/salva.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);
                $('#qt_openedit_img').click();
            }
        });

    }

    this.aggiungiSchema=function() {
        //lega lo schema in esame al panorama che si sta modificando
        //esegue la valutazione del form prima di proseguire
        if (!this.evaluate()) return;

        var param={};
        Object.assign(param,this.actual);
        
        param.panorama=$('#panedit_form_panorama').val();
        param.blocco_inizio=$('#panedit_form_inizio').val();

        param.griglia=JSON.stringify(param.griglia);

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/quartet/core/aggiungi.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);

                $('#qt_openedit_img').click();
            }
        });
    }

    this.confirmSubs=function() {

        var param={
            "panorama":"",
            "cod_flag":false,
            "subs":[]
        }

        var codef=$('input[id^="panedit_sub_subrep_radio"]:checked').val();

        $('input[id^="panedit_sub_subrep_chk_"]:checked').each(function(){

            param.panorama=$(this).data('pan');

            var s=$(this).data('sub');

            var temp={
                "sub":s,
                "cod_def":(s==codef)?'S':'N'
            }

            param.subs.push(temp);

            if (s==codef) param.cod_flag=true;
        });

        //console.log(JSON.stringify(param));

        if (!param.cod_flag) {
            alert('Non è stato definito il sub-reparto di default');
            return;
        }

        if (!confirm('Proseguendo sarà necessario ridefinire l\'ordine dei sub-reparti')) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/quartet/core/conferma_subs.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                console.log(ret);

                $('#qt_openedit_img').click();
            }
        });
    }

}