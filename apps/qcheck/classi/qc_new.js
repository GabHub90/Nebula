
window._js_chk_qc_new.scrivi_proprietario=function() {

    //alert(JSON.stringify(this.expo));

    var param=this.expo;

    $.ajax({
        "url": 'http://'+location.host+'/nebula/apps/qcheck/core/storico_new.php',
        "async": true,
        "cache": false,
        "data": { "param": param},
        "type": "POST",
        "success": function(ret) {
            //console.log(ret);
            window._nebulaApp.ribbonExecute();
        }
    });

}