
window._js_chk_qc_new.contesto.qcRif={
    "chiave":"",
    "intestazione":"",
    "tecnici":{},
    "rc":{}
};

window._js_chk_qc_new.confirmRiferimento=function() {

    var riferimento=$('#qc_new_form_riferimento').val();

    if (riferimento=="") return;

    window['_nebulaApp_'+window._nebulaApp.contesto.funzione].azzeraForm();

    var param={'rif':riferimento};

    $.ajax({
        "url": 'http://'+location.host+'/nebula/apps/qcheck/controlli/controllo_1.php',
        "async": true,
        "cache": false,
        "data": { "param": param },
        "type": "POST",
        "success": function(ret) {
            //console.log(ret);
            window._js_chk_qc_new.evalRiferimento(ret);
        }
    });

};

window._js_chk_qc_new.evalRiferimento=function(ret) {

    //alert(ret);

    try {
        eval('var arr='+ret);
    }catch(e) {
        return;
    }

    var abbinamento={
        "m1":"tecnici",
        "m2":"#m1",
        "m3":"rc"
    }

    //////////////////////////////////////////////////////////

    $('#qc_new_form_chiave').html(arr.chiave);
    $('#qc_new_form_intestazione').html(arr.intestazione.substr(0,30));

    $('input[js_chk_qc_new_tipo="chiave"]').val(arr.chiave);
    $('input[js_chk_qc_new_tipo="intestazione"]').val(arr.intestazione);

    //////////////////////////////////////////////////////////

    $('select[js_chk_qc_new_tipo^="op"]').each(function() {
 
        var idmodulo=$(this).data('idmodulo');
        var index=abbinamento[idmodulo];

        //alert(idmodulo+' '+index);

        if (index.substr(0,1)=='#') {
            var txt='<option value="'+index+'">'+index+'</option>';
            $(this).append(txt);
        }
        else {
            for (var x in arr[index]) {
                var txt='<option value="'+arr[index][x]+'">'+arr[index][x]+'</option>';
                $(this).append(txt);
            }
        }

    });

    window._js_chk_qc_new.js_chk();

};