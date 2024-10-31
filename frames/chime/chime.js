function chime_set_def() {
	
	chime_set_rep();
}

function chime_reset() {
	$(".chime_cal").html("");
	$(".chime_list").html("");
	$('#chime_cover').hide();
	_chime_lista={};
}

function chime_set_rep() {
	
	chime_reset();
	
	var txt='<option value="0">azione...</option>';
	
	_chime_rep=$('#chime_rep_sel').val();
	
	if (_chime_azioni[_chime_rep]) {
	
		for (var x in _chime_azioni[_chime_rep]) {
			txt+='<option value="'+x+'">'+_chime_azioni[_chime_rep][x].descrizione+'</option>';
		}
	}
	
	$('#chime_akt_sel').html(txt);
}

function chime_akt_set() {
	
	chime_reset();
	
	var val=$('#chime_akt_sel').val();
	
	if (val==0) return;
	
	_chime_akt=val;
	
	var param={"today":_chime_today,"reparto":_chime_rep,"offset":_chime_offset,"akt":_chime_akt,"azione":_chime_azioni[_chime_rep][_chime_akt]};
	
	//alert(JSON.stringify(param));
	
	$.ajax({"url":"core/set_akt.php","async":false,"cache":false, "data":{"param":param}, "type":"POST", "success":function(ret) {
		$(".chime_cal").html(ret);
	}});
}

function  chime_chg_rif(rif) {
	
	_chime_offset=rif;
	chime_akt_set();
}

function chime_get_list(giorno,cover) {
	
	_chime_lista={};
	
	var param={"giorno":giorno,"cover":cover,"azione":_chime_azioni[_chime_rep][_chime_akt],"reparto":_chime_rep};
	
	//alert(JSON.stringify(param));
	
	$.ajax({"url":"core/get_list.php","async":true,"cache":false, "data":{"param":param}, "type":"POST", "success":function(ret) {
		$(".chime_list").html(ret);
	}});
	
	var txt="";
	txt+='<div style="position: relative;top: 20px;text-align:center;">';
		txt+='<img style="width: 200px;height:200px;" src="img/wait.gif"/>';
	txt+='</div>';
	
	$(".chime_list").html(txt);
}

function chime_esegui() {
	
	_chime_obj={};
	
	_chime_flag=$('#chime_list_giorno').val();
	
	for (var x in _chime_lista) {
		
		var t=$('#chime_list_stato_'+x).val();
		
		//se la checkbox è disabled passa oltre
		if (t=='nogood' || t=='ok') continue;
		
		_chime_obj[x]=_chime_lista[x];
		
		//se è checked oppure 
		if ( $('#chime_list_chk_'+x).prop( "checked" ) ) {
			_chime_obj[x].d_invio='';
		}
		
		else _chime_obj[x].d_invio='-1';
	}
	
	var param={"giorno":_chime_flag,"azione":_chime_azioni[_chime_rep][_chime_akt],"lista":_chime_obj};
	
	$('#chime_cover').show();
	
	$.ajax({"url":"core/esegui.php","async":false,"cache":false, "data":{"param":param}, "type":"POST", "success":function(ret) {
		//alert(ret);
		chime_akt_set();
		chime_get_list(_chime_flag,0);
	}});
}

function chime_sel_tutti() {
	
	$('input[id^="chime_list_chk_"]').each(function() {
		
		if (!$(this).prop("disabled")) {
			$(this).prop("checked",true);
		}
	});
}

function chime_sel_nessuno() {
	
	$('input[id^="chime_list_chk_"]').each(function() {
		
		$(this).prop("checked",false);

	});
}
