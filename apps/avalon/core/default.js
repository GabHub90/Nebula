window._calnav_avalon.customExecute=function() {
    //alert(this.today);
    $('#ribbon_avl_today').val(this.today);
    window._nebulaApp.ribbonExecute();
};

/*window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].nebulaAppSetup=function() {

    var d=$("#ribbon_avl_today").val();
    this.setDay();
};*/