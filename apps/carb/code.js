
function carbCode(funzione) {

    this.funzione=funzione;

    this.closeMain=function() {
        //$('#carb_utilityDivBody').html("");
        $('#carb_mainDiv_body').html("");
        //$('#carb_utilityDiv').hide();
        $('#carb_mainDiv').hide();
    }

    this.buonoNew=function(reparto,dms,id_esec) {

        var param={
            "ambito":'new',
            "reparto":reparto,
            "dms":dms,
            "id_esec":id_esec
        }

        $('#carb_mainDiv_body').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif"/></div>');
        $('#carb_mainDiv').show();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/buono_edit.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

               $('#carb_mainDiv_body').html(ret);
               //$('#carb_utilityDiv').hide();
                     
            }
        });
    }

    this.buonoEdit=function(ambito,id,id_esec) {

        var param={
            "ambito":ambito,
            "ID":id,
            "id_esec":id_esec
        }

        $('#carb_mainDiv_body').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif"/></div>');
        $('#carb_mainDiv').show();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/buono_edit.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

               $('#carb_mainDiv_body').html(ret);
               //$('#carb_utilityDiv').hide();
                
            }
        });
    }

    this.carbCercaAnnulla=function(id_annullo) {

        var numero=$('#carb_strumenti_annullo').val();

        var errore=false;
        var pattern=/\D/;

        if (!numero || numero=="") errore=true;
        else if (pattern.test(numero)) errore=true;

        if (errore) {
            alert('Valore inserito non valido');
            return;
        }

        var param={
            'ID':numero,
            'id_annullo':id_annullo
        }

        $('#carb_mainDiv_body').html('<div style="text-align:center;"><img style="width:50px;height:50px;" src="http://'+location.host+'/nebula/main/img/busy.gif"/></div>');
        $('#carb_mainDiv').show();

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/carb/core/buono_elimina.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

               $('#carb_mainDiv_body').html(ret);
                
            }
        });
    }

}