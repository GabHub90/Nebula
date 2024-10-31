//oggetti

function elemento (chsum) {
	//"elemento" assume una compilazione corretta al momento dell'invio del modulo e serve per raccogliere le informazioni del modulo stesso. La funzione di spedizione ritorna del codice JS che corregge l'oggetto affinché possa avvenire una corretta registrazione. Prima dell'invio i metodi dell'oggetto non vengono usati.
	
	this.chsum=chsum;
	this.dati={};
		
	this.init=function() {
		this.chk_el=0;
		this.chk_sms=0;
		this.chk_mail=0;
		this.chk_link=0;
		this.phone="";
		this.address="";
		this.programma=0;
		
		this.model_sms=0;
		this.model_mail=0;
		this.model_link=0;
		
		//STATO: "DISABLED" "ENABLED"
		this.stato_el="disabled";
		this.stato_sms="disabled";
		this.stato_mail="disabled";
		this.stato_link="disabled";
		
		//ICON: "DEFAULT" (phone,mail,link) "OK" "STOP" "ERROR"
		this.icon_sms="img/stop.png";
		this.icon_mail="img/stop.png";
		this.icon_link="img/stop.png";
	}
	
	this.set_sms=function(val) {
		
		if (val=="ok" || val=="alert") {
			this.stato_sms="disabled";
			this.chk_sms=0;
			$("#chk_sms_"+this.chsum).attr("disabled",true);
			$("#chk_sms_"+this.chsum).attr("checked",false);
		}
		
		this.icon_sms="img/"+val+".png";
		this.set_icon("#report_sms_"+this.chsum,val);
	}
	
	this.set_mail=function(val) {
		
		if (val=="ok" || val=="alert") {
			this.stato_mail="disabled";
			this.chk_mail=0;
			$("#chk_mail_"+this.chsum).attr("disabled",true);
			$("#chk_mail_"+this.chsum).attr("checked",false);
		}
		
		this.icon_mail="img/"+val+".png";
		this.set_icon("#report_mail_"+this.chsum,val);
	}
	
	this.set_link=function(val) {
		
		if (val=="ok" || val=="alert") {
			this.stato_link="disabled";
			this.chk_link=0;
			$("#chk_link_"+this.chsum).attr("disabled",true);
			$("#chk_link_"+this.chsum).attr("checked",false);
		}
		
		this.icon_link="img/"+val+".png";
		this.set_icon("#report_link_"+this.chsum,val);
	}
	
	
	this.set_icon=function(id,val) {
		var src="img/"+val+".png";
		$(id).attr("src",src);
	}
	
	this.init();
}

//FINE OGGETTO -------------------------------------------------

function insert_elemento(obj) {
	_elementi.push(obj);
}

function find_elemento(chsum){
	_chsum=chsum;
	$.each(_elementi,function(key,val) {if (val.chsum==_chsum) _key=key;});
	return _key;
}

function fill_lista(obj) {
	//alert(JSON.stringify(obj));
	var chsum=obj.chsum;
	var el="";
	//imposta EL
	//if(obj.stato_el==0) $("#chk_el_"+chsum).attr("disabled",true);
	if(obj.chk_el==0) {
		$("#chk_el_"+chsum).attr("checked",false);
		chg_element(document.getElementById("chk_el_"+chsum),"el_"+chsum);
	}
	//imposta SMS
	if (_report.sms==1) {
		//if(obj.stato_sms==0) $("#chk_sms_"+chsum).attr("disabled",true);
		if(obj.chk_sms==0) {
			$("#chk_sms_"+chsum).attr("checked",false);
			chg_element(document.getElementById("chk_sms_"+chsum),"sms_"+chsum);
			if (obj.icon_sms=="img/ok.png") $("#chk_sms_"+chsum).attr("disabled",true);
		}
		$("#report_sms_"+chsum).attr("src",obj.icon_sms);
	}
	
	//imposta MAIL
	if (_report.mail==1) {
		//if(obj.stato_sms==0) $("#chk_sms_"+chsum).attr("disabled",true);
		if(obj.chk_mail==0) {
			$("#chk_mail_"+chsum).attr("checked",false);
			chg_element(document.getElementById("chk_mail_"+chsum),"mail_"+chsum);
			if (obj.icon_mail=="img/ok.png") $("#chk_mail_"+chsum).attr("disabled",true);
		}
		$("#report_mail_"+chsum).attr("src",obj.icon_mail);
	}
	
	//imposta LINK
	if (_report.link==1) {
		//if(obj.stato_sms==0) $("#chk_sms_"+chsum).attr("disabled",true);
		if(obj.chk_link==0) {
			$("#chk_link_"+chsum).attr("checked",false);
			chg_element(document.getElementById("chk_link_"+chsum),"link_"+chsum);
			if (obj.icon_link=="img/ok.png") $("#chk_link_"+chsum).attr("disabled",true);
		}
		$("#report_link_"+chsum).attr("src",obj.icon_link);
	}
}

