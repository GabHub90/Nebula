var telaio=$('#ribbon_gdm_telaio').val();
var dms=$('#ribbon_gdm_dms').val();

if (telaio && telaio!='' && dms && dms!='') {
    window._nebulaApp.ribbonExecute();
}