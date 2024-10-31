function validate() {

	var error=0;
	var txt="";
	
	$("#query_error").html("");

	if (_form_data=="") {
		if (error>0) txt=txt+" - ";
		txt=txt+"Scegliere una data!";
		error++;
	}
	
	//RETURN
	if (error==0) {
		//set _FORM_INPUT se necessario
		go_query();
	}
	else $("#query_error").html(txt);
}