//interfaccia

function open_menu(index) {
	if (_menu_open==1) return;
	if (index=='sms_d' && _env_sms==0) return;
	if (index=='mail_d' && _env_mail==0) return;
	if (index=='link_d' && _env_link==0) return;
	$("#cover").show();
	$("#"+index).show();
	_menu_open=1;
}

function close_menu() {
	$("div[id$='_d']").hide();
	$("#cover").hide();
	_menu_open=0;
}

function chg_reparto(tag) {
	$("#tagrep").html(tag);
	_reparto=tag;
	var param=JSON.stringify({'tag':tag});
	$("#report_d").load('func_handler.php',{'func':'get_report_menu_lines','param':param});
	$("#tagreport").html("");
	$("#query_report").html("");
	$("#desc_report").html('Seleziona un report');
	$("#lista").html("");
	//cancella menu sms,mail,link
	$("#sms_d").html("");
	$("#tagsms").html("no");
	$("#mail_d").html("");
	$("#tagmail").html("no");
	$("#link_d").html("");
	$("#taglink").html("no");
	//resetta i flag dei menù sms,mail e link
	set_def();
	close_menu();
}

function chg_report(index,desc,tag) {
	$("#lista").html("");
	//resetta i flag dei menù sms,mail e link
	set_def();
	$("#tagreport").html(tag);
	$("#desc_report").html(desc);
	$.getJSON("core/report.php?ID="+index,function(val){_report=eval(val);d_query();});
	close_menu();
}

function set_def() {
	
	_env_sms=0;
	_env_mail=0;
	_env_link=0;
	
	//Variabili globali standard per i parametri della query
	//DATA in formato Ymd e INPUT in formato JSON
	_actual_mese="";
	_actual_anno="";
	_actual_giorno="";
	_stato_indice="";
	_form_data="";
	_form_input={"form_data":""};
	_modo="";
}

function set_sms(ret) {
	//ret contiene un'espressione JS che valorizza la variabile globale "_sms"
	eval(ret);
	//cancella il DIV degli sms
	$("#sms_d").html("");
	//popola il div degli sms
	$.each(_sms,function(key,val) {
		$("#sms_d").append('<div class="menuline" onclick="reset_sms('+val.ID+',\''+val.tag+'\');"><div class="line_tag">('+val.tag+')</div>'+'<div>'+val.testo+'</div></div>');
		if (val.def==1) reset_sms(val.ID,val.tag);
	});
}

function set_mail(ret) {
	//ret contiene un'espressione JS che valorizza la variabile globale "_mail"
	eval(ret);
	//cancella il DIV delle mail
	$("#mail_d").html("");
	//popola il div delle mail
	$.each(_mail,function(key,val) {
		$("#mail_d").append('<div class="menuline" onclick="reset_mail('+val.ID+',\''+val.tag+'\');"><div class="line_tag">('+val.tag+')</div>'+'<div>'+val.descrizione+'</div></div>');
		if (val.def==1) reset_mail(val.ID,val.tag);
	});
}

