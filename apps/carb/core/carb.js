function nebulaCarb(tag) {

    this.form_tag=tag;

    this.closeUtil=function() {
        $('#carb_utilityDivBody').html("");
        $('#carb_utilityDiv').hide();
        $('#carb_form').show();
    }

    this.setTelaio=function(tipo) {

        if (tipo=="NEBULA") {
            $("#"+this.form_tag+"_gestione").val("NEBULA");
            $("#"+this.form_tag+"_veicolo").val(0);
            $("#"+this.form_tag+"_targa").val("");
            $("#"+this.form_tag+"_telaio").val("");
            $("#"+this.form_tag+"_des_veicolo").val("");
            $("#carb_veicoloDiv_targa").html("");
            $("#carb_veicoloDiv_telaio").html("");
            $("#carb_veicoloDiv_des_veicolo").html("");
            $("#carb_veicoloDiv_img_veicolo").html("");
            $("#carb_veicoloDiv_main").show();
        }

        if (tipo=="TANICA") {
            $("#"+this.form_tag+"_gestione").val("TANICA");
            $("#"+this.form_tag+"_veicolo").val(0);
            $("#"+this.form_tag+"_targa").val("");
            $("#"+this.form_tag+"_telaio").val("TANICA");
            $("#"+this.form_tag+"_des_veicolo").val("");
            $("#carb_veicoloDiv_main").hide();
        }

        window['_js_chk_'+this.form_tag].js_chk();    
    };

    this.cercaTT=function(reparto,dms) {

        var str=$('#carb_tt').val();

        if (!str || str.lenght<3) {
            alert('Errore stringa di ricerca');
            return;
        }

        var param={
            "str":str,
            "reparto":reparto,
            "dms":dms
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/lista_tt.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#carb_utilityDivBody').html(ret);
                $('#carb_form').hide();
                $('#carb_utilityDiv').show();
            }
        });

    }

    this.getTT=function(veicolo,targa,telaio,des_veicolo) {

        $('#carb_tt').val('');

        $('input[js_chk_'+this.form_tag+'_tipo="veicolo"]').val(veicolo);
        $('input[js_chk_'+this.form_tag+'_tipo="targa"]').val(targa);
        $('input[js_chk_'+this.form_tag+'_tipo="telaio"]').val(telaio);
        $('input[js_chk_'+this.form_tag+'_tipo="des_veicolo"]').val(atob(des_veicolo));

        $('#carb_veicoloDiv_targa').html(targa);
        $('#carb_veicoloDiv_telaio').html(telaio);
        $('#carb_veicoloDiv_des_veicolo').html(atob(des_veicolo).substring(0,25));

        var txt='<img style="width:20px;height:20px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/carb/img/trash.png" onclick="window._nebulaCarb.delTT();" />';

        $('#carb_veicoloDiv_img_veicolo').html(txt);
        
        this.closeUtil();

        window['_js_chk_'+this.form_tag].js_chk();
    }

    this.delTT=function() {

        $('input[js_chk_'+this.form_tag+'_tipo="veicolo"]').val("");
        $('input[js_chk_'+this.form_tag+'_tipo="targa"]').val("");
        $('input[js_chk_'+this.form_tag+'_tipo="telaio"]').val("");
        $('input[js_chk_'+this.form_tag+'_tipo="des_veicolo"]').val("");

        $('#carb_veicoloDiv_targa').html("");
        $('#carb_veicoloDiv_telaio').html("");
        $('#carb_veicoloDiv_des_veicolo').html("");
        $('#carb_veicoloDiv_img_veicolo').html("");

        window['_js_chk_'+this.form_tag].js_chk();
    }

    this.updateCausale=function() {

        this.checkNota();

        window['_js_chk_'+this.form_tag].js_chk();
    }

    this.checkNota=function() {

        var obj=$('#'+this.form_tag+'_causale option:selected');

        if (obj.data('ris')==1) {
            $('#'+this.form_tag+'_flag_ris').prop('checked',true);
            $('input[js_chk_'+this.form_tag+'_tipo="flag_ris"]').val(1);
        }

        if (!$('#'+this.form_tag+'_flag_ris').prop('checked')) $('input[js_chk_'+this.form_tag+'_tipo="flag_ris"]').val(0);

        if (obj.data('nota')==1) {
            $('#'+this.form_tag+'_flag_nota').val(1);
        }
        else $('#'+this.form_tag+'_flag_nota').val(0);

        if ($('input[js_chk_'+this.form_tag+'_tipo="flag_ris"]').val()==1) $('#'+this.form_tag+'_flag_nota').val(1);
        else if ($('input[js_chk_'+this.form_tag+'_tipo="gestione"]').val()=='TANICA') $('#'+this.form_tag+'_flag_nota').val(1);
        //else $('#'+this.form_tag+'_flag_nota').val(0);

    }

    this.risarcisci=function(id) {

        if (!confirm('Il buono è stato risarcito?')) return;

        var param={
            "id_ris":$('#'+this.form_tag+'_id_esec').val(),
            "ID":id,
            "stato":"risarcito"
        }

        window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeMain();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/risarcisci.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaApp.ribbonExecute();
            }
        });
    }

    this.noris=function(id) {

        var str=prompt('Nota mancato risarcimento:');

        if (!str || str=='') return;

        param={
            "id_ris":$('#'+this.form_tag+'_id_esec').val(),
            "ID":id,
            "stato":"pagato",
            "nota_ris":str
        }

        window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeMain();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/risarcisci.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaApp.ribbonExecute();
            }
        });

    }

    this.completa=function() {

        //in sostanza equivale a salva ma cambiando lo stato
        var param=window['_js_chk_'+this.form_tag].scrivi();

        if (!param) return;

        if ($('#'+this.form_tag+'_flag_ris').prop('checked')) $('#'+this.form_tag+'_stato').val('daris');
        else $('#'+this.form_tag+'_stato').val('completato');

        this.salva();

    }

    this.salva=function() {

        var param=window['_js_chk_'+this.form_tag].scrivi();

        if (!param) return;

        if (param.pieno=="") param.pieno='0';

        if (param.stato=="") param.stato='creato';

        window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeMain();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/salva.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaApp.ribbonExecute();

            }
        });
    }

    this.stampa=function() {

        var param=window['_js_chk_'+this.form_tag].scrivi();

        if (!param) return;

        if (param.pieno=="") param.pieno='0';

        //pieno ha la precedenza sul risarcimento quindi un pieno da risarcire
        //viene prima completato e poi risulterà da risarcire. 
        if (param.pieno=="1") param.stato='dacompletare';
        else if (param.flag_ris=="1") param.stato='daris';
        else param.stato='stampato';

        window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeMain();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/stampa.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                var obj=$.parseJSON(ret);

                obj.pdf=obj.pdf.replace('\\','');

                if (obj.stato==0) alert ("Errore database");

                else {

                    var a = document.createElement('a');
                    // Do it the HTML5 compliant way
                    var blob = window._nebulaMain.base64ToBlob(obj.pdf, "application/pdf");
                    var url = window.URL.createObjectURL(blob);
                    a.href = url;
                    a.target="_blank";
                    a.click();
                    window.URL.revokeObjectURL(url);

                    window._nebulaApp.ribbonExecute();

                }

            }
        });
    }

    this.cancella=function(id) {

        if (!confirm("Verrà cancellato il buono "+id+". L'operazione NON è recuperabile." )) return;

        var param={
            "ID":id
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/cancella.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaApp.ribbonExecute();

            }
        });

    }

    this.annulla=function(id) {

        var str=prompt('Nota di annullamento:');

        if (!str || str=='') return;

        var param={
            "id_annullo":$('#'+this.form_tag+'_id_esec').val(),
            "ID":id,
            "stato":"annullato",
            "nota_annullo":str
        }

        window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].closeMain();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/annulla.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);

                window._nebulaApp.ribbonExecute();
            }
        });
    }

}