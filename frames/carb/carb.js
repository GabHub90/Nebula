function data_db_to_ita(txt) {
	return txt.substr(6,2)+"/"+txt.substr(4,2)+"/"+txt.substr(0,4);
}

function data_ita_to_db(d) {
	return ''+d.substr(6,4)+d.substr(3,2)+d.substr(0,2);
}

function data_val_to_ita(d) {
	return d.substr(8,2)+"/"+d.substr(5,2)+"/"+d.substr(0,4);
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

//------------------------------------------------------------------------------------------------

//interfaccia

function carb_set_global() {
	//Variabili globali
	_carb_gescas=[];
	_carb_gestioni={};
	_carb_impstd={};
	_carb_reparti={};
	_carb_lista=[];
	_carb_creati=[];
	_carb_tofill=[];
	_carb_toris=[];
	_carb_values={"ID":0,"sel":"tartel","importo":0,"pieno":0,"vettura":0,"modello":"","reparto":"","nota":"","gestione":"","causale":""};
	_carb_id=0;
	_carb_psw={};
	_key=0;
	_carb_storico={};
	_carb_st_filters={};
	_carb_st_lista={};
	_carb_fe_lista={};
	_carb_st_tot={"tot":0,"pag":0,"ris":0,"num":0};
	_carb_temp=0;
	
	carb_home("");
}

// stato dei buoni
// CREATO --> [STAMPATO - DACOMPLETARE - DARIS] -si può cancellare
// STAMPATO --> ANNULLATO
// DACOMPLETARE --> [COMPLETATO - DARIS - ANNULLATO]
// DARIS --> [RISARCITO - PAGATO - ANNULLATO(se no pieno)]
// VERIFICA non è uno stato ma un flag


function carb_close_lista() {

	$("#carb_right").html('');
	$("#carb_cover_sx").hide();
}
/*function carb_cancella_importo(flag) { 
	if (flag) {
		$("#carb_importo").prop("disabled",true);
	}
	else {
		$("#carb_importo").prop("disabled",false);
	}
}*/

function carb_st_cover() {
	$("#carb_st_query_cover").toggle();
}

function carb_annulla_st_query() {
	$("#carb_right").html('');
	$("#carb_st_query_filters").html('');
	carb_st_cover();
}

function carb_home(key) {
	_key=key;
	$.ajax({"url":"core/home.php","type":"POST","data":{"key":key},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_main").html(ret);
		//$("#vers_pdf").html(ret);
		if (_key>0) {
			//alert(_key);
			setTimeout(function() {carb_stampa(_key);_key=0;},100);
		}
	}});
}

function carb_new() {
	$.ajax({"url":"core/crea.php","type":"POST","data":{"old":""},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_main").html(ret);
		//$("#vers_pdf").html(ret);
		setTimeout(carb_lista,200);
	}});
}

function carb_edit(id) {

	_carb_values.causale=_carb_creati[id].causale;
	
	$.ajax({"url":"core/crea.php","type":"POST","data":{"old":JSON.stringify(_carb_creati[id])},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_main").html(ret);
		//$("#vers_pdf").html(ret);
		setTimeout(function() {carb_lista(id);},200);
	}});
}

function carb_stampa(key) {
	
	_carb_id=key;
	
	$.ajax({"url":"core/stampa.php","type":"POST","data":{"obj":JSON.stringify(_carb_creati[key])},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_right").html(ret);
		//$("#vers_pdf").html(ret);
		$("#carb_cover_sx").show();
	}});
	
}

function carb_storico() {
	$.ajax({"url":"core/storico.php","type":"POST","async":false,"cache":false,"success":function(ret) {
		//compila storico
		$("#carb_main").html(ret);
		//$("#vers_pdf").html(ret);
	}});
}

function carb_tartel_switch(id) {

	if (id=='ok') {
		$("#carb_tartel_sel").css("display","none");
		$("#carb_tartel_ok").css("display","inline");
	}
	
	if (id=='sel') {
		$("#carb_tartel_sel").css("display","inline");
		$("#carb_tartel_ok").css("display","none");
		$("#carb_tt_telaio").html("");
		$("#carb_tt_targa").html("");
		$("#carb_tt_des").html("");
		
		_carb_values.vettura=0;
		_carb_values.modello="";
	}
	
	carb_check_sel('tartel');	
}

