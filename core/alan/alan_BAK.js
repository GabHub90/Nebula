function alanBlock() {

    this.info={
        "E":false,
        "U":false,
        "stato":"KO",
        "minuti":0,
        "tag":"",
        "IDcoll":""
    }

    this.getStato=function() {
        return this.info.stato;
    }

    this.checkU=function() {
        if (!this.info.U) return false;
        else return true;
    }

    this.add=function(arr,IDcoll) {
        if (arr.VERSOO=='E') this.info.E=arr;
        if (arr.VERSOO=='U') this.info.U=arr;

        this.info.tag=arr.d;
        this.info.IDcoll=IDcoll;

        this.evaluate();

        return this.info.minuti;
    }

    this.evaluate=function() {

        if (!this.info.E || !this.info.U) {
            this.info.stato='KO';
            this.info.minuti=0;
            return;
        }

        if (this.info.U.actualM<this.info.E.actualM) {
            this.info.stato='ERR';
            this.info.minuti=0;
            return;
        }

        this.info.minuti=this.info.U.actualM-this.info.E.actualM;
        this.info.stato='OK';
    }

    this.draw=function() {

        var txt='<div style="';
            if (this.info.stato=='KO' || this.info.stato=='ERR') txt+='color:red;';
            else txt+='color:black;';
        txt+='">';
            var tt="";
            if (this.info.E) {
                tt=this.info.E.h;
                if (this.info.E.actualH!="") tt+=' ('+this.info.E.actualH+')';
            }
            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">'+tt+'</div>';

            var tt="";
            if (this.info.U) {
                tt=this.info.U.h;
                if (this.info.U.actualH!="") tt+=' ('+this.info.U.actualH+')';
            }
            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">'+tt+'</div>';

            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">';
                if(this.info.stato=='OK') {
                    var tval=this.info.minuti/60;
                    txt+=tval.toFixed(2);
                }
            txt+='</div>';

        txt+='</div>';

        return txt;
    }

};


