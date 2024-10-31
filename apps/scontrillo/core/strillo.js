function scontrillo(cassa,chiusura) {

    this.busy=false;

    this.actualCassa=cassa;
    this.actualChiusura=chiusura;

    this.actualAperto="";
    this.tipiIncasso={};

    this.actualIncasso=[];

    this.setIncassi=function(b64) {
        this.tipiIncasso=$.parseJSON(window._nebulaMain.base64ToUtf8(b64));
    }

    this.unsetAperto=function() {
        $('div[id^="strillo_incasso"]').html('');
        this.actualAperto="";
        this.actualIncasso=[];
    }

    this.setAperto=function(id) {
        this.unsetAperto();

        this.actualAperto=id;

        $('#strillo_incasso_select_'+id).html(this.arrIncasso());

        //txt='<img style="width:40px;cursor:pointer;top: -15px;position: relative;" src="http://'+location.host+'/nebula/apps/scontrillo/img/dollari.png" />';

        let img = document.createElement('img');

        img.style="width:45px;cursor:pointer;top: -20px;position: relative;";
        img.src="http://"+location.host+"/nebula/apps/scontrillo/img/dollari.png";
        img.onclick=function(event) {
            window._strillo.confermaIncasso();
            //event.stopPropagation();
        }
        
        $('#strillo_incasso_img_'+id).append(img);
        /*$('#strillo_incasso_img_'+id).click(function(event){
            window._scontrillo.confermaIncasso();
            event.stopPropagation();
        });*/

        /*document.getElementById('strillo_incasso_select').addEventListener('click', function(event) {
            event.stopPropagation();
          });*/
    }

    this.addIncasso=function() {

        val=(this.actualIncasso.length==0)?$('#strillo_aperto_importo_'+this.actualAperto).data('importo'):0;

        temp={
            "val":val,
            "incasso":""
        }

        this.actualIncasso.push(temp);
    }

    this.subIncasso=function(id) {

        temp=[];

        for (x in this.actualIncasso) {
            if (x==id) continue;
            temp.push(this.actualIncasso[x]);
        }

        this.actualIncasso=temp;
    }

    this.aggiungiIncasso=function() {
        this.addIncasso();
        $('#strillo_incasso_select_'+this.actualAperto).html(this.arrIncasso());
    }

    this.togliIncasso=function(id) {
        this.subIncasso(id);
        $('#strillo_incasso_select_'+this.actualAperto).html(this.arrIncasso());
    }

    this.arrIncasso=function() {

        if (this.actualIncasso.length==0) {
            this.addIncasso();
        }

        this.busy=false;

        txt="";

        for (x in this.actualIncasso) {
            txt+='<div style="width:100%;height:35px;" >';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;box-sizing-border-box;text-align:left;" >';
                    txt+='<input id="strillo_incasso_'+x+'" type="text" style="width:95%;text-align:center;background-color:initial;height:25px;border: solid;border-color: #888888;" value="'+(this.actualIncasso[x].val==0?"":this.actualIncasso[x].val)+'" />';
                txt+='</div>';
                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;box-sizing-border-box;text-align:left;" >';
                    txt+='<select id="strillo_incasso_select_'+x+'" style="position:relative;width:90%;height:25px;text-align:center;background-color:inherit;font-weight:bold;">';
                        txt+='<option value="">Tipo incasso...</option>';
                        for (y in this.tipiIncasso ) {
                            txt+='<option value="'+this.tipiIncasso[y].codice+'" >'+this.tipiIncasso[y].tag+'</option>';
                        }
                    txt+='</select>';
                txt+='</div>';
                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;box-sizing-border-box;text-align:left;height:25px;" >';
                    if (x==0) {
                        txt+='<img style="width:25px;cursor:pointer;position:relative;top: 50%;transform: translate(0px, -50%);" src="http://'+location.host+'/nebula/apps/scontrillo/img/piu.png" onclick="window._strillo.aggiungiIncasso();" />';
                    }
                    else {
                        txt+='<img style="width:25px;cursor:pointer;position:relative;top: 50%;transform: translate(0px, -50%);" src="http://'+location.host+'/nebula/apps/scontrillo/img/meno.png" onclick="window._strillo.togliIncasso(\''+x+'\');"/>';
                    }
                txt+='</div>';
            txt+='</div>';
        }

        return txt;
    }

    this.confermaIncasso=function() {

        if (this.busy) return;

        var error={
            "incasso":{
                "flag":false,
                "tag":' - Incasso non specificato - '
            },
            "valore":{
                "flag":false,
                "tag":' - Ci sono valori non numerici - '
            },
            "totale":{
                "flag":false,
                "tag":' - Totale errato -'
            }
        }

        var v=$('#strillo_aperto_importo_'+this.actualAperto).data('importo');
        v=v.replace(/,/g, '.');
        var tot=parseFloat(v);

        for (x in this.actualIncasso) {

            this.actualIncasso[x].incasso=$('#strillo_incasso_select_'+x).val();
            if (this.actualIncasso[x].incasso==='undefined' || this.actualIncasso[x].incasso=='') error.incasso.flag=true;

            let v=$('#strillo_incasso_'+x).val();
            v=v.replace(/,/g, '.');

            //alert(v);

            if (isNaN(v)) error.valore.flag=true;
            else {
                this.actualIncasso[x].val=parseFloat(v).toFixed(2);
                tot=(tot-this.actualIncasso[x].val).toFixed(2);
                //alert(tot);
            }
        }

        if (tot!=0) error.totale.flag=true;

        let errorTxt="";

        for (x in error) {
            if (error[x].flag) {
                errorTxt+=error[x].tag;
            }
        }

        if (errorTxt!="") {
            alert(errorTxt);
            return;
        }

        this.busy=true;

        let res={
            "utente":window._nebulaMain.getMainLogged(),
            "rif_dms":this.actualAperto,
            "cassa":this.actualCassa,
            "chiusura":this.actualChiusura,
            "obj":$('#strillo_aperto_div_'+this.actualAperto).data('movimento'),
            "incassi":this.actualIncasso
        }

        console.log(JSON.stringify(res));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/scontrillo/core/registra_movimento.php',
            "async": true,
            "cache": false,
            "data": {"param": res},
            "type": "POST",
            "success": function(ret) {
                
                console.log(ret);
                window._nebulaApp.ribbonExecute();
                     
            }
        });
    }
}