function set_link(ret) {
	//ret contiene un'espressione JS che valorizza la variabile globale "_link"
	eval(ret);
	//cancella il DIV dei link
	$("#link_d").html("");
	//popola il div dei link
	$.each(_link,function(key,val) {
		$("#link_d").append('<div class="menuline" onclick="reset_link('+val.ID+',\''+val.tag+'\');"><div class="line_tag">'+val.tag+'</div></div>');
		if (val.def==1) reset_link(val.ID,val.tag);
	});
}

function reset_sms(index,tag) {
	//valorizza la variabile globale e la barra dei menu
	_env_sms=index;
	$("#tagsms").html(tag);
	close_menu();
}

function reset_mail(index,tag) {
	//valorizza la variabile globale e la barra dei menu
	_env_mail=index;
	$("#tagmail").html(tag);
	close_menu();
}

function reset_link(index,tag) {
	//valorizza la variabile globale e la barra dei menu
	_env_link=index;
	$("#taglink").html(tag);
	close_menu();
}

// QUERY

function d_query() {
	//azzera i modelli
	$("#tagsms").html("no");
	$("#tagmail").html("no");
	$("#taglink").html("no");
	
	
	//disegna il form per la selezione
	redraw_query();
	$.getScript("core/reports/"+_report.inc+".js");
	//valorizza i menu sms,mail e link
	if (_report.sms==1) {
		var param=JSON.stringify({'index':_report.rep_id});
		$.ajax('func_handler.php',{'type':'post', 'datatype':'text', 'data':{'func':'get_sms_menu_lines','param':param}, 'success':function(ret) {set_sms(ret)}});
	}
	
	if (_report.mail==1) {
		var param=JSON.stringify({'index':_report.rep_id});
		$.ajax('func_handler.php',{'type':'post', 'datatype':'text', 'data':{'func':'get_mail_menu_lines','param':param}, 'success':function(ret) {set_mail(ret)}});
	}
	
	if (_report.link==1) {
		var param=JSON.stringify({'index':_report.rep_id});
		$.ajax('func_handler.php',{'type':'post', 'datatype':'text', 'data':{'func':'get_link_menu_lines','param':param}, 'success':function(ret) {set_link(ret)}});
	}
	
}

function redraw_query() {
	//calcella il div query
	$("#query_report").html("");
	//disegna il form per la selezione
	if(_report.tipo!="PARAM") {
		$("#query_report").append('<div id="cal"></div>');
		if(_actual_mese=="") {
			d_cal(_report.init_tag.substring(4,6),_report.init_tag.substring(0,4));
		}
		else {
			d_cal(_actual_mese,_actual_anno);
		}
	}
	$("#query_report").append('<div id="main_query"></div>');
	$("#main_query").load("core/reports/"+_report.inc+".php");
	//$(document).ready(function(){fill_query();});
}

function fill_query() {
	$.each(_form_input,function(key,val) {fill_element(key,val);});
}

function fill_element(key,val) {
	//il campo FORM_DATA del REPORT è speciale perché viene selezionato SOLO attraverso il calendario e non è un oggetto INPUT
	if (key=="form_data" && val!="") {
		//alert(key+"/"+val);
		set_data(val,_modo,_stato_indice);
	}
	//codice per eventuali campi INPUT specifici del REPORT
	//if ($("#"+key).attr('type')==
}

function d_cal(mese,anno) {
	//richiama il calendario
	
	//alert(mese+","+anno);
	var param=JSON.stringify({"tipo":_report.tipo, "mese":mese, "anno":anno, "inizio":_report.init_ts, "back":_report.back, "forw":_report.forw, "indice":_report.indice,"rep_id":_report.rep_id});
	$("#cal").load("core/draw_cal.php",{"param":param});
	//DRAW_CAL alla fine contiene la chiamata JS a FILL_QUERY
}

function set_data(data,modo,stato) {
	//_form_data serve per la funzione VALIDATE
	_form_data=data;
	_modo=modo;
	//_STATO_INDICE viene aggiornato dai comandi della LISTA e serve per scrivere correttamente la registrazione dell'INDICE
	_stato_indice=stato;
	if (_report.tipo=="GIORNO") {
		var tag=data.substring(6)+"/"+data.substring(4,6)+"/"+data.substring(0,4);
	}
	else var tag=data.substring(4,6)+"/"+data.substring(0,4);
	
	$("#form_data").html(tag);
}

