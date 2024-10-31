class app_sthor_obj extends nebulaSystem {

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
                url="http://"+location.host+"/nebula/apps/sthor/index.php";
            }
            else if (funzione=='imag') {
                url="http://"+location.host+"/nebula/apps/pitstop/index.php";
            }
            else if (funzione=='ermes') {
                url="http://"+location.host+"/nebula/apps/ermes/index_cat.php";
            }
        }
        if (sistema=='ibis') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/ibis/ibis.php";
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
        }
        if (sistema=='lizard') {
            if (funzione=='home') {
                url="http://"+location.host+"/nebula/apps/lizard/index.php";
            }
        }

        return url;
    }

}