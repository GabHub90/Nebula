function nebulaAlan() {

    this.prefix="";
    this.collab={};
    this.statoOverall="OK";

    //è il riferimento all'oggetto che instanzia la classe
    //se !=null è quello che descrive i metodi per l'aggiornamento delle pagine dopo la modifica delle timbrature
    this.caller=null;

    this.init=function(prefix,obj) {

        this.caller=obj;
        this.prefix=prefix;
        var collab={};

        $('input[id^="'+prefix+'_alan_collInfo_"]').each(function() {

            var idcoll=$(this).data('idcoll');

            collab[idcoll]={
                "stato":"OK"
            };

        });

        this.collab=collab;

    }

    this.getCollStato=function(IDcoll) {
        //return  $('input[id^="'+prefix+'_alan_collInfo"][stato-idcoll="'+IDcoll+'"]').data['stato'];
        return this.collab[IDcoll].stato;
    }

    this.getStatoverall=function() {
        return this.statoOverall;
    }

    this.evaluate=function() {

        var prefix=this.prefix;
        var obj=this;

        this.statoOverall='OK';

        $('div[id^="'+prefix+'_alan_dayintestTag_"]').css('background-color','transparent');

        for (var x in this.collab) {
            //echo '<input id="'.$this->prefix.'_alan_dayinfo_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" type="hidden" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-tag="'.$d.'" data-nominale="'.(($this->info['turno'][$d])?$this->info['turno'][$d]['nominale']:'').'" data-actual="'.(($this->info['turno'][$d])?$this->info['turno'][$d]['actual']:'').'" data-calc="'.$this->res[$d]['minuti'].'" data-stato="'.$this->res[$d]['stato'].'" />';

            obj.collab[x].stato='OK';

            var today=new Date().toISOString().replace('-', '').split('T')[0].replace('-', '');

            $('input[id^="'+prefix+'_alan_dayinfo_"][data-idcoll="'+x+'"]').each(function() {

                var tag=$(this).data('tag');
                //var actual=$(this).data('actual');
                //var calc=$(this).data('calc');
                var stato=$(this).data('stato');

                //console.log(x+' '+tag+' '+today+' '+stato);

                if (parseInt(tag)>=parseInt(today)) return;

                if (stato=='KO') {
                    $('#'+prefix+'_alan_dayintestTag_'+tag+'_'+x).css('background-color','#ffc1c1');
                    obj.collab[x].stato='KO';
                    obj.statoOverall='KO';
                }
                else if (stato=='ALL') {
                    $('#'+prefix+'_alan_dayintestTag_'+tag+'_'+x).css('background-color','#ffff92');
                    if (obj.collab[x].stato!='KO') {    
                        obj.collab[x].stato='ALL';
                    }
                    if (obj.statoOverall!='KO') {
                        obj.statoOverall='ALL';
                    }
                }

            });

        }

    }

    this.swapVerso=function(IDtimbratura,tag,IDcoll) {

        window._alanCaller=this.caller;

        var param={
            "IDTIMBRATURA":IDtimbratura,
            "prefix":this.prefix,
            "tag":tag,
            "IDcoll":IDcoll
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/alan/core/swap.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._alanCaller.refreshAlanDay(ret);
            }
        });

    }

    this.forza=function(a,b,tag,IDcoll) {

        var x=prompt('Inserisci le ore e la frazione di ore (8.25 = 8 e 1/4):');

        x=parseFloat(x).toFixed(2);

        if (isNaN(x) || x<0 || x>12) return;

        window._alanCaller=this.caller;

        var param={
            "IDa":a,
            "IDb":b,
            "qta":x,
            "tag":tag,
            "IDcoll":IDcoll
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/alan/core/forza.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._alanCaller.refreshAlanDay(ret);
            }
        });
 
    }

    this.annullaForza=function(a,b,tag,IDcoll) {

        if (a.substr(0,1)=='k') {
            this.cancellaK(a,tag,IDcoll);
            return;
        }

        window._alanCaller=this.caller;

        var param={
            "IDa":a,
            "IDb":b,
            "tag":tag,
            "IDcoll":IDcoll
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/alan/core/annulla_forza.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._alanCaller.refreshAlanDay(ret);
            }
        });
 
    }

    this.addK=function(tag,IDDIP,IDcoll) {

        var x=prompt('Inserisci le ore e la frazione di ore (8.25 = 8 e 1/4):');

        x=parseFloat(x).toFixed(2);

        if (isNaN(x) || x<0 || x>12) return;

        window._alanCaller=this.caller;

        var param={
            "d":tag,
            "IDDIP":IDDIP,
            "forza_minuti":parseInt(x*60),
            "IDcoll":IDcoll,
            "tag":tag
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/alan/core/addK.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._alanCaller.refreshAlanDay(ret);
            }
        });
    }

    this.cancellaK=function(a,tag,IDcoll) {

        window._alanCaller=this.caller;

        var param={
            "ID":a.substr(1),
            "tag":tag,
            "IDcoll":IDcoll
        }

        //alert(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/alan/core/cancellaK.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                window._alanCaller.refreshAlanDay(ret);
            }
        });

    }

    /////////////////////////////////////////////////////////////////////////////// 

    this.tempoCallEvent=function(IDcoll,tag,tipo) {

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].tempoSetColl(IDcoll);

        $('#tpo_eventCaller_'+tipo).click();

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].selDay(tag);
    }
}