function data_db_to_ita(txt) {
	return txt.substr(6,2)+"/"+txt.substr(4,2)+"/"+txt.substr(0,4);
}

function data_ita_to_db(d) {
	return ''+d.substr(6,4)+d.substr(3,2)+d.substr(0,2);
}

// a = oggetto Data e b=now()=milliseconds
function dateDiffInDays(a, b) {
	var MS_PER_DAY = 1000 * 60 * 60 * 24;
	  // Esclude l'ora ed il fuso orario
	  var utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
	  //var utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
	
	  return Math.floor((b - utc1) / MS_PER_DAY);
}

function number_format(number, decimals, dec_point, thousands_sep) {
  //  discuss at: http://phpjs.org/functions/number_format/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: davook
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Michael White (http://getsprink.com)
  // bugfixed by: Benjamin Lupton
  // bugfixed by: Allan Jensen (http://www.winternet.no)
  // bugfixed by: Howard Yeend
  // bugfixed by: Diogo Resende
  // bugfixed by: Rival
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  //  revised by: Luke Smith (http://lucassmith.name)
  //    input by: Kheang Hok Chin (http://www.distantia.ca/)
  //    input by: Jay Klehr
  //    input by: Amir Habibi (http://www.residence-mixte.com/)
  //    input by: Amirouche
  //   example 1: number_format(1234.56);
  //   returns 1: '1,235'
  //   example 2: number_format(1234.56, 2, ',', ' ');
  //   returns 2: '1 234,56'
  //   example 3: number_format(1234.5678, 2, '.', '');
  //   returns 3: '1234.57'
  //   example 4: number_format(67, 2, ',', '.');
  //   returns 4: '67,00'
  //   example 5: number_format(1000);
  //   returns 5: '1,000'
  //   example 6: number_format(67.311, 2);
  //   returns 6: '67.31'
  //   example 7: number_format(1000.55, 1);
  //   returns 7: '1,000.6'
  //   example 8: number_format(67000, 5, ',', '.');
  //   returns 8: '67.000,00000'
  //   example 9: number_format(0.9, 0);
  //   returns 9: '1'
  //  example 10: number_format('1.20', 2);
  //  returns 10: '1.20'
  //  example 11: number_format('1.20', 4);
  //  returns 11: '1.2000'
  //  example 12: number_format('1.2000', 3);
  //  returns 12: '1.200'
  //  example 13: number_format('1 000,50', 2, '.', ' ');
  //  returns 13: '100 050.00'
  //  example 14: number_format(1e-8, 8, '.', '');
  //  returns 14: '0.00000001'

  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}

function addslashes (str) {
  //  discuss at: http://phpjs.org/functions/addslashes/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Ates Goral (http://magnetiq.com)
  // improved by: marrtins
  // improved by: Nate
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
  //    input by: Denny Wardhana
  //   example 1: addslashes("kevin's birthday");
  //   returns 1: "kevin\\'s birthday"

  return (str + '')
    .replace(/[\\"']/g, '\\$&')
    .replace(/\u0000/g, '\\0')
}

//interfaccia
function graffa_set_global() {
	//Variabili globali
	_graffa_reparto='';
	_graffa_pren={};
	_graffa_temp=[];
}

function graffa_setrep(rep) {
	_graffa_reparto=rep;
	graffa_step0();
}

function graffa_set_rifdata(d) {
	_graffa_rifdata=d;
	graffa_step0();
}

function graffa_step0() {
	$.ajax({
		"url":"core/step0.php",
		"type":"POST",
		"data":{"reparto":_graffa_reparto,"rifdata":_graffa_rifdata},
		"async":false,
		"cache":false,
		"success":function(ret) {
			$('#graffa_main').html(ret);
	}});
	
}

function generaPDF() {

	_graffa_temp=[];
	
	$("input[id^='graffa_chk']:checked").each(function() {
		_graffa_temp.push(_graffa_pren[this.value]);
	});
	
	if (_graffa_temp.length==0) return;
	
	//alert (JSON.stringify(_graffa_temp));
	$.ajax({"url":"core/generaPDF.php","type":"POST","data":{"obj":JSON.stringify(_graffa_temp),"rifdata":_graffa_rifdata},"async":false,"cache":false,"success":function(ret) {
		$('#graffa_pdf').prop("src","core/temp/graffa.pdf");
		$("input[id^='graffa_chk']").prop("checked",false);
		//alert(ret);
	}});
}












function ot_postit_on(odl) {
	//alert (_ot_pren_lam[odl]);
	//var lams=eval(_ot_pren_lam[odl]);
	_ot_temp1="";
	/*for (x in _ot_pren_lam[odl]) {
		_ot_temp1=_ot_temp1+'<div class="ot_postit_line">'+_ot_pren_lam[odl][x]+'</div>';
	}*/
	_ot_temp1=_ot_pren_lam[odl];
	var event = window.event;
	var y = event.clientY-20;
	//var y=(32*pos+0);
	$('#ot_postit').css('top',y+'px');
	$('#ot_postit').html(_ot_temp1);
	$('#ot_postit').show();
}

function ot_postit_off() {		
	$('#ot_postit').hide();
	$("#ot_cover").hide();
}

function ot_va_postit_on(txt) {

	var event = window.event;
	var y = event.clientY-40;
	var x = event.clientX-100;
	//var y=(32*pos+0);
	$('#ot_va_postit').css('top',y+'px');
	$('#ot_va_postit').css('left',x+'px');
	$('#ot_va_postit').html(txt);
	$('#ot_va_postit').show();
}

function ot_va_postit_off() {		
	$('#ot_va_postit').hide();
}


function ot_st0_sel(odl) {
	$("#ot_odl").val(odl);
	ot_query();
}


//logica query

function ot_query() {

	var txt="";
	var err_txt="";
	var tipo="";
	var temp="";
	var re=/\D/;
	
	//azzeramento
	$("#ot_error").html("");
	
	//controllo form
	temp=$("#ot_telaio").val();
	if (temp!="") {
		tipo="telaio";
		txt=temp;
	}
	
	temp=$("#ot_targa").val();
	if (temp!="") {
		if (txt!="") {
			err_txt="&Egrave; ammesso un solo valore";
		}
		else {
			tipo="targa";
			txt=temp;
		}
	}
	
	temp=$("#ot_odl").val();
	if (temp!="") {
		if (txt!="") {
			err_txt="&Egrave; ammesso un solo valore";
		}
		else if (re.test(temp)) {
			err_txt="ODL non è un numero";
		}
		else {
			tipo="odl";
			txt=temp;
		}
	}
	
	
	//esegui
	if (err_txt!="") {
		$("#ot_error").html(err_txt);
	}
	else if (txt=="") {
		$("#ot_error").html("Nessun valore immesso");
	}
	else {
		_ot_car={};
		var param=JSON.stringify({"tipo":tipo,"txt":txt});
		$.ajax({"url":"core/query.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		eval(ret);
		}});
	}
}

function ot_del_intest() {
	if (_ot_car.cod_cliente!=_ot_car.cod_intest) return;
	
	if (confirm('Vuoi veramente CANCELLARE il codice intestatario?')) {
	
	}
}

function ot_open_privacy() {
		
	$("#ot_cover").show();
	$.ajax({"url":"core/privacy_edit.php","type":"POST","data":{"car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
		$("#ot_privacy_main").html(ret);
		$("#ot_privacy").show();
	}});
}

function ot_chiudi_privacy() {
	$("#ot_privacy").hide();
	$("#ot_cover").hide();
}

function ot_privacy_green() {
	var tipo={};
	eval($("#ot_privacy_tipo").val());
	var txt="";
	var d=new Date();
	var d2=d.getUTCFullYear()+('00' + (d.getUTCMonth() + 1)).slice(-2)+('00' + d.getUTCDate()).slice(-2);
	
	//se il tipo impostato non autorizza l'indagine
	if (tipo.ril=="N") {
		tipo.key="P";
		tipo.ril="S";
		txt="Tipo cliente FORZATO a 'P' - CONFERMI ?";
	}
	else if (tipo.ril=="S") {
		txt="Tipo cliente è '"+tipo.key+"' - CONFERMI ?";
	}
		
	if (confirm(txt))  {
		//modifica dati ot_car
		if (_ot_car.dat_pri_1=='') _ot_car.dat_pri_1=d2;
		if (_ot_car.dat_pri_2=='') _ot_car.dat_pri_2=d2;
		_ot_car.tipo_cli=tipo.key;
		_ot_car.pri_1='S';
		_ot_car.pri_2='S';
		_ot_car.pri_3='S';
		_ot_car.critico='N';
		_ot_car.critico_amm='N';
		_ot_car.dat_cri=d2;
		_ot_car.utente_cri='m.cecconi';
		
		//alert (JSON.stringify(_ot_car));
		
		$.ajax({"url":"core/privacy_write.php","type":"POST","data":{"car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
			//alert(ret);
			ot_privacy_V();
			ot_chiudi_privacy();
		}});
	}
}

function ot_privacy_X() {
	var d=new Date();
	var d2=d.getUTCFullYear()+('00' + (d.getUTCMonth() + 1)).slice(-2)+('00' + d.getUTCDate()).slice(-2);

	if (confirm("Tipo cliente FORZATO a 'X' - CONFERMI ?"))  {
		//modifica dati ot_car
		if (_ot_car.dat_pri_1=='') _ot_car.dat_pri_1=d2;
		if (_ot_car.dat_pri_2=='') _ot_car.dat_pri_2=d2;
		_ot_car.tipo_cli='X';
		_ot_car.pri_1='S';
		_ot_car.pri_2='S';
		_ot_car.pri_3='S';
		_ot_car.critico='N';
		_ot_car.critico_amm='N';
		_ot_car.dat_cri=d2;
		_ot_car.utente_cri='m.cecconi';
	
	//alert (JSON.stringify(_ot_car));
	
		$.ajax({"url":"core/privacy_write.php","type":"POST","data":{"car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
			//alert(ret);
			ot_privacy_V();
			ot_chiudi_privacy();
		}});
	}	
}

function ot_privacy_set() {
	var tipo={};
	eval($("#ot_privacy_tipo").val());
	if (tipo.key=='') return;
	
	var d=new Date();
	var d2=d.getUTCFullYear()+('00' + (d.getUTCMonth() + 1)).slice(-2)+('00' + d.getUTCDate()).slice(-2);

	if (confirm("Tipo cliente è '"+tipo.key+"' - CONFERMI ?"))  {
		//modifica dati ot_car
		_ot_car.tipo_cli=tipo.key;
		_ot_car.critico='N';
		_ot_car.critico_amm='N';
		_ot_car.dat_cri=d2;
		_ot_car.utente_cri='m.cecconi';
	
	//alert (JSON.stringify(_ot_car));
	
		$.ajax({"url":"core/privacy_write.php","type":"POST","data":{"car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
			//alert(ret);
			ot_privacy_V();
			ot_chiudi_privacy();
		}});
	}	
}

//FUNZIONI STEP 1-----------------------------------------------------------------------

function ot_chg_tab(id) {
	//ripristina situazione iniziale
	$("div[id^='ot_moduli_div']").hide();
	$("td[id^='ot_moduli_tab']").css('border-bottom','4px solid #FFFFFF');
	//attiva tab
	$("#ot_moduli_tab_"+id).css('border-bottom','4px solid orange');
	$("#ot_moduli_div_"+id).show();
}


function change_gruppo(val) {
	
	//avviso che la modifica interesserà tutte le vetture con quel modello
	if (confirm("Le modifiche apportate riguarderanno tutte le vetture con il modello "+_ot_car.modello)) {
		$("#ot_step1_ok").attr("disabled","disabled");
		_ot_temp1=val;
		$("#ot_new_gruppo").css("background-color","#fdcccc");
		setTimeout("change_gruppo_eff()", 100);
	}
	else {
		$("#ot_board").html("");		
		var param=JSON.stringify(_ot_car);
		$.ajax({"url":"core/step1.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
			$("#ot_board").append(ret);
		}});
	}
}

function change_gruppo_eff() {

	var val=_ot_temp1;
		
	//se nuovo GRUPPO aprire maschera inserimento
	if (val=="new") {
		$("#ot_cover").show();
		$.ajax({"url":"core/step1_new_gruppo.php","type":"POST","data":{"marca":_ot_marca},"async":true,"cache":false,"success":function(ret) {
			$("#ot_new_gruppo_main").html(ret);
			$("#ot_new_gruppo").show();
		}});
	}
	//altrimenti apri maschera descrizione gruppo
	else {
		$("#ot_cover").show();
		$.ajax({"url":"core/step1_chg_gruppo.php","type":"POST","data":{"marca":_ot_marca,"gruppo":val,"effetto":"gruppo","car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
			$("#ot_new_gruppo_main").html(ret);
			$("#ot_new_gruppo").show();
		}});
	}
}

function change_criteri(effetto) {

	var str="";
	
	if (effetto=='modello') {
		str='Le modifiche apportate riguarderanno tutte le vetture con il modello '+_ot_car.modello;
	}
	else str='Le modifiche apportate riguarderanno il telaio '+_ot_car.telaio;
	
	//avviso su cosa interesserà la modifica
	if (confirm(str)) {
		$("#ot_step1_ok").attr("disabled","disabled");
		_ot_temp2=effetto;
		$("#ot_new_gruppo").css("background-color","#cbdafc");
		setTimeout("change_criteri_eff()", 100);
	}
	else {
		$("#ot_board").html("");		
		var param=JSON.stringify(_ot_car);
		$.ajax({"url":"core/step1.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
			$("#ot_board").append(ret);
		}});
	}
}

