
function pitstopCode(funzione) {

    this.funzione=funzione;

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }


    this.nuovaLista=function(logged) {

        var arg={
            "ut_creazione":logged,
            "ut_elaborazione":logged,
            "reparto":$('#pitstop_newlista_reparto').val()
        }

        //console.log(JSON.stringify(arg));

        this.openLista(arg);
    }

    this.openLista=function(arg) {

        //arg=$.parseJSON(arg);

        var param={
            "ut_creazione":"",
            "ut_elaborazione":"",
            "reparto":""
        }

        for (var x in param) {
            if (arg[x] !== 'undefined') param[x]=arg[x];
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/pitstop/core/loader_lista.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#pitstop_utilityDivBody').html(ret);
                $('#pitstop_utilityDiv').show();
                $('#pitstop_liste').hide();
            }
        });

    }

    this.closeOperazioni=function() {
        $('#pitstop_utilityDivBody').html('');
        $('#pitstop_utilityDiv').hide();
        $('#pitstop_liste').show();
    }
    
    //////////////////////////////////////////////////

    this.loadOfficina=function(reparto) {

        var param={
            "reparto":reparto
        }

        var w=this.setWaiter();

        $('#pitstop_officina_saldi').html(w);
        $('#pitstop_officina_pren').html(w);
        $('#pitstop_officina_comm').html(w);


        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/pitstop/core/load_officina.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var d=new Date();
                var obj=$.parseJSON(ret);

                if (obj!== null) {

                    $('#pitstop_officina_saldi').html(atob(obj.saldi));
                    $('#pitstop_officina_pren').html(atob(obj.pren));
                    $('#pitstop_officina_comm').html(atob(obj.comm));
                }

                var g=d.getDate();
                var m=d.getMonth()+1;

                $('#pitstop_officina_reload').html("Aggiornato: "+(g>9?g:'0'+g)+'/'+(m>9?m:'0'+m)+'/'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes());
            }
        });
    }

    this.loadBanco=function(reparto) {

        var param={
            "reparto":reparto,
            "operatore":$('#pitstop_banco_operatore').val()
        }

        var w=this.setWaiter();

        $('#pitstop_banco_saldi').html(w);
        $('#pitstop_banco_aperti').html(w);
        $('#pitstop_banco_liste').html(w);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/pitstop/core/load_banco.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                var d=new Date();
                var obj=$.parseJSON(ret);

                if (obj!== null) {

                    $('#pitstop_banco_saldi').html(atob(obj.saldi));
                    $('#pitstop_banco_aperti').html(atob(obj.aperti));
                    $('#pitstop_banco_liste').html(atob(obj.liste));
                }

                var g=d.getDate();
                var m=d.getMonth()+1;

                $('#pitstop_banco_reload').html("Aggiornato: "+(g>9?g:'0'+g)+'/'+(m>9?m:'0'+m)+'/'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes());
            }
        });
    }

    this.resetFiltroBanco=function() {

        $('div[id^="pitstop_banco_aperti_div_"]').show();
        $('div[id^="pitstop_banco_liste_div_"]').show();
        $('#pitstop_banco_filtro_ragsoc').val("");
    }

    this.setFiltroBanco=function() {

        var str=$('#pitstop_banco_filtro_ragsoc').val();

        if (str=="") return;

        $('div[id^="pitstop_banco_aperti_div_"]').each(function(){

            var ragsoc=$(this).data('ragsoc');
            var pos=ragsoc.search(str);

            if (typeof pos !== 'undefined' && pos!=-1) $(this).show();
            else $(this).hide();
        });

        $('div[id^="pitstop_banco_liste_div_"]').each(function(){

            var ragsoc=$(this).data('ragsoc');
            var pos=ragsoc.search(str);

            if (typeof pos !== 'undefined' && pos!=-1) $(this).show();
            else $(this).hide();
        });
    }

}