class app_isla_obj extends nebulaSystem {

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
                url="http://"+location.host+"/nebula/apps/idesk/index.php";
            }
            if (funzione=='avalon') {
                url="http://"+location.host+"/nebula/apps/avalon/index.php";
            }
            if (funzione=='storico') {
                url="http://"+location.host+"/nebula/apps/storico/index.php";
            }
            if (funzione=='voucher') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
            if (funzione=='gdm') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
            if (funzione=='circa') {
                url="http://"+location.host+"/nebula/apps/circa/index.php";
            }
        }
        if (sistema=='ibis') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/ibis/ibis.php";
            }
            if (funzione=='gabsms') {
                url="http://"+location.host+"/nebula/apps/gabsms/gabsms.php";
            }
            if (funzione=='chime') {
                url="http://"+location.host+"/nebula/apps/chime/index.php";
            }
        }
        if (sistema=='gdm') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
            if (funzione=='gdmp') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
            if (funzione=='gdms') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
            if (funzione=='gdmr') {
                url="http://"+location.host+"/nebula/apps/gdm/index.php";
            }
            if (funzione=='gdmq') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }
        if (sistema=='presenze') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/tempo/viewer.php";
            }
        }
        if (sistema=='analisi') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/c2r/index.php";
            }
            if (funzione=='incentivi') {
                url="http://"+location.host+"/nebula/apps/centavos/viewer.php";
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