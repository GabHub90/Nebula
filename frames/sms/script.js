function modificaSaluto() {
	var loop=document.forms[0].elements["saluto_ra"].length;
	for (var i=0; i < loop; i++) {
	   if (document.forms[0].elements["saluto_ra"][i].checked) var valore = document.forms[0].elements["saluto_ra"][i].value;
	}
	if (valore=="am") {
		document.getElementById("saluto_cont").innerHTML="Buongiorno";
		document.getElementById("salutoval").setAttribute("value","Buongiorno");
	}	
	if (valore=="pm") {
		document.getElementById("saluto_cont").innerHTML="Buonasera";
		document.getElementById("salutoval").setAttribute("value","Buonasera");
	}
	if (valore=="auto") {
		var oggi=new Date();
		if (oggi.getHours()>14)	{
			document.getElementById("saluto_cont").innerHTML="Buonasera";
			document.getElementById("salutoval").setAttribute("value","Buonasera");
		}
		else {
			document.getElementById("saluto_cont").innerHTML="Buongiorno";
			document.getElementById("salutoval").setAttribute("value","Buongiorno");
		}
	}
	var len=document.getElementById("salutoval").value.length+1;
	document.getElementById("salchr").value=len;
	document.getElementById("saluto_chr").innerHTML=len;
	calcolaLunghezza();	
}

function modificaIntest() {
	var loop=document.forms[0].elements["intest_ra"].length;
	for (var i=0; i < loop; i++) {
	   if (document.forms[0].elements["intest_ra"][i].checked) var valore = document.forms[0].elements["intest_ra"][i].value;
	}
	if (valore=="m") {
		document.getElementById("sesso").innerHTML="Sig.";
		document.getElementById("sessoval").setAttribute("value","Sig.");
		document.getElementById("sesso").style.display="inline";
	}	
	if (valore=="f") {
		document.getElementById("sesso").innerHTML="Sig.ra";
		document.getElementById("sessoval").setAttribute("value","Sig.ra");
		document.getElementById("sesso").style.display="inline";
	}
	if (valore=="n") {
		document.getElementById("sesso").style.display="none";
		document.getElementById("sessoval").setAttribute("value","");
	}
	//var len=document.getElementById("sessoval").value.length+23;
	//document.getElementById("intchr").value=len;
	//document.getElementById("intest_chr").innerHTML="["+len+"]";
	calcolaLunghezza();	
}

function modificaOptData() {
	var loop=document.forms[0].elements["data_ra"].length;
	for (var i=0; i < loop; i++) {
	   if (document.forms[0].elements["data_ra"][i].checked) var valore = document.forms[0].elements["data_ra"][i].value;
	}
	if (valore=="o") {
		if (restoreData=="") restoreData=document.getElementById("dataval").value;
		document.getElementById("datastr").innerHTML="oggi";
		document.getElementById("dataval").setAttribute("value","oggi");
		document.getElementById("sx").style.visibility="hidden";
		document.getElementById("dx").style.visibility="hidden";
	}
	if (valore=="d") {
		if (restoreData=="") restoreData=document.getElementById("dataval").value;
		document.getElementById("datastr").innerHTML="domani";
		document.getElementById("dataval").setAttribute("value","domani");
		document.getElementById("sx").style.visibility="hidden";
		document.getElementById("dx").style.visibility="hidden";
		
	}
	if (valore=="a") {
		document.getElementById("datastr").innerHTML=restoreData;
		document.getElementById("dataval").setAttribute("value",restoreData);
		document.getElementById("sx").style.visibility="visible";
		document.getElementById("dx").style.visibility="visible";
		restoreData="";
	}
	var len=document.getElementById("dataval").value.length+1;
	document.getElementById("datchr").value=len;
	document.getElementById("data_chr").innerHTML=len;
	calcolaLunghezza();
}

