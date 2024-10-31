class nebulaSystem {

    contesto={
        funzione:""
    };

    //definisce se è possibile eseguire il ribbon
    lock=false;

    //definisce gli argomenti da passare quando si chiama una funzione
    args={};
    //se apro il sistema (o arrivo da un altro sistema/galassia) non esiste ancora il ribbon
    ribbonArgs={};

    setArgs(arr) {
        this.args=arr;
    }

    setRibbonArgs(arr) {
        this.ribbonArgs=arr;
    }

    addArg(arr) {
        for (var x in arr) {
            this.args[x]=arr[x];
        }
    }

    getTagFunzione() {
        return this.contesto.funzione;
    }

    setFunction(func,refr) {

        //se è inizializzato l'oggetto ODL chiudilo attraverso la funzione del contesto in cui si è (per il momento  ISLA AVALON)
        if (typeof window._nebulaOdl !== 'undefined') {
            window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeOdl();
        }

        //alert(func+' '+refr);

        //verifica il tempo di inattività dall'ultima operazione
        if (!window._nebulaMain.checkTime()) return;

        var funzione=func;

        var param={
            "nebulaContesto":window._nebulaMain.contesto,
            "funzione":funzione,
            "args":this.args
        };

        //se apro il sistema (o arrivo da un altro sistema/galassia) non esiste ancora il ribbon
        try {
            param.ribbon=window['_js_chk_'+this.contesto.funzione].evaluateAnyway();
            //console.log(JSON.stringify(param.ribbon));
        }
        catch(err) {
            console.log(err);
            param.ribbon=this.ribbonArgs;
        }

        //12.02.2021 eliminato efficacia clausola "refr"
        //if (!refr) this.showFunction(funzione);
        //else {
            $('div[id^="nebulaFuncion_"]').html("");
            //$('#nebulaFuncion_'+funzione).html("");
            this.showFunction(funzione);
            window._nebulaMain.showBusy();
            this.lock=false;
        //}

        window._tempFuncName=funzione;

        $.ajax({
            "url": "http://"+location.host+"/nebula/main/function_link.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._nebulaApp.contesto.funzione=window._tempFuncName;
                $('#nebulaFuncion_'+window._tempFuncName).html(ret);
                window._nebulaMain.hideBusy();
            }
        });
    }

    showFunction(funzione) {
        //alert(funzione);
        $('div[id^="nebulaFuncion_"]').hide();
        $('.nebulaFunctionMenuTD').css('display','revert');

        $('#nebulaFuncion_'+funzione).show();
        $('#nebulaFunctionMenuTD_'+funzione).css('display','none');
    }

    ////////////////////////////////////////////////////////////////////

    collectParams() {

        var func=this.contesto.funzione;

        var param={
            "contesto":window._nebulaMain.contesto,
            "ribbon":window['_js_chk_'+func].scrivi(),
            "args":this.args,
            "nebulaFunzione":{'nome':func}
        };

        return param;
    }

    ribbonExecute() {

        //alert(this.lock);

        //verifica il tempo di inattività dall'ultima operazione
        if (!window._nebulaMain.checkTime()) return;

        if (this.lock) return;

        var func=this.contesto.funzione;
        var url="";

        //alert(window['_js_chk_'+func].js_chk());

        //se il form del ribbon è ok
        if ( window['_js_chk_'+func].js_chk()==0 ) {

            var galassia=window._nebulaMain.getMainapp();

            //alert(galassia+' '+func);

            //viene scritta dal JS proprio della galassia
            url=this.customExecute(galassia,func);

            if (url && url!="") {

                this.lock=true;

                var param=this.collectParams();

                /////////////////////////////////////
                //USARE la proprietà this.args ed i metodi sthis.etArgs() e this.addArg()
                /*
                try {
                    var p2=this.customParam();
                    for (var x in p2) {
                        param[x]=p2[x];
                    }
                }
                catch(err) {
                    console.log(err);
                }
                */
                /////////////////////////////////////

                $('#nebulaFunctionBody_'+func).html("");
                $('#nebulaFunctionBody_'+func).hide();
                $('#nebulaFunctionBody_loader_'+func).show();
                window._nebulaMain.showBusy();

                //console.log(JSON.stringify(param));

                $.ajax({
                    "url": url,
                    "async": true,
                    "cache": false,
                    "data": { "param": param },
                    "type": "POST",
                    "success": function(ret) {

                        window._nebulaApp.lock=false;
                        
                        $('#nebulaFunctionBody_'+func).html(ret);   
                        setTimeout(window._nebulaApp.reshow,100);    
                    }
                });

            }

        }
        else console.log('errore ribbon form');
    }

    reshow() {
        //serve per evitare glimpse grafici
        //essendo chiamata da setTimeout perde il riferimento THIS
        $('#nebulaFunctionBody_loader_'+window._nebulaApp.contesto.funzione).hide();
        $('#nebulaFunctionBody_'+window._nebulaApp.contesto.funzione).show();

        window._nebulaApp.args={};

        window._nebulaMain.hideBusy();
    }

    //chiama una funzione di  window["_nebula_app_"+this.contesto.funzione]
    //è un'operazione complicata, dovrebbe essere superata chiamando
    //window._nebulaApp.getTagFunzione() per comporre "window._nebulaApp_funzione"
    callOwnFunction(func,arg) {
        //arg è UNO SOLO
        //è un problema passare un JSON dovendolo scrivere in un ECHO
        return window["_nebulaApp_"+this.contesto.funzione][func](arg);
    }
    
    //serve per indirizzare l'utente ad una specifica funzione di uno specifico galassia:sistema:funzione
    linkFunk(link) {

        var temp=link.split(':');

        window._nebulaMain.linkFunction(temp[0],temp[1],temp[2]);
    }

}