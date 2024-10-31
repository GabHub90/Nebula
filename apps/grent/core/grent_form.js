var grentFormClass = class grentForm {

    constructor() {
        this.veicolo=$.parseJSON(atob($('#grent_form_veicolo').val()));
        this.noleggio=$.parseJSON(atob($('#grent_form_noleggio').val()));
        this.cliente={};

        //valori di default
        this.franchigia={
            "importo":1500,
            "perc":10,
            "limite":15000,
            "flag_importo":300,
            "flag_perc":10,
            "flag_limite":15000,
            "calc_max":500,
            "calc_min":50,
            "calc_indice":0.9
        };

        /*
        coeffkm         è l'indice nel calcolo esponenziale dell'aumento dell'indice kilometrico (minore è l'indice maggiore è la reattività)
        eccedenza       è l'indice nel calcolo del CALO esponenziale del valore dell'eccedenza kilometrica (minore è l'indice maggiore è la reattività)
        tariffa         è l'indice nel calcolo del CALO esponenziale del valore del coefficiente tariffario (minore è l'indice maggiore è la reattività)
        coeff           è il minimo che può raggiungere il coefficiente tariffario
        actualC         è l'attuale minimo raggiungibile
        kmg             km al giorno
        minkm           kilometri minimi
        fr              costo massimo per riduzione franchigia
        minfr           tariffa  minima per la franchigia
        frc             è l'indice nel calcolo esponenziale dell'aumento del costo per la clausola della franchigia (minore è l'indice maggiore è la reattività) 
        frode           aumento della tariffa per fondo frode       
        */
        this.param={
            "coeffkm":0.8,
            "eccedenza":0.9,
            "tariffa":0.6,
            "coeff":1.2,
            "actualC":1.2,
            "kmg":500,
            "minkm":3000,
            "fr":500,
            "minfr":50,
            "frc":0.9,
            "frode":0.02
        }

        /*VEICOLO
        "data_inizio"=>'20220627',
        "data_fine"=>"",
        "stato"=>1,
        "km_inizio"=>24473,
        "reset"=>'20220627',
        "km_reset"=>24473,
        "fascia"=>"A3",
        "coeff"=>4,
        "coeff_km"=>0.5,
        "sva"=>4000,
        "sva_km"=>30000,
        "sva_tempo"=>18,
        "pratica_id"=>0,
        "pratica_da"=>"",
        "pratica_a"=>"",
        "actual_km"=>24473,
        "note"=>"Prova",
        "info"=>false
        */
    }

    loadFranchigia=function(franchigia) {
        this.franchigia=franchigia;
        this.param.fr=franchigia.calc_max;
        this.param.minfr=franchigia.calc_min;
        this.param.frc=franchigia.calc_indice;
    }

    verificaLimiti=function() {
        //Verifica che le impostazioni di noleggio non superino i limiti di TEMPO e KM di svalutazione
        //calcola tutti i parametri dipendenti da tempo e km

        this.noleggio.limite=this.veicolo.actual_km+parseInt(this.noleggio.km);
        if (this.noleggio.limite>(this.veicolo.km_reset+this.veicolo.sva_km)) {
            this.noleggio.km=this.veicolo.km_reset+this.veicolo.sva_km-this.veicolo.actual_km;
            $('#grent_form_km').val(this.noleggio.km);
            this.noleggio.limite=this.veicolo.actual_km+parseInt(this.noleggio.km);
            //##################
            //colorare la tabella di intestazione
            //pensare ad un margine di sforamento
            //##################
        }
        $('#grent_form_flag_limite_txt').html(this.noleggio.limite+' km');

        //coefficiente kilometrico

        //calcolo logaritmico:
        // H                        ( log base 100 ( K * 100 ) ) *100
        // alpha                    fattore / 10
        // LIN                      calcolo lineare
        // risultato                H - alpha ( H - LIN )
        /*$h=log($k*100,100)*100;
        $f=$p['funzione']['fattore']/10;
        return $h-($f*($h-($k*100)));*/

        if (this.noleggio.km==0) {
            this.noleggio.actual_coeffkm=0;
        }
        else {
            var k = this.noleggio.km/this.veicolo.sva_km;
            /*var h = (Math.log(k*100) / Math.log(100))*this.veicolo.coeff_km;
            //il coefficiente era 2
            var a = this.param.coeffkm/10;
            this.noleggio.actual_coeffkm=h-(a*(h-(k*this.veicolo.coeff_km)));

            if (this.noleggio.actual_coeffkm<0) this.noleggio.actual_coeffkm=0;*/

            this.noleggio.actual_coeffkm=(k**this.param.coeffkm)*this.veicolo.coeff_km;
        }

        //this.noleggio.actual_coeffkm=this.veicolo.coeff_km*((this.noleggio.km/this.veicolo.sva_km)**1);

        // K                        (x - min) / scala
        //calcolo esponmenziale:    ( ( K ) ^ fattore ) * 100  (lineare=fattore=1)

        var k=this.noleggio.actual_coeffkm/this.veicolo.coeff_km;
        this.noleggio.eccedenza=0.3-((k**this.param.eccedenza)*0.2);
        //this.noleggio.eccedenza=(1+(this.veicolo.coeff_km-this.noleggio.actual_coeffkm+0.1))*0.2;
        this.noleggio.eccedenza=this.noleggio.eccedenza.toFixed(2);

        //alert(this.noleggio.actual_coeffkm);

        $('#grent_form_ckm').val(this.noleggio.actual_coeffkm.toFixed(2));
        $('#grent_form_eccedenza').val(this.noleggio.eccedenza);

        //franchigia
        if ($('#grent_form_flag_franchigia').prop('checked')) {
            
            var k=this.noleggio.durata/365;
            var t=(k**this.param.frc)*this.param.fr;

            if (t>this.param.minfr) {
                this.noleggio.franchigia=t/this.noleggio.durata;
            }
            else this.noleggio.franchigia=this.param.minfr/this.noleggio.durata;

            if ((this.noleggio.franchigia*this.noleggio.durata)>this.param.fr) {
                this.noleggio.franchigia=this.param.fr/this.noleggio.durata;
            }

            this.noleggio.flag_franchigia=1;
        }
        else {
            this.noleggio.franchigia=0;
            this.noleggio.flag_franchigia=0;
        }

        this.noleggio.tot_franchigia=(this.noleggio.franchigia*this.noleggio.durata).toFixed(2);
        this.noleggio.franchigia=this.noleggio.franchigia.toFixed(2);
        
        this.eval();
    }

    eval=function() {

        /*NOLEGGIO
        "nol_id"=>0,
        "reset_id"=>1,
        "stato"=>"prenotazione",
        "tot_fisso"=>6050,
        "tot_var"=>900,
        "actual_coeff"=>4,
        "actual_coeffkm"=>0,
        "eccedenza"=>0,
        "misura"=>"g",
        "durata"=>0,
        "km"=>0,
        "flag_limite"=>0,
        "franchigia"=>0,
        "flag_franchigia"=>0,
        "tot_franchigia"=>0
        "extra"=>array(),
        "pren_i"=>"",
        "ora_pren_i"=>"",
        "pren_f"=>"",
        "ora_pren_f"=>"",
        "data_fattura"=>"",
        "da"=>"",
        "a"=>"",
        "note"=>""
        */

        //TARIFFA

        //this.noleggio.actual_coeff=parseFloat($('#grent_form_c').val());

        //alert(this.noleggio.actual_coeff);

        var tariffa=this.calcolaTariffa(1);

        $('#grent_form_giornaliero_txt').html(tariffa);

        //FRANCHIGIA
        $('#grent_form_franchigia_txt').html(this.noleggio.franchigia);
        $('#grent_form_franchigia_txt_tot').html('('+this.noleggio.tot_franchigia+' +iva)');

        //TABELLA
        $('#grent_tariffa_txt_1').html((parseFloat(tariffa)+parseFloat(this.noleggio.franchigia)).toFixed(2));

        this.calcolaTabellaTariffe();

        var actual=parseFloat(this.calcolaTariffa(this.noleggio.durata))+parseFloat(this.noleggio.franchigia);
        $('#grent_form_tariffa_txt').html('<div>'+actual.toFixed(2)+'</div><div>'+(30*actual).toFixed(2)+'/30gg</div>');
        $('#grent_form_totale_txt').html('<div>'+(actual*this.noleggio.durata).toFixed(2)+' +iva</div><div>('+(1.22*actual*this.noleggio.durata).toFixed(2)+')</div>');

        this.noleggio.tariffa=actual;
        if (actual==0) this.noleggio.costo=0;
        else {
            this.noleggio.costo=((this.noleggio.tot_fisso+(this.noleggio.tot_var*(1+this.noleggio.actual_coeffkm)))/365)*this.noleggio.durata;
        }

        var margine=(actual*this.noleggio.durata)-this.noleggio.costo;
        //alert(this.noleggio.costo+' '+margine);
        //se il margine non supera una percentuale cop_fisso delle spese fisse, non c'è incentivo
        //a livello di veicolo vengono impostate il livello di incentivazione e la percentuale di copertura delle spese fisse per il singolo noleggio
        this.noleggio.incent=(actual*this.noleggio.durata)<(this.noleggio.tot_fisso*this.veicolo.cop_fisso)?0:margine*parseFloat(this.veicolo.incent);

        $('#grent_form_incent').html((this.noleggio.incent).toFixed(2));

        ////////////////////////////
        this.noleggio.note_pren=$('#grent_form_note').val();

        console.log(JSON.stringify(this.noleggio));
    }

    calcolaTariffa=function(d) {

        this.noleggio.actual_coeff=parseFloat($('#grent_form_c').val());

        var k=parseInt(d)/365;
        var h=-1*(k**this.param.tariffa)*parseFloat(this.noleggio.actual_coeff);
        h=h.toFixed(1);

        var c=parseFloat(this.noleggio.actual_coeff)+parseFloat(h);
        if (c<this.param.actualC) c=this.param.actualC;

        var tariffa=( ( (parseFloat(this.noleggio.tot_fisso)) + (parseFloat(this.noleggio.tot_var)*(1+parseFloat(this.noleggio.actual_coeffkm))) )*parseFloat(c))/365;

        if (this.noleggio.durata==0) {
            tariffa=0;
        }

        tariffa=tariffa*(1+this.param.frode);

        //alert(c+' '+d);

        return tariffa.toFixed(2);
    }

    calcolaTabellaTariffe=function() {
        $('#grent_tariffa_txt_2').html((parseFloat(this.calcolaTariffa(30))+parseFloat(this.noleggio.franchigia)).toFixed(2));
        $('#grent_tariffa_txt_3').html((parseFloat(this.calcolaTariffa(60))+parseFloat(this.noleggio.franchigia)).toFixed(2));
        $('#grent_tariffa_txt_4').html((parseFloat(this.calcolaTariffa(182))+parseFloat(this.noleggio.franchigia)).toFixed(2));
        $('#grent_tariffa_txt_5').html((parseFloat(this.calcolaTariffa(365))+parseFloat(this.noleggio.franchigia)).toFixed(2));
    }

    setKm=function(flag) {

        var m=/^[^0-9]+$/;

        var t=$('#grent_form_km').val().trim();
        if (t=="" || m.test(t)) t=0;

        if (parseInt(t)<this.param.minkm) t=this.param.minkm;

        $('#grent_form_km').val(t);
        if (flag) $('#grent_form_km').data('flag','1');
        this.noleggio.km=parseInt(t);

        this.verificaLimiti();
    }

    setLimite=function(flag) {

        this.setKm(false);

        //se ho flaggato max
        if (flag) {
            
            $('#grent_form_km').data('tempkm',$('#grent_form_km').val());
            var max=this.veicolo.km_reset+this.veicolo.sva_km;
            this.noleggio.km=((max-this.veicolo.actual_km)<0?0:max-this.veicolo.actual_km);
            $('#grent_form_km').attr('disabled',true);
            this.noleggio.flag_limite=1;
        }
        else {

            this.noleggio.km=parseInt($('#grent_form_km').data('tempkm'));
            $('#grent_form_km').attr('disabled',false);
            this.noleggio.flag_limite=0;
        }

        $('#grent_form_km').val(this.noleggio.km);

        this.verificaLimiti();

    }

    setDurata=function() {

        var i=$('#grent_form_inizio').val();
        var f=$('#grent_form_fine').val();

        //$('#grent_form_printicon').hide();
        $('.grent_form_allowbutton').hide();

        var d=0;

        if (i=="" || f=="" || parseInt(window._nebulaMain.data_form_to_db(i))>parseInt(window._nebulaMain.data_form_to_db(f))) d=0;
        else {
            const data_i = new Date(parseInt(i.substr(0,4)),parseInt(i.substr(5,2)-1),parseInt(i.substr(8,2)));
            const data_f = new Date(parseInt(f.substr(0,4)),parseInt(f.substr(5,2)-1),parseInt(f.substr(8,2)));
            const diffTime = Math.abs(data_f - data_i);
            d = Math.ceil(diffTime / (1000 * 60 * 60 * 24))+1;

            //$('#grent_form_printicon').show();
            $('.grent_form_allowbutton').show();
        }
       
        this.noleggio.durata=d;
        $('#grent_form_durata').val(d);

        this.noleggio.misura=$('input[name="grent_form_misura"]:checked').val();

        this.noleggio.pren_i=i.replaceAll('-','');
        this.noleggio.pren_f=f.replaceAll('-','');

        //scrittura km se non ancora inseriti
        if ($('#grent_form_km').data('flag')=='0') {
        
            $('#grent_form_km').val(d*this.param.kmg);
            this.setKm(false);
            /*var temp =d*this.param.kmg;
            if (temp<this.param.minkm) temp=this.param.minkm;
            $('#grent_form_km').val(temp);
            this.noleggio.km=temp;*/
        }
        //else this.verificaLimiti();

        this.verificaLimiti();
    }

    setCoeff=function(segno) {

        var v=parseFloat($('#grent_form_c').val());
        var def=parseFloat($('#grent_form_c').data('default'));

        v=v+(segno*0.1);

        //se il valore è minore di quello di default
        if (v<def) {
            v=def;
        }

        //aumenta anche il limite inferiore del coefficiente in base alla selezione
        //this.param.actualC=this.param.coeff+v-def;

        if (v==def) {
            $('button[id^="grent_form_pm_m"]').css('visibility','hidden');
        }
        else $('button[id^="grent_form_pm_m"]').css('visibility','visible');

        v=v.toFixed(1);

        $('#grent_form_c').val(v);

        this.eval();
    }

    stampa=function() {

        this.verificaLimiti();

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "veicolo":this.veicolo,
            "cliente":this.cliente,
            "noleggio":this.noleggio,
            "franchigia":this.franchigia
        }

        //alert(this.noleggio.note);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/stampa.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf;charset=UTF-8");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }

    prenota=function(lista) {

        this.verificaLimiti();

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "veicolo":this.veicolo,
            "noleggio":this.noleggio,
            "lista":lista
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/prenota.php',
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

    annulla=function(id) {

        if (!confirm('Confermi di annullare la prenotazione?')) return;

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "id":id
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/annulla.php',
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

    noleggia=function(id) {

    }

    ////////////////////////////////////////////////////////////////

}