class app_distro_obj extends nebulaSystem {

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
                url="http://"+location.host+"/nebula/apps/timeless/index.php";
            }
            else if (funzione=='ermes') {
                url="http://"+location.host+"/nebula/apps/ermes/index_cat.php";
            }
        }

        return url;
    }

}