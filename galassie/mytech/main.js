class app_rtech_obj extends nebulaSystem {

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
            if (funzione=='provo') {
                url="http://"+location.host+"/nebula/apps/qcheck/index.php";
            }
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/workshop/index.php";
            }
            if (funzione=='avalon') {
                url="http://"+location.host+"/nebula/apps/avalon/viewer/index.php";
            }
            if (funzione=='storico') {
                url="http://"+location.host+"/nebula/apps/storico/index.php";
            }
        }
        else if (sistema=='gdm') {
            if (funzione=='gdmr') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
        }
        else if (sistema=='presenze') {  
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/tempo/viewer.php";
            }
        }

         else if (sistema=='personale') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/c2r/index.php";
            }
            if (funzione=='incentivi') {
                url="http://"+location.host+"/nebula/apps/centavos/viewer.php";
            }
        }

        else if (sistema=='lizard') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }

        return url;
    }

}