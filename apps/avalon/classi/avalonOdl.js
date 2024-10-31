window._avalonMainOdl=class avalonOdl extends window._nebulaMainOdl {

    constructor() {
        super();
    }

    closeOdl() {

        $('#avalon_odielle').html('');

        window._avalon_divo.setStato(1,'Y');
        window._avalon_divo.selTab(0);

        window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].lockOdl=false;
    }
    
}

window._nebulaOdl=new window._avalonMainOdl();