// visualizzazione della LISTA

function go_query() {
	//cancella l'array degli elementi
	_elementi=new Array();
	//setta i valori ACTUAL di mese ed anno
	_actual_mese=_form_data.substring(4,6);
	_actual_anno=_form_data.substring(0,4);
	_actual_giorno=_form_data.substring(6);
	//setta la DATA dei parametri di QUERY
	_form_input.form_data=_form_data;
	// la funzione spedisce l'ID del report, il campo standard DATA ed un JSON contenente eventuali altri parametri della query.
	var param=JSON.stringify({"report":JSON.stringify(_report),"form_data":_form_data,"form_input":JSON.stringify(_form_input),"modo":_modo, "stato_indice":_stato_indice, "sms":_env_sms, "mail":_env_mail, "link":_env_link});
	// il parametro _FORM_INPUT viene valorizzato dalla funzione VALIDATE contenuta nel codice JS proprio del tipo di report
	$("#lista").load("core/lista.php",{"param":param});
}

function go_story(data,modo) {
	//visualizza uno storico senza curarsi della data selezionata ma passando la data del giorno da visualizzare
	var param=JSON.stringify({"report":JSON.stringify(_report),"form_data":data,"form_input":"{}","modo":modo});
	$("#lista").load("core/lista.php",{"param":param});
}

// gestione grafica LISTA
//queste funzioni vengono utilizzate per variare la visualizzazione della lista ma NON MODIFICANO GLI OGGETTI DEGLI ELEMENTI che vengono impostati solo dalla routine di spedizione e registrazione.
function chg_element(obj,chsum) {
	if (obj.checked) $("#div_"+chsum).css("background-color","transparent");
	else $("#div_"+chsum).css("background-color","#dddddd");
}

function chg_media(obj,chsum) {
	if (obj.checked) {
		//seleziona l'icona dando per default l'sms
		var src="img/phone.png";
		var patt=/mail/;
		if (patt.test(chsum)) src="img/mail.png";
		patt=/link/;
		if (patt.test(chsum)) src="img/link.png";
		
		$("#report_"+chsum).attr("src",src);
		chg_element(obj,chsum);
	}
	else {
		$("#report_"+chsum).attr("src","img/stop.png");
		chg_element(obj,chsum);
	}
}

// ESEGUI COMANDI

function invia() {
	$("#cover2").show();
	//$("#wait").show();
	setTimeout("invia2()", 500);
}

function invia2() {
	$("#messaggi_lista").html("");
	$.each(_elementi,function(key,obj) {elabora(obj);});
	
	if (_report.sms==1) {
		$.each(_elementi,function(key,obj) {if (obj.chk_el==1) spedisci_sms(obj);});
	}
	if (_report.mail==1) {
		$.each(_elementi,function(key,obj) {if (obj.chk_el==1) spedisci_mail(obj);});
	}
	
	_stato_indice="done";
	registra();
	//$("#wait").hide();
	$("#cover2").hide();
}

function salva() {
	$("#messaggi_lista").html("");
	$.each(_elementi,function(key,obj) {elabora(obj);});
	_stato_indice="saved";
	registra();
}

function annulla() {
	//cancella l'array degli elementi
	_elementi=new Array();
	$("#lista").html("");
}

function reset() {
	var tag=_actual_anno+_actual_mese+_actual_giorno;
	$.ajax('core/reset.php',{'type':'post', 'datatype':'text', 'async':false, 'data':{'report':_report.rep_id, 'tag':tag}, 'success':function() {redraw_query();}});
	
	_modo="nuovo";
	_stato_indice="";
	go_query();
}

