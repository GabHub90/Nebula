function nebulaWorkshop() {

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.graffaPdf=function(reparto) {

        var rifs=[];

        $('input[id^="avalonPrenCheckbox"]:checked').each(function() {

            var t={
                "rif":$(this).data('rif'),
                "dms":$(this).data('dms')
            }

            rifs.push(t);
        });

        if (rifs.length==0) return;

        var param={
            "reparto":reparto,
            "rifs":rifs
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/avalon/core/graffa_pdf.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                
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

    this.inarrivo=function(reparto) {

        var d=$('#wsp_generale_inarrivo_data').val();

        if (!d || d=='') return;

        $('#wsp_generale_inarrivo').html(this.setWaiter());

        var param={
            "reparto":reparto,
            "d":d
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/inarrivo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#wsp_generale_inarrivo').html(ret);     
            }
        });
    }

    this.inofficina=function(reparto,cliente) {

        $('#wsp_generale_officina').html(this.setWaiter());
        $('wsp_generale_sospesi').html(this.setWaiter());

        var param={
            "reparto":reparto,
            "cliente":cliente,
            "visuale":"",
            "rc":"",
            "marca":""
        }

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/workshop/core/inofficina.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                var obj=$.parseJSON(ret);

                if (obj) {
                    var t=atob(obj.sospesi);
                    $('#wsp_generale_sospesi').html(t);
                    if (t!="") window._workshop_divo.setStato(1,'R');
                    else window._workshop_divo.setStato(1,'Y');

                    $('#wsp_generale_officina').html(atob(obj.officina));

                }
            }
        });
    }

    this.getGDM=function(telaio) {

        $('#workshop_gdm').html(this.setWaiter());

        var param={
            "telaio":telaio,
            "gdm_ambito":'gdmr'
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/gdm/open_officina.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                $('#workshop_gdm').html(ret);

            }
        });
    }
}