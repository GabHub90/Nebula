window.nebulaChimeClass=class nebulaChime {

    //callback="";
    //args={};

    busy=false;

    constructor(callback,args){
        this.callback=callback;
        this.args=args;
    }

    //da definire dall'esterno
    /*exec() {
        window[this.callback](this.args);
    }*/

    setWaiter() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:35px;height:35px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    cerca(obj) {

        var param={
            "reparto":$(obj).data('reparto'),
            "pren":$(obj).data('pren'),
            "day":$(obj).data('day'),
            "pratica":$(obj).data('pratica'),
            "dms":$(obj).data('dms'),
            "ID":$('#nebulaChime_tipoLista').val()
        }

        //console.log(JSON.stringify(param));

        $('#nebulaChime_body').html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chime/lista.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $('#nebulaChime_body').html(ret);

            }
        });

    }

    setAvail(obj) {

        var o=$(obj).data('orig');
        var e=$(obj).data('eff');

        if (o=='enabled') {
            if (e=='enabled') e='escluso';
            else e='enabled';
        }

        if (o=='inviato') {
            if (e=='inviato') e='enabled';
            else e='inviato';
        }

        this.setAvailIcon($(obj).data('pratica'),e);
    }

    setAvailIcon(p,e) {

        //alert(p+ ' '+e);

        if (e=='inviato') {
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').attr('src','http://'+location.host+'/nebula/apps/avalon/img/call_G.png');
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').data('eff',e);
            $('div[id^="chime_msg_"][data-pratica="'+p+'"]').css('color','#777777');
        }

        else if (e=='escluso') {
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').attr('src','http://'+location.host+'/nebula/apps/avalon/img/call_R.png');
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').data('eff',e);
            $('div[id^="chime_msg_"][data-pratica="'+p+'"]').css('color','#777777');
        }

        if (e=='enabled') {
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').attr('src','http://'+location.host+'/nebula/apps/avalon/img/call_V.png');
            $('img[id^="chime_availIcon_"][data-pratica="'+p+'"]').data('eff',e);
            $('div[id^="chime_msg_"][data-pratica="'+p+'"]').css('color','green');
        }
    }

    invia(reparto,pren) {

        if (this.busy) return;

        var param={
            "reparto":reparto,
            "pren":pren,
            "lista":[]
        };

        $('img[id^="chime_availIcon_"]').each(function(){

                if ($(this).data('eff')!='inviato') {

                var obj={
                    "pratica":$(this).data('pratica'),
                    "dms":$(this).data('dms'),
                    "rif":$(this).data('rif'),
                    "stato":$(this).data('stato'),
                    "pren":$(this).data('pren'),
                    "utente":window._nebulaMain.getMainLogged(),
                    "chime":{
                        "modello":$(this).data('modello'),
                        "msg":$('div[id^="chime_msg_"][data-pratica="'+$(this).data('pratica')+'"]').html(),
                        "contatto":$('input[name="chime_contatto_'+$(this).data('pratica')+'"]:checked').val(),
                        "stato":$(this).data('eff')
                    }
                }

                param.lista.push(obj);
            }

        });

        $('#chime_button').html(this.setWaiter());

        //console.log(JSON.stringify(param));

        this.busy=true;

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chime/sender.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                window._nebulaChime.exec();

            }
        });

    }

}