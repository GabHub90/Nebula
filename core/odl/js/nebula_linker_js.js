function nebulaOdlLinker(param) {

    this.param=param;

    //console.log(JSON.stringify(this.param));

    //header dell'odl
    this.main={
        "veicolo":"",
        "abbinamento":"",
        "util":"",
        "intest":"",
        "locat":"",
        "fatt":"",
        "km":"",
        "dms":this.param.dms,
        "ambito":'avalon'
    }

    //usato in fase di modifica prima della conferma
    this.tlink={
        "veicolo":"",
        "abbinamento":"",
        "util":"",
        "intest":"",
        "locat":"",
        "fatt":"",
        "dms":""
    }

    this.divs={
        'head':'',
        'body':'',
        'info':'',
        'abbinamenti':''
    }

    this.abbinamenti={};

    //identifica lo stato del bottone di chiusura in alto a destra
    this.infoButton="";

    this.setMain=function(arr) {

        for (var x in this.main) {
            if (arr[x]) {
                this.main[x]=arr[x];
            }
        }
    }

    //###########################################
    //INIT
    if (this.param.linker) {
        this.setMain(this.param.linker);
    }
    //###########################################

    this.setTlink=function() {
        this.tlink=JSON.parse(JSON.stringify(this.main));
    }

    this.setVeicolo=function(rif) {

        this.tlink.veicolo=rif;

        this.updateAbbinamento();
    }

    this.setAnagra=function(rif) {

        var arr=["util","intest","locat","fatt"];

        //individua tipi anagrafica
        var ana=window._odlAna_divo.getSel();

        this.tlink[arr[ana]]=rif;

        this.updateAbbinamento();
    }

    this.delVeicolo=function() {

        this.tlink.veicolo="";

        this.updateAbbinamento();
    }

    this.delAnagra=function() {

        var arr=["util","intest","locat","fatt"];

        //individua tipi anagrafica
        var ana=window._odlAna_divo.getSel();

        this.tlink[arr[ana]]="";

        this.updateAbbinamento();
    }

    this.setAbbinamentoByVeicolo=function(obj) {

        var base=$(obj).data('info');
        var info=$.parseJSON(atob(base));

        if (!info) return;

        if (info.hasOwnProperty('progressivo') && info.progressivo!='999') this.tlink['abbinamento']=info.progressivo;
        else return;

        if (info.hasOwnProperty('rif')) this.tlink['veicolo']=info.rif;
        else this.tlink['veicolo']='';

        if (info.hasOwnProperty('cod_anagra_util')) this.tlink['util']=info.cod_anagra_util;
        else this.tlink['util']='';

        if (info.hasOwnProperty('cod_anagra_intest')) this.tlink['intest']=info.cod_anagra_intest;
        else this.tlink['intest']='';

        if (info.hasOwnProperty('cod_anagra_locat')) this.tlink['locat']=info.cod_anagra_locat;
        else this.tlink['locat']='';

        //alert(this.main.dms);

        this.tlink.dms=this.main.dms;

        //alert(JSON.stringify(this.tlink));

        this.updateAbbinamento();

    }

    this.setAbbinamentoByLink=function(obj) {

        var base=$(obj).data('info');
        var info=$.parseJSON(atob(base));

        if (!info) return;

        //alert(JSON.stringify(info));

        if (info.hasOwnProperty('progressivo')) this.tlink['abbinamento']=info.progressivo;
        else return;

        if (info.hasOwnProperty('veicolo')) this.tlink['veicolo']=info.veicolo;
        else this.tlink['veicolo']='';

        if (info.hasOwnProperty('cod_anagra_util')) this.tlink['util']=info.cod_anagra_util;
        else this.tlink['util']='';

        if (info.hasOwnProperty('cod_anagra_intest')) this.tlink['intest']=info.cod_anagra_intest;
        else this.tlink['intest']='';

        if (info.hasOwnProperty('cod_anagra_locat')) this.tlink['locat']=info.cod_anagra_locat;
        else this.tlink['locat']='';

        this.tlink.dms=this.main.dms;

        //alert(JSON.stringify(this.tlink));

        this.updateAbbinamento();
    }

    this.updateAbbinamento=function() {

        var divID=this.divs.head;

        //alert(JSON.stringify(this.tlink));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/linker/draw_head_edit.php',
            "async": true,
            "cache": false,
            "data": {"param": {"linker":this.tlink} },
            "type": "POST",
            "success": function(ret) {

                $('#'+divID).html(ret);

                window._nebulaOdl.linker.setAbbinamenti();
                window._nebulaOdl.linker.drawAbbinamenti();
                
            }
        });
    }

    this.setAbbinamenti=function() {

        var base=$('#odielleLinkerBase').data('info');

        this.abbinamenti=$.parseJSON(atob(base));

    }

    this.drawAbbinamenti=function() {

        if (!this.abbinamenti) return;

        this.infoButton="";

        var divID=this.divs.abbinamenti;

        var txt='<div style="width:100%;height:100%;padding:2px;box-sizing:border-box;overflow:scroll;overflow-x:hidden;" >';

            /*if (this.abbinamenti.hasOwnProperty('0')) {
                txt+=this.addHtmlAbbinamento(this.abbinamenti['0']);
            }*/

            for (var x in this.abbinamenti) {

                //if (x=='0') continue;

                txt+=this.addHtmlAbbinamento(this.abbinamenti[x]);

            }

        txt+='</div>';

        $('#'+divID).html(txt);

        window._nebulaOdl.linker.drawInfoHead();
    }

    this.addHtmlAbbinamento=function(a) {

        var color=this.checkAbbinamento(a);

        txt='<div style="position:relative;border:1px solid black;margin-top:2px;margin-bottom:2px;width:90%;padding:2px;box-sizing:border-box;text-align:left;background-color:'+color+';cursor:pointer;" data-info="'+btoa(unescape(encodeURIComponent(JSON.stringify(a))))+'" onclick="window._nebulaOdl.linker.setAbbinamentoByLink(this);" >';

            txt+='<div style="position:relative;">';
                txt+='<div style="position:relative;display:inline-block;width:8%;font-weight:bold;" >('+a.progressivo+')</div>';
                txt+='<div style="position:relative;display:inline-block;width:27%;font-weight:bold;" >'+a.targa+'</div>';
                txt+='<div style="position:relative;display:inline-block;width:65%;font-size:0.8em;" >'+a.des_veicolo.substr(0,25)+'</div>';
            txt+='</div>';

            //if (a.cod_anagra_util!="") {
                txt+='<div style="position:relative;">';
                    txt+='<div style="position:relative;display:inline-block;width:17%;font-size:0.8em;" >Util:</div>';
                    if (a.des_util) {
                        txt+='<div style="position:relative;display:inline-block;width:83%;" >'+a.des_util.substr(0,26)+'</div>';
                    }
                txt+='</div>';
            //}

            if (a.cod_anagra_intest!="") {
                txt+='<div style="position:relative;">';
                    txt+='<div style="position:relative;display:inline-block;width:17%;font-size:0.8em;" >Intest:</div>';
                    txt+='<div style="position:relative;display:inline-block;width:83%;" >'+a.des_intest.substr(0,26)+'</div>';
                txt+='</div>';
            }

            if (a.cod_anagra_locat!="") {
                txt+='<div style="position:relative;">';
                    txt+='<div style="position:relative;display:inline-block;width:17%;font-size:0.8em;" >Locat:</div>';
                    txt+='<div style="position:relative;display:inline-block;width:83%;" >'+a.des_locat.substr(0,30)+'</div>';
                txt+='</div>';
            }

        txt+='</div>';

        return txt;
    }

    this.checkAbbinamento=function(a) {

        if (a.veicolo!="") {

            if (a.veicolo==this.tlink.veicolo) {

                //alert(JSON.stringify(this.tlink));
                //alert(JSON.stringify(a));

                //match corretto
                if (this.tlink.util!="" && a.cod_anagra_util==this.tlink.util && a.cod_anagra_intest==this.tlink.intest && a.cod_anagra_locat==this.tlink.locat) {
                    this.tlink.abbinamento=a.progressivo;
                    this.infoButton='conferma';
                    return '#bdf5bd';
                }

                //se i nomi in tlink combaciano ma non abbracciano l'abbinamento completamente
                else if ( (this.tlink.util!="" && a.cod_anagra_util==this.tlink.util) || (this.tlink.intest!="" && a.cod_anagra_intest==this.tlink.intest) || (this.tlink.locat!="" && a.cod_anagra_locat==this.tlink.locat) ) {
                    if (this.infoButton!='conferma') {
                        this.tlink.abbinamento="";
                    }
                    return '#f5f5db';
                }

                else if (this.tlink.util!="") {
                    if (this.infoButton!='conferma') {
                        this.tlink.abbinamento="";
                        this.infoButton='nuovo';
                    }
                    return 'transparent';
                }
            }

        }

        return 'transparent';

    }

    this.drawHead=function(id) {

        //alert(JSON.stringify(this.main));

        divID=id;
        
        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/linker/draw_head.php',
            "async": true,
            "cache": false,
            "data": {"param": {"linker":this.main} },
            "type": "POST",
            "success": function(ret) {

                $('#'+divID).html(ret);
                
            }
        });
    }

    this.drawHeadEdit=function(id,body,info,abbinamenti) {

        //alert(JSON.stringify(this.param));

        divID=id;
        this.divs.head=id;
        //divBody=body;
        this.divs.body=body;
        //divInfo=info;
        this.divs.info=info;
        this.divs.abbinamenti=abbinamenti;
        
        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/linker/draw_head_edit.php',
            "async": true,
            "cache": false,
            "data": {"param": {"linker":this.tlink} },
            "type": "POST",
            "success": function(ret) {

                $('#'+divID).html(ret);

                window._nebulaOdl.linker.drawBody();
                
            }
        });
    }

    this.drawBody=function() {

        var divID=this.divs.body;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/linker/draw_body.php',
            "async": true,
            "cache": false,
            "data": {"param": {"linker":this.main} },
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#'+divID).html(ret);

                window._nebulaOdl.linker.setAbbinamenti();
                window._nebulaOdl.linker.drawAbbinamenti();
                
            }
        });

    }

    this.drawInfoHead=function() {

        var divID=this.divs.info;

        var txt='<div style="text-align:center;font-weight:bold;">Abbinamento Veicolo/Anagrafica</div>';

        //#############################
        //bottone di conferma e logiche di attivazione
        txt+='<div style="margin-top:15px;text-align:center;" >';

            if (this.infoButton!="") {

                if (this.infoButton=='conferma') txt+='<button>Conferma</button>';
                if (this.infoButton=='nuovo') txt+='<button>Nuovo</button>';
            }

        txt+='</div>';
        //#############################
        
        $('#'+divID).html(txt);
    }

    //////////////////////////////////////////////////////////////////////

    this.cercaVeicolo=function() {

        var param={'operatore':'AND'};
        var chk="";

        $('input[id^="odielleSearchVeicolo"]').each(function() {

            var temp=$(this).val().trim();

            if (temp!="") {

                param[$(this).data('tipo')]=temp;
                chk=temp;
            }

        });

        if (!chk || chk=="") {
            alert("dati non corretti");
            return;
        }

        param.dms=this.main.dms;

        if (param.contratto && param.contratto!="") {
            //ricerca tramite contratto
        }

        else {

            $('#linkerListaVeicoli').html(window._nebulaOdl.waiter());
            window._odlSearchVei_divo.selTab(0);

            $.ajax({
                "url": 'http://'+location.host+'/nebula/core/veicolo/cerca_linker.php',
                "async": true,
                "cache": false,
                "data": {"param": param},
                "type": "POST",
                "success": function(ret) {

                    var temp=$.parseJSON(ret);

                    if (temp) {
                        var txt=(temp.records>15)?'<div style="color:red;font-weight:bold;text-align:center;">Ci sono più di 15 record</div>':"";
                        $('#linkerListaVeicoli').html(txt+temp.html);
                    }
                    
                }
            });
        }

    }

    this.cercaAnagrafica=function() {

        var param={};
        var chk="";

        $('input[id^="odielleSearchAnagra"]').each(function() {

            var temp=$(this).val().trim();

            if (temp!="") {

                param[$(this).data('tipo')]=temp;
                chk=temp;
            }

        });

        if (!chk || chk=="") {
            alert("dati non corretti");
            return;
        }

        param.dms=this.main.dms;

        $('#linkerListaAnagrafiche').html(window._nebulaOdl.waiter());
        window._odlSearchAna_divo.selTab(0);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/anagrafica/cerca_linker.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var temp=$.parseJSON(ret);

                if (temp) {
                    var txt=(temp.records>15)?'<div style="color:red;font-weight:bold;text-align:center;">Ci sono più di 25 record</div>':"";

                    //$('#linkerListaAnagrafiche').html(txt+JSON.stringify(temp.html));
                    $('#linkerListaAnagrafiche').html(txt+temp.html);
                }
                
            }
        });

    }




    
}