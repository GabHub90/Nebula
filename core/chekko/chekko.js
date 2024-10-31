//===========================

/* COSTRUTTORE chekko
la funzione js_chk() cerca tutti gli oggetti marchiati con la CLASSE .js_chk_TAG
e li dà in pasto all'oggetto tramite il metodo CHECK

ELEMENTO HTML:
classe			chekko
attributo 		js_chk_req="{codice , anor , anand}"
					codice: 1=richiesto , 0=non richiesto , 2=consigliato , 3 richiesto SE (ifreq)
					anor: elenco campi richiesti in alternativa separati da # (o uno o l'altro o entrambi)
					anand: elenco campi richiesti contemporaneamente separati da # (devono esserci tutti)
					anxor: elenco campi richiesti esclusivamente separati da # (solo uno di loro)

attributo		js_chk_ifreq="{campo , op , val}"
					campo: campo da verificare per attivare REQ (far diventare l'opzione req codice 1)
					op: opratore
					val: valore
					
attributo		js_chk_TAG_tipo = nome del campo valido per l'oggetto jsChkVei

DIV ERRORE
id="js_chk_TAG_error_"+tipo

OGGETTO JS
Ad ogni "tipo" corrisponde un tipo di validazione.
Tutti i valori devono essere STRINGHE

metodo			init - cancella gli errori ed azzera lo stato di errore del form (proprietà CHK)
metodo			check - verifica la validità del campo e valorizza la proprietà CHK (true=errore , false=ok)
metodo			easy_check - verifica la conformità del dato senza verificare la clausola REQ
metodo			req_check - verifica la clausola REQ anche in funzione degli eventuali campi ANOR e ANAND
metodo			andor_check - raccoglie i dati dei campi per il cpontrollo con EASY_CHECK
metodo			kind_check - verifica la conformità del dato in base alla tipologia 

metodi			_SQL_	- medesimi del ciclo di check per un ARRAY fornito dall'esterno e non per un form	
*/

//==========================