function modificaData(valore) {
	var attuale=document.getElementById("dataval").value+"/"+document.getElementById("annoval").value;
	if ((valore<0) && (/Lun/.test(attuale))) valore -=1;
	if ((valore>0) && (/Sab/.test(attuale))) valore +=1; 	
	var str=calcolaData(attuale,valore);
	document.getElementById("datastr").innerHTML=str.substr(0,9);
	document.getElementById("dataval").setAttribute("value",str.substr(0,9));
	document.getElementById("annoval").setAttribute("value",str.substr(10,4));
	return false;
}

function calcolaData(def,delta) {
	var now= new Date();
	var str;
	now.setDate(parseInt(def.substr(4,2),10));
	now.setMonth(parseInt(def.substr(7,2),10)-1);
	now.setFullYear(parseInt(def.substr(10,4),10));
	now.setDate(now.getDate()+delta);
	switch(now.getDay()){
		case 0:
			str="Dom ";
		break;
		case 1:
			str="Lun ";
		break;
		case 2:
			str="Mar ";
		break;
		case 3:
			str="Mer ";
		break;
		case 4:
			str="Gio ";
		break;
		case 5:
			str="Ven ";
		break;
		case 6:
			str="Sab ";
		break;
	}
	var giorno=now.getDate();
	var mese=now.getMonth()+1;
	if (giorno>9) str=str+giorno+"/";
		else str=str+"0"+giorno+"/";
	if (mese>9) str=str+mese+"/";
		else str=str+"0"+mese+"/";
	str=str+now.getFullYear();
	return str;
}

function modificaOra(valore) {
	var str="";
	var attuale=parseInt(document.getElementById("oraval").value,10);
	attuale=attuale+valore;
	if (attuale>23) attuale=0;
	if (attuale<0) attuale=23;
	if (attuale<10) str="0"+attuale;
	else str=""+attuale;
	document.getElementById("oratd").innerHTML=str;
	document.getElementById("oraval").setAttribute("value",str);
}

function modificaMin(valore) {
	var str="";
	var attuale=parseInt(document.getElementById("minval").value,10);
	attuale=attuale+valore;
	if (attuale==60 | attuale==0) str="00";
	else { 
		if (attuale<0) attuale=45;
		str=attuale;
	}
	document.getElementById("mintd").innerHTML=str;
	document.getElementById("minval").setAttribute("value",str);
}

function setOrario(h,m) {
	document.getElementById("oratd").innerHTML=h;
	document.getElementById("oraval").setAttribute("value",h);
	document.getElementById("mintd").innerHTML=m;
	document.getElementById("minval").setAttribute("value",m);
}

function modificaChiusura() {
	var loop=document.forms[0].elements["chiusura_ra"].length;
	for (var i=0; i < loop; i++) {
	   if (document.forms[0].elements["chiusura_ra"][i].checked) var valore = document.forms[0].elements["chiusura_ra"][i].value;
	}
	if (valore=="gab") {
		document.getElementById("gab").style.display="inline";
		document.getElementById("col").style.display="none";
	}	
	if (valore=="col") {
		document.getElementById("gab").style.display="none";
		document.getElementById("col").style.display="inline";
	}			
}

function modificaExtra() {
	var ext=document.forms[0].elements["ext"].value;
	//alert(extra[ext][0]+" "+extra[ext][1]+" "+extra[ext][2]);
	document.forms[0].elements["exttext"].value=extra[ext][0];
	if (extra[ext][2]!="") {
		document.forms[0].elements["extattr"].value=extra[ext][1];
		document.forms[0].elements["extattr"].style.visibility="visible";
		document.getElementById("extet").innerHTML=extra[ext][2];
		document.getElementById("extet").style.visibility="visible";
	}
	else {
		document.forms[0].elements["extattr"].style.visibility="hidden";
		document.getElementById("extet").style.visibility="hidden";
	}
	calcolaLunghezza();
}