function change_criteri_eff() {

	var effetto=_ot_temp2;
		
	$("#ot_cover").show();
	$.ajax({"url":"core/step1_chg_criteri.php","type":"POST","data":{"marca":_ot_marca,"gruppo":_ot_gruppo,"effetto":effetto,"car":JSON.stringify(_ot_car)},"async":true,"cache":false,"success":function(ret) {
		$("#ot_new_gruppo_main").html(ret);
		$("#ot_new_gruppo").show();
	}});
}

function annulla_chg() {
	$("#ot_new_gruppo").hide();
	$("#ot_cover").hide();
	$("#ot_new_gruppo_main").html("");
	$("#ot_board").html("");
	
	var param=JSON.stringify(_ot_car);
	$.ajax({"url":"core/step1.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
	//alert(ret);
	$("#ot_board").append(ret);
	setTimeout(set_step1_info,100);
	}});
}

function change_new_codice() {

	_ot_error_new.codice=0;
	var lista={'1':'alim','2':'traz','3':'cambio','4':'manut'};
	
	_ot_temp1=_ot_marca;
	_ot_temp2=_ot_marca+" ";
	
	$.each(lista, function(key,val) {
		var obj={};
		eval($("#ot_new_"+val).val());
		_ot_temp1=_ot_temp1+obj.codice;
		_ot_temp2=_ot_temp2+obj.desc+" ";
		if (obj.codice=='!' || obj.codice=='!!') _ot_error_new.codice=1;
	});
	
	if ($("#ot_new_nota").val()=="") {
		_ot_temp2=_ot_temp2+"__________";
		_ot_error_new.codice=1;
	}
	else {
		_ot_temp2=_ot_temp2+'_'+$("#ot_new_nota").val();
	}
	
	$('#ot_new_codice_cod').html(_ot_temp1);
	$('#ot_new_codice_desc').html(_ot_temp2);
}

function change_ckb_obj(val,ckb) {
	if (ckb) {
		//scrittura valori STD se lo stato della riga è JMP
		if (_ot_oggetti_std[val].flag_mov=='jmp') {
			$('#std_tempo_obj_'+val).val(_ot_oggetti_std[val].dt);
			$('#min_tempo_obj_'+val).val(_ot_oggetti_std[val].mint);
			$('#max_tempo_obj_'+val).val(_ot_oggetti_std[val].maxt);
			$('#step_tempo_obj_'+val).val(_ot_oggetti_std[val].stet);
			
			$('#std_km_obj_'+val).val(_ot_oggetti_std[val].dkm);
			$('#min_km_obj_'+val).val(_ot_oggetti_std[val].minkm);
			$('#max_km_obj_'+val).val(_ot_oggetti_std[val].maxkm);
			$('#step_km_obj_'+val).val(_ot_oggetti_std[val].stekm);
		}
		
		$("input[id$='obj_"+val+"']").css("visibility","visible");
		$("td[id$='obj_"+val+"']").css("visibility","visible");	
	}
	else {
		$("input[id$='obj_"+val+"']").css("visibility","hidden");
		$("td[id$='obj_"+val+"']").css("visibility","hidden");
	}
}

