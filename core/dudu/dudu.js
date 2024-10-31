function duduObj(rif,prefix,enabled,lista,css,caller) {

	/*
	protected $css=array(
        "headH"=>"10%",
        "headFont"=>"1.2em",
        "bodyH"=>"90%",
        "bodyW"=>"90%",
        "lineH"=>"15px",
        "lineFont"=>"1em"
    );
	*/
	
	this.rif=rif;
	//prefisso del div contenente la lista
	this.prefix=prefix;
	this.enabled=(enabled==0)?false:true;

	//oggetto WINDOW da chiamare in caso di creazione TODO e LINK al contesto chiamante
	this.caller=caller;

	this.lista=$.parseJSON(window._nebulaMain.b64DecodeUnicode(lista));
	this.css=$.parseJSON(window._nebulaMain.b64DecodeUnicode(css));
	
	this.draw=function() {

		var txt="";

		for ( var x in this.lista) {

			txt+='<div style="position:relative;width:'+this.css.bodyW+';font-size:'+this.css.lineFont+';';
				if (this.lista[x].d_chiusura!='') txt+='color:#777777;';
			txt+='">';

				txt+='<div style="min-height:'+this.css.lineH+';">';
					txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;">';
						txt+='<input id="nebulaDudu_check_'+this.rif+'_'+x+'" type="checkbox" ';
							if (this.lista[x].d_chiusura!='') txt+=' checked disabled';
							else if (this.enabled) txt+=' onclick="window._nebulaDudu_'+this.prefix+'_'+this.rif+'.close(\''+this.rif+'\','+x+');"';
						txt+='/>';
					txt+='</div>';
					txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:80%;text-align:left;">';
						txt+='<div style="font-weight:'+this.css.lineFontW+';">'+this.lista[x].testo+'</div>';
						if (this.lista[x].d_chiusura!='') txt+='<div style="font-size:0.8em;" >Chiuso: '+window._nebulaMain.data_db_to_ita(this.lista[x].d_chiusura)+'</div>';
						else txt+='<div style="font-size:0.8em;" >Creato: '+window._nebulaMain.data_db_to_ita(this.lista[x].d_creazione)+'</div>';
					txt+='</div>';
				txt+='</div>';

			txt+='</div>';
		}

		if (this.enabled) {

			txt+='<div style="position:relative;width:'+this.css.bodyW+';font-size:'+this.css.lineFont+';margin-top:10px;">';
				txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:80%;text-align:left;">';
					txt+='<input id="nebulaDudu_input_'+this.rif+'" style="width:95%;" type="text" />';
				txt+='</div>';
				txt+='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;">';
					txt+='<img style="width:20px;height:20px;cursor:pointer;" src="'+location.protocol + '//' + location.host + '/nebula/core/dudu/img/add.png" onclick="window._nebulaDudu_'+this.prefix+'_'+this.rif+'.add();" />';
				txt+='</div>';
			txt+='</div>';
		}

		$('#nebulaDudu_body_'+this.prefix+'_'+this.rif).html(txt);
	}

	this.refresh=function(ret) {

		//aggiorna la lista e la riscrive
		if (lista=$.parseJSON(ret)) {
			this.lista=lista;
			this.draw();
		}
	}

	this.add=function() {

		var txt=$('#nebulaDudu_input_'+this.rif).val().trim();

		if (txt==='undefined' || txt=='') return;

		if(!confirm("Confermi l'iserimento del TODO?")) return;

		$('#nebulaDudu_body_'+this.rif).html(window._nebulaMain.setWaiter());

		//crea un nuovo TODO, lo linka al contesto chiamante e lo ricarica
		if (this.rif==0) {
			window[this.caller].addDudu(txt);
			return;
		}

		else {

			var param={
				"rif":this.rif,
				"txt":txt
			}

			$.ajax({
				"url": 'http://'+location.host+'/nebula/core/dudu/core/add.php',
				"async": true,
				"cache": false,
				"data": {"param": param},
				"type": "POST",
				"duduIndex":this.rif,
				"duduPrefix":this.prefix,
				"success": function(ret) {
					
					//ret è un JSON lista
					window["_nebulaDudu_"+this.duduPrefix+'_'+this.duduIndex].refresh(ret);
					
				}
			});


		}

	}

	this.close=function(rif,riga) {

		if(!confirm("Confermi la CHIUSURA del TODO?")) return;

		var param={
			"ID":rif,
			"riga":riga
		}

		$.ajax({
			"url": 'http://'+location.host+'/nebula/core/dudu/core/close.php',
			"async": true,
			"cache": false,
			"data": {"param": param},
			"type": "POST",
			"duduIndex":this.rif,
			"duduPrefix":this.prefix,
			"success": function(ret) {
				
				//ret è un JSON lista
				window["_nebulaDudu_"+this.duduPrefix+'_'+this.duduIndex].refresh(ret);
				
			}
		});
	}
	
}