function switchSaluto() {
	if (document.getElementById("saluto").checked) {
		document.getElementById("saluto_opz").style.display="inline";
		document.getElementById("saluto_cont").style.display="inline";
		document.getElementById("saluto_chr").style.display="inline";
	}
	else {
		document.getElementById("saluto_opz").style.display="none";
		document.getElementById("saluto_cont").style.display="none";
		document.getElementById("saluto_chr").style.display="none";
	}
	calcolaLunghezza();
}

function switchIntest() {
	if (document.getElementById("intest").checked) {
		document.getElementById("intest_opz").style.display="inline";
		document.getElementById("intest_cont").style.display="inline";
		document.getElementById("intest_chr").style.display="inline";
	}
	else {
		document.getElementById("intest_opz").style.display="none";
		document.getElementById("intest_cont").style.display="none";
		document.getElementById("intest_chr").style.display="none";
	}
	calcolaLunghezza();
}

function switchData() {
	if (document.getElementById("data").checked) {
		document.getElementById("data_opz").style.display="inline";
		document.getElementById("data_cont").style.display="inline";
		document.getElementById("data_chr").style.display="inline";
	}
	else {
		document.getElementById("data_opz").style.display="none";
		document.getElementById("data_cont").style.display="none";
		document.getElementById("data_chr").style.display="none";
	}
	calcolaLunghezza();
}

function switchOra() {
	if (document.getElementById("ora").checked) {
		document.getElementById("ora_opz").style.display="inline";
		document.getElementById("ora_cont").style.display="inline";
		document.getElementById("ora_chr").style.display="inline";
	}
	else {
		document.getElementById("ora_opz").style.display="none";
		document.getElementById("ora_cont").style.display="none";
		document.getElementById("ora_chr").style.display="none";
	}
	calcolaLunghezza();
}

function switchExtra() {
	if (document.getElementById("extra").checked) {
		document.getElementById("extra_opz").style.display="inline";
		document.getElementById("extra_cont").style.display="inline";
		document.getElementById("extra_chr").style.display="inline";
	}
	else {
		document.getElementById("extra_opz").style.display="none";
		document.getElementById("extra_cont").style.display="none";
		document.getElementById("extra_chr").style.display="none";
	}
	calcolaLunghezza();
}


function chiudiOverlay() {
	//elimina oggetti
	var waiter = document.getElementById("waiter");
	var wait = waiter.getElementsByTagName("img");
	if (wait.length>0) {
		waiter.removeChild(wait[0]);
	}
	document.getElementById("sender").setAttribute("src","blank.html");
	//nascondi overlay
	document.getElementById("overlay").style.display="none";
}