function validate_new_obj() {

	_ot_oggetti={};
	_ot_error_new.validate_obj=0;
	
	$(".ot_new_codice_error").html("");
	//validazione codice gruppo manutenzione
	if (_ot_error_new.codice==1) {
		//alert('sfvxrb');
		$("#ot_new_codice_error").html("Codice gruppo di manutenzione incompleto!");
	}
	
	$("input[id^='ot_new_ckb']").each(function(index,element) {
		if ($(this).prop('checked')) {
			//alert (this.value);
			
			validate_coerenza_obj(this.value);
			
			//scrivi oggetto se fino ad ora non ci sono stati errori
			if (_ot_error_new.validate_obj==0 && _ot_error_new.codice==0) {
				var oggetto_temp={};
				oggetto_temp.codice=_ot_oggetti_std[this.value].codice;
				//oggetto_temp.descrizione=_ot_oggetti_std[this.value].descrizione;
				//oggetto_temp.ambito=_ot_oggetti_std[this.value].ambito;
				//oggetto_temp.pos=_ot_oggetti_std[this.value].pos;
				//oggetto_temp.prioritario=_ot_oggetti_std[this.value].prioritario;
				
				oggetto_temp.dt=$('#std_tempo_obj_'+this.value).val();
				oggetto_temp.mint=$('#min_tempo_obj_'+this.value).val();
				oggetto_temp.maxt=$('#max_tempo_obj_'+this.value).val();
				oggetto_temp.stet=$('#step_tempo_obj_'+this.value).val();
				
				oggetto_temp.dkm=$('#std_km_obj_'+this.value).val();
				oggetto_temp.minkm=$('#min_km_obj_'+this.value).val();
				oggetto_temp.maxkm=$('#max_km_obj_'+this.value).val();
				oggetto_temp.stekm=$('#step_km_obj_'+this.value).val();
				
				_ot_oggetti[this.value]=oggetto_temp;
				
				//alert (JSON.stringify(_ot_oggetti));
			}
		}
	});
	
	//verifica se non ci sono stati errori
	if (_ot_error_new.validate_obj==0 && _ot_error_new.codice==0) {
		var param=JSON.stringify({"modello":_ot_modello,"codice":$('#ot_new_codice_cod').html(),"desc":$('#ot_new_codice_desc').html(),"indice":0,"obj":JSON.stringify(_ot_oggetti)});
		//alert (param);
		$.ajax({"url":"core/step1_insert_new_gruppo.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		annulla_chg();
		}});
	}
}

function validate_criteri_obj(effetto) {
	_ot_oggetti={};
	_ot_error_new.validate_obj=0;
	
	$("input[id^='ot_new_ckb']").each(function(index,element) {
	
		oggetto={"flag_mov":"jmp","ok":0,"dt":0,"mint":0,"maxt":0,"stet":0,"dkm":0,"minkm":0,"maxkm":0,"stekm":0,"del":0};
		
		if ($(this).prop('checked')) {
			oggetto.ok=1;
			if ($(this).prop('disabled')) {
				oggetto.flag_mov='ok';
			}
			else oggetto.flag_mov='ins';
			
			validate_coerenza_obj(this.value);
		}
		
		//creazione della lista da passare a PHP
		if (_ot_error_new.validate_obj==0) {
			oggetto.dt=$('#std_tempo_obj_'+this.value).val();
			oggetto.mint=$('#min_tempo_obj_'+this.value).val();
			oggetto.maxt=$('#max_tempo_obj_'+this.value).val();
			oggetto.stet=$('#step_tempo_obj_'+this.value).val();
			oggetto.dkm=$('#std_km_obj_'+this.value).val();
			oggetto.minkm=$('#min_km_obj_'+this.value).val();
			oggetto.maxkm=$('#max_km_obj_'+this.value).val();
			oggetto.stekm=$('#step_km_obj_'+this.value).val();
			if ($('#ot_del_ckb_'+this.value).prop('checked')) {
				oggetto.del=1;
				oggetto.flag_mov='del';
			}
			
			_ot_oggetti[this.value]=oggetto;
		}
				
	});
	
	if (_ot_error_new.validate_obj==0) {
		
		//alert (JSON.stringify(_ot_oggetti));
	
		var param=JSON.stringify({"effetto":effetto,"car":JSON.stringify(_ot_car),"gruppo":_ot_gruppo,"marca":_ot_marca,"obj":JSON.stringify(_ot_oggetti)});
		//alert (JSON.stringify(_ot_oggetti));
		$.ajax({"url":"core/step1_insert_criterio.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		annulla_chg();
		}});
	}
}

function validate_coerenza_obj(valore) {

	error_new={"obj_val":0,"obj_cri":0,"obj_mmx":0,"obj_ste":0};
	
	//verifica se ci sono valori negativi o non numeri
	$('input[id$="obj_'+valore+'"]').each(function() {
		if (isNaN(this.value) || this.value<0) {
			error_new.obj_val=1;
		} 
	});
	
	//verifica se dT o dKM sono diversi da 0
	if ($('#std_tempo_obj_'+valore).val()==0 && $('#std_km_obj_'+valore).val()==0) {		
		//chiedi SEMPRE?
		if (confirm("Impostare SEMPRE per l'oggetto "+valore+"?")) {
			//se STEP!=0 segna comunque come errore
			if ($('#step_tempo_obj_'+valore).val()!=0 || $('#step_km_obj_'+valore).val()!=0) {
				error_new.obj_ste=1;
			}
			else if (_ot_oggetti_std[valore].prioritario==1) {
				alert('Non possibile per oggetti tipo PRIORITARIO');
				error_new.obj_val=1;
			}
		}
		else {
			error_new.obj_cri=1;
		}
	}
	
	//verifica se min e max sono coerenti (se min e max sono diversi)
	if ($('#min_tempo_obj_'+valore).val()!=$('#max_tempo_obj_'+valore).val()) {
		if ($('#min_tempo_obj_'+valore).val()>$('#std_tempo_obj_'+valore).val() || $('#max_tempo_obj_'+valore).val()<$('#std_tempo_obj_'+valore).val()) {
			error_new.obj_mmx=1;
		}
	}
	if ($('#min_km_obj_'+valore).val()!=$('#max_km_obj_'+valore).val()) {
		if ($('#min_km_obj_'+valore).val()>$('#std_km_obj_'+valore).val() || $('#max_km_obj_'+valore).val()<$('#std_km_obj_'+valore).val()) {
			error_new.obj_mmx=1;
		}
	}
	
	//verifica se STEP>0 quando min e max sono diversi tra loro
	if ($('#min_tempo_obj_'+valore).val()!=$('#max_tempo_obj_'+valore).val() && $('#step_tempo_obj_'+valore).val()==0) {
		error_new.obj_ste=1;
	}
	if ($('#min_km_obj_'+valore).val()!=$('#max_km_obj_'+valore).val() && $('#step_km_obj_'+valore).val()==0) {
		error_new.obj_ste=1;
	}
	
	//se STEP>0 verifica la sua coerenza (differenza multiplo di step)
	if (error_new.obj_ste==0 || error_new.obj_mmx==0) {
	
		delta=$('#max_tempo_obj_'+valore).val()-$('#min_tempo_obj_'+valore).val();
		if ($('#step_tempo_obj_'+valore).val()!=0) {
			if (delta % $('#step_tempo_obj_'+valore).val()!=0) error_new.obj_ste=1;
		}
		
		delta=$('#max_km_obj_'+valore).val()-$('#min_km_obj_'+valore).val();
		if ($('#step_km_obj_'+valore).val()!=0) {
			if (delta % $('#step_km_obj_'+valore).val()!=0) error_new.obj_ste=1;
		}		
	}
	
	//scrivi gli errori
	if (error_new.obj_val==1) {
		$('#error_obj_'+valore).append("<span>&nbsp# Valori non validi #&nbsp</span>");
		_ot_error_new.validate_obj=1;
	}
	if (error_new.obj_cri==1) {
		$('#error_obj_'+valore).append("<span>&nbsp# Nessun criterio impostato #&nbsp</span>");
		_ot_error_new.validate_obj=1;
	}
	if (error_new.obj_mmx==1) {
		$('#error_obj_'+valore).append("<span>&nbsp# Min Max non coerenti #&nbsp</span>");
		_ot_error_new.validate_obj=1;
	}
	if (error_new.obj_ste==1) {
		$('#error_obj_'+valore).append("<span>&nbsp# Step non impostato o non coerente #&nbsp</span>");
		_ot_error_new.validate_obj=1;
	}
}

