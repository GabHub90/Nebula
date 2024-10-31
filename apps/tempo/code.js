
function tempoCode(funzione,coll,logged) {

    this.funzione=funzione;

    this.resolution=15;

    this.tpoColl=coll;
    this.loggedCollID=logged;

    //tipo di evento attualmente in fase di EDIT
    this.editEvento="";

    //è la variabile che verrà inizializzata ad oggetto nebulaAlan
    //se si gestiscono le badgiate
    //this.alan="";

    this.badgeStato='OK';

    this.schemiEdit=false;

    this.init=function() {
        //appartenenza dei collaboratori agli schemi
        this.grigliaCollDaySkema=$('#tpoSchemiData').data('arr');
        //ore di presenza ed array dei periodi dei turni occupati dal collaboratore
        this.tpoTurnoCollDay=$('#tpoTurnoCollDay').data('arr');
        //array di configurzione passato da TEMPO
        this.config=$('#tpoConfig').data('config');
    }

    this.nebulaAppSetup=function() {

        /*if (this.config.view) {
            this.tempoSetColl(this.loggedCollID);
        }
        else {
            this.tpoSetCollEvents();
        }*/

        this.tpoSetCollEvents();
    }

    this.tempoChangeRep=function(reparto) {

        if (reparto=="") return;

        $('#ribbon_tpo_reparto').val(reparto);
        $('#ribbon_tpo_coll').val('');

        window._nebulaApp.ribbonExecute();
    }

    this.selDay=function(tag) {

        if (this.editEvento=='') return;

        if (!this.tpoTurnoCollDay[this.tpoColl][tag].nominale && this.editEvento!='extra' ) {
            this.defaultEvent(this.editEvento);
            return;
        }

        if (this.editEvento=='periodo') {
            $('#tpoEventoInput_periodoDa').val(window._nebulaMain.data_db_to_form(tag));
        }

        if (this.editEvento=='permesso') {

            if (this.tpoTurnoCollDay[this.tpoColl][tag].actual<=0) {
                alert('Presenza non disponibile');
                return;
            }

            var first=true;

            var k=($('#tpoEventoInput_permessoID').val()!="")?'turnoNominale':'turno';

            for (var x in this.tpoTurnoCollDay[this.tpoColl][tag][k]) {

                var rif=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].i);
                var end=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].f);

                if (!first) {
                    $('#tpoEventoInput_permessoDa').append('<option value="" >--------</option>');
                    $('#tpoEventoInput_permessoA').append('<option value="" >--------</option>');
                }
                else {
                    $('#tpoEventoInput_permessoDa').html('<option value="" >--------</option>');
                    $('#tpoEventoInput_permessoA').html('<option value="" >--------</option>');
                    first=false;
                }

                while ( (rif+this.resolution) <= end ) {

                    $('#tpoEventoInput_permessoDa').append('<option value="'+window._nebulaMain.minToTime(rif)+'" >'+window._nebulaMain.minToTime(rif)+'</option>');
                    $('#tpoEventoInput_permessoA').append('<option value="'+window._nebulaMain.minToTime(rif+this.resolution)+'" >'+window._nebulaMain.minToTime(rif+this.resolution)+'</option>');

                    rif+=this.resolution;

                }
            }

            $('#tpoEventoInput_permessoGiorno').data('giorno',tag);
            $('#tpoEventoInput_permessoGiorno').html(window._nebulaMain.data_db_to_ita(tag));

            this.tpoCalcolaQta('permesso');

        }

        if (this.editEvento=='extra') {

            if (this.tpoTurnoCollDay[this.tpoColl][tag].nominale<=0) {
                //alert('Giorno non valido');
                //return;

                var arr=[{"i":"24:00","f":"24:00"}];
            }
            else var arr=this.tpoTurnoCollDay[this.tpoColl][tag]['turnoNominale'];

            var first=true;

            var k='turnoNominale';

            var rif=0;

            //for (var x in this.tpoTurnoCollDay[this.tpoColl][tag][k]) {
            for (var x in arr) {

                //var inizio=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].i);
                var inizio=window._nebulaMain.timeToMin(arr[x].i);

                if (!first) {
                    $('#tpoEventoInput_extraDa').append('<option value="" >--------</option>');
                    $('#tpoEventoInput_extraA').append('<option value="" >--------</option>');
                }
                else {
                    $('#tpoEventoInput_extraDa').html('<option value="" >--------</option>');
                    $('#tpoEventoInput_extraA').html('<option value="" >--------</option>');
                    first=false;
                }

                while ( rif <= (inizio-this.resolution ) ) {

                    $('#tpoEventoInput_extraDa').append('<option value="'+window._nebulaMain.minToTime(rif)+'" >'+window._nebulaMain.minToTime(rif)+'</option>');
                    $('#tpoEventoInput_extraA').append('<option value="'+window._nebulaMain.minToTime(rif+this.resolution)+'" >'+window._nebulaMain.minToTime(rif+this.resolution)+'</option>');

                    rif+=this.resolution;
                    
                }

                //rif=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].f);
                rif=window._nebulaMain.timeToMin(arr[x].f);

            }

            first=true;

            while (rif<=(1440-this.resolution)) {

                if (first) {
                    $('#tpoEventoInput_extraDa').append('<option value="" >--------</option>');
                    $('#tpoEventoInput_extraA').append('<option value="" >--------</option>');
                    first=false;
                }

                $('#tpoEventoInput_extraDa').append('<option value="'+window._nebulaMain.minToTime(rif)+'" >'+window._nebulaMain.minToTime(rif)+'</option>');
                $('#tpoEventoInput_extraA').append('<option value="'+window._nebulaMain.minToTime(rif+this.resolution)+'" >'+window._nebulaMain.minToTime(rif+this.resolution)+'</option>');

                rif+=this.resolution;
            }

            $('#tpoEventoInput_extraGiorno').data('giorno',tag);
            $('#tpoEventoInput_extraGiorno').html(window._nebulaMain.data_db_to_ita(tag));

            //this.tpoCalcolaQta('extra');

        }

        if (this.editEvento=='sposta') {

            if (this.tpoTurnoCollDay[this.tpoColl][tag].actual<=0) {
                alert('Presenza non disponibile');
                return;
            }

            var first=true;

            var k='turno';

            for (var x in this.tpoTurnoCollDay[this.tpoColl][tag][k]) {

                var rif=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].i);
                var end=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag][k][x].f);

                if (!first) {
                    $('#tpoEventoInput_spostaDa').append('<option value="" >--------</option>');
                    $('#tpoEventoInput_spostaA').append('<option value="" >--------</option>');
                }
                else {
                    $('#tpoEventoInput_spostaDa').html('<option value="" >--------</option>');
                    $('#tpoEventoInput_spostaA').html('<option value="" >--------</option>');
                    first=false;
                }

                while ( (rif+this.resolution) <= end ) {

                    $('#tpoEventoInput_spostaDa').append('<option value="'+window._nebulaMain.minToTime(rif)+'" >'+window._nebulaMain.minToTime(rif)+'</option>');
                    $('#tpoEventoInput_spostaA').append('<option value="'+window._nebulaMain.minToTime(rif+this.resolution)+'" >'+window._nebulaMain.minToTime(rif+this.resolution)+'</option>');

                    rif+=this.resolution;

                }
            }

            $('#tpoEventoInput_spostaGiorno').data('giorno',tag);
            $('#tpoEventoInput_spostaGiorno').html(window._nebulaMain.data_db_to_ita(tag));

        }

    }

    this.tpoCalcolaQta=function(ambito) {
        //era nato sia per "permesso" che "extra" ma è rimasto solo per "permesso"

        var tag=$('#tpoEventoInput_'+ambito+'Giorno').data('giorno');

        var d=$('#tpoEventoInput_'+ambito+'Da').val();
        var a=$('#tpoEventoInput_'+ambito+'A').val();

        if (d=="" || a=="" || a<d) {
            $('#tpoEventoInput_'+ambito+'Qta').html('0.0 h');
            return;
        }

        d=window._nebulaMain.timeToMin(d);
        a=window._nebulaMain.timeToMin(a);

        var minuti=0;

        for (var x in this.tpoTurnoCollDay[this.tpoColl][tag].turnoNominale) {

            var start=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag].turnoNominale[x].i);
            var end=window._nebulaMain.timeToMin(this.tpoTurnoCollDay[this.tpoColl][tag].turnoNominale[x].f);

            //verifica se il riferimento interseca (o non interseca per extra) l'intervallo attuale
            if (ambito=='permesso') {
               if (d<end && a>=start) {
                   var i=(d<start)?start:d;
                   var f=(a>=end)?end:a;
               }
               else continue;
            }

            /*if (ambito=='extra') { 
                if(d<=start && d>end) {

                }
            }*/

            minuti+=(f-i);
        }

        var res=minuti/60;
        $('#tpoEventoInput_'+ambito+'Qta').html(res.toFixed(2)+' h');


    }

    this.tempoSetColl=function(coll) {

        var chk=false;
        var idcoll=coll;

        //console.log(idcoll+' '+this.config.vincola_utente);

        //se l'impostazione "vincola_utente" è attiva
        //se l'utente non è quello loggato non proseguire
        if (idcoll!="" && this.config.vincola_utente) {

            if (idcoll!=this.loggedCollID) {
                //this.clearHead();
                return;
            }
        }

        this.tpoColl=idcoll;

        $('#ribbon_tpo_coll').val(idcoll);

        if (this.tpoColl=="") {
            //window._nebulaApp.ribbonExecute();
            $('.tpoCollIntest').css('background-color','white');
            $('.schemiGrigliaCover').html('');
            this.clearHead();
            return;
        }
        else {

            //se non si è in MAESTRO-TEMPO
            if (this.config.view) {
                //verifica se il collaboratore appartiene alla lista nel div di sinistra
                $('.tpoCollIntestx1').each(function() {
                    if($(this).attr('idcoll')==idcoll) chk=true;
                });
                if (!chk) {
                    this.clearHead();
                    return;
                }
            }

            this.tpoSetCollEvents();
        }
        
    }

    this.toggleHeadEvent=function(evento,obj) {

        if (this.tpoColl=="") return;

        if (obj!=null && $(obj).attr('tpoid')!="") {

            evento=$(obj).attr('tpoevento');

            this.editEvento=evento;

            if (evento=='periodo') {

                $('#tpoEventoInput_periodoError').html('');
                $('#tpoEventoContainer_periodoDa').css('border-color','transparent');
                $('#tpoEventoContainer_periodoA').css('border-color','transparent');

                $('input[name="tpoEventoInput_periodoTipo"]').each(function() {
                    if($(this).val()==$(obj).attr('tpotipo')) $(this).prop('checked',true);
                    else $(this).prop('checked',false);
                });

                $('#tpoEventoInput_periodoDa').val(window._nebulaMain.data_db_to_form($(obj).attr('tpodatai')));
                $('#tpoEventoInput_periodoA').val(window._nebulaMain.data_db_to_form($(obj).attr('tpodataf')));

                if ($(obj).attr('tpoconferma')!="") $('#tpoEventoInput_periodoConfirm').prop('checked',true);
                else $('#tpoEventoInput_periodoConfirm').prop('checked',false);

                $('#tpoEventoInput_periodoID').val($(obj).attr('tpoid'));
            }

            else if (evento=='permesso') {

                $('#tpoEventoInput_permessoError').html('');
                $('#tpoEventoContainer_permessoGiorno').css('border-color','transparent');
                $('#tpoEventoContainer_permessoDa').css('border-color','transparent');
                $('#tpoEventoContainer_permessoA').css('border-color','transparent');

                $('#tpoEventoInput_permessoID').val($(obj).attr('tpoid'));

                this.selDay($(obj).attr('giorno'));

                $('input[name="tpoEventoInput_permessoTipo"]').each(function() {
                    if($(this).val()==$(obj).attr('tpotipo')) $(this).prop('checked',true);
                    else $(this).prop('checked',false);
                });

                $('#tpoEventoInput_permessoGiorno').html(window._nebulaMain.data_db_to_ita($(obj).attr('giorno')));
                $('#tpoEventoInput_permessoGiorno').data('giorno',$(obj).attr('giorno'));

                $('#tpoEventoInput_permessoDa option[value="'+$(obj).attr('orai')+'"]').attr('selected',true);
                $('#tpoEventoInput_permessoA option[value="'+$(obj).attr('oraf')+'"]').attr('selected',true);

                if ($(obj).attr('tpoconferma')!="") $('#tpoEventoInput_permessoConfirm').prop('checked',true);
                else $('#tpoEventoInput_permessoConfirm').prop('checked',false);

                //occorre ripeterlo
                this.tpoCalcolaQta('permesso');
            }

            else if (evento=='extra') {

                $('#tpoEventoInput_extraError').html('');
                $('#tpoEventoContainer_extraGiorno').css('border-color','transparent');
                $('#tpoEventoContainer_extraDa').css('border-color','transparent');
                $('#tpoEventoContainer_extraA').css('border-color','transparent');

                $('#tpoEventoInput_extraID').val($(obj).attr('tpoid'));

                this.selDay($(obj).attr('giorno'));

                $('input[name="tpoEventoInput_extraTipo"]').each(function() {
                    if($(this).val()==$(obj).attr('tpotipo')) $(this).prop('checked',true);
                    else $(this).prop('checked',false);
                });

                $('#tpoEventoInput_extraGiorno').html(window._nebulaMain.data_db_to_ita($(obj).attr('giorno')));
                $('#tpoEventoInput_extraGiorno').data('giorno',$(obj).attr('giorno'));

                $('#tpoEventoInput_extraDa option[value="'+$(obj).attr('orai')+'"]').attr('selected',true);
                $('#tpoEventoInput_extraA option[value="'+$(obj).attr('oraf')+'"]').attr('selected',true);

                if ($(obj).attr('tpoconferma')!="") $('#tpoEventoInput_extraConfirm').prop('checked',true);
                else $('#tpoEventoInput_extraConfirm').prop('checked',false);

                //this.tpoCalcolaQta('extra',$(obj).attr('giorno'));
            }

            else if (evento=='sposta') {

                $('#tpoEventoInput_spostaError').html('');
                $('#tpoEventoContainer_spostaGiorno').css('border-color','transparent');
                $('#tpoEventoContainer_spostaDa').css('border-color','transparent');
                $('#tpoEventoContainer_spostaA').css('border-color','transparent');

                $('#tpoEventoInput_spostaID').val($(obj).attr('tpoid'));
                //$('#tpoEventoInput_spostaPanorama').val($(obj).attr('panorama'));

                this.selDay($(obj).attr('giorno'));

                $('#tpoEventoInput_spostaGiorno').html(window._nebulaMain.data_db_to_ita($(obj).attr('giorno')));
                $('#tpoEventoInput_spostaGiorno').data('giorno',$(obj).attr('giorno'));

                $('#tpoEventoInput_spostaDa option[value="'+$(obj).attr('orai')+'"]').attr('selected',true);
                $('#tpoEventoInput_spostaA option[value="'+$(obj).attr('oraf')+'"]').attr('selected',true);

                $('#tpoEventoInput_spostaSub option[value="'+$(obj).attr('suba')+'"]').attr('selected',true);
            }

            //////////
            if (evento!='sposta') {
                if ($(obj).attr('tpoconferma')!="") $('#tpoEventoInput_'+evento+'Confirm').prop('checked',true);
                else $('#tpoEventoInput_'+evento+'Confirm').prop('checked',false);
            }

            if (this.config.cancella) $('#tpoEventoInput_'+evento+'Trash').show();
            else $('#tpoEventoInput_'+evento+'Trash').hide();

            $('#tpoEventoInput_'+evento+'Button').html('Modifica');

        }
        else {
            this.defaultEvent(evento);

            this.editEvento=evento;

            if (this.config.autorizza) {
                $('#tpoEventoInput_'+evento+'Confirm').prop('checked',true);
            }
            else {
                $('#tpoEventoInput_'+evento+'Confirm').prop('checked',false);
            }

            //in ogni caso, se non è specificato un ID evento il cestino non serve
            $('#tpoEventoInput_'+evento+'Trash').hide();

            $('#tpoEventoInput_'+evento+'Button').html('Nuovo');
        }

        ////////////////////////////////////////////////////////////////////

        if (this.config.autorizza) {
            $('#tpoEventoInput_'+evento+'Confirm').prop('disabled',false);  
        }
        else {
            $('#tpoEventoInput_'+evento+'Confirm').prop('disabled',true);   
        }

        //////////////////////////////////////////////////////////////////

        $("div[id^='tpoHeadEvento_']").hide();

        $("#tpoHeadEvento_"+evento).show();

    }

    this.defaultEvent=function(evento) {

        if (evento=='periodo') {

            $('#tpoEventoInput_periodoError').html('');
            $('#tpoEventoContainer_periodoDa').css('border-color','transparent');
            $('#tpoEventoContainer_periodoA').css('border-color','transparent');

            $('input[name="tpoEventoInput_periodoTipo"]').each(function() {
                if($(this).val()=='F') $(this).prop('checked',true);
                else $(this).prop('checked',false);
            });

            $('#tpoEventoInput_periodoDa').val("");
            $('#tpoEventoInput_periodoA').val("");

            $('#tpoEventoInput_periodoID').val("");
        }

        else if (evento=='permesso') {

            $('#tpoEventoInput_permessoError').html('');
            $('#tpoEventoContainer_permessoGiorno').css('border-color','transparent');
            $('#tpoEventoContainer_permessoDa').css('border-color','transparent');
            $('#tpoEventoContainer_permessoA').css('border-color','transparent');

            $('input[name="tpoEventoInput_permessoTipo"]').each(function() {
                if($(this).val()=='P') $(this).prop('checked',true);
                else $(this).prop('checked',false);
            });

            $('#tpoEventoInput_permessoDa').html('<option value="">Seleziona giorno</option>');
            $('#tpoEventoInput_permessoA').html('<option value="">Seleziona giorno</option>');

            $('#tpoEventoInput_permessoGiorno').html('');
            $('#tpoEventoInput_permessoGiorno').data('giorno','');

            $('#tpoEventoInput_permessoID').val("");

            $('#tpoEventoInput_permessoQta').html("");
        }

        else if (evento=='extra') {

            $('#tpoEventoInput_extraError').html('');
            $('#tpoEventoContainer_extraGiorno').css('border-color','transparent');
            $('#tpoEventoContainer_extraDa').css('border-color','transparent');
            $('#tpoEventoContainer_extraA').css('border-color','transparent');

            $('input[name="tpoEventoInput_extraTipo"]').each(function() {
                if($(this).val()=='E') $(this).prop('checked',true);
                else $(this).prop('checked',false);
            });

            $('#tpoEventoInput_extraDa').html('<option value="">Seleziona giorno</option>');
            $('#tpoEventoInput_extraA').html('<option value="">Seleziona giorno</option>');

            $('#tpoEventoInput_extraGiorno').html('');
            $('#tpoEventoInput_extraGiorno').data('giorno','');

            $('#tpoEventoInput_extraID').val("");

            $('#tpoEventoInput_extraQta').html("");
        }

        else if (evento=='sposta') {

            $('#tpoEventoInput_spostaError').html('');
            $('#tpoEventoContainer_spostaGiorno').css('border-color','transparent');
            $('#tpoEventoContainer_spostaDa').css('border-color','transparent');
            $('#tpoEventoContainer_spostaA').css('border-color','transparent');

            $('#tpoEventoInput_spostaDa').html('<option value="">manca giorno</option>');
            $('#tpoEventoInput_spostaA').html('<option value="">manca giorno</option>');

            $('#tpoEventoInput_spostaGiorno').html('');
            $('#tpoEventoInput_spostaGiorno').data('giorno','');

            $('#tpoEventoInput_spostaSub').val("");

            //$('#tpoEventoInput_spostaPanorama').val("");

            $('#tpoEventoInput_spostaID').val("");
        }

    }

    this.clearHead=function() {
        $('#tpoHeadTools').hide();
        $('.tpo_griglia_presenza_cover').css('background-color','transparent');
        $('#tpoHeadSelected').html('');
        $('.schemiGrigliaCover').css('background-color','transparent');
    }

    this.tpoSetCollEvents=function() {

        $('.tpoCollIntest').css('background-color','white');
        $('.schemiGrigliaCover').html('');

        if (this.tpoColl=="") {
            //23.06.21 NON dovrebbe essere più eseguito per cambio codice in tempoSetColl()
            this.clearHead();
        }
        else {

            $('.tpoGoodDay').css('cursor','pointer');

            if (this.config.autorizza) {
                $('.tpo_griglia_presenza_cover[idcoll="'+this.tpoColl+'"]').css('cursor','pointer');
            }

            $('#tpoHeadTools').show();
            $('.tpo_griglia_presenza_cover').css('background-color','#ffffffbb');
            $('.tpo_griglia_presenza_cover[idcoll="'+this.tpoColl+'"]').css('background-color','transparent');
            $('.tpoCollIntest[idcoll="'+this.tpoColl+'"]').css('background-color','bisque');

            var txt=$('.tpoCollIntestx1[idcoll="'+this.tpoColl+'"]').html()+$('.tpoCollIntestx2[idcoll="'+this.tpoColl+'"]').html();

            //txt+='<img style="position:absolute;top:0px;right:0px;width:15px;height:15px;cursor:pointer;z-index:3;" src="http://'+location.host+'/nebula/apps/tempo/img/annulla.png" onclick="window._nebulaApp_'+this.funzione+'.tempoSetColl(\'\');" />';

            $('#tpoHeadSelected').html(txt);

            $('.schemiGrigliaCover').css('background-color','#ffffffbb');

            //schemi
            //[coll][giorno][skema]={"actual","standard","flag"}
            for (var giorno in this.grigliaCollDaySkema[this.tpoColl]) {

                for (var skema in this.grigliaCollDaySkema[this.tpoColl][giorno]) {

                    var arr=this.grigliaCollDaySkema[this.tpoColl][giorno][skema];

                    $('.schemiGrigliaCover[data-codice="'+skema+'"][data-tag="'+giorno+'"]').each(function() {
                        //console.log($(this).data('blocco'));
                        if ($(this).data('blocco')==arr.actual) {
                            if (arr.flag=='STD') {
                                $(this).css('background-color','transparent');
                            }
                            else if (arr.flag=='CNC') {
                                $(this).css('background-color','#c3bdbd9e');
                            }
                            else if (arr.flag=='AGG') {
                                $(this).css('background-color','#fff6888c');
                            }
                        }
                        else if ($(this).data('blocco')==arr.standard) {
                            //STD succede solo se actual==standard
                            //CNC succede solo se actual==standard
                            //a questo punto ACTUAL è per forza diverso da STANDARD
                            $(this).css('background-color','#c3bdbd9e');
                        }
                    });
                    
                }

            }

        }
    }

    this.tpoChangeSkema=function(obj) {

        if (!this.config.sostituzioni) return;

        /*this.grigliaCollDaySkema [coll][tag][skema]
        "panorama"=>$csk['panorama'],
        "actual"=>$temp['blocco'],
        "standard"=>$temp['blocco'],
        "flag"=> STD | AGG | CNC
        */

        if (this.tpoColl=="") return;

        var panorama=$(obj).data('panorama');
        var codice=$(obj).data('codice');
        var giorno=$(obj).data('tag');
        var blocco=$(obj).data('blocco');

        //alert(panorama+' '+codice+' '+giorno+' '+blocco);

        /*la classe intervallo la prima volta che incappa nel blocco lo scrive in "grigliaCollDaySkema"
        se poi il blocco risulta CNC per una sostituzione cambia il FLAG (actual rimane uguale a standard)
        se invece la sostituzione è AGG il record di "grigliaCollDaySkema" potrebbe esistere già come CNC
        in questo caso il flag diventa AGG ma ACTUAL diverrà diverso da STANDARD
        */

        var param={
            "collaboratore":this.tpoColl,
            "panorama":panorama,
            "tag":giorno,
            "skema":codice,
            "turno":blocco,
            "azione":"AGG",
            "operazione":"insert",
            "funzione":this.funzione
        }

        if (this.tpoColl in this.grigliaCollDaySkema) {

            if (giorno in this.grigliaCollDaySkema[this.tpoColl]) {

                if (codice in this.grigliaCollDaySkema[this.tpoColl][giorno]) {

                    //alert(JSON.stringify(this.grigliaCollDaySkema[this.tpoColl][giorno][codice]));

                    if (this.grigliaCollDaySkema[this.tpoColl][giorno][codice].flag=='STD') {

                        if (blocco==this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard) {
                            param.azione='CNC';
                        }
                        else if (this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard!="" || this.grigliaCollDaySkema[this.tpoColl][giorno][codice].actual!="") param.operazione='error';
                        //else param.operazione='error';
                    }

                    else if (this.grigliaCollDaySkema[this.tpoColl][giorno][codice].flag=='CNC') {

                        if (blocco==this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard) {
                            param.azione='CNC';
                            param.operazione='delete';
                            param.panorama=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].panorama;
                            param.turno=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard;
                        }
                    }

                    else if (this.grigliaCollDaySkema[this.tpoColl][giorno][codice].flag=='AGG') {

                        if (blocco==this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard) {

                            if (this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard==this.grigliaCollDaySkema[this.tpoColl][giorno][codice].actual) {
                                param.azione='AGG';
                                param.operazione='delete';
                                param.panorama=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].panorama;
                                param.turno=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard;
                            }
                            else {
                                param.operazione='error';
                            }
                        }
                        else if (blocco==this.grigliaCollDaySkema[this.tpoColl][giorno][codice].actual) {
                            param.azione='AGG';
                            param.operazione='delete';
                            param.panorama=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].panorama;
                            param.turno=this.grigliaCollDaySkema[this.tpoColl][giorno][codice].actual;
                        }
                        else {
                            param.operazione='error';
                        }
                    }
                }
            }

            console.log("blocco_"+blocco);
            console.log("actual_"+this.grigliaCollDaySkema[this.tpoColl][giorno][codice].actual);
            console.log("std_"+this.grigliaCollDaySkema[this.tpoColl][giorno][codice].standard);
        }

        //alert(JSON.stringify(param));

        if (param.operazione=='error') return;

        $(obj).html('<img style="position:relative;top:50%;left:50%;width:20px;height:20px;transform:translate(-50%,-50%);" src="http://'+location.host+'/nebula/main/img/busy2.gif" />');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/change_skema.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.stato=='OK') {
                    window["_nebulaApp_"+res.param.funzione].resumeSkema(res.param);
                }

                $('.schemiGrigliaCover').html('');

            }
        });

    }

    this.resumeSkema=function(param) {

        //#####################################################
        //in base a PARAM aggiorna "this.grigliaCollDaySkema"
        if (param.operazione=='insert') {

            if (param.azione=='CNC') {
                this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].flag='CNC';
            }
            else if (param.azione=='AGG') {
                var temp=false;
                //se il record per quello skema esiste già
                if (param.collaboratore in this.grigliaCollDaySkema) {
                    if (param.tag in this.grigliaCollDaySkema[param.collaboratore]) {
                        if (param.skema in this.grigliaCollDaySkema[param.collaboratore][param.tag]) {
                            this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].flag='AGG';
                            this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].actual=param.turno;
                            temp=true;
                        }
                    }
                }
                //se il record non esiste
                if (!temp) {
                    this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema]={
                        "panorama":param.panorama,
                        "actual":param.turno,
                        "standard":param.turno,
                        "flag":"AGG"
                    };
                }
            }
        }

        if (param.operazione=='delete') {

            if (param.azione=='CNC') {
                this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].flag='STD';
            }

            else if (param.azione=='AGG') {

                //se actual!=standard
                if (this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].actual!=this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].standard) {
                    this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].actual=this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].standard;
                    this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema].flag='CNC';
                }
                else {
                    delete(this.grigliaCollDaySkema[param.collaboratore][param.tag][param.skema]);
                }
            }
        }

        //#####################################################

        this.schemiEdit=true;

        this.tpoSetCollEvents();

    }

    this.tpoConfirmPeriodo=function() {

        var funzione=this.funzione;
        //var coll=this.tpoColl;

        $('#tpoEventoInput_periodoError').html('');
        $('#tpoEventoContainer_periodoDa').css('border-color','transparent');
        $('#tpoEventoContainer_periodoA').css('border-color','transparent');

        var param={
            "tipo":$('input[name="tpoEventoInput_periodoTipo"]:checked').val(),
            "da":$('#tpoEventoInput_periodoDa').val(),
            "a":$('#tpoEventoInput_periodoA').val(),
            "conferma":$('#tpoEventoInput_periodoConfirm').prop('checked'),
            "ID":$('#tpoEventoInput_periodoID').val(),
            "coll":this.tpoColl,
            "logged":window._nebulaMain.getMainLogged()
        };

        var error="";

        if (!this.tpoTurnoCollDay[this.tpoColl].hasOwnProperty(window._nebulaMain.data_form_to_db(param.da)) ) {
            $('#tpoEventoContainer_periodoDa').css('border-color','red');
            error='Data iniziale senza presenza';
        }

        else if (!this.tpoTurnoCollDay[this.tpoColl][window._nebulaMain.data_form_to_db(param.da)].nominale ) {
            $('#tpoEventoContainer_periodoDa').css('border-color','red');
            error='Data iniziale senza presenza';
        }

        else if (param.da=="") {
            $('#tpoEventoContainer_periodoDa').css('border-color','red');
            error='Data iniziale sbagliata';
        }

        else if (param.a=="") {
            $('#tpoEventoContainer_periodoA').css('border-color','red');
            error='Data finale sbagliata';
        }

        else if (param.a<param.da) {
            $('#tpoEventoContainer_periodoA').css('border-color','red');
            error='Data finale maggiore di quella iniziale';
        }

        //alert(this.tpoTurnoCollDay[this.tpoColl][window._nebulaMain.data_form_to_db(param.da)].nominale);

        if (error!="") {
            $('#tpoEventoInput_periodoError').html(error);
            return;
        }

        //////////////////////////////////////////////////

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/set_periodo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.stato=='OK') {
                    //window["_nebulaApp_"+funzione].tempoSetColl('');
                    window._nebulaApp.ribbonExecute();
                }
                else {
                    $('#tpoEventoContainer_periodoDa').css('border-color','red');
                    $('#tpoEventoContainer_periodoA').css('border-color','red');
                    $('#tpoEventoInput_periodoError').html(res.error);
                }

            }
        });

    }

    this.tpoDelPeriodo=function() {

        if (!confirm('Il periodo sarà cancellato (OPERAZIONE NON ANNULLABILE!!!)')) return;

        var param={
            "ID":$('#tpoEventoInput_periodoID').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/del_periodo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.tpoConfirmPermesso=function() {

        var funzione=this.funzione;
        //var coll=this.tpoColl;

        $('#tpoEventoInput_permessoError').html('');
        $('#tpoEventoContainer_permessoGiorno').css('border-color','transparent');
        $('#tpoEventoContainer_permessoDa').css('border-color','transparent');
        $('#tpoEventoContainer_permessoA').css('border-color','transparent');

        var param={
            "tipo":$('input[name="tpoEventoInput_permessoTipo"]:checked').val(),
            "giorno":$('#tpoEventoInput_permessoGiorno').data('giorno'),
            "da":$('#tpoEventoInput_permessoDa').val(),
            "a":$('#tpoEventoInput_permessoA').val(),
            "conferma":$('#tpoEventoInput_permessoConfirm').prop('checked'),
            "ID":$('#tpoEventoInput_permessoID').val(),
            "coll":this.tpoColl,
            "logged":window._nebulaMain.getMainLogged()
        };

        var error="";

        if (param.giorno=="") {
            $('#tpoEventoContainer_permessoGiorno').css('border-color','red');
            error='Giorno non selezionato';
        }

        else if (param.da=="") {
            $('#tpoEventoContainer_permessoDa').css('border-color','red');
            error='Orario iniziale non selezionato';
        }

        else if (param.a=="") {
            $('#tpoEventoContainer_permessoA').css('border-color','red');
            error='Orario finale non selezionato';
        }

        else if (param.a<=param.da) {
            $('#tpoEventoContainer_permessoA').css('border-color','red');
            error='Orario finale sbagliato';
        }

        if (error!="") {
            $('#tpoEventoInput_permessoError').html(error);
            return;
        }

        //////////////////////////////////////////////////

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/set_permesso.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.stato=='OK') {
                    //window["_nebulaApp_"+funzione].tempoSetColl('');
                    window._nebulaApp.ribbonExecute();
                }
                else {
                    $('#tpoEventoContainer_permessoDa').css('border-color','red');
                    $('#tpoEventoContainer_permessoA').css('border-color','red');
                    $('#tpoEventoInput_permessoError').html(res.error);
                }

            }
        });

    }

    this.tpoDelPermesso=function() {

        if (!confirm('Il periodo sarà cancellato (OPERAZIONE NON ANNULLABILE!!!)')) return;

        var param={
            "ID":$('#tpoEventoInput_permessoID').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/del_permesso.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.tpoConfirmExtra=function() {

        var funzione=this.funzione;
        //var coll=this.tpoColl;

        $('#tpoEventoInput_extraError').html('');
        $('#tpoEventoContainer_extraGiorno').css('border-color','transparent');
        $('#tpoEventoContainer_extraDa').css('border-color','transparent');
        $('#tpoEventoContainer_extraA').css('border-color','transparent');

        var param={
            "tipo":$('input[name="tpoEventoInput_extraTipo"]:checked').val(),
            "giorno":$('#tpoEventoInput_extraGiorno').data('giorno'),
            "da":$('#tpoEventoInput_extraDa').val(),
            "a":$('#tpoEventoInput_extraA').val(),
            "conferma":$('#tpoEventoInput_extraConfirm').prop('checked'),
            "ID":$('#tpoEventoInput_extraID').val(),
            "coll":this.tpoColl,
            "logged":window._nebulaMain.getMainLogged()
        };

        var error="";

        if (param.giorno=="") {
            $('#tpoEventoContainer_extraGiorno').css('border-color','red');
            error='Giorno non selezionato';
        }

        else if (param.da=="") {
            $('#tpoEventoContainer_extraDa').css('border-color','red');
            error='Orario iniziale non selezionato';
        }

        else if (param.a=="") {
            $('#tpoEventoContainer_extraA').css('border-color','red');
            error='Orario finale non selezionato';
        }

        else if (param.a<=param.da) {
            $('#tpoEventoContainer_extraA').css('border-color','red');
            error='Orario finale sbagliato';
        }

        if (error!="") {
            $('#tpoEventoInput_extraError').html(error);
            return;
        }

        //////////////////////////////////////////////////

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/set_extra.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.stato=='OK') {
                    //window["_nebulaApp_"+funzione].tempoSetColl('');
                    window._nebulaApp.ribbonExecute();
                }
                else {
                    $('#tpoEventoContainer_extraDa').css('border-color','red');
                    $('#tpoEventoContainer_extraA').css('border-color','red');
                    $('#tpoEventoInput_extraError').html(res.error);
                }

            }
        });

    }

    this.tpoDelExtra=function() {

        if (!confirm('Il periodo sarà cancellato (OPERAZIONE NON ANNULLABILE!!!)')) return;

        var param={
            "ID":$('#tpoEventoInput_extraID').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/del_extra.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.tpoConfirmSposta=function() {

        var funzione=this.funzione;
        //var coll=this.tpoColl;

        $('#tpoEventoInput_spostaError').html('');
        $('#tpoEventoContainer_spostaGiorno').css('border-color','transparent');
        $('#tpoEventoContainer_spostaDa').css('border-color','transparent');
        $('#tpoEventoContainer_spostaA').css('border-color','transparent');

        var param={
            "giorno":$('#tpoEventoInput_spostaGiorno').data('giorno'),
            "da":$('#tpoEventoInput_spostaDa').val(),
            "a":$('#tpoEventoInput_spostaA').val(),
            "suba":$('#tpoEventoInput_spostaSub').val(),
            "ID":$('#tpoEventoInput_spostaID').val(),
            "coll":this.tpoColl,
            "panorama":$('#tpoEventoInput_spostaPanorama').val(),
        };

        var error="";

        if (param.giorno=="") {
            $('#tpoEventoContainer_spostaGiorno').css('border-color','red');
            error='Giorno non selezionato';
        }

        else if (param.da=="") {
            $('#tpoEventoContainer_spostaDa').css('border-color','red');
            error='Orario iniziale non selezionato';
        }

        else if (param.a=="") {
            $('#tpoEventoContainer_spostaA').css('border-color','red');
            error='Orario finale non selezionato';
        }

        else if (param.a<param.da) {
            $('#tpoEventoContainer_spostaA').css('border-color','red');
            error='Orario finale minore di quello iniziale';
        }

        if (error!="") {
            $('#tpoEventoInput_spostaError').html(error);
            return;
        }

        //////////////////////////////////////////////////

        //alert(JSON.stringify(param));
        //return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/set_sposta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.stato=='OK') {
                    //window["_nebulaApp_"+funzione].tempoSetColl('');
                    window._nebulaApp.ribbonExecute();
                }
                else {
                    $('#tpoEventoInput_spostaError').html(res.error);
                }

            }
        });

    }

    this.tpoDelSposta=function() {

        if (!confirm('Il periodo sarà cancellato (OPERAZIONE NON ANNULLABILE!!!)')) return;

        var param={
            "ID":$('#tpoEventoInput_spostaID').val()
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/del_sposta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.linkAlerts=function(tag,IDcoll) {

        $('#ribbon_tpo_divo').val('presenza');
        $('#ribbon_tpo_coll').val(IDcoll);

        window._calnav_tempo.setToday(tag);
    }

    //////////////////////////////////////////////////////////////////////////////////////////

    this.setAlan=function() {
        window._tpo_alan=new nebulaAlan();
        window._tpo_alan.init('tpo',this);
        window._tpo_alan.evaluate();
    }

    this.alanCheck=function() {

        //var obj=this;

        //this.badgeStato='OK';

        $('input[id^="tpo_alan_collBadgeIndex_"]').each(function() {

            var idcoll=$(this).data('idcoll');

            var stato=window._tpo_alan.getCollStato(idcoll);

            if (stato=='OK') {
                window._badge_divo.setStato($(this).val(),'V');
            }
            else if (stato=='ALL') {
                window._badge_divo.setStato($(this).val(),'G');
                //if (obj.badgeStato!='KO') obj.badgeStato='ALL';
            }
            else {
                window._badge_divo.setStato($(this).val(),'R');
                //obj.badgeStato='KO';
            }

        });

        this.badgeStato=window._tpo_alan.getStatoverall();

        if (this.badgeStato=='OK') {
            window._tempo_divo.setStato($('#tpo_mainDivo_Badge').val(),'V');
        }
        else if (this.badgeStato=='ALL') {
            window._tempo_divo.setStato($('#tpo_mainDivo_Badge').val(),'G');
        }
        else {
            window._tempo_divo.setStato($('#tpo_mainDivo_Badge').val(),'R');
        }

    }

    this.refreshAlanDay=function(txt) {

        try {
            var obj=$.parseJSON(txt);
        }
        catch(error) {
            console.error(error);
            console.log(txt);
            alert(txt);
            return;
        }

        var param={
            "IDcoll":obj.IDcoll,
            "tag":obj.tag,
            "macroreparto":$('#ribbon_tpo_macroreparto').val(),
            "reparto":$('#ribbon_tpo_reparto').val()
        };

        window._refresher=param;
        window._refresher.funzione=this.funzione;

        $('div[id^="alanContainer_"][data-tag="'+window._refresher.tag+'"][data-idcoll="'+window._refresher.IDcoll+'"]').html('<img style="position:relative;width:30px;height:30px;left:50%;margin-left:-15px;margin-top:10px;" src="http://'+location.host+'/nebula/main/img/busy2.gif" />');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/badgeRefresh.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('div[id^="alanContainer_"][data-tag="'+window._refresher.tag+'"][data-idcoll="'+window._refresher.IDcoll+'"]').html(ret);
                //alert("_nebulaApp_"+window._refresher.funzione);
                setTimeout(window["_nebulaApp_"+window._refresher.funzione].refreshAlanCheck,150);
            }
        });

    }

    this.refreshAlanCheck=function() {
        window._tpo_alan.evaluate();
        window["_nebulaApp_"+window._refresher.funzione].alanCheck();
    }

    ////////////////////////////////////////////////////////////////

    this.allineaDms=function(obj) {

        var param={
            "obj":$(obj).data('info')
        }

        $('#tempo_allineadms_button').hide();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/tempo/core/allinea_dms.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
             
                //console.log(ret);

                $('#tempo_allineadms_button').show();
                alert('Allineamento eseguito!!');
            }
        });

    }
    
}