function cambiaModello(num) {
	modelloAttuale=num;
	var modello = new Array();
	modello = modelli[num];
	//saluto sempre on
	document.getElementById("saluto").checked=true;
	switchSaluto();
	//intest sempre off
	document.getElementById("intest").checked=false;
	switchIntest();
	//corpo
	document.getElementById("corpo_opz").innerHTML=modello[0];
	document.getElementById("corpo").value=modello[1];
	//data
	if (modello[2]=="no") {
		document.getElementById("data").checked=false;
		switchData();
	}
	if (modello[2]=="oggi") {
		var loop=document.forms[0].elements["data_ra"].length;
		for (var i=0; i < loop; i++) {
			if (document.forms[0].elements["data_ra"][i].value=="o") {
		   		document.forms[0].elements["data_ra"][i].checked=true;
		   		document.getElementById("data").checked=true;
		   		modificaOptData()
		   		switchData();
		   	}
		}
	}
	if (modello[2]=="dom") {
		var loop=document.forms[0].elements["data_ra"].length;
		for (var i=0; i < loop; i++) {
			if (document.forms[0].elements["data_ra"][i].value=="d") {
		   		document.forms[0].elements["data_ra"][i].checked=true;
		   		document.getElementById("data").checked=true;
		   		modificaOptData()
		   		switchData();
		   	}
		}
	}
	if (modello[2]=="altra") {
		var loop=document.forms[0].elements["data_ra"].length;
		for (var i=0; i < loop; i++) {
			if (document.forms[0].elements["data_ra"][i].value=="a") {
		   		document.forms[0].elements["data_ra"][i].checked=true;
		   		document.getElementById("data").checked=true;
		   		modificaOptData();
		   		switchData();
		   	}
		}
	}
	//ora
	if (modello[3]==1) {
		document.getElementById("ora").checked=true;
		if (modello[4]!="00") {
			restoreH=document.getElementById("oraval").value;
			restoreM=document.getElementById("minval").value;
			setOrario(modello[4],modello[5]);
		}
		else {
			if (restoreH!="") {
				document.getElementById("oraval").value=restoreH;
				document.getElementById("oratd").innerHTML=restoreH;
				document.getElementById("minval").value=restoreM;
				document.getElementById("mintd").innerHTML=restoreM;
			}
		}	
		switchOra();
	}
	else {
		document.getElementById("ora").checked=false;
		switchOra();
	}
	//extra
	if (modello[6]>0) {
		document.getElementById("extra").checked=true;
		document.forms[0].elements["ext"].value=modello[6];
		modificaExtra();
		switchExtra();
	}
	else {
		document.getElementById("extra").checked=false;
		document.forms[0].elements["ext"].value=0;
		modificaExtra();
		switchExtra();
	}
	
	//chiusura su collaboratore e numero ""
	var loop=document.forms[0].elements["chiusura_ra"].length;
	for (var i=0; i < loop; i++) {
		if (document.forms[0].elements["chiusura_ra"][i].value=="col") {
	   		document.forms[0].elements["chiusura_ra"][i].checked=true;
	   		modificaChiusura();
	   	}
	}
	document.getElementById("numero").value="";
}

function calcolaLunghezza() {
	//var tot=0;
	//if (document.getElementById("saluto").checked==true)
	//	tot=tot+parseInt(document.getElementById("salchr").value,10);
	//if (document.getElementById("intest").checked==true)
	//	tot=tot+parseInt(document.getElementById("intchr").value,10);
	//if (document.getElementById("data").checked==true)
	//	tot=tot+parseInt(document.getElementById("datchr").value,10);
	//if (document.getElementById("ora").checked==true)
	//	tot=tot+parseInt(document.getElementById("orachr").value,10);
	//tot=tot+parseInt(document.getElementById("chichr").value,10);
	//calcolo corpo
	//tot=tot+document.getElementById("corpo").value.length;
	//tot=tot+3;
	var msg = createMsg("abcdefghilm","1234567890");
	var tot = msg.length;
	//scrittura valore
	document.getElementById("totchr").value=tot;
	document.getElementById("total_chr").innerHTML=tot;
	//scrittura lunghezza intestazione
	var intmsg="";
	if (document.getElementById("intest").checked) {
		intmsg=intmsg+document.getElementById("sesso").innerHTML;
		intmsg=intmsg+" "+document.getElementById("cliente").value;
	}
	document.getElementById("intest_chr").innerHTML=intmsg.length;
	//scrittura lunghezza extra
	var extmsg="";
	if (document.getElementById("extra").checked) {
		extmsg=extmsg+document.getElementById("exttext").value;
		if (document.getElementById("extattr").value.length!=0) {
			extmsg=extmsg+" "+document.getElementById("extattr").value+" "+document.getElementById("extet").innerHTML;
		}
	}
	document.getElementById("extra_chr").innerHTML=extmsg.length;
	//calcola caratteri rimasti per il corpo (con 2 caratteri di margine di sicurezza)
	var left=160-tot;
	document.getElementById("corpo_chr").innerHTML="Caratteri<br/>Rimasti<br/>("+left+")";
}
	






