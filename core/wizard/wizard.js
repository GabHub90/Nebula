function nebulaWizard(id,contesto,widget) {

    //id del div principale dove appendere i widget
    this.id=id;
    this.contesto=contesto,
    this.widget=widget;

    this.draw=function() {

        for (var x in this.widget) {

            var txt='<div id="nebulaWizardElement_'+x+'" class="nebulaWidget" style="background-color: #ececec33;margin-left:2px;" >';
                txt+='<img style="position:relative;width:30px;height:30px;left:50%;margin-left:-15px;margin-top:10px;" src="http://'+location.host+'/nebula/core/wizard/img/wait.gif" />';
            txt+='</div>';

            $('#nebulaWizardMain_'+this.id).append(txt);

            var param={
                "funzione":x,
                "contesto":this.contesto,
                "args":this.widget[x],
                "mainParam":window._nebulaMain.getContesto()
            };

            //alert(JSON.stringify(param));

            $.ajax({
                "url": "http://"+location.host+"/nebula/core/wizard/loader.php",
                "async": true,
                "cache": false,
                "data": { "param": param },
                "type": "POST",
                "success": function(ret) {
                    $('#nebulaWizardElement_'+x).html(ret);
                       
                }
            });
        }

    }
}