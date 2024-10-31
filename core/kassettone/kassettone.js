function kassettone(tag) {

    this.tag=tag;

    this.val="";
    //mark viene usato per avere più bottoni che azionano la stessa lista
    //il valore viene poi preso da postSelect
    this.mark="";

    this.enable=true;

    //se si ha un solo bottone this.val corrisponde sempre al valore selezionato
    this.getVal=function() {
        return this.val;
    }

    this.setEnable=function(val) {
        this.enable=val;
    }

    this.setTitle=function(txt,mark) {
        $('#kassettone_button_image_'+mark).html(txt);
    }

    this.switch=function(mark) {

        if (!this.enable) return;

        var stato=$('#kassettone_lista_'+this.tag).css('display');

        if (stato=='none') {
            this.mark=mark;
            $('#kassettone_lista_'+this.tag).css('display','block');
            $('#kassettone_button_image_'+this.mark).css('background-size','80% 100%');
        }
        else {
            $('#kassettone_lista_'+this.tag).css('display','none');
            $('div[id^="kassettone_button_image_"]').css('background-size','100% 100%');
            this.val="";
            this.mark="";
        }
    }

    this.select=function(valore) {
        this.val=valore;
        $('#kassettone_lista_'+this.tag).css('display','none');
        $('div[id^="kassettone_button_image_"]').css('background-size','100% 100%');
        this.postSelect();
    }

    //scrive il bottone da JS (utile per più bottoni che insistono sulla stessa lista) 
    this.drawButton=function(titolo,mark) {

        var txt='<div id="kassettone_button_'+mark+'" style="position:relative;cursor:pointer;border:1px solid black;border-radius:5px;overflow:visible;padding:1px;text-align:center;font-weight:bold;';

        txt+='" onclick="window._kassettone_'+this.tag+'.switch(\''+mark+'\');" >';

            txt+='<div id="kassettone_button_image_'+mark+'" style="position:relative;width:100%;height:100%;background-image:url(http://'+location.host+'/nebula/core/kassettone/img/kasbak.png);background-size:100% 100%;background-repeat:no-repeat;border-radius:5px;background-position-x: 50%;" >';

                txt+=titolo;
            
            txt+='</div>';

        txt+='</div>';

        return txt;
    }

    this.postSelect=function() {

    }

}