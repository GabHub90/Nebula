function nebulaPraticaAlert() {

    this.busy=false;

    this.ambito="";

    this.call="";
    this.rif="";

    this.openEdit=function(obj,ambito) {

        if (window._nebulaPraticaAlert.busy) {
            
            if (!confirm("C'è un altra istanza aperta. Vouoi procedere comunque?")) return;
            
            window._nebulaPraticaAlert.closeEdit(window._nebulaPraticaAlert.busy);
        }

        this.ambito=ambito;

        var pratica=$(obj).data('pratica');
        var dms=$(obj).data('dms');
        var rif=$(obj).data('rif');
        var lam=$(obj).data('lam');
        var pren=$(obj).data('pren');
        var edit=$(obj).data('edit');
        var call=$(obj).data('call');
        var scadenza=$(obj).data('scadenza');

        $(obj).prop('src','http://'+location.host+'/nebula/core/odl/img/chiudi.png');

        $(obj).removeAttr('onclick');
        $(obj).attr('onclick','window._nebulaPraticaAlert.closeEdit(this);');

        /*$(obj).off('click');
        $(obj).click(function() {
            window._nebulaPraticaAlert.closeEdit(this);
        });*/

        var txt="";

        if (pren=='N' && ambito=='odl') {

            var d=$(obj).data('d');
            var o=$(obj).data('o');
            var m=$(obj).data('m');

            if (o=='') o='18';
            if (m=='') m='00';

            txt+='<div style="position:relative;margin-top:10px;">';
                txt+='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >';
                    txt+='Fine Prevista:';
                txt+='</div>';
                txt+='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-size:1em;" >';
                    txt+='<input id="nebula_alert_data_fine" style="width:130px;" type="date" value="'+d+'" />';
                    txt+='<input id="nebula_alert_ora_fine" style="margin-left:15px;width:40px;text-align:center;" type="text" maxlength="2" value="'+o+'" />';
                    txt+='<span style="margin-left:5px;margin-right:5px;">:</span>';
                    txt+='<input id="nebula_alert_min_fine" style="width:40px;text-align:center;" type="text" maxlength="2" value="'+m+'" />';
                txt+='</div>';
            txt+='</div>';
        }

        txt+='<div style="position:relative;margin-top:10px;" >';

            var stato=$('#nebula_alert_nota_div_'+rif+'_'+lam).data('stato');

            txt+='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >';
                txt+='Stato '+this.ambito+':';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-size:1em;" >';

                txt+='<select id="nebula_alert_stato_input" >';

                    if (pren=='S') {

                        txt+='<option value="RP" '+(stato=='RP'?'selected':'')+'>Regolare</opton>';

                        if (call=='odielle') {
                            txt+='<option value="PK" '+(stato=='PK'?'selected':'')+'>PrePicking</opton>';
                        }

                        if (this.ambito=='odl') {
                            txt+='<option value="SO" '+(stato=='SO'?'selected':'')+'>Sospeso</opton>';  
                        }
                    }

                    if (pren=='N') {

                        txt+='<option value="RP" '+(stato=='RP'?'selected':'')+'>Regolare</opton>';

                        if (this.ambito=='odl' || call=='odielle') {
                            txt+='<option value="SO" '+(stato=='SO'?'selected':'')+'>Sospeso</opton>';
                            txt+='<option value="EX" '+(stato=='EX'?'selected':'')+'>Esterno</opton>';
                            txt+='<option value="RO" '+(stato=='RO'?'selected':'')+'>Ricambi</opton>';
                            txt+='<option value="DL" '+(stato=='DL'?'selected':'')+'>Da Lavare</opton>';
                            txt+='<option value="OK" '+(stato=='OK'?'selected':'')+'>Pronta</opton>';
                            txt+='<option value="LA" '+(stato=='LA'?'selected':'')+'>Lavata</opton>';   
                        }
                    }

                txt+='</select>';

                txt+='<button id="nebula_alert_confirm" style="position:absolute;top:0px;right:10px;background-color:aquamarine;" data-pratica="'+pratica+'" data-dms="'+dms+'" data-rif="'+rif+'" data-lam="'+lam+'" data-pren="'+pren+'" data-edit="'+edit+'" data-call="'+call+'" onclick="window._nebulaPraticaAlert.confirm(this);">Conferma Tutto</button>';
        
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:5px;margin-bottom:5px;" >';

            txt+='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >';
                txt+='Nota '+this.ambito+':'; 
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;" >';
                txt+='<input id="nebula_alert_nota_edit" style="width:98%;" type="text" maxlenght="80" value="'+$('#nebula_alert_nota_div_'+rif+'_'+lam).html()+'" />';
            txt+='</div>';

        txt+='</div>';

        txt+='<div style="position:relative;margin-top:5px;margin-bottom:5px;" >';

            txt+='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >';
                txt+='Scadenza stato:'; 
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:45%;vertical-align:top;" >';
                txt+='<input id="nebula_alert_revisione_edit" style="width:150px;" type="date" value="'+window._nebulaMain.data_db_to_form(""+scadenza)+'" />';
            txt+='</div>';

            txt+='<div style="position:relative;display:inline-block;width:25%;vertical-align:top;" >';
                txt+='<button onclick="window._nebulaPraticaAlert.resetScadenza();">Reset</button>';
            txt+='</div>';

        txt+='</div>';

        $('#'+call+'_odl_block_edit_'+rif).html(txt);

        $('#'+call+'_odl_block_'+rif).hide();
        $('#'+call+'_odl_block_edit_'+rif).show();

        window._nebulaPraticaAlert.busy=obj;
    }

    this.closeEdit=function(obj) {

        var rif=$(obj).data('rif');
        var call=$(obj).data('call');

        $(obj).prop('src','http://'+location.host+'/nebula/core/odl/img/edit.png');
        
        $(obj).removeAttr('onclick');
        $(obj).attr('onclick','window._nebulaPraticaAlert.openEdit(this,\''+window._nebulaPraticaAlert.ambito+'\');');

        $('#'+call+'_odl_block_edit_'+rif).html('');

        $('#'+call+'_odl_block_'+rif).show();
        $('#'+call+'_odl_block_edit_'+rif).hide();

        window._nebulaPraticaAlert.busy=false;
    }

    this.confirm=function(obj) {

        //segnala il contesto da cui viene chiamata la funzione
        this.call=$(obj).data('call');
        this.rif=$(obj).data('rif');

        var param={
            "pratica":$(obj).data('pratica'),
            "dms":$(obj).data('dms'),
            "rif":$(obj).data('rif'),
            "lam":$(obj).data('lam'),
            "pren":$(obj).data('pren'),
            "edit":$(obj).data('edit'),
            "stato":$('#nebula_alert_stato_input').val(),
            "nota":$('#nebula_alert_nota_edit').val(),
            "scadenza":$('#nebula_alert_revisione_edit').val(),
            "utente":window._nebulaMain.getMainLogged(),
            "alert":""
        }

        param.alert=$('#nebula_alert_array_'+this.call+'_'+param.rif+'_'+param.lam).val();

        if (document.getElementById("nebula_alert_data_fine")) {
            var d=$('#nebula_alert_data_fine').val();
            var o=''+parseInt($('#nebula_alert_ora_fine').val());
            var m=''+parseInt($('#nebula_alert_min_fine').val());

            if (isNaN(parseInt(o)) || o<0 || o>23) o='00';
            else if (o<10) o='0'+o;
            if (isNaN(parseInt(m)) || m<0 || m>59) m='00';
            else if (m<10) m='0'+m;

            param.d=d;
            param.ora=(m=='' || o=='')?'':o+':'+m
        }        

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/set_alert.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);

                if (window._nebulaPraticaAlert.call=='avalon' || window._nebulaPraticaAlert.call=='search') {
                    //window._nebulaApp.ribbonExecute();
                    $('#nebula_alert_line_'+window._nebulaPraticaAlert.call+'_'+window._nebulaPraticaAlert.rif+'_').css('background-color','ffe0e0');
                    $('#nebula_alert_line_'+window._nebulaPraticaAlert.call+'_'+window._nebulaPraticaAlert.rif+'_').css('border','2px dotted red');
                    $('#pratica_odl_block_'+window._nebulaPraticaAlert.call+'_icon_'+window._nebulaPraticaAlert.rif).click();
                }
                else if (window._nebulaPraticaAlert.call=='odielle') {
                    window._nebulaPraticaAlert.busy=false;
                    window._nebulaOdl.refreshOdl();
                }
            }
        });

    }

    this.resetScadenza=function() {
        $('#nebula_alert_revisione_edit').val('');
    }

    /*this.switch=function(rif) {
        $('#avalon_odl_block_'+rif).show();
        $('#avalon_odl_block_edit_'+rif).hide();

        window._nebulaPraticaAlert.busy=false;
    }*/

    /*this.updateAlert=function(obj) {

        $('#nebula_alert_line_'+obj.rif+'_'+obj.lam).html(obj.line);
        $('#avalon_odl_block_nota_'+obj.rif+'_'+obj.lam).html(atob(obj.nota));

        window._nebulaPraticaAlert.switch(obj.rif);

    }*/

    this.openRicon=function(obj,dms) {

        if (document.getElementById("pratica_ricon_data_form")) {
            if (!confirm("C'è un altra istanza aperta. Vouoi procedere comunque?")) return;
        }

        var pratica=$(obj).data('pratica');
        var d=$(obj).data('d');
        var o=$(obj).data('o');
        var m=$(obj).data('m');

        if (d=='') {
            let dateObj = new Date();
            let month = dateObj.getUTCMonth() + 1; //months from 1-12
            let day = dateObj.getUTCDate();
            let year = dateObj.getUTCFullYear();
            d = year + "-" + (month<10?"0"+month:month) + "-" + (day<10?'0'+day:day);
        }
        if (o=='') o='18';
        if (m=='') m='00';

        var txt='<div style="position:relative;" >';
            txt+='<img style="position:relative;width:20px;margin-left:10px;margin-top:5px;" src="http://'+location.host+'/nebula/core/odl/img/chiudi.png" onclick="window._nebulaPraticaAlert.closeRicon(\''+pratica+'\');" />';
            txt+='<button style="position:absolute;right:10px;top:5px;" onclick="window._nebulaPraticaAlert.confirmRicon(\''+pratica+'\',\''+dms+'\');">Conferma</button>';
        txt+='</div>';

        txt+='<div style="position:relative;margin-top:5px;margin-bottom:10px;height:40px;">';
            txt+='Prevista Consegna:';
            txt+='<input id="pratica_ricon_data_form" style="margin-left:15px;width:130px;" type="date" value="'+d+'"/>';
            txt+='<input id="pratica_ricon_ora_form" style="margin-left:15px;width:40px;text-align:center;" type="text" maxlength="2" value="'+o+'" />';
            txt+='<span style="margin-left:5px;margin-right:5px;">:</span>';
            txt+='<input id="pratica_ricon_min_form" style="width:40px;text-align:center;" type="text" maxlength="2" value="'+m+'" />';
        txt+='</div>';

        $('div[id^="timeless_pratica_head_edit_"][data-pratica="'+pratica+'"]').html(txt);
        $('div[id^="timeless_pratica_head_"][data-pratica="'+pratica+'"]').hide();
        $('div[id^="timeless_pratica_head_edit_"][data-pratica="'+pratica+'"]').show();
    }

    this.closeRicon=function(pratica) {

        $('div[id^="timeless_pratica_head_edit_"][data-pratica="'+pratica+'"]').html('');
        $('div[id^="timeless_pratica_head_edit_"][data-pratica="'+pratica+'"]').hide();
        $('div[id^="timeless_pratica_head_"][data-pratica="'+pratica+'"]').show();

    }

    this.confirmRicon=function(pratica,dms) {

        var d=$('#pratica_ricon_data_form').val();
        var o=''+parseInt($('#pratica_ricon_ora_form').val());
        var m=''+parseInt($('#pratica_ricon_min_form').val());

        //alert(d+o+m);

        //alert('prima: '+d+' '+o+' '+m);

        if (isNaN(parseInt(o)) || isNaN(parseInt(m)) || d=="" || o=="" || m=="") return;

        //alert(d+' '+o+' '+m);

        if (o<0 || o>23 || m<0 || m>59) return;

        if (o<10) o='0'+o;
        if (m<10) m='0'+m;

        var param={
            "pratica":pratica,
            "d":d,
            "ora":o+':'+m,
            "dms":dms
        }

        if (!confirm('Verrà impostata la data di prevista consegna il '+param.d+' alle '+param.ora)) return;

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/set_ricon.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                //console.log(ret);
                window._nebulaApp.ribbonExecute();
            }
        });
    }

    this.updateAlertArray=function(rif,lam) {

        var param={
            "pratica":$('#nebula_alert_array_odielle_'+rif+'_'+lam).data('pratica'),
            "dms":$('#nebula_alert_array_odielle_'+rif+'_'+lam).data('dms'),
            "rif":$('#nebula_alert_array_odielle_'+rif+'_'+lam).data('rif'),
            "lam":$('#nebula_alert_array_odielle_'+rif+'_'+lam).data('lam'),
            "pren":$('#nebula_alert_array_odielle_'+rif+'_'+lam).data('pren'),
            "utente":window._nebulaMain.getMainLogged(),
            "alert":$('#nebula_alert_array_odielle_'+rif+'_'+lam).val(),
            "update":{}
        }        

        $('input[name^="nebulaAlertUpdate_"]:checked').each(function(){
            var alert=$(this).data('alert');

            param.update[alert]=$(this).val();
        });

        //console.log(JSON.stringify(param));

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/odl/core/update_alert.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                console.log(ret);
                window._nebulaOdl.refreshOdl();
            }
        });

    }

}