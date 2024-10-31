
function timelessCode(funzione) {

    this.funzione=funzione;

    this.setWaiter=function() {
        var txt='<div style="text-align:center;">';
        txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
        txt+='</div>';

        return txt;
    }

    this.chiudiUtility=function() {

        $('#timeless_utilityDivBody').html('');
        $('#timeless_utilityDiv_head').html('');
        $('#timeless_utilityDiv').hide();
        $('#timeless_main').show();
    }

    this.openUtility=function(officina,reparto,pratica,dms) {

        var param={
            "officina":officina,
            "reparto":reparto,
            "pratica":pratica,
            "dms":dms
        }

        $('#timeless_utilityDivBody').html(this.setWaiter());
        $('#timeless_utilityDiv_head').html('<div style="text-align:center;font-size:1.1em;">Commessa</div>');
        $('#timeless_utilityDiv').show();
        $('#timeless_main').hide();  

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/timeless/core/select_pratica.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#timeless_utilityDivBody').html(ret);
                
            }
        });    
    }

    this.chiudiSearch=function() {

        $('#tml_search_main').html('');
        $('#tml_search_div').hide();
        $('#tml_main_div').show();  
    }

    this.search=function(reparto) {

        var src=$('#tml_search').val().trim();
        var flag=$('#tml_prenFlag').prop('checked');

        if (!src || src=="" || src.length<3) return;

        var param={
            "reparto":reparto,
            "search":src
        }

        var link=(flag)?'/nebula/apps/idesk/core/inarrivo_search.php':'/nebula/apps/timeless/core/search.php'

        //$('#tml_search_main').html(this.setWaiter());
        //$('#tml_search_div').show();
        //$('#tml_main_div').hide();


        $('#timeless_utilityDivBody').html(this.setWaiter());
        $('#timeless_utilityDiv_head').html('<div style="text-align:center;font-size:1.1em;">Ricerca</div>');
        $('#timeless_utilityDiv').show();
        $('#timeless_main').hide();  

        $.ajax({
            "url": 'http://'+location.host+link,
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                //$('#tml_search_main').html(ret);
                $('#timeless_utilityDivBody').html(ret);
                
            }
        });
    }

    this.toPraticaDiv=function(pratica) {

        this.chiudiUtility();

        var myElement = document.getElementById('nebula_pratica_'+pratica);
        var topPos = myElement.offsetTop;

        window._tempo_divo.selTab(1);
        document.getElementById('divo_div_tempo_1').parentNode.scrollTop = topPos;
    }

    this.daAprire=function(reparto,da,a) {

        $('#timeless_daaprire').html(this.setWaiter());

        var param={
            "reparto":reparto,
            "da":da,
            "a":a
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/apps/idesk/core/inarrivo.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {
                
                $('#timeless_daaprire').html(ret);     
            }
        });
    }
    
}