function nebulaAlan() {

    this.prefix="";
    this.collab={};

    this.today=new Date().toISOString().replace('-', '').split('T')[0].replace('-', '');
    //console.log(this.today);

    this.init=function(prefix) {

        this.prefix=prefix;
        var collab={};

        $('input[id^="'+prefix+'_alan_"]').each(function() {

            var idcoll=$(this).data('idcoll');

            collab[idcoll]={
                "stato":"OK",
                "lista":$(this).data('lista'),
                "giorni":{}
            };

        });

        this.collab=collab;

        this.build();
    }

    this.getCollStato=function(IDcoll) {
        return this.collab[IDcoll].stato;
    }

    this.build=function() {

        var lastday="";
        var lastcoll="";
        var temp=false;

        for (var IDcoll in this.collab) {

            if (lastcoll=="") lastcoll=IDcoll;

            for (var tag in this.collab[IDcoll].lista) {
                /*{
                    "20210701":{
                        "480":{"IDDIP":19,"d":"20210701","h":"07:55","VERSOO":"E","IDTIMBRATURA":582506,"actualH":"08:00","oreSTD":8,"tipoSTR":"lav"},
                        "720":{"IDDIP":19,"d":"20210701","h":"12:03","VERSOO":"U","IDTIMBRATURA":582598,"actualH":"12:00","oreSTD":8,"tipoSTR":"lav"},
                        "900":{"IDDIP":19,"d":"20210701","h":"14:56","VERSOO":"E","IDTIMBRATURA":582742,"actualH":"15:00","oreSTD":8,"tipoSTR":"lav"},
                        "1140":{"IDDIP":19,"d":"20210701","h":"19:07","VERSOO":"U","IDTIMBRATURA":582834,"actualH":"19:00","oreSTD":8,"tipoSTR":"lav"}
                    }
                }*/

                //se c'è un blocco in sospeso (potrebbe essere di un altro collaboratore)
                if (temp) {
                    //console.log(temp.draw());
                    this.collab[lastcoll].giorni[lastday].blocks.push(temp);
                    temp=false;
                    lastcoll=IDcoll;
                }

                this.collab[IDcoll].giorni[tag]={
                    "minuti":0,
                    "oreSTD":0,
                    "tipoSTR":"",
                    "actual":$('#alan_dayinfo_'+tag+'_'+IDcoll).data('actual'),
                    "stato":'OK',
                    "blocks":[],
                };
                lastday=tag;

                for (var h in this.collab[IDcoll].lista[tag]) {

                    if (!temp) {
                        //se non esiste un blocco attivo crealo ed aggiungici la timbratura in esame
                        temp=new alanBlock();
                        this.collab[IDcoll].giorni[tag].minuti+=temp.add(this.collab[IDcoll].lista[tag][h],IDcoll);
                        this.collab[IDcoll].giorni[tag].oreSTD=this.collab[IDcoll].lista[tag][h].oreSTD;
                        this.collab[IDcoll].giorni[tag].tipoSTR=this.collab[IDcoll].lista[tag][h].tipoSTR;
                    }

                    else if (this.collab[IDcoll].lista[tag][h].VERSOO=='E') {
                        //se la timbratura è in entrata (ed a questo punto esiste un blocco attivo)
                        //salva il blocco attivo e creane un altro allegandoci la timbratura
                        this.collab[IDcoll].giorni[tag].blocks.push(temp);
                        temp=new alanBlock();
                        this.collab[IDcoll].giorni[tag].minuti+=temp.add(this.collab[IDcoll].lista[tag][h],IDcoll);
                        this.collab[IDcoll].giorni[tag].oreSTD=this.collab[IDcoll].lista[tag][h].oreSTD;
                        this.collab[IDcoll].giorni[tag].tipoSTR=this.collab[IDcoll].lista[tag][h].tipoSTR;
                    }

                    else {
                        //quindi la timbratura è in uscita
                        //se il blocco attivo ha già una timbratura in uscita salvalo e setta temp=false
                        //altrimenti aggiungi la timbratura al blocco attivo
                        if (temp.checkU()) {
                            this.collab[IDcoll].giorni[tag].blocks.push(temp);
                            temp=false;
                        }
                        else {
                            this.collab[IDcoll].giorni[tag].minuti+=temp.add(this.collab[IDcoll].lista[tag][h],IDcoll);
                            this.collab[IDcoll].giorni[tag].oreSTD=this.collab[IDcoll].lista[tag][h].oreSTD;
                            this.collab[IDcoll].giorni[tag].tipoSTR=this.collab[IDcoll].lista[tag][h].tipoSTR;
                        }
                    }
                }
            }
        }

        //l'ultimo blocco in sospeso
        if (temp) {
            //console.log(temp.draw());
            this.collab[lastcoll].giorni[lastday].blocks.push(temp);
        }
    }

    this.getHead=function() {

        var txt='<div style="width:100%;">';
            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">Entrata</div>';
            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">Uscita</div>';
            txt+='<div style="display:inline-block;width:33.3%;text-align:center;">Ore</div>';
        txt+='</div>';

        return txt;
    }

    this.draw=function(IDcoll) {

        var statoColl='OK';

        for (var tag in this.collab[IDcoll].giorni) {

            var txt='<div>';

                txt+='<div style="display:inline-block;width:40%;">';

                    //$('#alanContainer_'+IDcoll+'_'+tag).append(this.getHead());
                    txt+=this.getHead();

                    for (var index in this.collab[IDcoll].giorni[tag].blocks) {

                        txt+=this.collab[IDcoll].giorni[tag].blocks[index].draw();

                        tempst=this.collab[IDcoll].giorni[tag].blocks[index].getStato();

                        if (tempst!='OK') this.collab[IDcoll].giorni[tag].stato='KO';
                    }

                    //se le timbrature sono corrette ma la somma del tempo è diversa da quello che ci aspettiamo segnale errore di allinemento
                    if (tag!=this.today) {
                        if (this.collab[IDcoll].giorni[tag].minuti!=this.collab[IDcoll].giorni[tag].actual && this.collab[IDcoll].giorni[tag].stato!='KO') this.collab[IDcoll].giorni[tag].stato='ALL';
                    }

                txt+='</div>';

                txt+='<div style="display:inline-block;width:60%;vertical-align:top;';
                    //if (this.collab[IDcoll].giorni[tag].stato=='ALL') txt+='background-color:yellow;';
                txt+='">';

                    txt+='<div style="text-align:center;vertical-align:top;">Ore calcolate: ';

                        txt+='<span style="font-weight:bold;';
                            if (this.collab[IDcoll].giorni[tag].stato=='OK') txt+='color:black;';
                            else txt+='color:red;';
                        txt+='" >';

                            if (this.collab[IDcoll].giorni[tag].stato=='OK' || this.collab[IDcoll].giorni[tag].stato=='ALL') {
                                var tval=this.collab[IDcoll].giorni[tag].minuti/60;
                                txt+=tval.toFixed(2);
                            }
                            else txt+='errore';

                        txt+='</span>';

                        txt+=' - ore standard:&nbsp;<span style="font-weight:bold;">'+this.collab[IDcoll].giorni[tag].oreSTD+'</span>';
                        txt+=' (straordinario: '+this.collab[IDcoll].giorni[tag].tipoSTR+')';
                    txt+='</div>';

                    //AZIONI DI MODIFICA

                txt+='</div>';

            txt+='</div>';

            $('#alanContainer_'+IDcoll+'_'+tag).append(txt);

            $('#alan_dayinfo_'+tag+'_'+IDcoll).data('calc',this.collab[IDcoll].giorni[tag].minuti);

            //console.log(IDcoll+' '+tag+' '+$('#alan_dayinfo_'+tag+'_'+IDcoll).data('calc'));
            
            //ignora l'errore delle badgiate del giorno corrente in quanto potrebbero essere giustamente incomplete
            if (this.collab[IDcoll].giorni[tag].stato=='KO' && tag!=this.today) this.collab[IDcoll].stato='KO';
            else if (this.collab[IDcoll].giorni[tag].stato=='ALL') {
                this.collab[IDcoll].stato='ALL';
                //$('#alan_dayintest_'+tag+'_'+IDcoll).css('background-color','yellow');
            }
            else {
                //$('#alan_dayintest_'+tag+'_'+IDcoll).css('background-color','transparent');
            }

        }

        var obj=this;

        //colora le intestazioni dei giorni non coerenti (serve per valutare anche i giorni dove NON ci sono timbrature)
        $('div[id^="alan_dayinfo_"][data-idcoll="'+IDcoll+'"]').each(function() {

            if ($(this).data('tag')>=obj.today) return;

            if ( $(this).data('actual')!=$(this).data('calc') ) {
                $('#alan_dayintest_'+$(this).data('tag')+'_'+$(this).data('idcoll')).css('background-color','#ffff94');
                //non essendoci timbrature il giorno non esiste
                //obj.collab[IDcoll].giorni[$(this).data('tag')].stato='ALL';
                if (obj.collab[IDcoll].stato=='OK') obj.collab[IDcoll].stato='ALL';
            }
            //else $(this).css('background-color','transparent');

        });

    }


}