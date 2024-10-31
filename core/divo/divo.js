if (typeof divoObj === 'undefined') {

	function divoObj(rif,def_sel) {
		
		this.rif=rif;
		this.def_sel=def_sel;

		this.bk='wheat';

		this.getSel=function() {
			return this.def_sel;
		}

		this.setBk=function(colore) {
			this.bk=colore;
		}
		
		this.selTab=function(index) {

			//if (index=="") return;

			this.def_sel=index;
			$('div[id^="divo_tab_'+this.rif+'"]').css("background-color",'white');
			$('div[id^="divo_div_'+this.rif+'"]').hide();
			//$('div[id^="divo_div_'+this.rif+'"]').css('visibility','hidden');
			$('div[id^="divo_div_'+this.rif+'"]').css('position','absolute');
			$('#divo_div_'+this.rif+'_'+index).show();
			//$('#divo_div_'+this.rif+'_'+index).css('visibility','visible');
			$('#divo_div_'+this.rif+'_'+index).css('position','relative');
			$('#divo_tab_'+this.rif+'_'+index).css("background-color",this.bk);

			this.postSel();
		}
		
		this.setDef=function() {
			this.selTab(this.def_sel);
		}

		this.setStato=function(div,stato) {
			//stati G=giallo, V=verde, R=Rosso, X=Bianco, Y=grigio
			$('#divo_checkimg_'+this.rif+'_'+div).prop('src','http://'+ window.location.host + '/nebula/core/divo/img/stato_'+stato+'.png');
			$('#divo_checkimg_'+this.rif+'_'+div).data('stato',stato);
		}

		this.preSel=function(index) {
			//funzione da sovrascricvere alla bisogna

			this.selTab(index);
		}

		this.postSel=function() {
			//funzione da sovrascricvere alla bisogna
		}
		
	}

}
