function calnav(indice,risoluzione,today,config) {

    this.indice=indice;
    this.risoluzione=risoluzione;
    this.today=today;
    this.config=config;

    this.divOpt=false;

    this.setDisabled=function(val) {
        thie.config.disabled=val;
    }

    this.setToday=function(arg) {
        
        if (this.config.disabled) return;
        this.today=arg;
        this.customExecute();
    }

    this.getToday=function() {
        return this.today;
    }

    this.setDiv=function() {

        if (this.config.disabled) return;

        if (this.divOpt) {
            $('#calnavOpt_'+indice).hide();
            this.divOpt=false;
        }
        else {
            window['calnavY_'+this.indice]=-250;
            $('#calnavInnerOpt_'+this.indice).css("top",window['calnavY_'+this.indice]+"px");
            $('#calnavOpt_'+this.indice).show();
            this.divOpt=true;
            window['calnavInterv_'+this.indice]=setInterval(window['_calnav_'+this.indice].animateDiv,1,this.indice);
        }
    }

    this.animateDiv=function(indice) {
        //essendo chiamata da setInterval perde il riferimento THIS
        //console.log(indice);
        window['calnavY_'+indice]=window['calnavY_'+indice]+2;
        if (window['calnavY_'+indice]>=0) clearInterval(window['calnavInterv_'+indice]);
        else {
            document.getElementById('calnavInnerOpt_'+indice).style.top=window['calnavY_'+indice];
            //$('#calnavInnerOpt_'+this.indice).css("top",window['calnavY_'+this.indice]+"px");
        }
    }

    this.build=function(arg) {
        //calcola today in base all'argomento passato ed alla risoluzione del navigatore

        var txt="";

        if (this.risoluzione=='Y') {
            txt=""+arg+"0101";
        }
        else if (this.risoluzione=='M') {
            txt=""+$('#calnav_anno_'+this.indice).val()+arg+"01";
        }
        else if (this.risoluzione=='W') {
            var temp=""+$('#calnav_anno_'+this.indice).val()+$('#calnav_mese_'+this.indice).val()+arg;
            var d=new Date(parseInt(temp.substr(0,4)),(parseInt(temp.substr(4,2))-1),parseInt(temp.substr(6,2)));

            while (d.getDay()>0) {
                d.setDate(d.getDate()-1);
            }

            txt=''+d.getFullYear();
            txt+=((d.getMonth()+1)>9)?(d.getMonth()+1):'0'+(d.getMonth()+1);
            txt+=((d.getDate()+1)>9)?(d.getDate()):'0'+(d.getDate());
        }
        else {
            txt=""+$('#calnav_anno_'+this.indice).val()+$('#calnav_mese_'+this.indice).val()+arg;
        }

        return txt;
    }

    this.execute=function(arg) {

        if (this.config.disabled) return;
        
        var txt=this.build(arg);
        this.today=txt;
        this.customExecute();
    }

    this.executeNow=function(arg) {

        if (this.config.disabled) return;

        this.today=arg;
        this.customExecute();
    }

    this.refreshOpt=function() {
        //viene usata solo se la risoluzione è != Y

        var tx="";

        if (this.risoluzione=='W') txt=""+$('#calnav_anno_'+this.indice).val()+$('#calnav_mese_'+this.indice).val()+'01';
        else txt=this.build('01');

        $('#calnavInnerOpt_'+this.indice).html('');

        var param={
            "risoluzione":this.risoluzione,
            "today":txt,
            "config":this.config
        }

        var indice=this.indice;

        $.ajax({
            "url": "http://"+location.host+"/nebula/core/calendario/refresh_opt.php",
            "async": true,
            "cache": false,
            "data": { "param": param },
            "type": "POST",
            "success": function(ret) {

                $('#calnavInnerOpt_'+indice).html(ret);   
            }
        });

    }

    this.customExecute=function() {
        //questo metodo va sovrascritto in base alle necesità dell'applicazione che instanzia la classe 
    }

}