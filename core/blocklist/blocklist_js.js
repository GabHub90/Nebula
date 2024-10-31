function BlockList(rif,def) {
	
	this.rif=rif;
	this.stato=def;
	
	this.switch=function() {
		//alert(index);
		if (this.stato==0) {
			$('#blocklist_'+this.rif).show();
			$('#blocklist_img_'+this.rif).attr('src','http://'+window.location.host+'/apps/gals/blocklist/img/arrow_down.png');
			this.stato=1;
		}
		
		else {
			$('#blocklist_'+this.rif).hide();
			$('#blocklist_img_'+this.rif).attr('src','http://'+window.location.host+'/apps/gals/blocklist/img/arrow_right.png');
			this.stato=0;
		}
		
	}	
	
	this.get_stato=function () {
		return this.stato;
	}
}