function chg_mod_gru(codice) {
	var param=JSON.stringify({"modello":_ot_modello,"cod_ind":codice});
	//alert (param);
	$.ajax({"url":"core/step1_insert_chg_gruppo.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
	//alert(ret);
	annulla_chg();
	}});
}

//FUNZIONI STEP 2-----------------------------------------------------------------------

function mesi_diff(dataH,dataL) {
	//calcola la differenza in mesi tra due date H=maggiore ed L=minore
	//date sotto forma di  stringa AAAAMM
	
	//alert (parseInt(dataH)+" "+parseInt(dataL));
	if (parseInt(dataH)<parseInt(dataL)) return 0;
	
	var res=0;
	
	var yh=parseInt(dataH.substr(0,4));
	var mh=parseInt(dataH.substr(4,2));
	var yl=parseInt(dataL.substr(0,4));
	var ml=parseInt(dataL.substr(4,2));
	
	while (yh>yl || mh>ml) {
		res++;
		mh--;
		
		if (mh==0) {
			yh--;
			mh=12;
		}
	}
	
	return res;
}

function ot_to_italian(d) {
	return ''+d.substr(6,2)+'/'+d.substr(4,2)+'/'+d.substr(0,4);
}

function controllo_data(stringa){
	var espressione = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
	if (!espressione.test(stringa))
	{
	    return false;
	}else{
		anno = parseInt(stringa.substr(6),10);
		mese = parseInt(stringa.substr(3, 2),10);
		giorno = parseInt(stringa.substr(0, 2),10);
		
		var data=new Date(anno, mese-1, giorno);
		if(data.getFullYear()==anno && data.getMonth()+1==mese && data.getDate()==giorno){
			return true;
		}else{
			return false;
		}
	}
}

function ot_switch_open() {
	alert("La selezione riguarderà SOLO questa visualizzazione");
	if (_ot_st2_open=='S') _ot_st2_open='N';
	else _ot_st2_open='S';

	to_2();
}

function ot_calcola_eventi() {

	//alert(JSON.stringify(_ot_oggetti));

	_ot_temp3={"last":new Array(),"stat":new Array(),"cons":0,"stima":0};
	
	//componi la data di oggi
	var d=new Date();
	var _ot_temp2=''+d.getFullYear();
	var dtemp=d.getMonth()+1;
	if (dtemp<10) _ot_temp2=_ot_temp2+'0'+dtemp;
	else _ot_temp2=_ot_temp2+dtemp;
	
	$.each(_ot_eventi, function(key,obj) {
		//key è il codice dell'oggetto (FANT,MAN)
		_ot_temp_stat={"num":0,"data_i":0,"km_i":0,"data_f":0,"km_f":0,"stat_dt":0,"stat_dkm":0,"last_dt":0,"last_dkm":0,"alert_km0":0,"last_km0":0};
		
		_ot_temp1=key;
		
		var temp=0;
		
		//alert (key);
		//analisi movimenti e calcolo statistico
		$.each(obj,function (num,obj) {
			//OBJ è il movimento
			
			//init
			_ot_temp_stat.last_km0=0;
			
			//se è il primo movimento
			if (_ot_temp_stat.num==0) {
				//se è un movimento con chilometri
				if (obj.error.km0==0) _ot_temp_stat.km_i=_ot_eventi[_ot_temp1][num].km;
				_ot_temp_stat.data_i=_ot_eventi[_ot_temp1][num].data_fine;
			}
			
			_ot_temp_stat.num++;
			
			if (obj.error.km0==0) _ot_temp_stat.km_f=_ot_eventi[_ot_temp1][num].km;
			_ot_temp_stat.data_f=_ot_eventi[_ot_temp1][num].data_fine;
			
			if (obj.error.km0==1) {
				_ot_temp_stat.alert_km0++;
				_ot_temp_stat.last_km0=1;
			}
		});
		
		//calcolo delta rispetto al presente
		if (_ot_temp_stat.last_km0==0) _ot_temp_stat.last_dkm=_ot_car.km-_ot_temp_stat.km_f;
		
		//_OT_TEMP2 = OGGI (AAAAMM) !!!!! POI la variabile viene utilizzata per altro
		
		temp=mesi_diff(_ot_temp2,_ot_temp_stat.data_f.substr(0,6));
		if (temp==0) temp=1;
		_ot_temp_stat.last_dt=temp;
		
		if (_ot_temp_stat.num-_ot_temp_stat.alert_km0>1) {
			_ot_temp_stat.stat_dt=Math.round((mesi_diff(_ot_temp_stat.data_f.substr(0,6),_ot_temp_stat.data_i.substr(0,6)))/(_ot_temp_stat.num-1));			
			_ot_temp_stat.stat_dkm=Math.round((_ot_temp_stat.km_f-_ot_temp_stat.km_i)/(_ot_temp_stat.num-_ot_temp_stat.alert_km0-1));
			
			//aggiungi km/mese STAT
			//alert(_ot_temp_stat.stat_dkm/_ot_temp_stat.stat_dt);
			_ot_temp3.stat.push(_ot_temp_stat.stat_dkm/_ot_temp_stat.stat_dt);
		}
		
		//aggiungi km/mese LAST
		if (_ot_temp_stat.last_km0==0) _ot_temp3.last.push(_ot_temp_stat.last_dkm/_ot_temp_stat.last_dt);
		
		_ot_stat[key]=_ot_temp_stat;
		
	});
	
	//alert (JSON.stringify(_ot_temp3.last));
	
	//aggiungi km/mese CONS
	if (_ot_car.consegna!="") {
		_ot_temp3.cons=Math.round((_ot_car.km/mesi_diff(_ot_temp2,_ot_car.consegna.substr(0,6))));
	}
	
	//aggiungi km/mese STIMA
	if (_ot_car.stima!=0) {
		_ot_temp3.stima=Math.round(_ot_car.stima/12);
	}
	
	//alert (JSON.stringify(_ot_car));
	
	$.each(_ot_stat,function(key,obj) {
		//key è il codice dell'oggetto (FANT,MAN)
		$("#ot_data_evento_"+key).html(ot_to_italian(obj.data_f));
		$("#ot_dt_evento_"+key).html(obj.last_dt);
		$("#ot_dkm_evento_"+key).html(obj.last_dkm);
		//
		$("#ot_tot_stat_evento_"+key).html("("+obj.num+" eventi)");
		if (obj.num>1) {
			$("#ot_dt_stat_evento_"+key).html(obj.stat_dt);
			$("#ot_dkm_stat_evento_"+key).html(obj.stat_dkm);
		}
		else {
			$("#ot_dt_stat_evento_"+key).html('');
			$("#ot_dkm_stat_evento_"+key).html('');
		}
	});
	
	//LAST,STAT,CONS,STIMA
	//Possibilità di inserire i km STIMA che saranno scritti in CONCERTO???
	//alert(JSON.stringify(_ot_temp3));
	var last=0;
	if (_ot_temp3.last.length>0) {
		_ot_temp1=0;
		var k2=0;
		for (var i in _ot_temp3.last) {
			//alert (_ot_temp3.last[i]);
			_ot_temp1=_ot_temp1+_ot_temp3.last[i];
			k2++;
		}
		last=Math.round(_ot_temp1/k2);
	}
	
	var stat=last;
	if (_ot_temp3.stat.length>0) {
		_ot_temp1=0;
		var k2=0;
		//alert (_ot_temp3.stat.length);
		for (var i in _ot_temp3.stat) {
			_ot_temp1=_ot_temp1+_ot_temp3.stat[i];
			k2++;
		}
		stat=Math.round(_ot_temp1/k2);
	}
	
	var kmm_flag=-1;
	$("input[id^='ot_kmm']").prop("disabled",false);
	$("input[id^='ot_kmm']").prop("checked",false);
	
	if (last>0) {
		$("#ot_kmm_txt_last").html(last);
		$("#ot_kmm_last").prop("checked",true);
		$("#ot_kmm_last").attr("value",last);
		kmm_flag=last;
		
		$("#ot_kmm_txt_stat").html(stat);
		$("#ot_kmm_stat").attr("value",stat);
	}
	else {
		$("#ot_kmm_txt_last").html("");
		$("#ot_kmm_txt_stat").html("");
		
		$("#ot_kmm_last").prop("disabled",true);
		$("#ot_kmm_stat").prop("disabled",true);
	}
	
	if (_ot_temp3.cons>0) {
		$("#ot_kmm_txt_cons").html(_ot_temp3.cons);
		$("#ot_kmm_cons").attr("value",_ot_temp3.cons);
		
		if (kmm_flag==-1) {
			$("#ot_kmm_cons").prop("checked",true);
			kmm_flag=_ot_temp3.cons;
		}
		
		//_ot_temp2=data di OGGI (AAAAMM)
		_ot_rif_tonext.t=parseInt(mesi_diff(_ot_temp2,_ot_car.consegna.substr(0,6)))+parseInt($("#ot_kmm_next").val());
		_ot_rif_tonext.km=parseInt(_ot_car.km)+parseInt(($("#ot_kmm_next").val()*kmm_flag));
		
//alert(parseInt($("#ot_kmm_next").val())+"_"+kmm_flag);
				
	}
	else {
		$("#ot_kmm_txt_cons").html("");		
		$("#ot_kmm_cons").prop("disabled",true);
		
		_ot_rif_tonext.t=0;
	}
	
	if (_ot_temp3.stima>0) {
		$("#ot_kmm_txt_stima").html(_ot_temp3.stima);
		$("#ot_kmm_stima").attr("value",_ot_temp3.stima);
		if (kmm_flag==-1) {
			$("#ot_kmm_stima").prop("checked",true);
			kmm_flag=_ot_temp3.stima;
		}
	}
	else {
		$("#ot_kmm_txt_stima").html("");		
		$("#ot_kmm_stima").prop("disabled",true);
	}
	
	if (kmm_flag==-1) {
		_ot_rif_tonext.km=0;
		$("#ot_kmm_next_tot").html('<b style="color:red;font-weight:bold;">Errore</b>');
		$("ot_kmm_next").prop("disabled",true);
	}
	else {
		$("#ot_kmm_next_tot").html(parseInt(_ot_car.km)+parseInt(($("#ot_kmm_next").val()*kmm_flag)));
		$("ot_kmm_next").prop("disabled",false);
		
		if (_ot_rif_tonext.t!=0) {
			$("#ot_kmm_next_tot_t").html('('+_ot_rif_tonext.t);
		}
		else $("#ot_kmm_next_tot_t").html("");
	}
	
	//INIZIALIZZA _OT_SERVICE
	_ot_service={};
	$.each(_ot_oggetti,function(key,obj) {
		_ot_service[key]={"pri":obj.prioritario,"limit_dt":0,"limit_dkm":0,"rif_dt":0,"rif_dkm":0,"topt":0,"topkm":0,"stato":"no","next_si":-1,"next2_si":-1};
	});
	
	//se non è -1 segna i km/mese selezionati
	_ot_kmm_service=kmm_flag;
	
	//ABILITA PROGRAMMI
	_ot_allow_prog.four='inline';
	_ot_allow_prog.gsp='inline';
	ot_allow_prog();
	
	//alert($("#ot_kmm_next").val());
	
	setTimeout(function() {_ot_service=ot_calcola_service($("#ot_kmm_next").val(),_ot_service);ot_draw_service();},100);
}

