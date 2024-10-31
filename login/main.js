function app_login_obj() {

    this.confirm=function() {

        $('#app_login_error').html('');

        var usr = $("#app_login_input_usr").val();
        var psw = $("#app_login_input_psw").val();

        if (usr == '' || psw == '') {
            //alert(usr+' '+psw);
            $('#app_login_error').html('dati incompleti');
            return;
        }

        var param = { "mainLogged": usr, "mainPassword": psw, "mainApp":"home:home" };

        window._nebulaMain.openApp(param);
    }

}