function carb_check_sel(id) {

	_carb_values.sel=id;
	
	if (id=="tartel") {	
		$("#carb_tartel_main").css("visibility","visible");
	}
	else {
		$("#carb_tartel_main").css("visibility","hidden");
	}
}

function carb_cancella_importo(flag) { 

	if (flag) {
		$("#carb_importo").prop("disabled",true);
	}
	else {
		$("#carb_importo").prop("disabled",false);
	}
}

function carb_lista(flag) {
//AZZERAMENTO VALORI
	 _carb_values.vettura=0;
	 _carb_values.gestione="";
	var txt=$("#carb_tt").val();
	
	if (txt==="") return;
	
	$("#carb_cover_sx").show();
	
	//FLAG è $old[ID] quindi è = 0 se crea nuovo e diverso se crea modifica
	$.ajax({"url":"core/get_tt.php","type":"POST","data":{"txt":txt,"flag":flag},"async":true,"cache":false,"success":function(ret) {
		$('#carb_right').html(ret);
	}});
	
}

function carb_tartel_sel(key,modello) {
	
	_carb_values.vettura=key;
	_carb_values.modello=modello;
	_carb_values.gestione=_carb_lista[key].cod_natura;
	
	$("#carb_tt_telaio").html(_carb_lista[key].telaio);
	$("#carb_tt_targa").html(_carb_lista[key].targa);
	$("#carb_tt_des").html(_carb_lista[key].des);
	$("#carb_tt_ges").html('Gestione: '+_carb_lista[key].des_natura);
	
	write_opt_cau();
	
	carb_tartel_switch('ok');
	//_carb_values.gestione="";
	carb_close_lista();
}

function write_opt_cau() {

	//alert(_carb_values.causale);

	//se non c'è una vettura selezionata non eseguire
	if (_carb_values.vettura==0) return;
	
	//se non è stato scelto un reparto
	if ($("#carb_reparto").val()=="") {
		var txt='<option value="">Seleziona prima un reparto</option>';
	}
	else {
		//scrivi options causale
		var txt='<option value="">Seleziona ...</option>';
		for (var x in _carb_gescas) {
			if (_carb_gescas[x].tipo_rep==_carb_reparti[$("#carb_reparto").val()].tipo) {
				if (_carb_gescas[x].gestione==_carb_values.gestione) {
					txt+='<option value="'+_carb_gescas[x].causale+':'+_carb_gescas[x].autz+'" ';
						if(_carb_gescas[x].causale==_carb_values.causale) txt+='selected="selected"';
					txt+='>'+_carb_gescas[x].testo+'</option>';
				}
			}
		}
		
		txt+='<option value="FORGES"><b>Forza gestione</b></option>';
	}
	
	$("#carb_causale").html(txt);
	
	write_importo_std();
}

function write_importo_std() {

	//se non è stata selezionata una causale
	if ($("#carb_causale").val()=="") {
		return;
	}
	
	//se la causale è FORGES richiama la funzione specifica per forzare una GESTIONE
	if ($("#carb_causale").val()=="FORGES") {
		setTimeout(carb_force_ges,100);
		return;
	}
	
	$("#carb_importo_std").html("");
	
	var id=""+_carb_values.modello.substr(0,2)+$("#carb_causale").val();
	
	//alert(id);
	
	//se esiste un valore standard
	if (id in _carb_impstd) {
		//scrivi l'avviso
		$("#carb_importo_std").html("Importo standard: "+number_format(_carb_impstd[id].importo,2,',','.'));
		//se importo è 0 e non è pieno scrivi l'importo nel form
		if ($("#carb_importo").val()==0 && !$("#carb_sel_pieno").prop("checked")) {
			$("#carb_importo").val(_carb_impstd[id].importo);
		}
	}
}

//------------------------------------------------------------------------------------------------