function elabora(obj) {
	//l'array ELEMENTI contiene i riferimenti agli oggetti XCHSUM
	
	obj.init();
	
	if ($("#chk_el_"+obj.chsum).is(":disabled")==false) obj.stato_el="enabled";
	if ($("#chk_el_"+obj.chsum).is(":checked")==true) obj.chk_el=1;
	
	if (_report.sms==1) {
		if ($("#chk_sms_"+obj.chsum).is(":disabled")==false) {
			obj.stato_sms="enabled";
			if ($("#chk_sms_"+obj.chsum).is(":checked")==true) {
				obj.chk_sms=1;
				obj.icon_sms="img/phone.png";
			}
			
			obj.phone=$("#phone_el_"+obj.chsum).val();
			if ($("#sel_sms_"+obj.chsum).val()>0) {
				obj.model_sms=$("#sel_sms_"+obj.chsum).val();
			}
		}
		else {
			obj.icon_sms=$("#report_sms_"+obj.chsum).attr("src");	
		}
	}
	
	if (_report.mail==1) {
		if ($("#chk_mail_"+obj.chsum).is(":disabled")==false) {
			obj.stato_mail="enabled";
			if ($("#chk_mail_"+obj.chsum).is(":checked")==true) {
				obj.chk_mail=1;
				obj.icon_mail="img/mail.png";
			}
			
			obj.address=$("#mail_el_"+obj.chsum).val();
			if($("#sel_mail_"+obj.chsum).val()>0) {
				obj.model_mail=$("#sel_mail_"+obj.chsum).val();
			}
		}
		else {
			obj.icon_mail=$("#report_mail_"+obj.chsum).attr("src");	
		}
	}
	
	if (_report.link==1) {
		if ($("#chk_link_"+obj.chsum).is(":disabled")==false) {
			obj.stato_link="enabled";
			if ($("#chk_link_"+obj.chsum).is(":checked")==true) {
				obj.chk_link=1;
				obj.icon_link="img/link.png";
			}
			
			obj.programma=$("#link_el_"+obj.chsum).val();
			if($("#sel_link_"+obj.chsum).val()>0) {
				obj.model_link=$("#sel_link_"+obj.chsum).val();
			}
		}
		else {
			obj.icon_link=$("#report_link_"+obj.chsum).attr("src");	
		}
	}
}

function spedisci_sms(obj) {
	if (obj.chk_sms==0 || obj.model_sms==0) return;
	_obj=obj;
	var param=JSON.stringify({"report":JSON.stringify(_report), "elemento": JSON.stringify(obj), "modo":_modo});
	$.ajax('core/spedisci_sms.php',{'type':'post', 'datatype':'text','async':false ,'data':{'param':param}, 'success':function(ret) {eval(ret);}});
	//$.ajax('core/spedisci_sms.php',{'type':'post', 'datatype':'text', 'async':false, 'data':{'param':param}, 'success':function(ret) {alert(ret);}});
}

function spedisci_mail(obj) {
	if (obj.chk_mail==0 || obj.model_mail==0) return;
	_obj=obj;
	var param=JSON.stringify({"report":JSON.stringify(_report), "elemento": JSON.stringify(obj), "modo":_modo});
	$.ajax('core/spedisci_mail.php',{'type':'post', 'datatype':'text','async':false ,'data':{'param':param}, 'success':function(ret) {eval(ret);}});
	//$.ajax('core/spedisci_mail.php',{'type':'post', 'datatype':'text', 'async':false, 'data':{'param':param}, 'success':function(ret) {alert(ret);}});
}

function registra() {
	//se il report non prevede storico ritorna subito a meno che lo stato dell'indice non sia SAVED
	if(_report.indice==0 && _stato_indice!="saved") return
	
	//registra PHP
	var param=JSON.stringify({"stato_indice":_stato_indice,"report":_report.rep_id, "indice":_report.indice, "storico":_report.storico, "parametri":JSON.stringify(_form_input),"elementi":JSON.stringify(_elementi)});
	$.ajax('core/registra.php',{'type':'post', 'datatype':'text', 'data':{'param':param}, 'success':function(val) {redraw_query();$("#messaggi_lista").html(val);}});
	
	_modo="attuale";
	$("#but_reset").attr("disabled",false);
	$("#but_annulla").attr("disabled",true);
	$("#but_salva").attr("disabled",true);
}