function nebulaHorse(prefix) {

    this.prefix=prefix;

    this.info={
        "targa":"",
        "telaio":"",
        "marca":"",
        "descrizione":"",
        "cavaliere_flag":1,
        "cavaliere_intest":"",
        "cavaliere_util":""
    }

    this.res="";

    this.set=function(a) {

        for(var x in a) {
            if (this.info[x]!=='undefined') this.info[x]=a[x];
        }
    }

    this.draw=function() {

        this.res='';

        this.res+='<div style="width:100%;height:15%;">';

            this.drawHead();

        this.res+='</div>';

        this.res+='<div style="width:100%;height:75%;overflow:scroll;overflow-x:hidden;">';
        
            if (this.info.cavaliere_flag==1) {
                this.drawCavaliere();
            }

        this.res+='</div>';
        
        return this.res;
    }

    this.drawHead=function() {

        this.res+='<div style="position:relative;width:95%;border:1px solid black;padding:3px;box-sizing:border-box;background-color:#dddddd;">';

            this.res+='<div style="position:relative;width:100%;" >';
                this.res+='<div style="position:relative;display:inline-block;vertical-align:top;width:40%;">';
                    this.res+='Targa:<span style="font-weight:bold;margin-left:10px;">'+this.info.targa+'</span>';
                this.res+='</div>';
                this.res+='<div style="position:relative;display:inline-block;vertical-align:top;width:60%;">';
                    this.res+='Telaio:<span style="font-weight:bold;margin-left:10px;">'+this.info.telaio+'</span>';
                this.res+='</div>';
            this.res+='</div>';

            this.res+='<div style="position:relative;width:100%;" >';
                this.res+='<div style="position:relative;display:inline-block;vertical-align:top;width:40%;">';
                    this.res+='Marca:<span style="font-weight:bold;margin-left:10px;">'+this.info.marca+'</span>';
                this.res+='</div>';
                this.res+='<div style="position:relative;display:inline-block;vertical-align:top;width:60%;">';
                    this.res+='<span style="font-weight:bold;margin-left:10px;">'+this.info.descrizione+'</span>';
                this.res+='</div>';
            this.res+='</div>';

        this.res+='</div>';

    }

    this.drawCavaliere=function() {

        this.res+='<div style="position:relative;width:95%;border-bottom:2px solid black;box-sizing:border-box;margin-top:10px;">';

            this.res+='<div style="position:relative;font-weight:bold;">Cavaliere:</div>';

            this.res+='<div style="position:relative;margin-top:10px;height:50px;" >';
                this.res+='<div style="position:relative;display:inline-block;width:20%;text-align:center;vertical-align:middle;line-height:50px;height:50px;">';
                    this.res+='<input id="nebulaHorse_cavaliere_flag1" name="nebulaHorse_cavaliere_flag" type="radio" checked />';
                this.res+='</div>';
                this.res+='<div style="position:relative;display:inline-block;width:80%;">';
                    this.res+='<div style="position:relative;font-size:1.2em;">'+this.info.cavaliere_intest+'</div>';
                    this.res+='<div style="position:relative;font-size:1em;">'+this.info.cavaliere_util+'</div>';
                this.res+='</div>';
            this.res+='</div>';

            this.res+='<div style="position:relative;height:50px;" >';
                this.res+='<div style="position:relative;display:inline-block;width:20%;text-align:center;vertical-align:middle;line-height:50px;height:50px;">';
                    this.res+='<input id="nebulaHorse_cavaliere_flag2" name="nebulaHorse_cavaliere_flag" type="radio" />';
                this.res+='</div>';
                this.res+='<div style="position:relative;display:inline-block;width:80%;">';
                    this.res+='<div style="position:relative;">';
                        this.res+='<input id="nebulaHorse_cavaliere_intest" type="text" style="width:80%;" maxlenght="40" />';
                    this.res+='</div>';
                    this.res+='<div style="position:relative;margin-top:5px;">';
                        this.res+='<input id="nebulaHorse_cavaliere_util" type="text" style="width:80%;" maxlenght="40" />';
                    this.res+='</div>';
                this.res+='</div>';
            this.res+='</div>';

            this.res+='<div style="position:relative;margin-top:10px;text-align:right;margin-bottom:10px;" >';
                this.res+='<button style="margin-right:20px;" onclick="'+this.prefix+'.stampaCavaliere();" >Stampa Cavaliere</button>';
            this.res+='</div>';

        this.res+='</div>';

    }

    this.stampaCavaliere=function() {

        var param={
            "targa":this.info.targa,
            "telaio":this.info.telaio,
            "marca":this.info.marca,
            "descrizione":this.info.descrizione,
            "intest":"",
            "util":""
        }

        if ($('#nebulaHorse_cavaliere_flag1').is(":checked")) {
            param.intest=this.info.cavaliere_intest;
            param.util=this.info.cavaliere_util;
        }
        else {
            param.intest=$('#nebulaHorse_cavaliere_intest').val();
            param.util=$('#nebulaHorse_cavaliere_util').val();
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/horse/stampa_cavaliere.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);
                
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

}