function ot_calcola_service(next,obj) {
	//NEXT sono i mesi nel futuro per i quali avviene il calcolo
	//calcola gli oggetti SERVICE in base alla statistica
	if (_ot_kmm_service==-1) return;
	
	_ot_pri_flag=1;
	var _ot_temp3=obj;
	
	//azzera next_si se non siamo in next (NEXT li azzera prima di partire)
	if (_next_flag==0) {
		for (var ix in _ot_temp3) {
			_ot_temp3[ix].next_si=-1;
			_ot_temp3[ix].next2_si=-1;
		}
	}
	
	//mesi next
	_ot_temp1=parseInt(next);
	//km next
	_ot_temp2=parseInt(next)*_ot_kmm_service;
	
	$.each(_ot_oggetti,function(key,obj) {
		//aggiorna impostazioni (ot_cri_def_dt_FANT ... ot_cri_def_dkm_FANT) sono <TD>
		_ot_temp3[key].limit_dt=parseInt($("#ot_cri_def_dt_"+key).html());
		_ot_temp3[key].limit_dkm=parseInt($("#ot_cri_def_dkm_"+key).html());
		
		_ot_temp3[key].topt=_ot_oggetti[key].topt;
		_ot_temp3[key].topkm=_ot_oggetti[key].topkm;
	});
	
	//alert(JSON.stringify(_ot_temp3));
	
	//VEDERE SU CONCERTO - CAMPO KM STIMA - CAMPO REALE CONSEGNA
	//INSERIRE POSSIBILITÀ (+STAT) nei passaggi (Per il momento per indicare il cambio di proprietario)
	
	$.each(_ot_temp3, function(key,obj) {
		//uso di _BF_ONERI a fini DIAGNOSTICI per NEXT
	
		var riferimento={"t":0,"km":0};
		//se non è stato forzato manualmente oppure se siamo nel programma NEXT
		if ((_ot_temp3[key].stato!="sel" && _ot_temp3[key].stato!="del") || _next_flag==1) {
		
			//se next2_si è stato assegnato o è un oggetto senza limiti salta
			if (obj.next2_si<0 && _ot_temp3[key].limit_dt!=999) {
			
				//se next_si è valorizzato usa come OFFSET ultimo evento
				var off_t=0;
				var off_km=0;
				
				if (obj.next_si>=0) {
					off_t=obj.next_si;
					off_km=obj.next_si*_ot_kmm_service;
				}

				//se esiste un ultimo evento
				if ($("#ot_dkm_evento_"+key).html()!="" || off_t>0) {
					//RIFERIMENTO	
					if (off_t>0) {	
						riferimento.t=_ot_temp1-off_t;
						riferimento.km=_ot_temp2-off_km;
					}
					else {
						riferimento.t=parseInt($("#ot_dt_evento_"+key).html())+_ot_temp1;
						riferimento.km=parseInt($("#ot_dkm_evento_"+key).html())+_ot_temp2;
						//riferimento.t=_ot_rif_tonext.t-parseInt($("#ot_dt_evento_"+key).html());
						//riferimento.km=_ot_rif_tonext.km-parseInt($("#ot_dkm_evento_"+key).html());
					}
				}
				//se non c'è un ultimo intervento
				else {
					//se la data di consegna è valida
					if (_ot_car.consegna!="") {
						//riferimento.t=_ot_rif_tonext.t;
						//componi la data di oggi
						var d=new Date();
						var d2=''+d.getFullYear();
						var dtemp=d.getMonth()+1;
						if (dtemp<10) d2=d2+'0'+dtemp;
						else d2=d2+dtemp;
						
						//alert(d2);
						
						riferimento.t=(mesi_diff(d2,_ot_car.consegna.substr(0,6)))+_ot_temp1;
						
						//alert(riferimento.t);
					}
					//riferimento.km=_ot_rif_tonext.km;
					riferimento.km=parseInt(_ot_car.km)+parseInt(_ot_temp2)-parseInt(off_km);	
					
					//alert(riferimento.km);
				}
				
				//scrivi riferimenti in SERVICE
				_ot_temp3[key].rif_dt=riferimento.t;
				_ot_temp3[key].rif_dkm=riferimento.km;
				
	//alert(JSON.stringify(riferimento));
				//VERIFICA SUPERAMENTO LIMITE
				//"riferimento" continete il riferimento per l'oggetto, "ot_rif_tonext" contiene il riferimento alla vita totale della vettura
				if ((riferimento.t>=_ot_temp3[key].limit_dt && _ot_temp3[key].limit_dt!=0) || (riferimento.km>=_ot_temp3[key].limit_dkm && _ot_temp3[key].limit_dkm!=0) || (_ot_temp3[key].limit_dt==0 && _ot_temp3[key].limit_dkm==0)) {
					//verifica superamento TOP
					if ((_ot_temp3[key].topt==0 || _ot_rif_tonext.t<=_ot_temp3[key].topt) && (_ot_temp3[key].topkm==0 || _ot_rif_tonext.km<=_ot_temp3[key].topkm)) {
						 
						_ot_temp3[key].stato="si";
						//next_si conserva i mesi next in cui è stato attivato il SI
						
						if (obj.next_si<0) {
							if (_ot_temp1!=1) _ot_temp3[key].next_si=_ot_temp1+1;
							else _ot_temp3[key].next_si=_ot_temp1;
							_ot_pri_flag=0;
						}
						else {
							_ot_temp3[key].next2_si=_ot_temp1;
						}
					}
					else _ot_temp3[key].stato="del";
				}		
				else {
					_ot_temp3[key].stato="no";
					//azzera _ot_pri_flag se la prima scadenza non è ancora avvenuta
					if (_ot_temp3[key].pri==1 && obj.next2_si==-1) _ot_pri_flag=0;
				}
				
			//chiude if next2	
			}
			
			//corregge stato in caso di programma NEXT
			if (_next_flag==1) {
				if (_ot_temp3[key].pri==1) _ot_temp3[key].stato="next";
				if (_ot_temp3[key].pri==0) _ot_temp3[key].stato="no";
			}
			
			//corregge il passaggio da NEXT a BF
			if (_next_flag==0) {
				if (_ot_temp3[key].stato=="next") _ot_temp3[key].stato="no";
			}
		}
		
		/*DIANGOSI NEXT
		if (key=='LIQFR') {
			bf_temp={"limit_t":_ot_temp3[key].limit_dt,"limit_km":_ot_temp3[key].limit_dkm,"off_t":off_t,"off_km":off_km,"rif_t":riferimento.t,"rif_km":riferimento.km,"next_si":_ot_temp3[key].next_si,"next2_si":_ot_temp3[key].next2_si,"stato":_ot_temp3[key].stato,"flag":_ot_pri_flag};
			/*_bf_oneri[_ot_temp1].limit_dt=_ot_temp3[key].limit_dt;
			_bf_oneri[_ot_temp1].limit_dkm=_ot_temp3[key].limit_km;
			_bf_oneri[_ot_temp1].off_t=off_t;
			_bf_oneri[_ot_temp1].off_km=off_km;
			_bf_oneri[_ot_temp1].riferimento_t=riferimento.t;
			_bf_oneri[_ot_temp1].riferimento_km=riferimento.km;
			_bf_oneri[_ot_temp1].next_si=_ot_temp3[key].next_si;
			_bf_oneri[_ot_temp1].next2_si=_ot_temp3[key].next2_si;
			_bf_oneri[_ot_temp1]=bf_temp;	
		}*/
		//DIAGNOSI NEXT
		
	});
	
	return _ot_temp3;
}

