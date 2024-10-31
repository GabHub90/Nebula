
window._js_chk_qc_new.contesto.qcRif={
    "chiave":"",
    "intestazione":"",
    "reparto":""
    
};

window._js_chk_qc_new.confirmRiferimento=function() {

    var riferimento=$('#qc_new_form_riferimento').val();
    var reparto=$('input[js_chk_qc_new_tipo="reparto"]').val();

    if (riferimento=="") return;

    window['_nebulaApp_'+window._nebulaApp.contesto.funzione].azzeraForm();

    var param={'targa':riferimento,'reparto':reparto};

    $.ajax({
        "url": 'http://'+location.host+'/nebula/apps/qcheck/controlli/controllo_2.php',
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

    //////////////////////////////////////////////////////////

    $('#qc_new_form_chiave').html(arr.chiave);
    $('#qc_new_form_intestazione').html(arr.intestazione.substr(0,30));

    $('input[js_chk_qc_new_tipo="chiave"]').val(arr.chiave);
    $('input[js_chk_qc_new_tipo="intestazione"]').val(arr.intestazione);

    //////////////////////////////////////////////////////////

    $('select[js_chk_qc_new_tipo^="op"]').each(function() {
        txt='<option value="'+arr['reparto']+'">'+arr['reparto']+'</option>';
        $(this).append(txt);
    });

    window._js_chk_qc_new.js_chk();

};