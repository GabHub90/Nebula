class app_ammo_obj extends nebulaSystem {

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
                url="http://"+location.host+"/nebula/apps/ibis/ibis.php";
            }
            if (funzione=='panorama') {
                url="http://"+location.host+"/nebula/apps/tempo/panorama_info.php";
            }
        }
        if (sistema=='carb') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/carb/index.php";
            }
            if (funzione=='lizard') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }
        if (sistema=='comest') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/comest/index.php";
            }
            if (funzione=='comestSalvate') {
                url="http://"+location.host+"/nebula/apps/comest/salvate.php";
            }
            if (funzione=='comestAperte') {
                url="http://"+location.host+"/nebula/apps/comest/aperte.php";
            }
            if (funzione=='comestArchivio') {
                url="http://"+location.host+"/nebula/apps/comest/archivio.php";
            }
        }

        if (sistema=='storico') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/storico/index.php";
            }
        }

        if (sistema=='lizard') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }

        if (sistema=='cassa') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/scontrillo/index.php";
            }
        }

        return url;
    }
}
