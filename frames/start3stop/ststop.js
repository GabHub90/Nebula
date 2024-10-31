//interfaccia

function chg_rep(tag) {
	_reparto=tag;
	_selected_coll="";
	_edit=0;
	aggiorna();
}

function aggiorna() {
	window.location='index.php?reparto='+_reparto+'&coll='+_selected_coll+'&edit='+_edit;
	//st_reset();
}

function st_reset() {
	window.location='index.php?reparto='+_reparto;
}

function ststop_visualizza() {
	$("body").show();
}

function st_apri_nuovo(tipo,info) {
	
	//tipo= bottone premuto
	//info= eventuale informazione aggiuntiva
	
	$.ajax({"url":"core/bt_nuovo.php","type":"POST","data":{"coll":_selected_coll,"reparto":_reparto,"tipo":tipo,"info":info}, "async":false, "success":function(ret) {
		
		$('#st_bt_nuovo_main').html(ret);
		$('#st_cover').show();
		$('#st_bt_nuovo').show();
	}});
}

function st_chiudi_nuovo() {
	$('#st_bt_nuovo').hide();
	$('#st_cover').hide();
}

//==================================================================
//collaboratori
//==================================================================

function ststop_select_coll(id) {
	_selected_coll=id;
	aggiorna();
}

function st_set_pitlane(id,mov,lam,off) {
	//alert(JSON.stringify(_ststop_open[id]));
	var obj={"num_rif_movimento":mov,"cod_inconveniente":lam,"cod_operaio":id,"cod_off":off,"annotazioni":$('#st_pitlane_text_'+id).val()};
	
	$.ajax({"url":"core/set_pitlane.php","type":"POST","data":{"obj":obj}, "async":false, "success":function(ret) {
		aggiorna();
	}});
}

//==================================================================
//marcature
//==================================================================

function st_view_lamentati(val) {
	
	$("#st_open_lamentati").html('');
	
	//alert(val);
	
	//verifica se è lo stesso ordine da chiudere
	if (val==$('#st_open_form_odl_open').val()) {
		$("#st_open_lamentati").html('<div class="error" style="text-align:center;">Collaboratore già marcato su questo ordine</div>');
		return;
	}
	
	var prova=$('#st_open_form_coll_prova').val();
	
	//carica i lamentati
	$("#st_open_lamentati").html("");
	$.ajax({"url":"core/bt_lista_lamentati.php","type":"POST","data":{"odl":val,"prova":prova}, "async":false, "success":function(ret){
		$("#st_open_lamentati").html(ret);
	}});
}

function st_open_pw(speciale,img) {
	$('#st_open_form_speciale_tipo').val(speciale);
	$('#st_div_open_password_img').html('');
	$('#st_open_form_speciale_password').val('');
	var elem = document.createElement("img");
	elem.src = 'img/'+img;
	elem.classList.add('nuovo_speciale_img_pw');
	document.getElementById("st_div_open_password_img").appendChild(elem);
	$('#st_div_open_speciale').hide();
	$('#st_div_open_password').show();
}

//incvio campo password macature speciali
function st_special_pw(val) {
	
	//se la password è sbagliata non andare avanti
	if (val!=$('#st_open_form_password').val()) return;
	
	var speciale=$('#st_open_form_speciale_tipo').val();
	st_apri_form_speciale(speciale);
}

function st_close_pw() {
	$('#st_div_open_password').hide();
	$('#st_div_open_speciale').show();
}

//chiude la vecchia marcatura se c'è ed apre la nuova
function st_switch(mov,lam,speciale,stato_lam) {
	
	//alert(mov+'/'+lam+'/'+speciale+'/'+stato_lam);
	//return;
	
	$.ajax({
		"url":"core/bt_switch.php",
		"type":"POST",
		"data":{"mov":mov,"lam":lam,"coll":_selected_coll,"speciale":speciale,"stato":stato_lam,"reparto":_reparto},
		"async":false,
		"success":function(ret) {
			//alert (ret);
			st_reset();
	}});
}

//comando start dal form nuovo
function st_apri_form_start(mov,lam) {
	
	var speciale="";
	var stato_lam="";
	
	if ($('#st_open_form_odl_open').val()!='') {
		stato_lam=$("input:radio[name='st_open_form_chiusura_prec']:checked").val();
		if (!stato_lam) stato_lam="";
	}
	
	st_switch(mov,lam,speciale,stato_lam);
}

