class app_maestro_obj extends nebulaSystem {

    customExecute(galassia,funzione) {

        var sistema=galassia.split(':')[1];
        var url="";

        if (sistema=='home') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/tempo/index.php";
            }
            if (funzione=='brogliaccio') {
                url="http://"+location.host+"/nebula/apps/brogliaccio/index.php";
            }
            if (funzione=='panorama') {
                url="http://"+location.host+"/nebula/apps/tempo/panorama.php";
            }
        }

        if (sistema=='gruppi') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/ensamble/index.php";
            }
            if (funzione=='schemi') {
                url="http://"+location.host+"/nebula/apps/quartet/index.php";
            }
        }

        if (sistema=='incentivi') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/centavos/index.php";
            }
        }

        if (sistema=='lizard') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }

        return url;
    }

}