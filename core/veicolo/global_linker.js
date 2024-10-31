function nebulaGlobalLinker() {

    this.contesto="";

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.selectLink=function(dms,telaio) {
        if (this.contesto=='') return;

        //alert(dms+' '+telaio);

        window[this.contesto].execGlobalLinker(dms,telaio);
    }

    this.selectLinkErmes=function(obj) {
        if (this.contesto=='') return;

        window[this.contesto].execGlobalLinker($.parseJSON(atob(obj)));
    }

    this.setContesto=function(txt) {
        this.contesto=txt;
    }

    this.closeGlobalLinker=function() {
        if (this.contesto=='') return;
        window[this.contesto].closeGlobalLinker();
    }

    this.readLista=function() {

        var tt=$('#global_linker_input').val();

        tt=tt.trim();
        if (!tt || tt=="") return;
        if (tt.lenght<=3) return;

        var param={
            "dms":"tutti",
            "tt":tt
        }

        $("#global_linker_lista_div").html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/veicolo/search_global_linker.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $("#global_linker_lista_div").html(ret);
            }
        });

    }

    this.readListaErmes=function() {

        var tt=$('#global_linker_input').val();

        tt=tt.trim();
        if (!tt || tt=="") return;
        if (tt.lenght<=3) return;

        var param={
            "dms":"tutti",
            "tt":tt
        }

        $("#global_linker_lista_div").html(this.setWaiter());

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/veicolo/search_global_linker_ermes.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                $("#global_linker_lista_div").html(ret);
            }
        });

    }

}