//comando fine dal form nuovo
function st_apri_form_fine() {
	
	var stato_lam="";
	
	if ($('#st_open_form_odl_open').val()!='') {
		stato_lam=$("input:radio[name='st_open_form_chiusura_prec']:checked").val();
		if (!stato_lam) stato_lam="";
	}
	
	st_switch('','','',stato_lam);
}

//bottone marcatura speciale
function st_apri_form_speciale(speciale) {
	
	//alert(speciale);
	
	var stato_lam="";
	
	if ($('#st_open_form_odl_open').val()!='') {
		stato_lam=$("input:radio[name='st_open_form_chiusura_prec']:checked").val();
		if (!stato_lam) stato_lam="";
	}
	
	st_switch('','',speciale,stato_lam);
}

function st_apri_form_prova(mov) {
	
	//alert(speciale);
	
	var stato_lam="";
	
	if ($('#st_open_form_odl_open').val()!='') {
		stato_lam=$("input:radio[name='st_open_form_chiusura_prec']:checked").val();
		if (!stato_lam) stato_lam="";
	}
	
	st_switch(mov,'','PRV',stato_lam);
}

function st_apri_form_allinea(ora) {
	
	var stato_lam="";
	
	if ($('#st_open_form_odl_open').val()!='') {
		stato_lam=$("input:radio[name='st_open_form_chiusura_prec']:checked").val();
		if (!stato_lam) stato_lam="";
	}
	
	$.ajax({"url":"core/bt_allinea.php","type":"POST","data":{"ora":ora,"coll":_selected_coll,"stato_lam":stato_lam}, "async":false, "success":function(ret) {
			st_reset();
	}});
}
