function cp_service() {

	copy_ot_service = jQuery.extend({}, _ot_service);
	for (var next=1;next>0;next++) {
		//if (next==1) copy_ot_service=ot_calcola_service(next,_ot_service);			
		//else copy_ot_service=ot_calcola_service(next,copy_ot_service);
		copy_ot_service=ot_calcola_service(next,copy_ot_service);
		//_ot_service=ot_calcola_service(next,_ot_service);
		if (_ot_pri_flag==1) break;
		if (next==60) break;
	}
	
	return copy_ot_service;
}

function ot_draw_service() {
	if (_ot_kmm_service==-1) return;
	
	//alert ('caracalla');
	
	//alert (JSON.stringify(_ot_service));
	//$("").css('background-color',"");
	
	_bf_flag.uno="";
	
	$.each(_ot_service,function(key,obj) {
		
		//se STATO==no
		if (obj.stato=='no') {
			$("#ot_cri_def_flag_"+key).css('background-color',"white");
			$("#ot_cri_def_img_"+key).attr("src","img/dot.png");
		}
		
		//se STATO==si
		if (obj.stato=='si') {
			$("#ot_cri_def_flag_"+key).css('background-color',"#cbdafc");
			$("#ot_cri_def_img_"+key).attr("src","img/ok.png");
			//se scade segnalalo per BF
			_bf_flag.uno=_bf_flag.uno+'-'+key;
		}
		
		//se STATO==sel
		if (obj.stato=='sel') {
			$("#ot_cri_def_flag_"+key).css('background-color',"#fedbfe");
			$("#ot_cri_def_img_"+key).attr("src","img/ins.png");
		}
		
		//se STATO==del
		if (obj.stato=='del') {
			$("#ot_cri_def_flag_"+key).css('background-color',"#f8faa6");
			$("#ot_cri_def_img_"+key).attr("src","img/del.png");
		}
		
		//se STATO==next
		if (obj.stato=='next') {
			$("#ot_cri_def_flag_"+key).css('background-color',"white");
			$("#ot_cri_def_img_"+key).attr("src","img/apps/verses.png");
		}
	});
	
	//if (_next_flag==1 && _ot_allow_prog.gsp=='none') chg_prog('next');
	//else {
		//se BF è nella fase 2
		if (_bf_flag.due==1) bf_update();
		setTimeout(trim_event_table,100);
	//}
}


function step_val(obj,op,ste,rif) {

	var valore=Number($('#ot_cri_def_'+obj).html());
	
	if (valore==rif) return;
	
	if (op=='-') valore=valore-ste;
	if (op=='+') valore=valore+ste;
	
	$('#ot_cri_def_'+obj).html(valore);
	
	//alert ($("#ot_kmm_next").val());
	
	if (_next_flag==1) setTimeout(function() {chg_prog('next')},100);
	else setTimeout(function() {_ot_service=ot_calcola_service($("#ot_kmm_next").val(),_ot_service);ot_draw_service();},100);
}

function ot_new_event(codice,tipo,rif) {
	
	//se sto già modificando un'altra riga torna indietro
	if (_ot_flag_st2==1) return;

	var txt="<span>Ev:</span>"+_ot_vnt_sel;
	
	var tmp='onclick="ot_insert_event(\''+codice+'\',\''+tipo+'\','+rif+');"';
	
	txt=txt+'<span style="position:relative;margin-left:5px;">qt&agrave;:</span>';
	
	txt=txt+'<input id="ot_st2_qta" type="text" size="5" value="1.0"/>';
	
	txt=txt+'<button style="position:relative;margin-left:5px;" '+tmp+'>-></button>';
	
	txt=txt+'<button style="position:relative;margin-left:5px;font-weight:bold;color:red;" onclick="ot_esc_st2('+rif+')">C</button>';
	
	_ot_flag_st2=1;
	
	$("#ot_st2_res_"+rif).html(txt);
}

function ot_esc_st2(rif) {
	$("#ot_st2_res_"+rif).html("");
	_ot_flag_st2=0;
}

function ot_esc_riga_st2(rif) {
	$("#ot_insert_riga_"+rif).html("");
	_ot_flag_st2=0;
}

