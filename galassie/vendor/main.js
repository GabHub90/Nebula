class app_vendor_obj extends nebulaSystem {

    customExecute(galassia,funzione) {

        //#############################################
        //prevedere il passaggio di argomenti
        //creare altre funzioni CUSTOM
        //USARE in fine la proprietà this.args ed i metodi sthis.etArgs() e this.addArg()
        //window._nebulaApp.setArgs(arr);
        //#############################################

        var sistema=galassia.split(':')[1];
        var url="";

        //alert(sistema+' '+funzione);

        if (sistema=='home') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/comune/under_construction.php";
            }
        }
        if (sistema=='lizard') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }
        if (sistema=='grent') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/grent/index.php";
            }
        }

        return url;
    }

}