/*
function chg_rep_eff(tag,coll,edit){
	if(tag=="") return
	_reparto=tag;
	_coll=coll;
	_edit=edit;
	_ciclo=0;
	_txt="";
	_actual={};
	//riempi l'array ACTUAL con i collaboratori del reparto attivo
	$.each(_collaboratori,function(key,val) {
		if(val.reparto==_reparto) {
			_actual[key]=val;
		}
	});
	//aggiorna nome reparto
	$("#repbar").css('color','#'+_reparti[tag].fore);
		//imposta colore header del DIV per aprire una marcatura su un nuovo ordine
		$(".nuovo_header").css('color','#'+_reparti[tag].fore);
	$("#repbar").html(_reparti[tag].descrizione);
	//aggiorna bottoni collaboratori
	$.each(_actual, function(key,val) {
		var delta=60*_ciclo;
		_txt+='<div class="collbutt" style="color:#'+_reparti[val.reparto].fore+';border:2px solid #'+_reparti[val.reparto].fore+';left:'+delta+';" onclick="pop_coll('+key+');">'+key+'</div>';
		_ciclo+=1;
	});
	if (_edit==1) {
		_txt+='<div id="annulla" class="collbutt" style="width:100px;color:orange;border:2px solid orange;left:'+(60*_ciclo)+';" onclick="annulla(\''+_coll+'\');">Annulla</div>';
	}
	$("#collbar").html(_txt);
	
	//aggiorna DIV PRINCIPALE
	var param=JSON.stringify(_actual);
	$.ajax({"url":"core/update.php","type":"POST","data":{"param":param,"reparto":_reparto},"async":false,"cache":false,"success":function(ret) {$("#main").html(ret);}});
	
	if (coll!="") {
		//alert (coll);
		$("#coll_div_"+coll).insertBefore("#main>div:first");
		if (_edit==1) {
			$("div[id$='_b_"+_coll+"']").show();
			//determina l'odl FITTIZIO
			_txt=_reparti[_collaboratori[_coll].reparto].concerto;
			$.each(_servizio,function (key,val) {if (val.rep==_txt) _odl_fittizio=key;});
			//alert(_odl_fittizio);
		}
	}
}



function open_nuovo(coll,open) {
	_open=open;
	var txt='['+coll+'] '+_collaboratori[coll].nome+' '+_collaboratori[coll].cognome;
	$(".nuovo_header").html(txt);
	
	//se non ci sono marcature aperte dai la possibilità di marcare in ATTESA ORDINE altrimenti visualizza DIV per il tipo di chiusura
	if(open==0 && _collaboratori[coll].gruppo!='RT') $(".nuovo_attesa").show();
	else $(".nuovo_chiusura").show();
	
	if (_collaboratori[coll].gruppo!='RT') {
		$(".nuovo_speciale").show();
	}
	
	$(".cover").show();
	$(".nuovo_div").show();
}

function close_nuovo() {
	//nascondi il sottoDIV - ATTESA
	$(".nuovo_attesa").hide();
	$(".nuovo_lam").html("");
	$("#odl").val("");
	$("input:radio[name='odl_prec'][value='T']").prop('checked',true);
	$(".nuovo_speciale").hide();
	$(".nuovo_div").hide();
	$(".cover").hide();
}

function open_chiusura(tipo,opt,esclusione_special) {
	_esclusione_special=esclusione_special;
	if(tipo=='START') $(".chiusura_start").show();
	if(tipo=='STOP') $(".chiusura_stop").show();
	if(tipo=='STOP_RT') $(".chiusura_stop_rt").show();
	//
	if(opt=='standard') $(".chiusura_ant").show();
	//
	$(".cover").show();
	$(".chiusura_div").show();
}

function close_chiusura() {
	$("input:radio[name='odl_prec_c'][value='T']").prop('checked',true);
	$(".chiusura_start").hide();
	$(".chiusura_stop").hide();
	$(".chiusura_stop_rt").hide();
	$(".chiusura_ant").hide();
	$(".chiusura_div").hide();
	$(".cover").hide();
}

function open_extra(tipo) {
	var txt="";
	var d=new Date();
	_extra=tipo;
	
	$(".cover").show();
	$(".extra_div").show();
	if (tipo=='EXT') {
		txt=d.getTime()+"_"+_coll;
		$("#extra_code_code").html(txt);
		$("#extra_hidden").val(txt);
		$("#extra_code").show();
		$("#extra_start").show();
	}
	if (tipo=='MCQ') {
		$("#extra_telaio").val("");
		$("#extra_km").val("");
		$("#extra_error").html("");
		$("#extra_1500").show();
		$("#extra_start").show();
	}
}

function close_extra() {
	$("#extra_start").hide();
	$("#extra_code").hide();
	$("#extra_1500").hide();
	$(".extra_div").hide();
	$(".cover").hide();
}

function open_pw(tipo) {

	_speciale=tipo;

	if (tipo=="SER") $("#pw_title").html("ASSENZA per SERVIZIO");
	if (tipo=="VRT") $("#pw_title").html("SOSTITUZIONE temporanea RT");
	if (tipo=="EXI") $("#pw_title").html("USCITA");
	
	$(".cover2").show();
	$(".pw_div").show();
}

function close_pw() {
	$("#pw_password_pw").val("");
	$("#pw_error").val("");
	$(".pw_div").hide();
	$(".cover2").hide();
}

function open_prova_div() {
	$(".cover").show();
	
	// AZZERA SELEZIONE IN POSITIVO
	$("input:radio[name='prova_esito']").each(function() {
		if ($(this).val()=="OK") $(this).prop("checked",true);
		else $(this).prop("checked",false);
	});
	
	$(".prova_div").show();
}

function close_prova_div() {
	$(".prova_div").hide();
	$(".cover").hide();
}

//DIV PRINCIPALE

function pop_coll(id) {
	_coll=id;
	_edit=1;
	aggiorna();
	//setTimeout(pop_coll_eff(id),1000);
}

function pop_coll_eff(id) {
	$("#coll_div_"+id).insertBefore("#main>div:first");
}

function annulla(id) {
	$("div[id$='_b_"+_coll+"']").hide();
	$("#annulla").hide();
	_edit=0;
}

//GESTIONE TIMBRATURE

function allinea_timbratura(id,error) {
	if (!confirm('La modifica non è annullabile. Procedo?')) return;
	//alert(JSON.stringify(_error_js[error.toString()][id.toString()]));
	//alert(JSON.stringify(_error_js[error][id]));
	//$.ajax({"url":"core/allinea.php","type":"POST","data":{"param":JSON.stringify(_error_js[error][id])}, "async":false, "success":function(ret) {alert(ret);}});
	$.ajax({"url":"core/t_allinea.php","type":"POST","data":{"param":JSON.stringify(_error_js[error][id]),"tipo":'allinea'}, "async":false, "success":function(ret) {_edit=0;aggiorna();}});
}

function conferma_straordinario(id,error) {
	if (!confirm('La modifica non è annullabile. Procedo?')) return;
	
	$.ajax({"url":"core/t_allinea.php","type":"POST","data":{"param":JSON.stringify(_error_js[error][id]),"tipo":'straordinario'}, "async":false, "success":function(ret) {
		_edit=0;
		aggiorna();
		}
	});
}

function switch_timbratura(tipo,id,keylam) {
	//alert(tipo+" "+id+" "+JSON.stringify(_lamentati[keylam]));
	_param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentati[keylam]),"stato":"","note":""};
	open_chiusura(tipo,'standard','');
}

function switch_timbratura_eff() {
	//alert(tipo+" "+id+" "+JSON.stringify(_lamentati[keylam]));
	_param.stato=$("input:radio[name='odl_prec_c']:checked").val();
	//alert(JSON.stringify(_param));
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function switch_timbratura_special (tipo,special) {
	//esegue lo switch alla marcatura speciale dalla finestra di chiusura
	//se il tipo di chiusura è escluso ritorna (non eseguibile ricursivamente)
	if (special==_esclusione_special) return;
	var lam={};
	lam.mov=_odl_fittizio;
	lam.inc='';
	_param.stato=$("input:radio[name='odl_prec_c']:checked").val();
	_param.note=special;
	_param.tipo=tipo;
	_param.lam=JSON.stringify(lam);
	_param.coll=_coll;
	//alert(JSON.stringify(_param));
	$.ajax({"url":"core/t_switch_special.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function chiudi_timbratura (tipo,id,keylam) {
	//chiusura timbratura specifica per RT senza marcatura fittizia
	_coll=id;
	//valorizza l'oggetto PARAM per assecondare una eventuale marcatura speciale
	_param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentati[keylam]),"stato":"","note":""};
	open_chiusura('STOP_RT','standard','');
}

function chiudi_timbratura_eff() {
	var stato=$("input:radio[name='odl_prec_c']:checked").val();
	$.ajax({"url":"core/t_chiudi.php","type":"POST","data":{"coll":_coll,"stato":stato}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function riprendi_timbratura(tipo,id,keylam) {
	//alert(tipo+" "+id+" "+JSON.stringify(_lamentati[keylam]));
	var param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentati[keylam]),"stato":""};
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function cambia_odl(tipo,id,keylam) {
	//alert (JSON.stringify(_lamentato_nuovo[keylam]));
	//legge lo stato di chiusura della marcatura dalla maschera NUOVO
	var stato=$("input:radio[name='odl_prec']:checked").val();
	var param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentato_nuovo[keylam]),"stato":stato,"note":""};
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function chiudi_CIN(id,mov,inc) {
	var lam={"mov":mov,"inc":inc};
	var param={"tipo":"START","coll":id,"lam":JSON.stringify(lam),"stato":"T","note":""};
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function chiudi_PER(id,mov,inc) {
	var lam={"mov":mov,"inc":inc};
	var param={"tipo":"START","coll":id,"lam":JSON.stringify(lam),"stato":"T","note":""};
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

function chiudi_speciale(tipo,id,keylam,esclusione_special) {
	//chiusura di una marcatura speciale
	_param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentati[keylam]),"stato":"","note":""};
	open_chiusura(tipo,'special',esclusione_special);
	//alert(JSON.stringify(_param));
}

function riapri_anticipata(tipo,id,keylam) {
	//sposta la marcatura fittizia nel punto in cui era stata interrotta la marcatura sull'ordine
	$.ajax({"url":"core/t_sposta_special.php","type":"POST","data":{"coll":id}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

//TIMBRATURE EXTRA

function extra_start() {
	var lam={};
	lam.mov=_odl_fittizio;
	lam.inc='';
	_param.lam=JSON.stringify(lam);
	_param.stato='R';
	_param.tipo='STOP';
	_param.coll=_coll;
	_param.note=_extra;
	
	if (_extra=='EXT') {
		_param.code=$('#extra_hidden').val();
	}
	
	if (_extra=='MCQ') {	
		if(!extra_validate()) {
			$("#extra_error").html("Dati non corretti");
			return;
		}
		_param.telaio=$("#extra_telaio").val();
		_param.km=$("#extra_km").val();
	}
	
	$.ajax({"url":"core/t_switch_special.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
				if(ret=='error') alert('error');
				_edit=0;
				aggiorna();
			}});	
	
	close_extra();
}

function extra_validate() {
	var ok=true;
	var txt="";
	var reg=/\D/;
	//verifica il telaio
	txt=$("#extra_telaio").val();
	if (txt.length<17) ok=false;
	//else if (reg.test(txt.substr(11))) ok=false;
	//verifica i kilometri
	txt=$("#extra_km").val();
	if (txt.length==0) ok=false;
	else if (reg.test(txt)) ok=false;
	return ok;
}

function riapri_extra(tipo,id,keylam) {
	_param={"tipo":tipo,"coll":id,"lam":JSON.stringify(_lamentati[keylam]),"stato":"","note":""};
	_param.stato='T';
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}

//TIMBRATURE SPECIALI (PW)

function pw_start() {
	if ($('#pw_password_pw').val()!=_password_sp) {
		$("#pw_error").html("Password non corretta");
		return;
	}
	
	var lam={};
	lam.mov=_odl_fittizio;
	lam.inc='';
	_param.lam=JSON.stringify(lam);
	_param.stato=$("input:radio[name='odl_prec_c']:checked").val();
	if (_open==1) _param.tipo='STOP';
	if (_open==0) _param.tipo='PAUSE';
	_param.coll=_coll;
	_param.note=_speciale;
		
	$.ajax({"url":"core/t_switch_special.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
				if(ret=='error') alert('error');
				_edit=0;
				aggiorna();
			}});	
	
	close_pw();
	close_nuovo();
}

function prova_start(odl) {
	var lam={};
	lam.mov=_odl_fittizio;
	lam.inc='';
	_param.odl=odl;
	_param.lam=JSON.stringify(lam);
	_param.stato=$("input:radio[name='odl_prec_c']:checked").val();
	if (_open==1) _param.tipo='STOP';
	if (_open==0) _param.tipo='PAUSE';
	_param.coll=_coll;
	_param.note='PRV';
		
	$.ajax({"url":"core/t_switch_special.php","type":"POST","data":{"param":JSON.stringify(_param)}, "async":false, "success":function(ret) {
				if(ret=='error') alert('error');
				_edit=0;
				aggiorna();
			}});
	
	//alert (JSON.stringify(lam));
}

function esito_prova(id,odl) {
	var data={'coll':id,'odl':odl};
	$("#prova_hidden").prop("value",JSON.stringify(data));
	open_prova_div();
}

function chiudi_prova() {
	//leggi stato da prova_hidden
	var str="var data="+$("#prova_hidden").val();
	eval(str);
	data.esito=$("input:radio[name='prova_esito']:checked").val();
	data.stato="R";
	
	//alert(JSON.stringify(data));
	$.ajax({"url":"core/t_chiudi_prova.php","type":"POST","data":data, "async":false, "success":function(ret) {
				if(ret=='error') alert('error');
				_edit=0;
				aggiorna();
			}});
}

function chiudi_nopresenza(tipo,id,keylam) {
	$.ajax({"url":"core/t_chiudi.php","type":"POST","data":{"coll":id,"stato":"R"}, "async":false, "success":function(ret) {
				if(ret=='error') alert('error');
				_edit=0;
				aggiorna();
			}});	
}
*/

/*function marca_fittizio(tipo,inc,special){
	//genera le informazioni per il lamentato fittizio
	var lam={};
	lam.mov=_odl_fittizio;
	lam.inc=inc;
	//se la richiesta proviene dalla maschera NUOVO ODL -> STATO="" altrimenti verifica il RADIO
	var stato="";
	if (tipo=='STOP') stato=$("input:radio[name='odl_prec_c']:checked").val();
	//
	var param={"tipo":tipo,"coll":_coll,"lam":JSON.stringify(lam),"stato":stato,"note":special};
	$.ajax({"url":"core/t_switch.php","type":"POST","data":{"param":JSON.stringify(param)}, "async":false, "success":function(ret) {
			if(ret=='error') alert('error');
			_edit=0;
			aggiorna();
		}});
}*/

//=============================================================================


