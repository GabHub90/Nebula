
function c2rCode(funzione) {

    this.funzione=funzione;

    this.param={
        "reparti":"",
        "allReparti":"",
        "marche":"",
        "inizio":"",
        "fine":"",
        "prodTipo":"",
        "obso":"",
        "default":"",
    }

    this.ambiti={
        "fatturato_S":false,
        "prod_S":false,
        "budget_S":false,
        "giac_M":false,
        "ven_M":false,
        "acq_M":false
    }

    this.c2rTableLabel=true;

    this.link=false;

    this.refreshReparti=function() {

        $('input[id^="c2r_reparto_set_"]').each(function() {

            var v=$(this).val();

            var id=$(this).data('id');

            if (v=='0') $('#c2r_reparto_cover_'+id).show();
            else $('#c2r_reparto_cover_'+id).hide();
        });

        this.clear();
    }

    this.setReparto=function(reparto) {

        var v=$('#c2r_reparto_set_'+reparto).val();

        $('#c2r_reparto_set_'+reparto).val(v=='0'?'1':'0');

        this.refreshReparti();
    }

    this.setRepartoAll=function() {

        var v=$('#c2r_divbutton_tutti').html();

        if (v=='tutti') {
            $('input[id^="c2r_reparto_set_"]').val('1');
            $('#c2r_divbutton_tutti').html('nessuno');
        }
        else {
            $('input[id^="c2r_reparto_set_"]').val('0');
            $('#c2r_divbutton_tutti').html('tutti');
        }

        this.refreshReparti();

    }

    this.setMarcaAll=function(flag) {

        //alert(flag);

        $('input[id^="c2r_marca_set_"]').prop('checked',flag);

        this.clear();
    }

    this.setPeriodo=function(inizio,fine,tipo) {

        if (inizio!='') {

            var anno=$('#c2r_periodo_opzioni_anno').val();
            var oggi=new Date();

            if (inizio=='YTD') {
                inizio='01-01';
                oggi=window._nebulaMain.moveByDays(oggi,-1);
                var td=oggi.getDate();
                var tm=oggi.getMonth()+1;
                fine=''+( (tm<10)?'0'+tm.toString():tm )+'-'+( (td<10)?'0'+td.toString():td );
            }

            //se la fine è febbraio
            else if (fine=='02-28') {
                if ( window._nebulaMain.leapYear(anno) ) fine='02-29';
            }

            inizio=anno.toString()+'-'+inizio;
            fine=anno.toString()+'-'+fine;

            //alert(inizio+' '+fine);

            $('#c2r_periodo_opzioni_inizio').val(inizio);
            $('#c2r_periodo_opzioni_fine').val(fine);
        }

        //m=mese , t=trimestre , s=semestre , a=anno , f=free e ytd
        //this.tipo_periodo=tipo;

        if (tipo=='f') {
            $('#c2r_periodo_opzioni_inizio').prop('disabled',false);
            $('#c2r_periodo_opzioni_fine').prop('disabled',false);
        }
        else {
            $('#c2r_periodo_opzioni_inizio').prop('disabled',true);
            $('#c2r_periodo_opzioni_fine').prop('disabled',true);   
        }

        this.clear();

    }

    this.chgAnno=function() {
        $('#c2r_periodo_opzioni_inizio').val('');
        $('#c2r_periodo_opzioni_fine').val('');
        $('#c2r_periodo_opzioni_inizio').prop('disabled',false);
        $('#c2r_periodo_opzioni_fine').prop('disabled',false);

        this.clear();
    }

    this.clear=function() {
        $('#c2r_navigator_error').html('');
        $('div[id^="c2r_navigator_"').css("border-color","transparent");
    }

    this.check=function() {

        this.clear();

        //verifica reparti
        var txt="";
        var all="";
        $('input[id^="c2r_reparto_set_"]').each(function() {
            var v=$(this).val();
            var id=$(this).data('id');
            var area=$(this).data('area');

            if (v=='1' && area!='X') txt+="'"+id+"',";
            if (area!='X') all+="'"+id+"',";
        });

        this.param.reparti=txt;
        this.param.allReparti=all;

        //verifica marche
        /*var txt="";
        $('input[id^="c2r_marca_set_"]').each(function() {
            var v=$(this).val();

            if ($(this).prop('checked')==true) txt+="'"+v+"',";
        });

        this.param.marche=txt;
        */

        this.param.inizio=$('#c2r_periodo_opzioni_inizio').val();
        this.param.fine=$('#c2r_periodo_opzioni_fine').val();

        //controllo
        var chk=true;

        /*if (this.param.reparti=="" || this.param.marche=="") {
            $('#c2r_navigator_head').css("border-color","red");
            chk=false;
        }*/

        if (this.param.reparti=="") {
            $('#c2r_navigator_head').css("border-color","red");
            chk=false;
        }

        if (!this.param.inizio || !this.param.fine || this.param.fine<this.param.inizio) {
            $('#c2r_navigator_range').css("border-color","red");
            chk=false;
        }

        if (!chk) {
            $('#c2r_navigator_error').html('errore');
        }

        //alert(JSON.stringify(this.param));

        return chk;
    }

    this.start=function(tipo) {

        if (!this.check()) return;

        //verifica condizioni per BUDGET
        if (tipo=="budget_S") {
            temp2=$.parseJSON(window._nebulaMain.base64ToUtf8(this.link));
            let i = this.param.inizio.replace(/-/g, '');
            let f = this.param.fine.replace(/-/g, '');
            let txt="";

            if (this.param.reparti!=temp2.reparti) {
                txt+='<div style="font-weight:bold;">I reparti di Produttività non corrispondono all\'analisi richiesta.</div>';
            }
            if (i!=temp2.inizio) {
                txt+='<div style="font-weight:bold;">La data di inizio di Produttività non corrisponde a quella dell\'analisi richiesta.</div>';
            }
            if (f!=temp2.fine) {
                txt+='<div style="font-weight:bold;">La data di fine di Produttività non corrisponde a quella dell\'analisi richiesta.</div>';
            }

            if (txt!="") {
                $('#c2r_main_'+tipo).html(txt);
                return;
            }
        }

        ////////////////////////////////////////////

        var url="";

        if (tipo=="fatturato_S") {
            url='http://'+location.host+'/nebula/apps/c2r/core/fatturato_S.php';
            this.c2rTableLabel=true;
            $('#c2rTableLabel').prop('checked',true);
            this.param.default=$('#c2rDefault').data('default');
        }
        else if (tipo=="prod_S") {
            url='http://'+location.host+'/nebula/apps/c2r/core/produttivita_S.php';
            this.param.prodTipo=$('#c2rProdTipo').val();
            this.param.default=$('#c2rDefault').data('default');
        }
        else if (tipo=="budget_S") {
            url='http://'+location.host+'/nebula/apps/c2r/core/budget_S.php';
            this.param.default=$('#c2rDefault').data('default');
            this.param.prodLink=$('#c2r_prod_budget_link').val();
        }
        else if (tipo=="giac_M") {
            url='http://'+location.host+'/nebula/apps/c2r/core/giacenza_M.php';
            this.param.obso=$('#c2rMagObso').val();
            this.param.default=$('#c2rDefault').data('default');
        }

        if (url=='') return;

        /*$('#c2r_modulo_data_'+tipo).html(window._nebulaMain.data_form_to_ita(this.param.inizio)+' - '+window._nebulaMain.data_form_to_ita(this.param.fine));

        var temp=this.param.reparti.slice(0,-1).split(',');
        var txt="";
        for (var x in temp) {
            txt+=temp[x]+' ';
        }
        $('#c2r_modulo_reparti_'+tipo).html(txt);
        */

        $('#c2r_main_'+tipo).html('<div style="text-align:center;margin-top:20px;width:100%;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

        var id=tipo;

        $.ajax({
            "url": url,
            "async": true,
            "cache": false,
            "data": { "param": this.param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#c2r_main_'+id).html(ret);
                window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].ambiti[id]=true;
                $('#c2r_reload_'+id).css('visibility','visible');
                $('#c2r_del_'+id).css('visibility','visible');
                //window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].c2rMainToggle(id);
            }
        });
    }

    this.c2rDelete=function(id) {
        this.ambiti[id]=false;
        $('#c2r_main_'+id).html("");
        $('#c2r_reload_'+id).css('visibility','hidden');
        $('#c2r_del_'+id).css('visibility','hidden');
    }

    this.c2rReload=function(id) {
        this.ambiti[id]=false;
        this.c2rMainToggle(id);
    }

    this.c2rMainToggle=function(id) {

        if(!window._nebulaMain.checkTime()) return;

        $('div[id^="c2r_main_"]').hide();
        $('#c2r_main_'+id).show();

        if (id=='budget_S') {
            this.link=$('#c2r_prod_budget_link').val();
            if (!this.link) {
                $('#c2r_main_'+id).html('<div style="font-weight:bold;">Produttività non calcolata.</div>');
                return;
            }
        }

        if (!this.ambiti[id]) this.start(id);

    }

    this.c2r_totToggle=function() {

        this.c2rTableLabel=$('#c2rTableLabel').prop('checked');

        if (this.c2rTableLabel) {
            $('.c2rTableLabel').show();
        }
        else {
            $('.c2rTableLabel').hide();
        }
    }

    this.c2r_prodTipoToggle=function() {

        var id=$('#c2rProdTipo').val();

        $('div[id^="c2r_resoconto"]').hide();
        $('div[id^="c2r_resoconto_'+id+'"]').show();
    }

    this.c2rm_deltaTrToggle=function() {

        var s=$('#c2rm_deltaHidden').val();

        if (s==1) {
            $('.c2rm_deltaTr').hide();
            $('#c2rm_deltaHidden').val(0);
        }
        if (s==0) {
            $('.c2rm_deltaTr').show();
            $('#c2rm_deltaHidden').val(1);
        }

    }

}