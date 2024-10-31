function nebulaFidel(index,param) {

    this.busy=false;

    this.index=index;

    try {
        this.param=$.parseJSON(window._nebulaMain.b64DecodeUnicode(param));
    }catch {
        this.param={};
    }

    this.generico=function() {

        $('#fidel_'+this.index+'_titolo').val('');
        $('#fidel_'+this.index+'_titolo').attr('disabled',false);
        $('#fidel_'+this.index+'_offerta').val('');
        $('#fidel_'+this.index+'_offerta').attr('disabled',false);
        $('#fidel_'+this.index+'_nota').val('');
        $('#fidel_'+this.index+'_scadenza').val('');
        $('#fidel_'+this.index+'_tipoid').val('');

        $('#fidel_'+this.index+'_tipo').html('');

    }

    this.scegli=function(obj) {

        var a=$.parseJSON(window._nebulaMain.b64DecodeUnicode($(obj).data('info')));

        this.generico();

        $('#fidel_'+this.index+'_titolo').val(a.titolo);
        $('#fidel_'+this.index+'_titolo').attr('disabled',true);
        $('#fidel_'+this.index+'_offerta').val(a.offerta[1]);
        $('#fidel_'+this.index+'_offerta').attr('disabled',true);
        $('#fidel_'+this.index+'_nota').val(a.nota);
        $('#fidel_'+this.index+'_tipoid').val(a.ID);

        $('#fidel_'+this.index+'_tipo').html(a.tipo);

        if (a.durata==0) {
            //console.log(parseInt(a.data_f.substring(0,4)));
            //console.log(parseInt(a.data_f.substring(4,6)));
            //console.log(parseInt(a.data_f.substring(6,8)));
            var date= new Date(parseInt(a.data_f.substring(0,4)),parseInt(a.data_f.substring(4,6))-1,parseInt(a.data_f.substring(6,8)),0,0,0,0);
        }
        else {
            var date = new Date();
            date.setDate(date.getDate() + a.durata);
        }

        $('#fidel_'+this.index+'_scadenza').val(window._nebulaMain.phpDate('Y-m-d',date.getTime()/1000));


        window['_fidel_'+this.index+'_divo'].selTab(1);

    }

    this.conferma=function() {

        if (this.busy) {
            alert('Registrazione precedente non è andata a buon fine.');
            return;
        }

        $('.fidel_label').css('color','black');

        var param={
            "index":this.index,
            "param":this.param,
            "tag":this.param.tag,
            "dms":this.param.dms,
            "utente":this.param.utente,
            "ben1":this.param.ben1,
            "ben2":this.param.ben2,
            "ID":$('#fidel_'+this.index+'_tipoid').val(),
            "titolo":$('#fidel_'+this.index+'_titolo').val().trim(),
            "offerta":$('#fidel_'+this.index+'_offerta').val().trim(),
            "scadenza":$('#fidel_'+this.index+'_scadenza').val(),
            "nota":$('#fidel_'+this.index+'_nota').val()
        }

        var error=false;

        if (param.tag==='undefined' || param.tag=='') {
            alert('Manca identificativo');
            return;
        }

        if (param.titolo==='undefined' || param.titolo.length<5) {
            $('#fidel_'+this.index+'_titolo_label').css('color','red');
            error=true;
        }

        if (param.offerta==='undefined' || param.offerta.length<5) {
            $('#fidel_'+this.index+'_offerta_label').css('color','red');
            error=true;
        }

        if (param.scadenza==='undefined' || param.scadenza=='') {
            $('#fidel_'+this.index+'_scadenza_label').css('color','red');
            error=true;
        }
        else {

            let today=new Date();
            let scad=new Date(param.scadenza);

            if (scad<today) {
                $('#fidel_'+this.index+'_scadenza_label').css('color','red');
                error=true;
            }
        }

        if (error) {
            alert('Ci sono degli errori');
            return;
        }

        //console.log(JSON.stringify(param));

        if (!confirm('Confermi la creazione del Voucher?')) return;

        this.busy=true;

        window._fidelTemp=this.index;

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/fidel/core/conferma.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaFidel_'+window._fidelTemp).html(ret);
                window['_fidel_'+window._fidelTemp].busy=false;
            }
        });
    }

    this.print=function() {

        var param={
            "index":this.index,
            "param":this.param
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/fidel/core/print.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                //var obj=$.parseJSON(ret);
                //obj.pdf=obj.pdf.replace('\\','');

                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf");
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);

            }
        });
    }

    this.usa=function(id) {

        var param={
            "index":this.index,
            "param":this.param,
            "voucher":id,
            "dms":this.param.dms,
            "utente":this.param.utente,
            "odl":this.param.odl
        }

        if (!confirm("Il Voucher sarà chiuso in riferimento all'attuale ordine di lavoro. OPERAZIONE NON ANNULLABILE.")) return;

        window._fidelTemp=this.index;

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/fidel/core/usa.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaFidel_'+window._fidelTemp).html(ret);
                
            }
        });

    }

    this.annulla=function(id) {

        var param={
            "index":this.index,
            "param":this.param,
            "voucher":id,
            "dms":this.param.dms,
            "utente":this.param.utente,
            "odl":this.param.odl
        }

        if (!confirm("Il Voucher sarà cancellato. OPERAZIONE NON ANNULLABILE.")) return;

        window._fidelTemp=this.index;

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/fidel/core/annulla.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#nebulaFidel_'+window._fidelTemp).html(ret);
                
            }
        });

    }

    this.getTT=function() {

        var param={
            "tt":$('#fidel_fidi_interface_search').val()
        }

        $('#fidel_fidi_interface_viewer').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/fidel/getTT.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#fidel_fidi_interface_viewer').html(ret); 
            }
        });

    }

    this.setTT=function(obj) {

        var param={
            "tt":$(obj).data('info'),
            "utente":window._nebulaMain.getMainLogged()
        }

        $('#fidel_fidi_interface_viewer').html(window._nebulaMain.setWaiter());

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/fidel/setTT.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#fidel_fidi_interface_viewer').html(ret); 
            }
        });
    }
} 