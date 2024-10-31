function tempoPanoramaCode(funzione) {

    this.funzione=funzione;

    this.openFiltri=function() {

        $('.panorama_line').css('display','none');
        $('#panorama_filtri').show();
    }

    this.closeFiltri=function() {

        $('#panorama_filtri').hide();
        $('.panorama_line').css('display','table-row');

        //nascondi i reparti non flaggati
        $($('input[id^="panorama_filtro_reparto_"]')).each(function() {
            if (!$(this).prop('checked')) {
                $('.panorama_line[data-reparto="'+$(this).val()+'"]').css('display','none');
            }
        });

        //nascondi i gruppi non flaggati
        $($('input[id^="panorama_filtro_gruppo_"]')).each(function() {
            if (!$(this).prop('checked')) {
                $('.panorama_line[data-gruppo="'+$(this).val()+'"]').css('display','none');
            }
        });

    }

    this.toggleFiltri=function(tipo) {

        var v=$('#panorama_toggle_'+tipo).data('val');

        v=(v==1)?0:1;

        $('#panorama_toggle_'+tipo).data('val',v);

        $('input[id^="panorama_filtro_'+tipo+'"]').each(function(){
            if(v==1) $(this).prop('checked',true);
            else $(this).prop('checked',false);
        });

    }

    this.view=function(id) {

        $('#tempo_panorama_view').html('');

        var txt='';

        var obj=$('#'+id);

        txt+='<div style="text-align:center;">'+obj.data('tag')+' - '+obj.data('reparto')+'</div>';
        txt+='<div style="text-align:center;">'+obj.data('nome')+'</div>';

        txt+='<div style="text-align:center;">'+obj.data('turno')+'</div>';

        $('#tempo_panorama_view').html(txt);
    }

}