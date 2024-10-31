function skyhawk() {

    this.wsloop='';
    this.hrloop='';

    //serve per quello stronzo di firefox che tiene in memoria le ultime impostazioni dei select quando si fa un refresh
    $(window).unload(function() {
        $('select option').remove();
    });

    this.strFormat=function(val) {

        var a=parseInt(val);

        if (a<10) return '0'+a;
        else return ''+a;

    }

    this.workshop_init=function() {

        //var today=''+$('#skyhawk_workshop_nextcheck').data('today');
        //var now=''+$('#skyhawk_workshop_nextcheck').data('now');

        //var d=new Date(parseInt(today.substring(0,4)), parseInt(today.substring(4,6))-1, parseInt(today.substring(6,8)), parseInt(now.substring(0,2)), parseInt(now.substring(3,5)), parseInt(now.substring(6,8)));

        //var next=new Date(d.getTime() + 2*60000);

        this.checkToday();
        this.workshop_check();

        //this.wsloop=setTimeout(window.skyhawk.workshop_check,60000);  
    }

    this.HR_init=function() {

        this.checkToday();
        this.HR_check();
    }

    this.checkToday=function() {
        var obj=window.skyhawk;
        var d=new Date();

        var temp=''+obj.strFormat(d.getFullYear())+''+obj.strFormat((d.getMonth()+1))+''+obj.strFormat(d.getDate());
        //se la data non è più attuale ricarica la pagina
        if (temp>$('#sky_today').val()) {
            location.reload();
        }
    }

    this.HR_check=function() {

        var obj=window.skyhawk;
        obj.checkToday();

        var d=new Date();

        var str=''+obj.strFormat(d.getDate())+'/'+obj.strFormat((d.getMonth()+1))+'/'+obj.strFormat(d.getFullYear());

        if (d.getTime()>=$('#skyhawk_HR_nextcheck').data('limit')) {

            //controlla il superamento dell'intervallo di aggiornamento timbrature
            //var effect=$('#skyhawk_workshop_nexteffect').data('effect');
            var effect=$('#skyhawk_HR_checkpoints').val();
            var rif=obj.strFormat(d.getHours())+':'+obj.strFormat(d.getMinutes())+':'+obj.strFormat(d.getSeconds());

            //#####################################
            //BLOCCO IMPOSTATO IL 01/02/2024
            //effect=false;
            //#####################################

            if (effect && rif>effect) {

                //############ AJAX
                $.ajax({
                    "url": "http://"+location.host+"/nebula/core/alan/HR.php",
                    "async": true,
                    "cache": false,
                    "data": { "param": {} },
                    "type": "POST",
                    "success": function(ret) {

                        console.log(ret);
                        
                        //if success
                        $('#skyhawk_HR_nexteffect').html(str+' '+rif);

                        $('#skyhawk_HR_checkpoints option').each(function(){

                            if ($(this).val()>rif) {
                                $(this).prop('selected',true); 
                                return false;
                            }
                        });
                    }
                });
            }

            //###########################

            //aggiorna nuovo limite check

            var next=new Date(d.getTime() + 2*60000);

            $('#skyhawk_HR_nextcheck').html(str+' '+obj.strFormat(next.getHours())+':'+obj.strFormat(next.getMinutes())+':'+obj.strFormat(next.getSeconds()));
            $('#skyhawk_HR_nextcheck').data('limit',next.getTime());
            
        }

        this.hrloop=setTimeout(window.skyhawk.HR_check,60000);
    }

    this.workshop_check=function() {

        var obj=window.skyhawk;
        obj.checkToday();

        var d=new Date();

        /*var temp=''+obj.strFormat(d.getFullYear())+''+obj.strFormat((d.getMonth()+1))+''+obj.strFormat(d.getDate());
        //se la data non è più attuale ricarica la pagina
        if (temp>$('#skyhawk_workshop_nextcheck').data('today')) {
            //alert(temp+' '+$('#skyhawk_workshop_nextcheck').data('today'));
            location.reload();
        }*/

        var str=''+obj.strFormat(d.getDate())+'/'+obj.strFormat((d.getMonth()+1))+'/'+obj.strFormat(d.getFullYear());

        //console.log($('#skyhawk_workshop_nextcheck').data('limit'));

        if (d.getTime()>=$('#skyhawk_workshop_nextcheck').data('limit')) {

            //###########################
            //controlla il superamento dell'intervallo di aggiornamento timbrature
            //var effect=$('#skyhawk_workshop_nexteffect').data('effect');
            var effect=$('#skyhawk_workshop_checkpoints').val();
            var rif=obj.strFormat(d.getHours())+':'+obj.strFormat(d.getMinutes())+':'+obj.strFormat(d.getSeconds());

            if (effect && rif>effect) {

                //alert('ethsxrhrsgac');

                //############ AJAX
                $.ajax({
                    "url": "http://"+location.host+"/nebula/apps/workshop/reset.php",
                    "async": true,
                    "cache": false,
                    "data": { "param": {} },
                    "type": "POST",
                    "success": function(ret) {

                        console.log(ret);
                        
                        //if success
                        $('#skyhawk_workshop_nexteffect').html(str+' '+rif);

                        $('#skyhawk_workshop_checkpoints option').each(function(){

                            if ($(this).val()>rif) {
                                $(this).prop('selected',true); 
                                return false;
                            }
    
                        });
                    }
                });
            }

            //###########################

            //aggiorna nuovo limite check

            var next=new Date(d.getTime() + 3*60000);

            $('#skyhawk_workshop_nextcheck').html(str+' '+obj.strFormat(next.getHours())+':'+obj.strFormat(next.getMinutes())+':'+obj.strFormat(next.getSeconds()));
            $('#skyhawk_workshop_nextcheck').data('limit',next.getTime());
            
        }

        this.wsloop=setTimeout(window.skyhawk.workshop_check,60000);

    }

}