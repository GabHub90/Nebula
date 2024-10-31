class app_home_obj extends nebulaSystem {

    customExecute(galassia,funzione) {

        //#############################################
        //prevedere il passaggio di argomenti
        //creare altre funzioni CUSTOM
        //USARE in fine la proprietà this.args ed i metodi sthis.etArgs() e this.addArg()
        //window._nebulaApp.setArgs(arr);
        //#############################################

        var sistema=galassia.split(':')[1];
        var url="";

        if (sistema=='home') {

            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/campobase/index.php";
            }
            if (funzione=='ticket') {
                url="http://"+location.host+"/nebula/apps/ermes/index.php";
            }
            
        }

        if (sistema=='panorama') {

            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/tempo/panorama_info.php";
            }
            
        }

        return url;
    }
    
}