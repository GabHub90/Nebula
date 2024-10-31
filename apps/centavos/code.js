
function centavosCode(funzione) {

    this.funzione=funzione;

    this.linkInfo={
        "varianti":{},
        "varobj":{},
        "periodi":{},
        "firstPeriodo":"",
        "lastPeriodo":"",
        "modifiche":false,
        "next":0,
        "piano":0,
        "ID_coll":0
    };
    //oggetto usato per valutare la correttezza della sequenza dei link del collaboratore
    this.linkObj={};

    this.analisiInfo={
        "periodi":{},
        "actual":false
    };

    this.varDivo={};

    this.loadLink=function(obj) {

        var i=0;

        for (var x in obj) {
            this.linkObj[i]=obj[x];
            i++;
        }
    }

    this.loadLinkInfo=function(obj) {
        for (var x in this.linkInfo) {
            if (x in obj) {
                this.linkInfo[x]=obj[x];
            }
        }
    }

    this.loadAnalisi=function(obj) {

        for (var x in obj) {
            this.analisiInfo.periodi[x]=obj[x];
            if (obj[x].stato=='actual') this.analisiInfo.actual=true;
        }
    }

    this.loadVarDivo=function(obj) {
        this.varDivo=obj;
    }

    this.ctvChangeRep=function(reparto) {

        $('#ribbon_ctv_panorama').val('');
        $('#ribbon_ctv_variante').val('');
        $('#ribbon_ctv_sezione').val('');
        $('#ribbon_ctv_linkoll').val('');

        if (reparto=="") return;

        window._nebulaApp.ribbonExecute();
    }

    this.ctvNavigator=function(index) {

        $('#ribbon_ctv_openType').val(index);
        //window._nebulaApp.ribbonExecute(this.funzione);

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.ctvChangePanorama=function(id) {
        $('#ribbon_ctv_panorama').val(id);
        $('#ribbon_ctv_variante').val('');
        $('#ribbon_ctv_sezione').val('');
        $('#ribbon_ctv_linkoll').val('');

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.ctvChangeVariante=function(id) {
        $('#ribbon_ctv_variante').val(id);
        $('#ribbon_ctv_sezione').val('');

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    //viene chiamata da window._nebulaApp.callOwnFunction
    this.ctvChangeSezione=function(id) {

        if (!id || id=="") return;

        $('#ribbon_ctv_sezione').val(id);

        var args={};
        window._nebulaApp.setArgs(args);

        window._nebulaApp.ribbonExecute();
    }

    this.ctvSave=function() {

        //alert(JSON.stringify(form));

        var form=window._ctv_ckMulti.salva();

        if (!form) return;

        var param={
            "form":form,
            "ID":$('#ribbon_ctv_sezione').val()
        }

        //non chiedetemi perchè ma se GRADI, che è una stringa che rappresenta un json è il primo elemento dell'array
        //expo diventa un array vuoto []
        if (param.form.ctv_FSG) {
            var temp=param.form.ctv_FSG.expo.gradi;
            param.form.ctv_FSG.expo={
                "cazzo":"cazzo",
                "gradi":temp
            }
        }
        //alert(param.form.ctv_FSG.expo.gradi);
        //param.form.ctv_FSG.expo.gradi='"'+param.form.ctv_FSG.expo.gradi+'"';
        //console.log(JSON.stringify(param));

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/update.php",
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

    this.ctvValuta=function() {

        if (!window._ctv_ckMulti.salva()) return;

        var form={};

        $('input[id^="ctv_valuta"]').each(function(){
            var id=$(this).data("id");
            var v=$(this).val();

            if (v=="") v=0;

            //se il record in FORM esiste già scrivi nel campo il valore esistente
            //significa che ci sono più parametri che si riferisconio alla stessa sorgente
            if (form.hasOwnProperty(id)) {
                $(this).val(form[id]);
            }
            else {
                form[$('select[js_chk_ctm_'+id+'_tipo="sorgente"]').val()]=v;
            }
        });

        //alert(JSON.stringify(form));
        //alert(Object.keys(form).length);

        if (Object.keys(form).length==0) {
            alert('Non ci sono elementi da valutare !!!');
            return;
        }

        var param={
            "piano":$('#ribbon_ctv_panorama').val(),
            "variante":$('#ribbon_ctv_variante').val(),
            "sezione":$('#ribbon_ctv_sezione').val(),
            "sorgenti":form
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/simula.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                //compilazione campi risultati FORM
                eval('var res='+ret+';');

                $('#ctv_simula_incentivo').html(res.incentivo.toFixed(2)+' €');
                $('#ctv_simula_punteggio_sezione').html(res.punteggio.toFixed(2));

                for (var x in res.moduli) {
                    $('#ctv_simula_punteggio_modulo_'+x).html(res.moduli[x].punteggio.toFixed(2));
                    $('#ctv_simula_punteggio_principali_'+x).html(res.moduli[x].principali.punteggio.toFixed(2));
                    $('#ctv_simula_punteggio_modificatori_'+x).html(res.moduli[x].modificatori.punteggio.toFixed(0)+' %');

                    for (var y in res.moduli[x].principali.parametri) {
                        $('#ctv_simula_punteggio_parametro_'+y).html(res.moduli[x].principali.parametri[y].punteggio.toFixed(2));
                    }
                    for (var y in res.moduli[x].modificatori.parametri) {
                        $('#ctv_simula_punteggio_parametro_'+y).html(res.moduli[x].modificatori.parametri[y].punteggio.toFixed(0)+' %');
                    }
                }

            }
        });
    }

    this.ctvAddSezione=function() {

        var txt=prompt('Inserisci il nome della sezione:');

        if (!txt || txt=='') return;

        var param={
            "titolo":txt,
            "piano":$('#ribbon_ctv_panorama').val(),
            "variante":$('#ribbon_ctv_variante').val()
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/add_sezione.php",
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

    this.ctvAddModulo=function() {

        var txt=$('#ctv_new_modulo_titolo').val();

        if (txt=='') return;

        var param={
            "titolo":txt,
            "sezione":$('#ribbon_ctv_sezione').val()
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/add_modulo.php",
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

    this.ctvDelModulo=function(modulo) {

        if (!confirm('Il modulo verrà CANCELLATO !!! (l\'operazione NON è reversibile)')) return;

        var param={
            "modulo":modulo,
            "sezione":$('#ribbon_ctv_sezione').val()
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/del_modulo.php",
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

    this.ctvAddParametro=function(tipo,modulo) {

        if (!window._ctv_ckMulti.check()) {
            alert('Correggi prima gli altri dati');
            return;
        }

        var txt=prompt('Inserisci il nome del parametro:');
        if (!txt || txt=='') return;

        var param={
            "tipo":tipo,
            "titolo":txt,
            "modulo":modulo
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/add_parametro.php",
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

    this.ctvDelParametro=function(tipo,parametro,modulo) {

        if (!confirm('Il parametro verrà CANCELLATO !!! (l\'operazione NON è reversibile)')) return;

        var param={
            "tipo":tipo,
            "modulo":modulo,
            "parametro":parametro
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/del_parametro.php",
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

    this.ctvOpenLink=function(linkoll) {

        $('#ribbon_ctv_linkoll').val(linkoll);
        window._nebulaApp.ribbonExecute();
        
    }

    this.selectPeriodo=function(periodo,logged) {

        var param={
            "piano":$('#ribbon_ctv_panorama').val(),
            "periodo":periodo,
            "logged":logged
        }

        $('#ctv_right_div').html('<div style="text-align:center;margin-top:20px;width:100%;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/periodo.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#ctv_right_div').html(ret);
            }
        });

    }

    this.editExt=function(tag,titolo,inizio,fine) {
        
        var param={
            "tag":tag,
            "titolo":titolo,
            "inizio":inizio,
            "fine":fine,
            "piano":$('#ribbon_ctv_panorama').val()
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/lista_ext.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#ctv_right_div').html(ret);
            }
        });

    }

    this.checkExt=function(tag) {

        var param={
            "tag":tag,
            "collaboratore":$('#ctv_extForm_coll').val(),
            "team":$('#ctv_extForm_team').prop('checked'),
            "valore":$('#ctv_extForm_valore').val(),
            "d_validita":$('#ctv_extForm_d').val(),
            "utente":window._nebulaMain.getMainLogged()
        }

        if (param.team) {
            cck=true;
            param.collaboratore='';
        }
        else if (param.collaboratore=="") {
            cck=false;
        }
        else {
            cck=true;
        }

        param.valore=param.valore.replace(',','.');

        if (!cck || param.valore=="" || param.d_validita=="") {
            alert('dati non corretti');
            return;
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/insert_ext.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                obj=$.parseJSON(ret);

                if (obj) {
                    if (obj.stato=='') $('#ctv_extButton_'+obj.tag).click();
                    else alert(obj.stato);
                }
                
            }
        });        

    }

    this.delExt=function(tag,id) {

        if (!confirm('Vuoi cancellare il record '+id+' ?')) return;

        var param={
            "tag":tag,
            "ID":id
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/delete_ext.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                obj=$.parseJSON(ret);

                if (obj) {
                    if (obj.stato=='') $('#ctv_extButton_'+obj.tag).click();
                    else alert(obj.stato);
                }
                
            }
        }); 
    }

    this.editRet=function(tag,titolo) {
        
        var param={
            "tag":tag,
            "titolo":titolo,
            "piano":$('#ribbon_ctv_panorama').val()
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/lista_ret.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#ctv_right_div').html(ret);
            }
        });

    }

    this.checkRet=function(tag) {

        var param={
            "piano":$('#ribbon_ctv_panorama').val(),
            "parametro":tag,
            "collaboratore":$('#ctv_retForm_coll').val(),
            "team":$('#ctv_retForm_team').prop('checked'),
            "valore":$('#ctv_retForm_valore').val(),
            "periodo":$('#ctv_retForm_periodo').val(),
            "utente":window._nebulaMain.getMainLogged()
        }

        if (param.team) {
            cck=true;
            param.collaboratore='';
        }
        else if (param.collaboratore=="") {
            cck=false;
        }
        else {
            cck=true;
        }

        param.valore=param.valore.replace(',','.');

        if (!cck || param.valore=="" || param.periodo=="") {
            alert('dati non corretti');
            return;
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/insert_ret.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                obj=$.parseJSON(ret);

                if (obj) {
                    if (obj.stato=='') $('#ctv_retButton_'+obj.tag).click();
                    else alert(obj.stato);
                }
                
            }
        });        

    }

    this.delRet=function(tag,id) {

        if (!confirm('Vuoi cancellare il record '+id+' ?')) return;

        var param={
            "tag":tag,
            "ID":id
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/delete_ret.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                obj=$.parseJSON(ret);

                if (obj) {
                    if (obj.stato=='') $('#ctv_retButton_'+obj.tag).click();
                    else alert(obj.stato);
                }
                
            }
        }); 
    }

    //////////////////////////////////////////////////////////////////////////////////

    this.ctvAnalisiInfoOpen=function(periodo) {

        this.ctvAnalisiInfoClose();

        var stato=this.analisiInfo.periodi[periodo].stato;

        var stati={
            "new":{'tag':'Nuovo','testo':'Prossimo periodo valido non ancora visibile.'},
            "actual":{'tag':'Attuale','testo':'Periodo attuale visibile agli interessati.'},
            "close":{'tag':'Chiuso','testo':'Periodo passato in aggiornamento e consultabile.'},
            "freezed":{'tag':'Congelato','testo':'Periodo passato bloccato e consultabile.'}
        }

        var temp={
            "hidden":$('#ctv_analisi_periodo_info_'+periodo).data('hdn'),
            "stato":$('#ctv_analisi_periodo_info_'+periodo).data('stato')
        }

        var txt='<div id="ctv_analisi_info_main" style="height:150px;border-bottom:2px solid black;width:90%;" >';

            txt+='<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;" >';

                for (var x in stati) {

                    if (x=='new' && stato!='new') continue;
                    if (x=='actual' && stato!='new' && stato!='close') continue;
                    if (x=='close' && stato!='actual') continue;
                    //if (x=='freezed' && stato!='actual' && stato!='close') continue;
                    if (x=='freezed' && stato!='freezed') continue;

                    
                    txt+='<div style="" >';
                        if (x!='new' && x!='freezed') {
                            txt+='<input name="ctv_analisi_periodo_form_stato" type="radio" value="'+x+'" ';
                                if (stato==x) txt+=' checked ';
                                if ( (x=='actual' && stato!='actual') && this.analisiInfo.actual) txt+=' disabled ';
                            txt+='/>';
                        }
                        txt+='<span style="font-weight:bold;margin-left:5px;">'+stati[x].tag+'</span>';
                    txt+='</div>';
                    
                    txt+='<div style="font-size:0.9em;" >';
                        txt+=stati[x].testo;
                    txt+='</div>';
                }

            txt+='</div>';

            txt+='<div style="position:relative;width:100%;margin-top:15px;text-align:center;" >';

                txt+='<button style="position:relative;top:5px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAnalisiInfoSet(\''+periodo+'\');">Conferma</button>';

                txt+='<img style="position:absolute;top:0px;left:10px;width:30px;height:30px;" src="http://'+location.host+'/nebula/apps/centavos/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAnalisiInfoClose();" />';
                
                txt+='<div style="position:absolute;top:0px;right:10px;">';
                    txt+='<input id="ctv_analisi_periodo_form_hidden" type="checkbox" style="position:relative;top:-8px;" ';
                        if (this.analisiInfo.periodi[periodo].hidden==1) txt+=' checked ';
                    txt+='/>';
                    txt+='<img style="position:relative;width:30px;height:30px;margin-left:10px;" src="http://'+location.host+'/nebula/apps/centavos/img/hide.png" />';
                txt+='</div>';

            txt+='</div>';

        txt+='</div>';

        $('#ctv_analisi_periodo_info_'+periodo).append(txt);

    }

    this.ctvAnalisiInfoClose=function() {

        var elem=document.getElementById('ctv_analisi_info_main');
        if (elem) elem.remove();
    } 

    this.ctvAnalisiInfoSet=function(periodo) {

        var param={
            'periodo':periodo,
            'stato':$('input[name="ctv_analisi_periodo_form_stato"]:checked').val(),
            'hidden':$('#ctv_analisi_periodo_form_hidden').prop('checked')?1:0
        }

        if (!param.stato) param.stato="";

        if (param.stato!='') {
            if (!confirm('Sarà cambiato lo stato del periodo. L\'operazione NON È REVERSIBILE!!!')) return;
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/edit_stato_periodo.php",
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

    this.comprimiAnalisi=function() {

        $('.centavosAnalisiCollModulo').hide();
        $('.centavosAnalisiCollCoeff').hide();

        $('#centavosComprimiImg').hide();
        $('#centavosEspandiImg').show();
    }

    this.espandiAnalisi=function() {

        $('.centavosAnalisiCollModulo').show();
        $('.centavosAnalisiCollCoeff').show();

        $('#centavosComprimiImg').show();
        $('#centavosEspandiImg').hide();
    }

    this.printAnalisi=function() {

        /*var blocchi=[];
        
        $('.centavosAnalisiMainVariante').each(function() {

            var coll=$(this).data('coll');
            var txt='<div style="font-weight:bold;border-top:1px solid black;">'+atob($('#centavosAnalisiMainColl_'+coll).data('head'))+'</div>';
            txt+='<div>'+atob($('#centavosAnalisiMainColl_'+coll).data('body'))+'</div>';

            //txt=btoa(unescape(encodeURIComponent($(this).html())));

            blocchi.push(txt);

        });
    
        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/centavos/core/print_analisi.php',
            "async": true,
            "cache": false,
            "data": {"blocchi": blocchi},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });*/

        /*var txt="";

        $('.centavosAnalisiMainVarianteAll').each(function() {
            txt+=$(this).html();
        });
        
        var url = window.URL.createObjectURL(txt);
        a.href = url;
        a.target="_blank";
        a.click();
        window.URL.revokeObjectURL(url);*/

        var index=window._centavos_analisi_divo.getSel();

        var wi = window.open();
        var html = $('#divo_div_centavos_analisi_'+index+' .centavosAnalisiMainVarianteAll').html();
        $(wi.document.body).html(html);

    }

    this.freezeAnalisi=function(periodo) {

        if (!confirm('Stai congelando i risultati in maniera DEFINITIVA!!!')) return;

        window.freezeParam={
            "periodo":periodo,
            "lista":[],
            "actualColl":false,
            "actualIndex":-1,
            "html":{}
        }

        window.freezeClass={

            hiddenClone:function(element) {
                // Create clone of element
                var clone = element.cloneNode(true);
                
                // Position element relatively within the 
                // body but still out of the viewport
                var style = clone.style;
                style.position = 'relative';
                style.top = window.innerHeight + 'px';
                style.left = 0;
                
                // Append clone to body and return the clone
                document.body.appendChild(clone);
                return clone;
            },

            next:function() {
                window.freezeParam.actualIndex++;
                if (!window.freezeParam.lista.hasOwnProperty(window.freezeParam.actualIndex)) {
                    //console.log(JSON.stringify(window.freezeParam.html));
                    window.freezeClass.execute();
                    return;
                }

                window.freezeParam.actualColl=window.freezeParam.lista[window.freezeParam.actualIndex].coll;

                //console.log(window.freezeParam.lista[window.freezeParam.actualIndex].id);

                var offScreen = document.querySelector('#'+window.freezeParam.lista[window.freezeParam.actualIndex].id);

                // Clone off-screen element
                var clone = window.freezeClass.hiddenClone(offScreen);
                clone.style.display='block';

                //html2canvas(document.getElementById(window.freezeParam.lista[window.freezeParam.actualIndex].id),
                html2canvas(clone).then(function(canvas) {
                    
                    //if (window.freezeParam.actualColl) {
                        window.freezeParam.html[window.freezeParam.actualColl]=canvas.toDataURL('image/png');
                        document.body.removeChild(clone);
                        window.freezeClass.next();
                    //}

                });
            },

            execute:function() {

                $.ajax({
                    "url": "http://"+location.host+"/nebula/apps/centavos/core/freeze.php",
                    "async": true,
                    "cache": false,
                    "data": {"param": window.freezeParam},
                    "type": "POST",
                    "success": function(ret) {
                        //console.log(ret);
                        window._nebulaApp.ribbonExecute();
                    }
                });
            }
        }

        //raccolta dei codici html
        for (var x in this.varDivo) {

            $('div[id^="centavosAnalisiMainVariante_'+x+'"]').each(function() {

                var coll=$(this).data('coll');
                window.freezeParam.html[coll]="";

                var temp={
                    "id":$(this).attr("id"),
                    "coll":coll
                }
                window.freezeParam.lista.push(temp);
            
            });
        }

        var main=document.querySelector('#centavos2canvas');
        main.style.display='none';

        var waiter=document.createElement('div');
        waiter.style.position='relative';
        waiter.style.width='100%';
        waiter.style.textAlign='center';
        waiter.innerHTML="elaborazione in corso...";

        document.querySelector('#ctv_right_div').appendChild(waiter);

        window.freezeClass.next();

    }

    /////////////////////////////////////////////////////

    this.ctvCheckLink=function() {

        this.linkInfo.modifiche=true;
        var error=false;

        var prev=false;

        for (var x in this.linkObj) {
            //console.log(JSON.stringify(this.linkObj[x]));

            //##############################################
            //correggi i link con i dati reali del form
            this.linkObj[x].variante=$('#ctv_link_variante_select_'+x).val();
            this.linkObj[x].periodo_i=$('#ctv_link_periodoi_select_'+x).val();
            this.linkObj[x].periodo_f=$('#ctv_link_periodof_select_'+x).val();

            this.linkObj[x].dlink_i=$('#ctv_link_periodoi_select_'+x+' option:selected').data('d');
            this.linkObj[x].dlink_f=$('#ctv_link_periodof_select_'+x+' option:selected').data('d');

            this.linkObj[x].grado={};

            for (var idg in this.linkInfo.varobj[this.linkObj[x].variante]) {
                this.linkObj[x].grado[idg]=$('#ctv_link_livello_'+x+'_'+idg+'_'+this.linkObj[x].variante).val();
            }


            //##############################################

            //if (this.linkObj[x].periodo_f<this.linkObj[x].periodo_i)  error=true;
            if (this.linkObj[x].dlink_f<=this.linkObj[x].dlink_i)  error=true;

            if (prev) {
                if (this.linkObj[x].dlink_i<=prev.f) error=true;
            }

            prev={
                "i":this.linkObj[x].dlink_i,
                "f":this.linkObj[x].dlink_f
            }
        }

        if (this.linkInfo.modifiche && !error) {
            $('#ctv_link_confirm').show();
            //console.log(JSON.stringify(this.linkObj));
        }
        else {
            $('#ctv_link_confirm').hide();
            if (error) alert('Errori nelle date dei link.');
        }

        return !error;
    }

    this.ctvChangeLinkVariante=function(link,id) {
        $('div[id^="ctv_livello_div_'+link+'"]').hide();
        $('#ctv_livello_div_'+link+'_'+id).show();

        this.ctvCheckLink();
    }

    this.ctvAddLink=function() {

        /*
        "ID_coll": 9,
  
        "variante": "TEC",
        "ID_link": 3,
        "ID_piano": 1,
        "dlink_i": "20211001",
        "dlink_f": "20221231",
        "periodo_i": 3,
        "periodo_f": 9999,
        "grado": {
            "1": 2,
            "2": 2,
            "3": 2
        }
        */

        this.linkObj[this.linkInfo.next]={
            "ID_coll":this.linkInfo.ID_coll,
            "ID_link":0,
            "ID_piano":this.linkInfo.piano,
            "dlink_i":"",
            "dlink_f":"",
            "periodo_i":0,
            "periodo_f":9999,
            "variante":"",
            "grado":{}
        }

        //#######################
        //valorizzare variante
        for (var idv in this.linkInfo.varianti) {
            this.linkObj[this.linkInfo.next].variante=this.linkInfo.varianti[idv];
            break;
        }
        //#######################

        this.ctvDrawLink();
        this.ctvCheckLink();
    }

    this.ctvConfirmLink=function() {
        
        if (!this.ctvCheckLink()) return;

        var param={
            "link":this.linkObj
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/edit_link.php",
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

    this.ctvConsolidaLink=function(piano) {

        if (!confirm('Allineo le date di fine link per tutti i collaboratori ?')) return;

        //alert(piano);

        var param={
            "piano":piano
        }

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/consolida_link.php",
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

    this.ctvDrawLink=function() {

        //$('#ctv_link_edit_main').html(console.log(JSON.stringify(this.linkInfo)));

        var txt="";
        this.linkInfo.next=0;

        for (var idlink in this.linkObj) {

            this.linkInfo.next++;
        
            txt+='<div style="position:relative;border:2px solid brown;padding:10px;box-sizing:border-box;margin-top:10px;min-height:80px;width:95%;" >';

                txt+='<div style="display:inline-block;width:15%;text-align:center;vertical-align:top;" >';

                    txt+='<div style="font-weight:bold;">variante</div>';

                    txt+='<div>';
                        txt+='<select id="ctv_link_variante_select_'+idlink+'" style="width:95%;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvChangeLinkVariante(\''+idlink+'\',this.value);" >';

                            for (var idvar in this.linkInfo.varianti) {
                                txt+='<option value="'+this.linkInfo.varianti[idvar]+'" ';
                                    if (this.linkInfo.varianti[idvar]==this.linkObj[idlink].variante) txt+='selected="selected"';
                                txt+='>';
                                    txt+=this.linkInfo.varianti[idvar];
                                txt+='</option>';
                            }
                        txt+='</select>';
                    txt+='</div>';

                txt+='</div>';

                txt+='<div style="display:inline-block;width:22%;text-align:center;vertical-align:top;" >';
                
                    txt+='<div style="font-weight:bold;">da</div>';

                    txt+='<div>';

                        txt+='<select id="ctv_link_periodoi_select_'+idlink+'" style="font-size:1.1em;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCheckLink();" >';
                            for (var idp in this.linkInfo.periodi) {
                                txt+='<option value="'+(idp==this.linkInfo.firstPeriodo?'0':idp)+'" data-d="'+this.linkInfo.periodi[idp].d_inizio+'" ';
                                    if (this.linkObj[idlink].periodo_i==idp || (this.linkObj[idlink].periodo_i==0 && idp==this.linkInfo.firstPeriodo) ) txt+=' selected ';
                                txt+='>'+idp+' - '+window._nebulaMain.data_db_to_ita(this.linkInfo.periodi[idp].d_inizio)+'</option>';
                            }
                        txt+='</select>';

                    txt+='</div>';
                            
                    txt+='<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_i']).'" />';
                        //echo '<div style="width:100%;text-align:center;" >'.mainFunc::gab_todata($l['dlink_i']).'</div>';

                    txt+='</div>';

                txt+='</div>';

                txt+='<div style="display:inline-block;width:22%;text-align:center;vertical-align:top;" >';
                    
                    txt+='<div style="font-weight:bold;">a</div>';

                    txt+='<div>';

                        txt+='<select id="ctv_link_periodof_select_'+idlink+'" style="font-size:1.1em;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCheckLink();" >';
                            for (var idp in this.linkInfo.periodi) {
                                txt+='<option value="'+(idp==this.linkInfo.lastPeriodo?'9999':idp)+'" data-d="'+this.linkInfo.periodi[idp].d_fine+'" ';
                                    if (this.linkObj[idlink].periodo_f==idp || (this.linkObj[idlink].periodo_f==9999 && idp==this.linkInfo.lastPeriodo) ) txt+=' selected ';
                                txt+='>'+idp+' - '+window._nebulaMain.data_db_to_ita(this.linkInfo.periodi[idp].d_fine)+'</option>';
                            }
                        txt+='</select>';

                    txt+='</div>';
                            
                    txt+='<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_f']).'" />';
                        //echo '<div style="width:100%;text-align:center;" >'.mainFunc::gab_todata($l['dlink_f']).'</div>';

                    txt+='</div>';

                txt+='</div>';

                txt+='<div style="display:inline-block;width:33%;text-align:center;vertical-align:top;" >';
                    
                    txt+='<div style="font-weight:bold;">livello</div>';
                            
                    txt+='<div>';

                        for (var idv in this.linkInfo.varianti) {
                            
                            txt+='<div id="ctv_livello_div_'+idlink+'_'+this.linkInfo.varianti[idv]+'" style="padding-left:5px;box-sizing:border-box;';
                                txt+=(this.linkInfo.varianti[idv]==this.linkObj[idlink].variante)?'display:block;':'display:none;';
                            txt+='">';

                                if (this.linkInfo.varobj.hasOwnProperty(this.linkInfo.varianti[idv])) {

                                    for (var idg in this.linkInfo.varobj[this.linkInfo.varianti[idv]]) {
                                        txt+='<div style="display:inline-block;width:75%;text-align:left;" >'+this.linkInfo.varobj[this.linkInfo.varianti[idv]][idg].titolo.substr(0,25)+'</div>';

                                        txt+='<div style="display:inline-block;width:25%;text-align:center;" >';

                                            txt+='<select id="ctv_link_livello_'+idlink+'_'+idg+'_'+this.linkInfo.varianti[idv]+'" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCheckLink();" >';

                                                for (var i=0;i<=4;i++) {
                                                    txt+='<option value="'+i+'" ';
                                                        if (this.linkObj[idlink].grado.hasOwnProperty(idg)) {
                                                            if (i==this.linkObj[idlink].grado[idg]) txt+='selected="selected"';
                                                        }
                                                    txt+='>'+(i+1)+'</option>';
                                                }

                                            txt+='</select>';

                                        txt+='</div>';
                                    }
                                }

                            txt+='</div>';
                        }

                    txt+='</div>';

                txt+='</div>';

                txt+='<div style="position:absolute;top:5px;right:5px;font-size:0.8em;" >'+this.linkObj[idlink].ID_link+'</div>';

            txt+='</div>';

        }

        txt+='<div style="margin-top:15px;width:90%;">';

            txt+='<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >';
                txt+='<img style="width:30px;height:30px;margin-left:5%;" src="http://'+location.host+'/nebula/apps/centavos/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAddLink();" />';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:50%;vertical-align:top;text-align:right;" >';
                txt+='<button id="ctv_link_confirm" style="margin-top:5px;display:none;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvConfirmLink();" >Conferma modifiche</button>';
            txt+='</div>';

        txt+='</div>';

        $('#ctv_link_edit_main').html(txt);
    }

    this.addPeriodo=function(piano) {
        //alert(piano);

        var param={
            "piano":piano
        }

        $('.ctv_right_div').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/add_periodo.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('.ctv_right_div').html(ret);
                //window._nebulaApp.ribbonExecute();
            }
        });
    }

    this.execAddPeriodo=function(obj) {

        var param={
            "inizio":$(obj).data('inizio'),
            "fine":$(obj).data('fine'),
            "piano":$(obj).data('piano'),
        }
        
        //console.log(JSON.stringify(param));

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/add_periodo_exec.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                var a=$.parseJSON(ret);
                if (!a || a.result=='KO') alert('Creazione periodo fallita!!!!');
                else {
                    window._nebulaApp.ribbonExecute();
                }
            }
        });
    }

    this.ctvCopiaPiano=function() {

        var param={
            "piano":$('#ctv_panorama_select').val()
        }

        $('.ctv_right_div').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/copia_piano.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('.ctv_right_div').html(ret);
                //window._nebulaApp.ribbonExecute();
            }
        });
    }

    this.ctvCopiaPianoExec=function(old) {

        var param={
            "old":old,
            "desc":$('#ctv_desc_input').val()
        }

        if (param.desc==="undefined" || param.desc=='') return;

        if (!confirm('Confermi la creazione del nuovo piano?')) return;

        $('#ctv_copia_monitor').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/centavos/core/copia_piano_exec.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                $('#ctv_copia_monitor').html(ret);
                //window._nebulaApp.ribbonExecute();
            }
        });

    }

}