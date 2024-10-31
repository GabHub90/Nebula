window._pitstopListaClass=class pitstopLista {

    constructor(arg,container) {

        this.info={
            "ID":"",
            "reparto":"",
            "dms":"",
            "do_creazione":"",
            "ut_creazione":"",
            "do_elaborazione":"",
            "ut_elaborazione":"",
            "destinazione":"",
            "id_destinazione":"",
            "nota":"",
            "allegati":"",
            "stato":"nuova",
            "originale":""
        }
    
        this.container=container;
    
        this.operazione={
            "nuova_etichetta":"",
            "etichetta":"",
            "elementi":[]
        }

        for (var x in this.info) {
            if (arg[x]!=="undefined") this.info[x]=arg[x];
        }

    }

    setOperazione() {

        this.operazione.nuova_etichetta="";

        var net=$('#pitstop_newlista_form_etichetta').val();
        net=net.trim();

        if (net!="") {
            if (!confirm('Vuoi creare una nuova etichetta "'+net+'"?')) return;
            this.operazione.nuova_etichetta=net;
        }
        else {

            var et=false;
            et=$('input[name="pitstop_lista_etichetta"]:checked').val();

            if (!et || et=="") this.operazione.etichetta="0";
            else this.operazione.etichetta=""+et;
        }

        this.operazione.elementi=[];

        var temp=this.operazione.elementi;

        $('input[id^="pitstop_lista_elemento_"]:checked').each(function(){
            temp.push($(this).val());
        });
    }

    addGenerico() {

        var txt=$('#pitstop_newlista_form_codice').val();
        txt=txt.trim();

        if (txt=='') return;

        this.setOperazione();

        console.log(JSON.stringify(this.operazione));

    }

}