
function grentCode(funzione) {

    this.funzione=funzione;

    this.chiudiVei=function() {

        $('div[ID^="grent_veiblock_"]').css('background-color','transparent');
        $('div[ID^="grent_noresblock_"]').css('background-color','transparent');
        $('#grent_main').html('');

    }

    this.apriResetForm=function() {
        $('#grent_manage_reset').hide();
        $('#grent_manage_add_reset').hide();
        $('#grent_manage_reset_form').show();
    }

    this.chiudiResetForm=function() {
        $('#grent_manage_reset').show();
        $('#grent_manage_add_reset').show();
        $('#grent_manage_reset_form').hide();
    }

    this.selVei=function(ID,rent,marca,vei) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "tipoRent":rent,
            "marca":marca,
            "rif":vei
        }

        $('div[ID^="grent_veiblock_"]').css('background-color','transparent');
        $('div[ID^="grent_noresblock_"]').css('background-color','transparent');
        $('#'+ID).css('background-color','#ffe0ff');

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/grent/core/sel_vei.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#grent_main').html(ret);
            }
        });

    }

    this.selManage=function(ID,rent,marca,vei,grent) {

        var param={
            "logged":window._nebulaMain.getMainLogged(),
            "tipoRent":rent,
            "marca":marca,
            "rif":vei,
            "grent_id":grent
        }

        $('div[ID^="grent_veiblock_"]').css('background-color','transparent');
        $('div[ID^="grent_noresblock_"]').css('background-color','transparent');
        $('#'+ID).css('background-color','#ffe0ff');

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/grent/core/manage_vei.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#grent_main').html(ret);
            }
        });

    }

    this.addVei=function(rent) {

        var param={
            "tipoRent":rent
        };

        $.ajax({
            "url": "http://"+location.host+"/nebula/apps/grent/core/add_vei.php",
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                //console.log(ret);
                $('#grent_main').html(ret);
            }
        });

    }

    this.getTT=function() {

        var str=$('#grent_tt').val();

        if (!str || str.lenght<3) {
            alert('Errore stringa di ricerca');
            return;
        }

        var param={
            "str":str,
            "dms":"infinity"
        }

        $('#grent_addvei_main').html('<div style="text-align:center;margin-top:20px;width:100%;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif" /></div>');

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/lista_tt.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#grent_addvei_main').html(ret);
            }
        });
    }

    this.selectVeicolo=function(rif,marca,targa,dms) {

        var param={
            "rif":rif,
            "marca":marca,
            "lista":$('#grent_form_rent').val(),
            "dms":dms
        }

        if (!param.lista || param.lista=='') {
            alert('Errore lista');
            return;
        }

        if (!confirm("Confermi l'inserimento della vettura "+targa+" in data odierna?")) return;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/insert_veicolo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var temp=$.parseJSON(ret);

                if (!temp) alert('Errore Inserimento');
                else if (temp.res=='ko') alert(temp.error);
                else {
                    window._nebulaApp.ribbonExecute();
                }
            }
        });
    }

    this.editVeiNote=function(grent) {

        var txt=$('#grent_reset_vei_nota').val();

        if (!confirm('Vuoi modificare la nota del veicolo?')) return;

        var param={
            "grent_id":grent,
            "txt":txt
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/grent/core/edit_vei_nota.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                window._nebulaApp.ribbonExecute();
            }
        });
    }

}