function carb_confirm() {

	var txt="";
	var str=/^[1-9][0-9]*$/;
	
	$("#carb_main_error").html("");
	
	//verifica vettura
	if (_carb_values.sel=='tartel') {
		if (_carb_values.vettura===0) {
			txt+="- Vettura non selezionata -";
		}
		//verifica causale
		if ($("#carb_causale").val()=="" || $("#carb_causale").val()=="FORGES") {
			txt+="- manca causale -";
		}
		else {
			_carb_values.causale=$("#carb_causale").val();
		}
	}
	//verifica tanica
	if (_carb_values.sel=='tanica') {
		_carb_values.gestione="TANICA";
		_carb_values.causale="INT";
	};
	//verifica importo
	if (!$("#carb_sel_pieno").prop("checked")) {
		if (!str.test($("#carb_importo").val())) {
			txt+="- Importo non corretto -";
		}
		else {
			_carb_values.importo=$("#carb_importo").val();
			_carb_values.pieno=0;
		}
	}
	else {
		_carb_values.pieno=1;
		_carb_values.importo=0;
	}
	
	//verifica reparto
	if ($("#carb_reparto").val()==="") {
		txt+="- Seleziona un reparto -";
	}
	else {
		_carb_values.reparto=$("#carb_reparto").val();
	}
	
	//alert($("input[id='carb_urante']:checked").val());
	//verifica carburante
	if (!$("input[id='carb_urante']:checked").val()) {
		txt+="- Seleziona tipo carburante -";
	}
	else {
		_carb_values.carb_tipo=$("input[id='carb_urante']:checked").val();
	}

	//check finale
	if (txt.length>0) {
		$("#carb_main_error").html(txt);
		return;
	}
	
	//lettura nota
	_carb_values.nota=$("#carb_nota").val();
	
	//lettura ID
	_carb_values.ID=$("#carb_buono_id").val();
	
	
	//scrittura buono
	//alert (JSON.stringify(_carb_values));
	

	$.ajax({"url":"core/buono_crea.php","type":"POST","data":{"values":JSON.stringify(_carb_values)},"async":false,"cache":false,"success":function(ret) {
		//apri pdf
		//carb_home(ret);
		//alert(ret);
		eval(ret);
	}});

}
	
	
function carb_scrittura(){
	
	$("#carb_stampa_error").html("");
	
	var psw=$("#carb_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//se necessita una password autz e non lo è errore
	var autz=$("#carb_stampa_autz").val();
	if (_carb_psw[psw].autz!=autz) {
		$("#carb_stampa_error").html("utente non abilitato");
		return;
	}
	
	//controllo dati
	var carb_form_st={"utente":0,"des_utente":"","rich":0,"causale":""};
	var error="";
	
	//verifica richiedente
	if ($("#carb_richiedente").val()==0) {
		error+="- manca richiedente -";
	}
	else {
		carb_form_st.rich=$("#carb_richiedente").val();
	}
	
	//nota obbligatoria se gestione CLIENTE
	if (_carb_creati[_carb_id].gestione=='_CLIENTE_' && $("#carb_nota").val()=="") {
		error+="- Nota obbligatoria se CLIENTE -";
	}
	else {
		
		_carb_creati[_carb_id].nota=$("#carb_nota").val();
	}
	
	
	if  (error!="") {
		$("#carb_stampa_error").html(error);
		
		return;
	}
	
	//scrittura utente
	carb_form_st.utente=_carb_psw[psw].n_collab;
	carb_form_st.des_utente=_carb_psw[psw].cognome+" "+_carb_psw[psw].nome;
	
	//alert (_carb_id);
	
	
	//creazione pdf
	$.ajax({"url":"core/genera_buono.php","type":"POST","data":{"b1":JSON.stringify(_carb_creati[_carb_id]),"b2":JSON.stringify(carb_form_st),"storico":JSON.stringify(_carb_storico)},"async":false,"cache":false,"success":function(ret) {
		//apri pdf
		//$("#carb_pdf").prop("src","core/prova.php");
		//$("#carb_pdf").html("Hello world");
		window.frames['carb_pdf'].document.getElementById('carb_pdf_fr').innerHTML=ret;
		//$("#carb_pdf").html(ret);
		setTimeout(carb_pp,200);
	}});

}

function carb_pp() {
	window.frames["carb_pdf"].focus();
	window.frames["carb_pdf"].print();
	$("#carb_right").html("");
	carb_home(0);
}


function carb_elimina(id) {	
	//alert(id);
	if (confirm('Vuoi procedere con l\'eliminazione del buono?')) {
	
		$.ajax({"url":"core/elimina_buono.php","type":"POST","data":{"id":id},"async":false,"cache":false,"success":function(ret) {
			document.location.reload();
		}});
	} 
}

function carb_annulla(key,tipo) {

	_carb_id=key;
	
	if (tipo=="fill") {
		
		$.ajax({"url":"core/annulla.php","type":"POST","data":{"obj":JSON.stringify(_carb_tofill[key]),"tipo":tipo},"async":false,"cache":false,"success":function(ret) {
			//compila home
			$("#carb_right").html(ret);
			//$("#vers_pdf").html(ret);
			$("#carb_cover_sx").show();
		}});
	}
	
	if (tipo=="ris") {
		
		$.ajax({"url":"core/annulla.php","type":"POST","data":{"obj":JSON.stringify(_carb_toris[key]),"tipo":tipo},"async":false,"cache":false,"success":function(ret) {
			//compila home
			$("#carb_right").html(ret);
			//$("#vers_pdf").html(ret);
			$("#carb_cover_sx").show();
		}});
	}
	
	if (tipo=="storico") {
		
		$.ajax({"url":"core/annulla.php","type":"POST","data":{"obj":JSON.stringify(_carb_st_lista[key]),"tipo":tipo},"async":false,"cache":false,"success":function(ret) {
			//compila home
			$("#carb_st_annulla").html(ret);
			//$("#vers_pdf").html(ret);
			$("#carb_cover").show();
			$("#carb_st_annulla").show();
		}});
	}
}

function carb_close_annulla(){
	
	$("#carb_annulla_error").html("");
	
	var psw=$("#carb_annulla_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//controllo dati
	var carb_annulla_form={"nota":"","utente":0};
	var error="";
	
	//verifica nota obbligatoria
	if ($("#carb_annulla_nota").val()=="") {
		error+="- Nota obbligatoria -";
	}
	else {
		carb_annulla_form.nota=$("#carb_annulla_nota").val();
	}
	
	if  (error!="") {
		$("#carb_annulla_error").html(error);
		
		return;
	}
	
	carb_annulla_form.utente=_carb_psw[psw].n_collab;
	
	//scrittura
	$.ajax({"url":"core/set_annulla.php","type":"POST","data":{"id":_carb_id,"obj":JSON.stringify(carb_annulla_form)},"async":false,"cache":false,"success":function(ret) {
		$("#carb_cover_sx").hide();
		document.location.reload()
	}});
}

function carb_close_st_annulla(){
	
	$("#carb_annulla_error").html("");
	
	var psw=$("#carb_annulla_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//controllo dati
	var carb_annulla_form={"nota":"","utente":0};
	var error="";
	
	//verifica nota obbligatoria
	if ($("#carb_annulla_nota").val()=="") {
		error+="- Nota obbligatoria -";
	}
	else {
		carb_annulla_form.nota=$("#carb_annulla_nota").val();
	}
	
	if  (error!="") {
		$("#carb_annulla_error").html(error);
		
		return;
	}
	
	carb_annulla_form.utente=_carb_psw[psw].n_collab;
	
	//scrittura
	$.ajax({"url":"core/set_annulla.php","type":"POST","data":{"id":_carb_id,"obj":JSON.stringify(carb_annulla_form)},"async":false,"cache":false,"success":function(ret) {
		carb_close_st_lista();
		carb_st_cerca();
	}});
}


function carb_fill(key) {

	_carb_id=key;
		
	$.ajax({"url":"core/fill.php","type":"POST","data":{"obj":JSON.stringify(_carb_tofill[key])},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_right").html(ret);
		//$("#vers_pdf").html(ret);
		$("#carb_cover_sx").show();
	}});
	
}

function carb_close_fill(){

	var str=/^[1-9][0-9]*$/;
		
	$("#carb_fill_error").html("");
	
	var psw=$("#carb_fill_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//controllo dati
	var carb_fill_form={"importo":0,"gestione":_carb_tofill[_carb_id].gestione,"nota":_carb_tofill[_carb_id].nota};
	var error="";
	
	//verifica importo
	if (!str.test($("#carb_fill_importo").val())) {
		error+="- Importo non corretto -";
	}
	else {
		carb_fill_form.importo=$("#carb_fill_importo").val();
	}
	
	//verifica nota obbligatoria se gestione CLIENTE
	if (_carb_tofill[_carb_id].gestione=='_CLIENTE_' && $("#carb_fill_nota").val()=="") {
		error+="- Nota obbligatoria se CLIENTE -";
	}
	else {
		carb_fill_form.nota=$("#carb_fill_nota").val();
	}
	
	if  (error!="") {
		$("#carb_fill_error").html(error);
		
		return;
	}
	
	//scrittura importo
	$.ajax({"url":"core/set_importo.php","type":"POST","data":{"id":_carb_id,"obj":JSON.stringify(carb_fill_form)},"async":false,"cache":false,"success":function(ret) {
		$("#carb_cover_sx").hide();
		document.location.reload()
	}});

}


function carb_ris(key) {

	_carb_id=key;
		
	$.ajax({"url":"core/ris.php","type":"POST","data":{"obj":JSON.stringify(_carb_toris[key])},"async":false,"cache":false,"success":function(ret) {
		//compila home
		$("#carb_right").html(ret);
		//$("#vers_pdf").html(ret);
		$("#carb_cover_sx").show();
	}});
	
}


function carb_close_ris(){
	
	$("#carb_ris_error").html("");
	
	var psw=$("#carb_ris_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//controllo dati
	var carb_ris_form={"flag":0,"nota":"","utente":0};
	var error="";
	
	//lettura RADIO
	var ris="";
	
	ris=$("input[name='carb_ris_radio']:checked").val();
	
	//se non c'è stata una selezione ritorna
	if (ris==null) return;
	else carb_ris_form.flag=ris;
	
	//verifica nota obbligatoria se NON risarcito
	if (ris==0 && $("#carb_ris_nota").val()=="") {
		error+="- Nota obbligatoria se NON risarcito -";
	}
	else {
		carb_ris_form.nota=$("#carb_ris_nota").val();
	}
	
	if  (error!="") {
		$("#carb_ris_error").html(error);
		
		return;
	}
	
	carb_ris_form.utente=_carb_psw[psw].n_collab;
	
	//scrittura
	$.ajax({"url":"core/set_ris.php","type":"POST","data":{"id":_carb_id,"obj":JSON.stringify(carb_ris_form)},"async":false,"cache":false,"success":function(ret) {
		$("#carb_cover_sx").hide();
		document.location.reload()
	}});

}

function carb_st_cerca() {

	var val={"data_i":"","data_f":"","tartel":"","reparto":"","richiedente":"","operatore":"","tipo":"","verifica":"","v_flag":0};
	var psw=$("#carb_cerca_pw").val();
	
	if ($("input[name='carb_st_validate']").prop("checked")) val.v_flag=1;

	//verifica password se verifica=1
	if (val.v_flag==1 && !(psw in _carb_psw)) return;
	
		
	$("#carb_st_error").html("");
	
	if ($("#carb_st_data_i").val()>$("#carb_st_data_f").val() || $("#carb_st_data_f").val()=="") {
		$("#carb_st_error").html("Data inizio maggiore di Data fine");
		return;
	}
	
	val.data_i=$("#carb_st_data_i").val();
	val.data_f=$("#carb_st_data_f").val();
	val.tartel=$("#carb_st_tartel").val();
	val.reparto=$("#carb_st_reparto").val();
	val.richiedente=$("#carb_st_richiedente").val();
	val.operatore=$("#carb_st_operatore").val();
	val.tipo=$("input[name='carb_st_tipo']:checked").val();
	val.verifica=$("input[name='carb_st_v']:checked").val();
	
	//alert(JSON.stringify(val));
	
	
	$.ajax({"url":"core/storico_lista.php","type":"POST","data":{"val":JSON.stringify(val)},"async":false,"cache":false,"success":function(ret) {
		carb_st_cover();
		$('#carb_right').html(ret);
		setTimeout(carb_st_setfilters,100);
	}});

}

function carb_st_setfilters() {

	var txt='<div style="margin-top:5px;">';
		txt+='<label>Numero buoni:</label><span id="carb_st_num" style="margin-left:5px;font-size:13pt;"></span>';
		txt+='<label style="margin-left:20px;">Importo totale:</label><span id="carb_st_tot" style="margin-left:5px;font-size:13pt;"></span>';
		txt+='<span style="margin-left:20px;">( Pagati:</span><span id="carb_st_tot_pag" style="font-size:13pt;margin-left:5px;"></span><span> / Risarciti:</span><span id="carb_st_tot_ris" style="font-size:13pt;margin-left:5px;"></span><span> )</span>';
	txt+='</div>';

	txt+='<table style="margin-top:5px;border-top:1px solid black;border-collapse:collapse;font-size:10pt;">';
		txt+='<thead>';
			txt+='<tr>';
				for (var x in _carb_st_filters) {
					txt+='<th>'+x+'</th>';
				}
			txt+='</tr>';
		txt+='</thead>';
		txt+='<tbody>';
			txt+='<tr style="text-align:left;">';
				for (var x in _carb_st_filters) {
					txt+='<td style="vertical-align:top;padding:4px;">';
						for (var y in _carb_st_filters[x]) {
							//if (x=='Gestione') alert(y);
							if (_carb_st_filters[x][y].flag==1) {
								txt+='<div>';
									txt+='<input type="checkbox" value="'+y+'" checked="checked" onclick="carb_set_filter(this.checked,this.value,\''+x+'\');"/>';
									txt+='<span>'+_carb_st_filters[x][y].testo.substr(0,20)+'</span>';
								txt+='</div>';
							}
						}
					txt+='</td>';
				}
			txt+='</tr>';
		txt+='</tbody>';
	txt+='</table>';
	
	$("#carb_st_query_filters").html(txt);
	//$("#carb_st_query_filters").html(JSON.stringify(_carb_st_filters));
	
	carb_update_tot();
}

function carb_set_filter(flag,val,key) {
	//flag=checked True/False
	//val=valore dell'attributo
	//key=c_attributo del TR
	_carb_temp=flag;
	
	$("tr[c_"+key+"="+val+"]").each(function() {
		if(_carb_temp) {
			$(this).show();
			_carb_st_lista[$(this).attr("c_ID")].stampa=1;
		}
		else {
			$(this).hide();
			_carb_st_lista[$(this).attr("c_ID")].stampa=0;
		}	
	});
	
	carb_update_tot();	
}

function carb_update_tot() {
	_carb_st_tot.tot=0;
	_carb_st_tot.pag=0;
	_carb_st_tot.ris=0;
	_carb_st_tot.num=0;
	
	for (var x in _carb_st_lista) {
		//se il record non è filtrato e non è annullato
		if (_carb_st_lista[x].stampa==1 && _carb_st_lista[x].stato!='annullato') {
			_carb_st_tot.tot+=Number(_carb_st_lista[x].importo);
			_carb_st_tot.num++;
			//se stato=risarcito alimenta il valore
			if (_carb_st_lista[x].stato=='risarcito') {
				_carb_st_tot.ris+=Number(_carb_st_lista[x].importo);
			}
		}
	}
	
	_carb_st_tot.pag=_carb_st_tot.tot-_carb_st_tot.ris;
	
	//aggiorna a video
	$("#carb_st_tot").html(number_format(_carb_st_tot.tot,2,',','.'));
	$("#carb_st_tot_pag").html(number_format(_carb_st_tot.pag,2,',','.'));
	$("#carb_st_tot_ris").html(number_format(_carb_st_tot.ris,2,',','.'));
	$("#carb_st_num").html(number_format(_carb_st_tot.num,0,',','.'));
	
	$("#carb_stlista_tot").html(number_format(_carb_st_tot.tot,2,',','.'));
	$("#carb_stlista_tot_pag").html(number_format(_carb_st_tot.pag,2,',','.'));
	$("#carb_stlista_tot_ris").html(number_format(_carb_st_tot.ris,2,',','.'));
	$("#carb_stlista_num").html(number_format(_carb_st_tot.num,0,',','.'));
}

function carb_st_verifica(id,flag) {
	
	if (flag) {
		$("#carb_v_"+id).prop("checked",false);
		
		$.ajax({"url":"core/set_verifica.php","type":"POST","data":{"id":id,"flag":flag},"async":false,"cache":false,"success":function(ret) {
			//alert(ret);
			$("#carb_v_span_"+id).html('V');
			$("#carb_v_"+id).prop("checked",true);
		}});
	}
	else {
		$("#carb_v_"+id).prop("checked",true);
		
		$.ajax({"url":"core/set_verifica.php","type":"POST","data":{"id":id,"flag":flag},"async":false,"cache":false,"success":function(ret) {
			//alert(ret);
			$("#carb_v_span_"+id).html('');
			$("#carb_v_"+id).prop("checked",false);
		}});
	}
}



function carb_stampa_st_query() {
	$.jPrintArea('#carb_st_lista_div');
}

function carb_stampa_fe_query() {
	$.jPrintArea('#carb_st_lista_div');
}

function carb_close_st_lista() {

	$("#carb_st_annulla").html('');
	$("#carb_cover").hide();
}

function carb_force_ges() {

	var tipo_rep=_carb_reparti[$("#carb_reparto").val()].tipo;
	
	//alert(tipo_rep);
		
	$.ajax({"url":"core/force_ges.php","type":"POST","data":{"tipo":tipo_rep},"async":false,"cache":false,"success":function(ret) {
		$("#carb_right").html(ret);
		$("#carb_cover_sx").show();
	}});
	
}

function carb_close_forges() {

	$("#carb_forges_error").html("");
	
	var psw=$("#carb_forges_pw").val();
	//verifica password
	if (!(psw in _carb_psw)) return;
	
	//controllo dati
	var ges=$("#carb_forges_ges").val();
	//var carb_forges_form={"gestione":$("#carb_forges_ges").val(),"nota":""};
	var error="";
		
	//verifica nota obbligatoria
	if ($("#carb_forges_nota").val()=="") {
		error+="- Nota obbligatoria -";
	}
	/*else {
		carb_forges_form.nota=$("#carb_forges_nota").val();
	}*/
	
	if  (error!="") {
		$("#carb_forges_error").html(error);
		
		return;
	}
	
	
	_carb_values.gestione=ges;
	$("#carb_tt_ges").html("Gestione: "+_carb_gestioni[ges]);
	
	
	$("#carb_right").html("");
	$("#carb_cover_sx").hide();
	
	write_opt_cau();
}

///////////////////////////////////////////////////////////////////////////////

function carb_query_fe() {
	
	var val={"data_i":"","data_f":"","sede":""};
	
	val.data_i=$("#carb_st_data_i").val();
	val.data_f=$("#carb_st_data_f").val();
	val.sede=$("#carb_fe_sede").val();
	
	$.ajax({"url":"core/storico_fe.php","type":"POST","data":{"param":val},"async":false,"cache":false,"success":function(ret) {
		carb_st_cover();
		$('#carb_right').html(ret);
	}});
	
}

function carb_genera_tracciato() {
	
	//alert(JSON.stringify(_lista));
	
	$.ajax({"url":"core/genera_tracciato.php","async":true,"type":"POST","data":{"lista":_carb_fe_lista},"cache":false,"success":function(ret) {
		$("#download").prop("src","http://"+window.location.hostname+"/apps/ammo/core/carb/core/download.php");
	}});	
	
}