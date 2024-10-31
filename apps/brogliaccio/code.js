
function brogliaccioCode(funzione) {

    this.funzione=funzione;

    //sono i totali [reparto][Idcoll][tag].array()  
    this.totali={};

    this.bgcLoadTotali=function(arr,IDcoll,reparto) {

        if (!this.totali.hasOwnProperty(reparto)) this.totali[reparto]={};
        if (!this.totali[reparto].hasOwnProperty(IDcoll)) this.totali[reparto][IDcoll]=arr;
    }

    this.bgcChangeRep=function(reparto) {

        if (reparto=="") return;

        if (reparto=='tutti') {

            //#########################################
            //scrittura del campo UNCOMMON "bgc_tutti"
            $('#ribbon_bgc_tutti').val('1');
            //#########################################

            $('#ribbon_bgc_reparto').val('');

        }
        else {
            $('#ribbon_bgc_reparto').val(reparto);
            $('#ribbon_bgc_tutti').val('0');
        }

        window._nebulaApp.ribbonExecute();
    }

    this.bgcOpenCodici=function(tag,IDcoll,reparto) {

        this.bgcResetCodici();

        var index='<img style="width:100%;height:100%;margin-top:-30%;" src="http://'+location.host+'/nebula/apps/brogliaccio/img/index.png" />';
        
        $('#bgc_codici_index_'+tag+'_'+IDcoll+'_'+reparto).html(index);

        //////////////////////////////////////
        //creazione del FORM per modificare i codici
        var delta=( $('#bgc_codici_'+tag+'_'+IDcoll+'_'+reparto).data('bgc')-$('#bgc_codici_'+tag+'_'+IDcoll+'_'+reparto).data('std') );

        var verso=(delta>0)?'P':'M';

        /*{"delta":-480,"somma":0,"tot": {
            "timbrato":{"titolo":"timbrato","codice":"TIM","valore":0,"op":"P","edit":0,"verso":""},
            "malattia":{"titolo":"malat","codice":"ML","valore":0,"op":"P","edit":1,"verso":"M"},
            "permesso":{"titolo":"perm","codice":"P","valore":0,"op":"P","edit":1,"verso":"M"},
            "ferie":{"titolo":"ferie","codice":"F","valore":0,"op":"P","edit":1,"verso":"M"},
            "cento4":{"titolo":"104","codice":"HT","valore":0,"op":"P","edit":1,"verso":"M"},
            "maternita":{"titolo":"mater","codice":"MT","valore":0,"op":"P","edit":1,"verso":"M"},
            "riposo":{"titolo":"riposo","codice":"R","valore":0,"op":"P","edit":1,"verso":"M"},
            "strord":{"titolo":"str ord","codice":"SO","valore":0,"op":"","edit":1,"verso":"P"},
            "strsab":{"titolo":"str sab","codice":"SS","valore":0,"op":"","edit":1,"verso":"P"},
            "strdom":{"titolo":"str dom","codice":"SD","valore":0,"op":"","edit":1,"verso":"P"},
            "lavorato":{"titolo":"lavorato","codice":"LAV","valore":0,"op":"","edit":0,"verso":""}}}
        */
        
        var txt=this.drawBlock('delta','_delta_',Math.abs(delta),null);

        for (var x in this.totali[reparto][IDcoll][tag].tot) {

            //T funziona in entrambi i versi
            if (this.totali[reparto][IDcoll][tag].tot[x].verso!=verso && this.totali[reparto][IDcoll][tag].tot[x].verso!='T') {
                if (this.totali[reparto][IDcoll][tag].tot[x].verso!="") {
                    this.totali[reparto][IDcoll][tag].tot[x].valore=0;
                }
                continue;
            }

            txt+=this.drawBlock(this.totali[reparto][IDcoll][tag].tot[x].titolo,this.totali[reparto][IDcoll][tag].tot[x].codice,this.totali[reparto][IDcoll][tag].tot[x].valore,this.totali[reparto][IDcoll][tag].tot[x]);
        }

        txt+='<input id="bgc_codici_param" type="hidden" data-tag="'+tag+'" data-idcoll="'+IDcoll+'" data-reparto="'+reparto+'" />';

        //////////////////////////////////////

        $('#bgc_codici_footmain_'+IDcoll+'_'+reparto).html(txt);

        $('#bgc_codici_foot_'+IDcoll+'_'+reparto).show();

        setTimeout(this.bgcCheckCodici,100);
    }

    this.bgcResetCodici=function() {

        $('div[id^="bgc_codici_index_"]').html('');
        $('div[id^="bgc_codici_footmain_"]').html('');
        $('div[id^="bgc_codici_foot_"]').hide();
    }

    this.bgcCodiciShortSelection=function(index) {

        var delta=$('#bgc_codici_form__delta_').val();

        $('input[id^="bgc_codici_form_"]').each(function(){

            if ($(this).data('index')!='_delta_') {

                if ($(this).data('index')==index) $(this).val(delta);
                else $(this).val(0);
            }

        });

        this.bgcCheckCodici();
    } 

    this.bgcCheckCodici=function() {

        var delta=$('#bgc_codici_form__delta_').val();
        var somma=0;

        var param=$('#bgc_codici_param');
        var tag=$(param).data('tag');
        var IDcoll=$(param).data('idcoll');
        var reparto=$(param).data('reparto');

        var obj=window["_nebulaApp_"+window._nebulaApp.getTagFunzione()];

        $('input[id^="bgc_codici_form_"]').each(function(){

            if ($(this).data('index')!='_delta_') {

                if (obj.totali[reparto][IDcoll][tag].tot[$(this).data('index')].verso!='') {

                    var v=parseInt($(this).val());

                    if (isNaN(v)) {
                        v=0;
                        $(this).val(0);
                    }

                    somma+=v;

                    obj.totali[reparto][IDcoll][tag].tot[$(this).data('index')].valore=v;
                }
            }
        });

        obj.totali[reparto][IDcoll][tag].somma=somma;

        if (delta==somma) {
            $('#bgc_codici_ok').data('check',1);
            $('#bgc_codici_ok').css("background-color",'#00ff08');
            //alert($('#bgc_codici_ok').css('margin-left'));
            return true;
        }
        else{
            $('#bgc_codici_ok').data('check',0);
            $('#bgc_codici_ok').css("background-color",'red');
            return false;
        }

    }

    this.bgcConfirmCodici=function() {

        var p=$('#bgc_codici_param');

        var param={
            "tag":$(p).data('tag'),
            "coll":$(p).data('idcoll'),
            "reparto":$(p).data('reparto')
        }

        if (!this.bgcCheckCodici() ) {
                
            //var src="http://"+location.host+"/nebula/apps/brogliaccio/img/X.png";
            //$('#bgc_codici_img_'+param.tag+'_'+param.IDcoll+'_'+param.reparto).attr('src',src);
            return;
        }

        param.obj={};

        for (var x in this.totali[param.reparto][param.coll][param.tag].tot) {
            if (this.totali[param.reparto][param.coll][param.tag].tot[x].edit==1) {
                param.obj[x]=this.totali[param.reparto][param.coll][param.tag].tot[x].valore;
            }
        }

        //console.log(JSON.stringify(param));

        window._bgc_temp_param=param;
        //window._bgc_temp_param.caller=this;

        //se i dati inseriti non vanno bene non arriviamo mai fino a qui

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/brogliaccio/core/aggiorna_dettaglio.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                var res=$.parseJSON(ret);

                if (res.result=='OK') {
                    var src="http://"+location.host+"/nebula/apps/brogliaccio/img/V.png";
                    $('#bgc_codici_img_'+window._bgc_temp_param.tag+'_'+window._bgc_temp_param.coll+'_'+window._bgc_temp_param.reparto).attr('src',src);
                    window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].bgcAggiornaTotali();
                }
                else {
                    alert('errore DB');
                }

            }
        });

    }

    this.bgcAggiornaTotali=function() {
        //quando viene chiamato questo metodo è già stato inizializzato l'oggetto  "_bgc_temp_param"

        var caller=window["_nebulaApp_"+window._nebulaApp.getTagFunzione()];

        var temp={};

        //inizializza l'array dei totali
        $('td[id^="bgc_totaliOverall_'+window._bgc_temp_param.coll+'_'+window._bgc_temp_param.reparto+'"]').each(function(){
            temp[$(this).data('codice')]=0;
        });

        //$('div[id^="bgc_codici_"][data-reparto="'+window._bgc_temp_param.reparto+'"][data-idcoll="'+window._bgc_temp_param.coll+'"]').each(function(){

            //var tag=$(this).data('tag');

            //temp['TIM']+=parseInt($(this).data('bgc'));
            //temp['LAV']+=parseInt($(this).data('bgc'));

            for (var tag in caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll]) {
                for (var k in caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot) {

                    console.log(tag+' '+k+' '+JSON.stringify(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll]) );

                    //###########################################
                    temp[k]+=parseInt(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].valore);
                
                    if (caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].verso=='M') {
                        temp['LAV']+=parseInt(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].valore);
                    }
                    else if (caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].verso=='P' && k=='XXX') {
                        temp['LAV']-=parseInt(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].valore);
                        //temp['TIM']-=parseInt(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].valore);
                    }
                    //se il verso è P o T
                    /*else if (caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].verso=='P' || caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].verso=='T') {
                        temp['TIM']+=parseInt(caller.totali[window._bgc_temp_param.reparto][window._bgc_temp_param.coll][tag].tot[k].valore);
                    }*/
                    //###########################################
                }
            }
        //});

        //console.log(JSON.stringify(temp));

        for (var x in temp) {
            var v=temp[x]/60;
            $('#bgc_totaliOverall_'+window._bgc_temp_param.coll+'_'+window._bgc_temp_param.reparto+'_'+x).html(v.toFixed(2));
        }

        this.bgcResetCodici();
    }

    this.drawBlock=function(titolo,index,valore,obj) {

        var txt='<div style="display:inline-block;margin-left:10px;height:40px;border:1px solid black;vertical-align:top;font-size:0.7em;text-align:center;position:relative;top:4px;';
            if (index=='_delta_') txt+='background-color:beige;';
        txt+='">';

            txt+='<div style="font-weight:bold;width:100%;height:30%;" >';
                txt+='<div style="position:relative;top:2px;" ';
                    if (index!='_delta_') txt+='onclick="window._nebulaApp_'+this.funzione+'.bgcCodiciShortSelection(\''+index+'\');" '; 
                txt+='>'+titolo+'</div>';
            txt+='</div>';

            txt+='<div style="width:100%;height:70%;padding:3px;box-sizing:border-box;" >';

                if (obj != null) {
                    txt+='<span style="margin-right:5px;font-weight:bold;">'+obj.codice+'</span>';
                }

                txt+='<input id="bgc_codici_form_'+index+'" type="text" maxlength="4" style="width:50px;text-align:center;" value="'+valore+'" data-index="'+index+'" ';

                    if (obj==null || obj.edit==0) txt+='disabled';

                txt+=' onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].bgcCheckCodici();" />';

            txt+='</div>';

        txt+='</div>';

        return txt;

    }

    this.bgcEsporta=function() {

        var param=window._nebulaApp.collectParams();

        window._nebulaMain.showBusy();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/brogliaccio/core/esporta.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);

                var res=$.parseJSON(ret);

                var a = document.createElement('a');
                if (window.URL && window.Blob && ('download' in a) && window.atob) {
                    // Do it the HTML5 compliant way
                    var blob = window._nebulaMain.base64ToBlob(res.data, res.mimetype);
                    var url = window.URL.createObjectURL(blob);
                    a.href = url;
                    a.download = res.filename;
                    a.click();
                    window.URL.revokeObjectURL(url);
                }

                window._nebulaMain.hideBusy();
            }
        });

    }

}
