function gommaio(id) {

    this.id=id;

    this.gomme=[];
    this.importo={};

    this.pfu=2.50;
    this.iva=1.22;

    this.set=function() {

        var val=$('#gommaio_vei_'+this.id).val();

        var config=$.parseJSON(atob($('#gommaio_vei_'+this.id+' option:selected').data('config')));

        //console.log(JSON.stringify(config));
        //{"dms":"infinity","mano":"40","tempo":"35","marg":"20"}

        for (var x in config) {
            $('#gommaio_'+x+'_'+this.id).val(config[x]);
        }

        this.calcola();

    }

    this.trovaSconto=function(articolo) {
        return 40;
    }

    this.calcola=function() {

        this.importo={
            "qta":0,
            "pneumatici":0.00,
            "montaggio":0.00,
            "tasse":this.pfu.toFixed(2),
            "totale":0.00
        }

        var temp=0;
        var flagListino=true;

        var txt='<label>Pneumatici:</label>';

        txt+='<div style="margin-top:5px;">';

            for (var x in this.gomme) {
                this.importo.qta+=parseInt(this.gomme[x].qta);

                txt+='<div style="position:relative;width:95%;height:30px;margin-top:5px;margin-bottom:5px;padding:3px;box-sizing:border-box;" >';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:center;line-height:30px;width:7%;">';
                        txt+='<div style="vertical-align:middle;">'+this.gomme[x].qta+' x</div>';
                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:57%;">';
                        txt+='<div style="font-size:0.8em;">';
                            txt+='<span>'+this.gomme[x].articolo+' - '+this.gomme[x].des_marca+'</span>';
                            txt+='<img style="position:relative;width:10px;height:10px;margin-left:10px;cursor:pointer;" src="http://'+location.host+'/nebula/apps/sthor/img/sub.png" onclick="window._gom_'+this.id+'.elimina('+x+');" />';
                        txt+='</div>';
                        txt+='<div style="font-size:0.8em;">'+this.gomme[x].descr_articolo.substr(0,32)+'</div>';
                    txt+='</div>';

                    var img='Y.png';
                    if (parseInt(this.gomme[x].giacenza)<parseInt(this.gomme[x].qta)) img='R.png';
                    else if (parseInt(this.gomme[x].dispo)<parseInt(this.gomme[x].qta)) img='G.png';
                    else if (parseInt(this.gomme[x].dispo)>=parseInt(this.gomme[x].qta)) img='V.png';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:right;line-height:30px;width:15%;">';

                        var scmar=Math.ceil(($('#gommaio_marg_'+this.id).val()/this.gomme[x].listino)*100);
                        //var sc=this.trovaSconto(this.gomme[x].articolo)-scmar;
                        var sc=this.gomme[x].scontoAC-scmar;
                        if (sc<0) sc=0;
                        this.gomme[x].sconto=(sc/100).toFixed(2);

                        var temp=this.gomme[x].listino*(1-this.gomme[x].sconto)*this.gomme[x].qta;

                        this.importo.pneumatici+=temp;

                        txt+='<div style="vertical-align:middle;line-height:15px;">';
                            txt+='<div style="font-size:0.8em;text-align:right;">'+(parseFloat(this.gomme[x].listino).toFixed(2))+'</div>';
                            txt+='<div style="font-size:0.9em;';
                                if (parseInt(this.gomme[x].giacenza)<parseInt(this.gomme[x].qta)) {
                                    //txt+='text-decoration: line-through;';
                                    flagListino=false;
                                }
                            txt+='">'+((this.gomme[x].listino*this.gomme[x].qta).toFixed(2))+'</div>';
                        txt+='</div>';

                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:center;line-height:30px;width:13%;">';
                        txt+='<div style="vertical-align:middle;">';
                            txt+='<img style="position:relative;width:15px;height:15px;" src="http://'+location.host+'/nebula/apps/sthor/img/'+img+'" />';
                            txt+='<span style="margin-left:5px;font-size:0.8em;">'+this.gomme[x].magazzino+'</span>';
                        txt+='</div>';                      
                    txt+='</div>';

                    txt+='<div style="position:relative;display:inline-block;vertical-align:top;height:100%;text-align:right;line-height:30px;width:8%;">';
                        txt+='<div style="vertical-align:middle;font-size:0.8em;line-height:15px;text-align:center;';
                            if (sc==0) txt+='color:red;font-weight:bold;';
                        txt+='">';
                            txt+='<div>('+this.gomme[x].scontoAC+'%)</div>';
                            txt+='<div>'+(this.gomme[x].sconto*100).toFixed(0)+'%</div>';
                        txt+='</div>';
                    txt+='</div>';
                
                txt+='</div>';
 
            }

            txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:75%;text-align:right;';
                if (!flagListino) txt+='text-decoration: line-through;';
            txt+='" >';
                txt+=this.importo['pneumatici'].toFixed(2);
            txt+='</div>';

        txt+='</div>';

        temp=(parseInt($('#gommaio_tempo_'+this.id).val())/100)*parseInt($('#gommaio_mano_'+this.id).val());
        this.importo['montaggio']=temp.toFixed(2);

        txt+='<label style="margin-top:5px;">Montaggio:</label>';

        txt+='<div style="position:relative;margin-top:5px;">';

            txt+='<div style="width:100%;">';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;" >';
                    txt+=this.importo['qta'];
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;" >x</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:right;" >';
                    txt+=this.importo['montaggio'];
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:35%;text-align:right;" >';
                    txt+=(this.importo['montaggio']*this.importo['qta']).toFixed(2);
                txt+='</div>';
                
            txt+='</div>';

            txt+='<div style="width:100%;">';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;" >';
                    txt+=this.importo['qta'];
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;" >x</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:right;" >';
                    txt+=this.importo['tasse'];
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:35%;text-align:right;" >';
                    txt+=(this.importo['tasse']*this.importo['qta']).toFixed(2);
                txt+='</div>';
                
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:5px;">';

            this.importo['totale']=this.importo['pneumatici']+(this.importo['montaggio']*this.importo['qta'])+(this.importo['tasse']*this.importo['qta']);

            txt+='<div style="width:100%;">';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:left;font-weight:bold;" >';
                    txt+='<span>Totale:</span>';
                    if (!flagListino) {
                        txt+='<span style="color:red;font-weight:bold;font-size:0.9em;margin-left:5px;">';
                            txt+='LISTINO da verificare';
                        txt+='</span>';
                    }
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:25%;text-align:right;';
                    if (!flagListino) txt+='text-decoration: line-through;';
                txt+='" >';
                    txt+=this.importo['totale'].toFixed(2);
                txt+='</div>';

                txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;font-weight:bold;" >';
                    txt+=(this.importo['totale']*this.iva).toFixed(2);
                txt+='</div>';
                
            txt+='</div>';

            txt+='<div style="width:100%;text-align:right;margin-top:10px;">';
                txt+='<button style="margin-right:15px;" onclick="window._gom_'+this.id+'.print();" >Stampa</button>';
            txt+='</div>';

        txt+='</div>';

        $('#gommaio_'+this.id+'_totale').html(txt);

        window['_gom_'+this.id+'_divo'].selTab(0);
    }

    this.get=function(b64) {

        var obj=$.parseJSON(atob(b64));

        if (obj!=='undefined') this.gomme.push(obj);

        this.calcola();
    }

    this.getList=function() {

        var param={
            "reparto":$('#gommaio_dms_'+this.id).val(),
            "tipo":$('#gommaio_tipo_'+this.id).val(),
            "d":$('#gommaio_D_'+this.id).val(),
            "r":$('#gommaio_R_'+this.id).val(),
            "marca":$('#gommaio_pneu_'+this.id).val(),
            "qta":$('#gommaio_Q_'+this.id).val(),
            "mag":"T",
            "id":this.id
        }

        //console.log(JSON.stringify(param));

        $('#gommaio_'+this.id+'_lista').html(window._nebulaMain.setWaiter());
        window['_gom_'+this.id+'_divo'].selTab(1);

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/sthor/core/gommaio_select.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                var t=$.parseJSON(ret);

                if (t!=='undefined') {
                
                    $('#gommaio_'+t.id+'_lista').html(atob(t.txt));
                }
            }
        });

    }

    this.elimina=function(id) {

        var temp=[];

        for (var x in this.gomme) {
            if (x==id) continue;
            temp.push(this.gomme[x]);
        }

        this.gomme=temp;

        this.calcola();
    }

    this.conferma=function() {

        //{"magazzino":"04","precodice":"V","articolo":"ZTS215557WPC70","descr_articolo":"215\/55R17 94W P7 CINTURATO PIRELLI","giacenza":"4.000","dispo":"4.000"}
        var tipo=$('#gomlibero_tipo_'+this.id).val();
        var marca=$('#gomlibero_pneu_'+this.id).val();
        var listino=$('#gomlibero_listino_'+this.id).val().replace(',','.');
        var obj={
            "magazzino":'xx',
            "precodice":'V',
            "articolo":tipo+'xxxxxx'+marca+'xxx',
            "descr_articolo":$('#gomlibero_desc_'+this.id).val().trim(),
            "des_marca":(marca)?$('#gomlibero_pneu_'+this.id+' option:selected').html():'',
            "giacenza":0,
            "dispo":0,
            "listino":parseFloat(listino).toFixed(2),
            "qta":$('#gomlibero_Q_'+this.id).val(),
            "scontoAC":0
        }

        var txt="";

        if (marca=='') txt+='- pneumatico non selezionato -';
        if (tipo=='') txt+='- tipo non selezionato -';
        if (obj.listino === 'undefined' || obj.listino === 'NaN' || obj.listino=='') txt+='- listino non valido -';
        if (obj.descr_articolo=='') txt+='- descrizione non valida -';

        if (txt!='') {
            alert (txt);
            return;
        }

        var sc=$.parseJSON(atob($('#gomlibero_pneu_'+this.id+' option:selected').data('sconto')));

        if (sc!=='undefined') obj.scontoAC=sc[tipo];

        //console.log(JSON.stringify(obj));

        this.gomme.push(obj);

        this.calcola();
    }

    this.print=function() {

        var param={
            "gomme":this.gomme,
            "importo":this.importo
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/sthor/core/stampa_gommaio.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);
                
                var a = document.createElement('a');
                // Do it the HTML5 compliant way
                //var blob = window._nebulaMain.base64ToBlob8(ret, "application/pdf;charset=UTF-8");
                var blob = window._nebulaMain.base64ToBlob(ret, "application/pdf;charset=ISO-8859-1");
                //var url='data:application/pdf;base64,' + ret;
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.target="_blank";
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });

    }

}