function ot_insert_event(codice,tipo,rif) {
	//alert (codice+"."+tipo+"."+rif);
	
	var obj=$("#ot_st2_sel").val();
	var qta=$("#ot_st2_qta").val();
	
	if (confirm("Il codice "+codice+" verrà assegnato all'oggetto "+obj+" ("+_ot_oggetti[obj].descrizione+")")) {
	
		var param=JSON.stringify({"codice":codice,"tipo":tipo,"obj":obj,"qta":qta});
		
		$.ajax({"url":"core/step2_insert_event.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
		
	}
	
	else ot_esc_st2(rif);
}

function ot_del_event(codice,tipo,obj) {

	var param=JSON.stringify({"codice":codice,"tipo":tipo,"obj":obj});
	
	if (confirm("Il codice "+codice+" non indicherà più un evento "+obj)) {
		$.ajax({"url":"core/step2_del_event.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_new_selez(telaio,odl,riga,old,nuovo,obj,rif) {
	//se sto già modificando un'altra riga torna indietro
	if (_ot_flag_st2==1) return;
	
	var txt='Abilita per ('+_ot_oggetti[obj].descrizione+')';
	
	var tmp='onclick="ot_insert_selez(\''+telaio+'\','+odl+','+riga+',\''+old+'\',\''+nuovo+'\');"';
	
	txt=txt+'<button style="position:relative;margin-left:5px;" '+tmp+'>-></button>';
	
	txt=txt+'<button style="position:relative;margin-left:5px;font-weight:bold;color:red;" onclick="ot_esc_st2('+rif+')">C</button>';
	
	_ot_flag_st2=1;
	
	$("#ot_st2_res_"+rif).html(txt);
}

function ot_insert_selez(telaio,odl,riga,old,nuovo) {
	
	var param=JSON.stringify({"telaio":telaio,"odl":odl,"riga":riga,"old":old,"new":nuovo});
	
	$.ajax({"url":"core/step2_insert_selez.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
	//alert(ret);
	to_2();
	}});
}

function ot_del_selez(telaio,odl,riga,codice,obj) {
	//alert (telaio+'.'+odl+'.'+riga);
	
	var param=JSON.stringify({"telaio":telaio,"odl":odl,"riga":riga});
	
	if (confirm("Annullamento registrazione manuale per il codice "+codice)) {
		$.ajax({"url":"core/step2_del_selez.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_new_escl(telaio,odl,riga,old,nuovo) {

	if (confirm("ESCLUSIONE")) {
	
		var param=JSON.stringify({"telaio":telaio,"odl":odl,"riga":riga,"old":old,"new":nuovo});
		
		$.ajax({"url":"core/step2_insert_selez.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_escludi_pass(rif) {
	if (confirm("(ESCLUSIONE ODL) L'odl "+rif+" non sarà più considerato")) {
		var param=JSON.stringify({"odl":rif,"telaio":_ot_car.telaio});
		$.ajax({"url":"core/step2_escludi_pass.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_includi_pass(rif) {
	if (confirm("(AMMISSIONE ODL) L'odl "+rif+" sarà nuovamente considerato")) {
		var param=JSON.stringify({"odl":rif});
		$.ajax({"url":"core/step2_includi_pass.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_add_riga(rif) {
	//alert(JSON.stringify(_ot_pass[rif]));
	
	//se sto già modificando un'altra riga torna indietro
		if (_ot_flag_st2==1) return;
	
		var txt="<span>Ins:</span>"+_ot_vnt_sel;
		
		var tmp='onclick="ot_insert_riga(\''+rif+'\');"';
		
		txt=txt+'<button style="position:relative;margin-left:5px;" '+tmp+'>-></button>';
		
		txt=txt+'<button style="position:relative;margin-left:5px;font-weight:bold;color:red;" onclick="ot_esc_riga_st2(\''+rif+'\')">C</button>';
		
		_ot_flag_st2=1;
		
		$("#ot_insert_riga_"+rif).html(txt);
}

function ot_insert_riga(rif) {
	if (confirm('Aggiungi evento: '+$('#ot_st2_sel').val())) {
		var param=JSON.stringify({"odl":rif,"tipo":$('#ot_st2_sel').val(),"car":JSON.stringify(_ot_car)});
		//alert(param);
		$.ajax({"url":"core/step2_insert_riga.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_del_riga_man(rif,tipo) {
	if (confirm('Eliminare inserimento manuale '+tipo)) {
		var param=JSON.stringify({"odl":rif,"tipo":tipo});
		$.ajax({"url":"core/step2_del_riga.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

/*function ot_editc(rif) {
	//alert(JSON.stringify(_ot_pass[rif]));
	//var param=JSON.stringify({"rif":rif,"pass":_ot_pass});
	$("#ot_cover").show();
	
	$("div[id^='editc_data']").css('background-color','#c1faba');
	$("div[id^='editc_km']").css('background-color','#c1faba');
	
	//odl da modificare
	_ot_temp1=rif;
	//dati precedenti
	_ot_temp3={"rif":0,"data":"","km":0,"passo":'p'};
	
	$.each(_ot_pass,function(key,obj) {
		//alert (JSON.stringify(_ot_temp3)+"."+key);
		var error={"data":0,"km":0};
		
		//SUCCESSIVO
		if (_ot_temp3.passo=='a') {
			$("#editc_rif_s").html(obj.rif);
			$("#editc_data_s").html(data_db_to_ita(obj.data_fine));
			$("#editc_km_s").html(obj.km);
			_ot_temp3.passo='s';	
		}
		
		//se "successivo" non è stato ancora determinato
		if (_ot_temp3.passo!='s') {
			//se il passaggio è questo
			if (key==_ot_temp1) {
				//PRECEDENTE
				if (_ot_temp3.rif!=0) {
					$("#editc_rif_p").html(_ot_temp3.rif);
					$("#editc_data_p").html(data_db_to_ita(_ot_temp3.data));
					$("#editc_km_p").html(_ot_temp3.km);
				}
				else {
					$("#editc_rif_p").html("");
					$("#editc_data_p").html("");
					$("#editc_km_p").html("");
				}
				
				//ATTUALE
				$("#editc_rif_a").html(obj.rif);
				$("#editc_data_a").val(data_db_to_ita(obj.data_fine));
				$("#editc_km_a").val(obj.km);
				_ot_temp3.passo='a';
			}
		}		
		
		//SCRIVI TEMP3
		_ot_temp3.rif=obj.rif;
		_ot_temp3.data=obj.data_fine;
		_ot_temp3.km=obj.km;
	});
	
	//se "successivo" non è statodeterminato
	if (_ot_temp3.passo!='s') {
		$("#editc_rif_s").html("");
		$("#editc_data_s").html("");
		$("#editc_km_s").html("");
	}
	
	$("#ot_st2_editc").show();
}*/

function ot_editc(rif,manual) {
	if (manual==1) return;
	var temp='';
	
	//alert(rif);

	$("#ot_cover").show();
	
	//decisione del tipo di ordine
	if(rif.substr(0,3)=='man') {
		$("#ot_st2_editc").css('background-color','#cbdafc');
		$("#editc_alert").hide();
		$("#editc_conferma").attr('onclick','conferma_editc("man");');
	}
	else {
		$("#ot_st2_editc").css('background-color','#c1faba');
		$("#editc_alert").show();
		$("#editc_conferma").attr('onclick','conferma_editc("con");');
	}
	
	//PRECEDENTE
	temp=_ot_pass[rif].ordine.prec;
	if (temp=='') {
		$("#editc_rif_p").html("");
		$("#editc_data_p").html("");
		$("#editc_km_p").html("");
	}
	else {
		$("#editc_rif_p").html(_ot_pass[temp].rif);
		$("#editc_data_p").html(data_db_to_ita(_ot_pass[temp].data_fine));
		$("#editc_km_p").html(_ot_pass[temp].km);
	}
	
	//ATTUALE
	$("#editc_rif_a").html(_ot_pass[rif].rif);
	$("#editc_data_a").val(data_db_to_ita(_ot_pass[rif].data_fine));
	$("#editc_km_a").val(_ot_pass[rif].km);
	
	//SUCCESSIVO
	temp=_ot_pass[rif].ordine.succ;
	if (temp=='') {
		$("#editc_rif_s").html("");
		$("#editc_data_s").html("");
		$("#editc_km_s").html("");
	}
	else {
		$("#editc_rif_s").html(_ot_pass[temp].rif);
		$("#editc_data_s").html(data_db_to_ita(_ot_pass[temp].data_fine));
		$("#editc_km_s").html(_ot_pass[temp].km);
	}
	
	$("#ot_st2_editc").show();
}


function annulla_editc() {
	$("#ot_st2_editc").hide();
	$("#ot_cover").hide();
}

function conferma_editc(contesto) {
	var data=$("#editc_data_a").val();
	var km=parseInt($("#editc_km_a").val());
	var rif=$("#editc_rif_a").html();
	
	//alert(km);
	
	if (isNaN(km) || !controllo_data(data)) {
		alert('DATI NON CORRETTI');
		return;
	}
	
	var param=JSON.stringify({'data':data,'km':km,'rif':rif,'telaio':_ot_car.telaio});
	
	if (contesto=='con') {
		if (confirm('Stai per modificare i dati in CONCERTO')) {
			$.ajax({"url":"core/step2_modifica_odl_con.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
			//alert(ret);
			$("#ot_cover").hide();
			to_2();
			}});
		} 
	}
	
	if (contesto=='man') {
		if (confirm('Modifica dati ODL')) {
			$.ajax({"url":"core/step2_modifica_odl_man.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
			//alert(ret);
			$("#ot_cover").hide();
			to_2();
			}});
		} 
	}
}

function ot_passman() {
	$("#ot_cover").show();
	//txt=JSON.stringify(_ot_oggetti);
	_ot_temp1=0;
	$("#ot_st2_passman_main").html('');
	
	$.each(_ot_oggetti,function(key,obj) {
		var txt='<div style="position:relative;margin-top:5px;"><input type="checkbox" value="'+obj.codice+'" id="passman_chk_'+_ot_temp1+'"/>';
		txt=txt+'<span style="position:relative;left:5px;">('+obj.codice+') '+obj.descrizione+'</span></div>';
		$("#ot_st2_passman_main").append(txt);
		_ot_temp1++;
	});
	
	$("#ot_st2_passman").show();
}

function annulla_passman() {
	$("#ot_st2_passman").hide();
	$("#ot_cover").hide();
}

function conferma_passman() {
	_ot_temp1=0;
	_ot_temp3={};
	var data=$("#passman_data").val();
	var km=parseInt($("#passman_km").val());
	
	if (isNaN(km) || !controllo_data(data)) {
		alert('DATI NON CORRETTI');
		return;
	}
	
	$("input[id^='passman_chk_']:checked").each(function(index) {
		//alert($(this).val());
		_ot_temp3[_ot_temp1]=$(this).val();
		_ot_temp1++;
	});
	
	//alert(JSON.stringify(_ot_temp3));
	
	if (_ot_temp1==0) {
		alert('NESSUN INTERVENTO SELEZIONATO');
		return;
	}
	
	//costruisci passaggio manuale
	var passman={};

	passman.veicolo=_ot_car.veicolo;	
	passman.km=km;
	passman.data_fatt=data_ita_to_db(data);
	passman.data_odl=data_ita_to_db(data);
	passman.data_fine=data_ita_to_db(data);
	
	//alert(JSON.stringify(passman));
	var param=JSON.stringify({"passman":JSON.stringify(passman),"righe":JSON.stringify(_ot_temp3),"car":JSON.stringify(_ot_car)});
	$.ajax({"url":"core/step2_insert_passman.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
	//alert(ret);
	$("#ot_cover").hide();
	to_2();
	}});
}

function ot_del_passman(rif) {
	if (confirm('CANCELLA passaggio')) {
		var param=JSON.stringify({'rif':rif,'telaio':_ot_car.telaio});
		$.ajax({"url":"core/step2_del_passman.php","type":"POST","data":{"param":param},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		to_2();
		}});
	}
}

function ot_radio_kmm(val) {
	_ot_kmm_service=val;
	_ot_rif_tonext.km=parseInt(_ot_car.km)+parseInt(($("#ot_kmm_next").val()*_ot_kmm_service));
	$("#ot_kmm_next_tot").html(parseInt(_ot_car.km)+parseInt(($("#ot_kmm_next").val()*_ot_kmm_service)));
	if (_next_flag==1) setTimeout(function() {chg_prog('next')},100);
	else setTimeout(function() {_ot_service=ot_calcola_service($("#ot_kmm_next").val(),_ot_service);ot_draw_service();},100);
}

function ot_select_kmm(next) {
	if (_ot_kmm_service!=-1) {
		//componi la data di oggi
		var d=new Date();
		var dd=''+d.getFullYear();
		var dtemp=d.getMonth()+1;
		if (dtemp<10) dd=dd+'0'+dtemp;
		else dd=dd+dtemp;
		
		//se la data di consegna è valida
		if (_ot_car.consegna!="") {
			_ot_rif_tonext.t=parseInt(mesi_diff(dd,_ot_car.consegna.substr(0,6)))+parseInt(next);
		}
		else _ot_rif_tonext.t=0;
						
		_ot_rif_tonext.km=parseInt(_ot_car.km)+parseInt(next*_ot_kmm_service);
		
		$("#ot_kmm_next_tot").html(parseInt(_ot_car.km)+parseInt(next*_ot_kmm_service));
		
		if (_ot_rif_tonext.t!=0) {
			$("#ot_kmm_next_tot_t").html('('+_ot_rif_tonext.t);
		}
		else $("#ot_kmm_next_tot_t").html("");

		//alert (_next_flag);
		if (_next_flag==1) setTimeout(function() {chg_prog('next')},100);
		else setTimeout(function() {_ot_service=ot_calcola_service(next,_ot_service);ot_draw_service();},100);
	}
}

function ot_edit_kmm() {
	//modifica data di consegna o stima kilometrica
}

function edit_service_stato(key) {
	var old=_ot_service[key].stato;
	
	if (old=='no') {
		if (confirm("INCLUDI l'oggetto?")) {
			_ot_service[key].stato='sel';
		}
	}
	
	if (old=='si') {
		if (confirm("ESCLUDI l'oggetto?")) {
			_ot_service[key].stato='del';
		}
	}
	
	if (old=='sel') {
		if (confirm("CANCELLA l'oggetto?")) {
			_ot_service[key].stato='no';
		}
	}
	
	if (old=='del') {
		if (confirm("RIABILITA l'oggetto?")) {
			_ot_service[key].stato='si';
		}
	}
	
	ot_draw_service();
}

//----------------------------------------------------------------------------------------------------------

function update_alert_fixed() {

	var color='white';
	
	for (var i in _ot_moduli) {
		for (var x in _ot_moduli[i]) {
			if (_ot_moduli[i][x].azione==1) color='green';
			else if (_ot_moduli[i][x].escludi==1) color='white';
			else if (_ot_moduli[i][x].answer.elementi>0) {
				color='yellow';
				if (_ot_moduli[i][x].answer.ok==1 && _ot_moduli[i][x].periodo==1) color='red';
			}
			
			_ot_moduli[i][x].stato=color;
			$('#ot_fixed_alert_'+x).css('background-color',color);
		}
	}
}

function set_alert_escluso(id) {
	//alert(id);
	for (var i in _ot_moduli) {
		for (var x in _ot_moduli[i]) {
			//alert (i+' '+x+' '+id+' '+_ot_moduli[i][x].escludi);
			if (x==id) {
				if (_ot_moduli[i][x].escludi==1) _ot_moduli[i][x].escludi=0;
				else _ot_moduli[i][x].escludi=1;
				//alert (i+' '+x+' '+id+' '+_ot_moduli[i][x].escludi);
			}
		}
	}
	
	update_alert_fixed();
}

//------------------------------------------------------------------------------------------------------
//VENDITA ATTIVA

function ot_sel_va(lam,tipo) {
	$(".OTVA_"+lam+"_"+tipo).each(function() {
		if ($(this).prop('checked')) {
			$(this).prop('checked',false);
		}
		else {
			$(this).prop('checked',true);
		}
	});
}

function ot_esegui_va(codice) {

	_ot_temp1=_ot_venatt[codice];
	_ot_temp3={};
	
	$("input[id^='OTVA']:checked").each(function() {
		_ot_temp3[$(this).val()]=_ot_temp1;
	});
	
	//alert(JSON.stringify(_ot_temp3));
	//alert(Object.keys(_ot_temp3).length);
	
	if (Object.keys(_ot_temp3).length>0) {
		$.ajax({"url":"core/esegui_va.php","type":"POST","data":{"odl":_ot_car.odl,"marca":_ot_car.marca,"obj":JSON.stringify(_ot_temp3)},"async":false,"cache":false,"success":function(ret) {
			//alert(ret);
			ot_va_postit_off();
			set_V();
		}});
	}
}

function ot_del_va(riga) {
	$.ajax({"url":"core/del_va.php","type":"POST","data":{"odl":_ot_car.odl,"riga":riga},"async":false,"cache":false,"success":function(ret) {
		//alert(ret);
		set_V();
	}});
}