function chekko(form_tag) {

    this.form_tag=form_tag;

    this.chk = 0;

    //FASE
    //indica se il controllo (check) sta avvenendo in validazione o edit
    //VALIDATE= conrollo a scopo di esportazione dei dati (impostato dalla funzione SCRIVI)
    //EDIT= controllo standard ed esportazione dei campi del FORM così come sono
    //l'attributo CONTESTO può essere alimentato dai valori necessari all'oggetto
    this.contesto={"fase":"EDIT"};

    this.fields = {};

    //variabili di supporto nei cicli each
    //this.chk_array={};
    this.target = "";
    this.val = [];

    //variabili specifiche

    //variabile per quando si verifica una query
    this.sql_arr = {};
    this.conv_sql = {};

    this.tipi = {};
    this.conv = {};
    this.expo = {};

    this.scoreFlag=false;
    this.score = {};

    this.errorColor='#f5b3b322';
    this.okColor='#00ee0022';

    this.actualScore={"punteggio":0,"risposte":0,"domande":0};

    //solo maiuscolo
    this.pattern_digit = /[^0-9.,-]/;
    this.pattern_word = /[^\w\'\- ]/;
    this.pattern_wordup = /[^\w\'\- ]/;
    this.pattern_text = /[^\w\'\/\-.,àèéìòù ()*]/i;
    this.pattern_textup = /[^\w\'\/\-.,àèéìòù ()*]/i;
    this.pattern_data = /([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/;
    this.pattern_phone = /\d{6,12}/;
    this.pattern_mail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
   
    this.load_fields = function(obj) {
        //è la configurazione dei campi del modulo come passate da PHP
        this.fields = obj;
    }

    this.load_pattern= function(codice,patt) {
        this['pattern_'+codice]=new RegExp(patt);
    }

    this.load_tipi = function(arr) {
        //sono i criteri di validazione del dato
        /*
        arr = {
        "rif": "none",
        "mat_motore": "word",
        "d_cons": "data",
        "infocar_anno":"none",
        "infocar_mese":"none"
        .........
    };*/

        this.tipi=arr;
    }

    //l'array EXPO DEVE riguardare gli stessi identici campi di CONV
    this.load_expo=function(arr) {

        //mappa i valori da esportare ad una applicazione esterna
        /*var expo = {
            "num_rif_veicolo": "",
            "mat_targa": "",
            "mat_telaio": "",
            "cod_veicolo": "",
            "mat_motore": "",
            "des_note_off": ""
        };*/

         this.expo=arr;
    }

     //l'array CONV DEVE riguardare gli stessi identici campi di EXPO
    this.load_conv=function(arr) {

         //linka il cmpo EXPO al campo del DB
         /*
         var conv = {
            "num_rif_veicolo": "rif",
            "mat_targa": "targa",
            "mat_telaio": "telaio",
            "cod_marca": "cod_marca",
            "des_note_off": "note"
         }*/

         this.conv=arr;
    }

    this.load_score=function(arr) {

        /*
         var score = {

            TIPO: {
                VALORE: {
                    "peso": percentuale (20,20,60...),
                    "count": numero (1 ... -1 == IRRILEVANTE)
                }   
            }
            
         }*/

        this.scoreFlag=true;
        this.score=arr;
    }

    this.js_chk= function() {

        //return 0;

        this.iniz();

        var ft=this.form_tag;

        $('.js_chk_'+ft).each(function() {

            var tipo = $(this).attr('js_chk_'+ft+'_tipo');

            //alert(tipo);

            window['_js_chk_'+ft].check( window['_js_chk_'+ft].fields[tipo].js_chk_req, tipo, $(this).val(), $(this).attr('id'));
            //_js_chk_vei.check(_js_chk_vei.fields[tipo].js_chk_vei_req, tipo, $(this).val(), $(this).attr('id'));

        });

        this.post_check();

        if (this.chk==0) {
            $('#js_chk_'+this.form_tag+'_head').css('background-color',this.okColor);
            $('#js_chk_'+this.form_tag+'_head').css('border-color','green');
        }
        else {
            $('#js_chk_'+this.form_tag+'_head').css('background-color',this.errorColor);
            $('#js_chk_'+this.form_tag+'_head').css('border-color','red');
        }

        //0= NO ERRORI - 1= ERRORI
        return this.chk;
    }

    this.iniz = function() {

        //ripristina la variabile di contesto - FASE
        this.contesto.fase='EDIT';

        $('.js_chk_'+this.form_tag+'_error').html('');
        $('.js_chk_'+this.form_tag+'_error').css('background-color','transparent');
        $('div[id^="js_chk_'+this.form_tag+'_elem_"]').css('border-color','transparent');
        $('div[id^="js_chk_'+this.form_tag+'_tag_"]').css('color','black');

        $('#js_chk_'+this.form_tag+'_head').css('background-color','transparent');
        $('#js_chk_'+this.form_tag+'_head').css('border-color','transparent');
        this.chk = 0;

        this.actualScore={"punteggio":0,"risposte":0,"domande":0};

        this.pre_check();
    }

    //da sovrascrivere in base alle necessità
    this.pre_check=function() {

    }
    //da sovrascrivere in base alle necessità
    this.post_check=function() {

    }

    this.check = function(req, tipo, val, id) {

        //I VALORI DEVONO ESSERE TUTTE STRINGHE
        //alert (req+' '+tipo+' '+val);
        //alert (this.req_check(req,tipo,val));

        var ft=this.form_tag;
        var errorColor=this.errorColor;

        //se è richiesto  ma non è valorizzato
        if (this.req_check(req, tipo, val)) {
            //eval ("var obj="+req);
            var obj = req;
            var ttex = "";
            if (obj.anor != "") ttex = ' (' + obj.anor + ")";
            $('#js_chk_'+ft+'_error_' + tipo).html('Errore' + ttex);
            $('#js_chk_'+ft+'_error_' + tipo).css('background-color',errorColor);
            $('#js_chk_'+ft+'_elem_' + tipo).css('border-color','red');
            $('#js_chk_'+ft+'_tag_' + tipo).css('color','red');
            this.chk = 1;
            if (this.scoreFlag) this.updateScore(tipo,val,false);
            return;
        }

        //determina il tipo di controllo da eseguire
        var fn = this.tipi[tipo];
        if (!fn) {
            $('#js_chk_'+ft+'_error_' + tipo).html('NDF');
            $('#js_chk_'+ft+'_error_' + tipo).css('background-color',errorColor);
            $('#js_chk_'+ft+'_elem_' + tipo).css('border-color','red');
            $('#js_chk_'+ft+'_tag_' + tipo).css('color','red');
            this.chk = 1;
            return;
        }

        var temp = this.kind_check(fn, val, id);
        //se a questo punto val==0 non può essere sbagliato
        if (val == "") temp = false;
        //----------------------------------------------------

        //se temp è TRUE = ERRORE
        if (temp) {
            $('#js_chk_'+ft+'_error_' + tipo).html('Errore');
            $('#js_chk_'+ft+'_error_' + tipo).css('background-color',errorColor);
            $('#js_chk_'+ft+'_elem_' + tipo).css('border-color','red');
            $('#js_chk_'+ft+'_tag_' + tipo).css('color','red');
            this.chk = 1;
        }

        if (this.scoreFlag) this.updateScore(tipo,val,temp);
    }

    this.easy_check = function(req, tipo, val, id) {

        var fn = this.tipi[tipo];

        if (val == "") {
            return true;
        } else return this.kind_check(fn, val, id);
    }

    this.req_check = function(req, tipo, val) {

        //js_chk_vei_req="{codice:1,anor:'campo#campo',anand:'campo#campo',anxor:'campo#campo'}
        //eval ("var obj="+req);
        var obj = req;

        var codice = obj.codice;
        var errorColor=this.errorColor;

        //se non è richiesto ritorna FALSE == NO errore
        if (codice == 0) return false;

        //se il codice=3 e si avvera la condizione "ifreq" => codice=1
        if (codice == 3) {

            /*var temp_str=$('input[js_chk_vei_tipo="'+tipo+'"]').attr('js_chk_vei_ifreq');
            try{
            	eval ("var ifreq="+temp_str);
            }
            catch {
            	return false;
            }*/

            var ifreq =  window['_js_chk_'+this.form_tag].fields[tipo].js_chk_ifreq;
            //var ifreq = _js_chk_vei.fields[tipo].js_chk_vei_ifreq;

            //alert (temp_str);

            //verifica condizione ifreq

            //NON FUNZIONA CON RADIO E CHECKBOX
            var temp_val = $('.js_chk_'+this.form_tag+'[js_chk_'+this.form_tag+'_tipo="' + ifreq.campo + '"]').val();

            temp_res = false;

            temp_str = "if (";
            temp_str += "'" + temp_val + "'" + ifreq.op + "'" + ifreq.val + "'";
            temp_str += ") temp_res=true;";

            try {
                //alert(ifreq.campo+'-'+temp_str);
                eval(temp_str);
            } catch (err) {
                //alert (temp_str);
                //alert (err);
                return false;
            }

            //se deve essere compilato e non lo è da errore altrimenti valutalo in base alle altre clausole
            if (temp_res) {
                if (!val || val == "") return true;
                else codice = 1;
            }
            //se non deve essere compilato obbligatoriamente ma lo è valutalo per le altre clausole
            else if (val && val!="") codice=1;
        }

        //se è richiesto
        if (codice == 1) {

            var temp = false;
            //se il valore è "" allora non va bene
            if (!val || val == "") temp = true;

            //verifica clausole AND (solo se temp è false altrimenti è inutile)
            if (temp == false) {

                var arr = obj.anand.split('#');
                if (arr[0] != "") {
                    for (var x in arr) {
                        var dati = this.andor_check(arr[x]);
                        var res = this.easy_check(dati.r, dati.t, dati.v, dati.id);
                        //se una clausola non va bene setta risultato a TRUE
                        if (res) temp = true;
                    }
                }

                //se c'è la clausula AND , la clausula XOR viene esclusa
                else {
                    var arr = obj.anxor.split('#');
                    if (arr[0] != "") {

                        for (var x in arr) {
                            var dati = this.andor_check(arr[x]);
                            var res = this.easy_check(dati.r, dati.t, dati.v, dati.id);

                            //se una clausola va bene, siccome temp==false allora temp=>true
                            if (!res) temp=true;
                        }
                    }
                }
            }

            //verifica clausole OR (solo se è TRUE altrimenti non ha senso)
            else if (temp != false) {
                var arr = obj.anor.split('#');
                if (arr[0] != "") {
                    for (var x in arr) {
                        var dati = this.andor_check(arr[x]);
                        var res = this.easy_check(dati.r, dati.t, dati.v, dati.id);
                        //se una clausola va bene setta risultato a FALSE
                        if (!res) temp = false;
                    }
                }

                //se c'è la clausula OR , la clausula XOR viene esclusa
                else {
                    var arr = obj.anxor.split('#');
                    if (arr[0] != "") {
                        var numxor = 0;
                        for (var x in arr) {
                            var dati = this.andor_check(arr[x]);
                            var res = this.easy_check(dati.r, dati.t, dati.v, dati.id);
                            //se una clausola va bene contale (perché solo una può essere corretta)
                            if (!res) numxor++;
                        }

                        if (numxor == 1) temp = false;
                    }
                }
            }

            return temp;
        }

        //consigliato
        else if (codice == 2) {
            if (val == "") {
                $('#js_chk_'+this.form_tag+'_error_' + tipo).html('<span style="color:#c400ff;">Consigliato</span>');
                $('#js_chk_'+this.form_tag+'_error_'+  tipo).css('background-color',errorColor);
                $('#js_chk_'+this.form_tag+'_elem_' + tipo).css('border-color','red');
                $('#js_chk_'+this.form_tag+'_elem_' + tipo).css('color','red');
            }
            return false;
        }
    }

    this.andor_check = function(t) {

        var ret = { "r": "{codice:1,anand:'',anor:''}", "t": t, "v": "", "id": "" }

        this.target = t;
        this.val = [];

        var ft=this.form_tag;

        $('.js_chk_'+ft).each(function() {
            if ($(this).attr('js_chk_'+ft+'_tipo') == window['_js_chk_'+ft].target) {
                window['_js_chk_'+ft].val.push($(this).val());
                window['_js_chk_'+ft].val.push($(this).attr('id'));
            }
        });

        ret.v = this.val[0];
        ret.id = this.val[1];

        return ret;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    this.kind_check = function(fn, val, id) {

        //true=ERRORE
        var temp = true;

        try {
            temp=window['_js_chk_'+this.form_tag]['kind_'+fn](val,id);
        }catch(error) {
            alert('Errore check: '+fn);
        }

        return temp;
    }

    this.kind_none=function(val,id) {
        return false;
    }

    this.kind_digit=function(val,id) {
        return this.pattern_digit.test(val);
    }

    this.kind_word=function(val,id) {
        //val = val.toUpperCase();
        this.chg_val(id, val);
        //se TRUE significa che sono stati trovati caratteri non validi
        return this.pattern_word.test(val);
    }

    this.kind_wordup=function(val,id) {
        val = val.toUpperCase();
        this.chg_val(id, val);
        //se TRUE significa che sono stati trovati caratteri non validi
        return this.pattern_word.test(val);
    }

    this.kind_text=function(val,id) {
        //val = val.toUpperCase();
        this.chg_val(id, val);
        //se TRUE significa che sono stati trovati caratteri non validi
        return this.pattern_text.test(val);
    }

    this.kind_textup=function(val,id) {
        val = val.toUpperCase();
        this.chg_val(id, val);
        //se TRUE significa che sono stati trovati caratteri non validi
        return this.pattern_text.test(val);
    }

    this.kind_data=function(val,id) {
         
         if (parseInt(val.substr(0, 4)) < 1900) return true;
         else {
             //se TRUE significa che la data è valida
             return !this.pattern_data.test(val);
         }
    }

    this.kind_scad=function(val,id) {
         //se la funzione restituisce TRUE significa che la data è valida			
         var tx=!this.pattern_data.test(val);
         var errorColor=this.errorColor;

         if (!tx) {
             var now = new Date();
             var rif = new Date(parseInt(val.substr(0, 4)), parseInt(val.substr(5, 2)) - 1, parseInt(val.substr(8, 2)), 23, 59, 59);
             if (rif <= now) {
                 $('#js_chk_'+this.form_tag+'_error_' + fn).html("SCADUTO");
                 $('#js_chk_'+this.form_tag+'_error_' + fn).css('background-color',errorColor);
                 $('#js_chk_'+this.form_tag+'_elem_' + tipo).css('border-color','red');
                 $('#js_chk_'+this.form_tag+'_tag_' + tipo).css('color','red');
                 return true;
             }
             else return false;
         }
         else return tx;
   }

   this.kind_phone=function(val,id) {
         
        val = val.replace(" ", "");
        this.chg_val(id, val);
        //se TRUE significa che è una cifra corretta tra 6 e 12 numeri
        return !this.pattern_phone.test(val);
    }

    this.kind_mail=function(val,id) {

        val = val.toLowerCase();
        this.chg_val(id, val);
        //se TRUE significa che è una mail valida
        return !this.pattern_mail.test(val);
    }

    this.chg_val = function(id, val) {
        $('#' + id).val(val);
    }

    this.chg_radio_std=function(tipo,val) {

        $('input[js_chk_'+this.form_tag+'_tipo="'+tipo+'"]').val(val);
        this.js_chk();
    };

    this.chg_flagCkb_std=function(tipo,val) {

        var v=0;
        if (val) v=1;

        $('input[js_chk_'+this.form_tag+'_tipo="'+tipo+'"]').val(v);
        this.js_chk();
    };

    this.chg_flagCkb_null=function(tipo,val) {

        var v="";
        if (val) v="1";

        $('input[js_chk_'+this.form_tag+'_tipo="'+tipo+'"]').val(v);
        this.js_chk();
    };

    this.chg_switch_std=function(id,val) {

        //se il campo è disabled non eseguire
        if ( $('input[js_chk_'+this.form_tag+'_tipo="'+id+'"]').prop('disabled') ) return;

        if (val=='N') {
            //#f5b6b6
            a={'bk':'#cfd2cf'};
            $('input[js_chk_'+this.form_tag+'_tipo="'+id+'"]').val('N');
        }
        else if(val=='S') {
            //#b5f7b5
            a={'bk':'#cfd2cf'};
            $('input[js_chk_'+this.form_tag+'_tipo="'+id+'"]').val('S');
        }


        $('#sw_'+this.form_tag+'_'+id+'_S').css('background-color','transparent');
        $('#sw_'+this.form_tag+'_'+id+'_N').css('background-color','transparent');
        //////////////////////
        $('#sw_'+this.form_tag+'_'+id+'_'+val).css('background-color',a.bk);

        this.js_chk();
    };

    this.disable_form=function() {

        var ft=this.form_tag;

        $('.js_chk_'+ft).each(function() {

            //setta l'elemento DISABLED
            $(this).prop('disabled',true);

            var group=$(this).data('chk_group');

            $('input[chk_group_'+ft+'="'+group+'"]').each( function() {
                //setta ogni INPUT collegato al gruppo (tipo RADIO) come DISABLED
                $(this).prop('disabled',true);
            });

            $('select[chk_group_'+ft+'="'+group+'"]').each( function() {
                //setta ogni INPUT collegato al gruppo (tipo RADIO) come DISABLED
                $(this).prop('disabled',true);
            });

        });

        this.disable_form_post();
    }

    this.updateScore=function(tipo,val,error) {

        //se il tipo non è menzionato in score ritorna
        if ( !(tipo in this.score) ) return;

        //se la risposta è in errore conta solo una domanda in più
        if (error) {
            this.actualScore.domande++;
            return;
        }

        //alert(tipo+' '+val);

        val=""+val;

        if ( (val in this.score[tipo]) ) {
            this.actualScore.punteggio+=this.score[tipo][val].peso;

            //se è -1 significa che la risposta non inficia le percentuali
            //(peso:20 count:-1)
            if (this.score[tipo][val].count!=-1) {
                this.actualScore.risposte+=this.score[tipo][val].count;
                this.actualScore.domande++;
            }
        }
    }

    //metodo da sovrascrivere per casi particolari
    this.disable_form_post=function() {};

    ////////////////////////////////////////////////////////////////////////////////////////////
    //COLLECT DATA
    ////////////////////////////////////////////////////////////////////////////////////////////

    this.evaluate = function() {

        this.js_chk();

        if (this.chk == 1) return;

        this.contesto.fase='EDIT';

        return this.collect_data();
    }

    this.evaluateAnyway = function() {

        this.js_chk();

        this.contesto.fase='EDIT';

        return this.collect_data();
    }

    this.scrivi = function() {

        this.js_chk();

        if (this.chk == 1) return;

        this.contesto.fase='VALIDATE';

        this.collect_data();

        this.scrivi_proprietario();
        
        return this.expo;

        //this.scrivi_proprietario(res);
    }

    this.salva = function() {
        
        this.contesto.fase='VALIDATE';

        this.collect_data();

        this.scrivi_proprietario();
        
        return this.expo;
    }

    this.collect_data = function() {
        //raccoglie le informazioni
        //se viene chiamata da SCRIVI() la fase viene settata in VALIDATE e viene utilizzato l'array EXPO + CONV
        //altrimenti (chiamata da evaluate() )la fase è EDIT e viene usato CONV basandosi sul valore dell'array
        //in questo modo in caso di VALIDATE viene ritornato un array conforme all'applicazione che ne deve usufruire
        //in caso di EDIT i valori rimangono conformi al form

        //RACCOLTA VALORI
        if (this.contesto.fase=='EDIT') {

            var res={};

            for (var x in this.conv) {

                res[this.conv[x]]=this.collectField(this.conv[x]);
            }
            //alert(this.contesto.fase+' '+JSON.stringify(res));
            return res;
        }

        else {

            for (var x in this.expo) {

                this.expo[x]=this.collectField(this.conv[x]);
            }

            this.collect_data_proprietario();

            //alert(this.contesto.fase+' '+JSON.stringify(this.expo));
            return this.expo;
        }
        
    }

    this.collectField=function(campo) {

        //alert(campo);

        var res="";

        res = $('input[js_chk_'+this.form_tag+'_tipo="' + campo + '"]').val();

        if (!res) {
            res = $('select[js_chk_'+this.form_tag+'_tipo="' + campo + '"]').val();
        }
        if (!res) {
            res = $('textarea[js_chk_'+this.form_tag+'_tipo="' + campo + '"]').val();
        }

        //se abbiamo letto una data
        if (this.tipi[campo]=='data' || this.tipi[campo]=='scad') {
            if (res) res = '' + res.substr(0,4) + res.substr(5,2) + res.substr(8,2);
        }

        if (!res) res="";

        return res;
    }

    this.scrivi_proprietario=function() {
        //metodo da sovrascrivere con le istruzioni proprie dell'oggetto
        //funzioni AJAX per scrivere il database
    }

    /*
    this.scrivi_result=function(res) {
        //metodo da sovrascrivere con le istruzioni proprie dell'oggetto
        //da chiamare in caso di success in scrivi_proprietario
    }
    */

    this.collect_data_proprietario=function() {
        //metodo da sovrascrivere con le istruzioni proprie dell'oggetto
        //raccolta di campi non standard su cui fare calcoli particolari o trasformazione di contenuti
    }

    this.execApp=function(app,arr) {

        this["app_"+app](arr);
    }

}