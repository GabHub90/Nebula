function nebulaOdl(param) {
    //questa classe fa da collante tra le classi che operano sull'odl

    //{"actualQuick":"0","mainView":"","data_i":"20211105","data_f":"20211105","wormhole":[{"inizio":"20211105","fine":"20211105","dms":"concerto","result":"false"}]}
    this.param=param;

    this.linker=new nebulaOdlLinker(param);
    this.body=new nebulaOdlBody(param);
    this.horse=new nebulaHorse('window._nebulaOdl.horse');

    //viene flaggato quando ci sono delle modifiche non salvate
    //viene valutato prima di cambiare contesto dell'odl
    this.flagEdit=false;

    this.closeOdl=function() {
        //da scrivere per chiudere l'odl quando si cambia galassia o sistema da dentro ad un odl
        //viene chiamato da MAINFUNC o SYSTEM...
    }

    this.selDiv=function(id) {

        $('.nebulaOdlMainDiv').hide();
        $('div[id^="odielleInfoHeadDiv"]').hide();

        this.param.mainView=id;

        $('#nebulaOdlMainDiv_'+id).show();
        $('#odielleInfoHeadDiv_'+id).show();
    }

    this.waiter=function() {

        txt='<div style="position:relative;width:100%;height:100%;text-align:center;top:50%;" >';
            txt+='<img style="height:40%;transform:translate(0px,-50%);" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    /*this.drawMain=function() {

        var txt="";

        for (var x in this.struttura) {
            txt+='<div id="nebulaOdlMainDiv_'+this.struttura[x]+'" class="nebulaOdlMainDiv" style="width:100%;height:100%;display:none;"></div>';
        }

        return txt;

    }*/

    this.drawAll=function() {

        //$('#nebulaOdlMain').html(this.drawMain());

        $('#nebulaOdlLinkerDiv').html(this.waiter);
        this.linker.drawHead('nebulaOdlLinkerDiv');

        /*$('#nebulaOdlMainDiv_odl').html(this.waiter);
        this.body.drawBody('nebulaOdlMainDiv_odl');*/

        this.body.setDates();

        this.selDiv('odl');

        this.buildDedalo();

    }

    this.refreshOdl=function() {

        var arr={
            "rif":$('#odielle_refresh_hidden_rif').val(),
            "dms":$('#odielle_refresh_hidden_dms').val(),
            "lista":$('#odielle_refresh_hidden_lista').val(),
        }

        window._nebulaApp.setArgs(arr);

        var param=window._nebulaApp.collectParams();

        $('#avalon_odielle').html(this.waiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/open_odl.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#avalon_odielle').html(ret);
            }
        });
    }

    this.checkFlagEdit=function() {

        if (this.flagEdit) {
            var temp=confirm("Ci sono modifiche non salvate che verranno perdute...");

            if (temp) {
                this.flagEdit=false;
            }
        }
        
        //se flag è true = operazione non ammessa)
        //se flag è false indicare che si può proseguire
        return this.flagEdit;
        
    }

    this.outRoutine=function() {
        //esegue delle operazioni in uscita dal contesto
        if (this.param.mainView=='linker') {

            $('#nebulaOdlLinkerDiv').html(this.waiter);
            this.linker.drawHead('nebulaOdlLinkerDiv');
        }
    }

    this.setOdl=function(flag) {

        if (this.checkFlagEdit()) return;

        this.outRoutine();

        //se flag è 1 => reload altrimenti basta solo switchare il DIV
        if (flag==0) {
            this.selDiv('odl');
        }

        $('div[id^="odielleSide"]').hide();
        $('div[id^="odielleBodyMain_"]').hide();

        $('#odielleSideMain').show();
        $('#odielleBodyMain_odl').show();

        this.param.mainView='odl';

    }

    this.setLinker=function() {

        if (this.checkFlagEdit()) return;

        $('#nebulaOdlMainDiv_linker').html(this.waiter());
        $('#odielleInfoHeadDiv_linker').html("");

        this.selDiv('linker');

        this.linker.setTlink();
        this.linker.drawHeadEdit('nebulaOdlLinkerDiv','nebulaOdlMainDiv_linker','odielleInfoHeadDiv_linker','linkerListaAbbinamenti');
        this.linker.drawBody('nebulaOdlMainDiv_linker');
        this.linker.drawInfoHead('odielleInfoHeadDiv_linker');

        this.param.mainView='linker';

    }

    this.setDedalo=function() {

        if (this.checkFlagEdit()) return;

        this.outRoutine();

        $('.nebulaOdlMainDiv').hide();
        $('div[id^="odielleInfoHeadDiv"]').hide();
        $('div[id^="odielleSide"]').hide();
        $('div[id^="odielleBodyMain_"]').hide();

        $('#nebulaOdlMainDiv_odl').show();
        $('#odielleInfoHeadDiv_dedalo').show();
        $('#odielleSideDedalo').show();
        $('#odielleBodyMain_dedalo').show();

        this.param.mainView='dedalo';

    }
    
    this.buildDedalo=function() {

        $('#odielleSideDedalo').html(this.waiter());

        var param={"prova":"urcaurca"};

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/body/build_dedalo.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                var obj=$.parseJSON(ret);

                if (obj) {

                    $('#odielleSideDedalo').html(atob(obj.lav));
                    $('#odielleBodyDedalo_Rit').html(atob(obj.rit));
                    $('#odielleBodyDedalo_Ric').html(atob(obj.ric));
                }
            }
        });

    }

    //////////////////////////////////////////////////////////////////////////////////////

    this.cercaVeicolo=function() {
        this.linker.cercaVeicolo();
    }

    this.cercaAnagrafica=function() {
        this.linker.cercaAnagrafica();
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    this.apriUtility=function() {
        $('#nebulaOdlContainerDiv_container').hide();
        $('#nebulaOdlContainerDiv_utility').show();
    }

    this.chiudiUtility=function() {
        $('#nebulaOdlContainerDiv_container').show();
        $('#nebulaOdlContainerDiv_utility').hide();

        $('#nebulaOdlContainerDiv_utility_body').html('');
    }

    this.apriMain2=function() {
        $('#odielleBodyMain_2_content').html('');
        $('#odielleBodyMain_1').hide();
        $('#odielleBodyMain_2').show();
    }

    this.chiudiMain2=function() {
        $('#odielleBodyMain_2_content').html('');
        $('#odielleBodyMain_1').show();
        $('#odielleBodyMain_2').hide();
    }

    this.apriGDM=function() {

        var param={
            "nebulaContesto":window._nebulaMain.contesto,
            "telaio":$('#odielle_apriGDM_icon').data('telaio'),
            "dms":$('#odielle_apriGDM_icon').data('dms')
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/open_gestione.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaOdlContainerDiv_utility_body').html(ret);

                window._nebulaOdl.apriUtility();
               
            }
        });
    }

    this.apriHorse=function(obj) {

        var temp=$.parseJSON(atob($(obj).data('info')));

        window._nebulaOdl.horse.set(temp);

        this.apriMain2();
        
        $('#odielleBodyMain_2_content').html(window._nebulaOdl.horse.draw());
    }

    this.apriComest=function(obj) {

        var param=$.parseJSON(atob($(obj).data('info')));

        if (!param) return;

        param.contesto=window._nebulaMain.contesto;
        param.nebulaFunzione=window._nebulaApp.getTagFunzione();

        this.apriMain2();

        $('#odielleBodyMain_2_content').html(this.waiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/archivio.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                $('#odielleBodyMain_2_content').html(ret);
               
            }
        });   
    }

    this.nuovaPraticaComest=function(obj) {

        if (!confirm('Verrà creata una nuova commessa esterna!')) return;

        var info=$.parseJSON(atob($(obj).data('info')));

        if (!info) {
            alert('Errore passaggio informazioni.');
            return;
        }

        var param={
            "targa":info.targa,
            "telaio":info.telaio,
            "descrizione":info.descrizione,
            "dms":info.dms,
            "odl":info.odl,
            "utente":window._nebulaMain.getMainLogged()
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/comest/core/open_new.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaOdlContainerDiv_utility_body').html('<div id="nebula_comest_main" style="width:100%;height:100%;">'+ret+'</div>');

                window._nebulaOdl.apriUtility();

                window._nebulaOdl.chiudiMain2();
            }
        });
    }

    this.switchLamEdit=function(lam) {

        $("#odielle_body_lam_edit_"+lam).toggle();
    }

    this.confirmLamEdit=function(rif,lam,dms,pren) {

        var param={
            "rif":rif,
            "lam":lam,
            "dms":dms,
            "pren":pren,
            "txt":$("#odielle_body_lam_edit_"+lam+'_txt').val()
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/edit_lam.php',
            "async": true,
            "cache": false,
            "data": {"param": param },
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                window._nebulaOdl.refreshOdl();
               
            }
        });
    }


}