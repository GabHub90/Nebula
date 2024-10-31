function imageSelect(tag) {

    this.tag=tag;
    this.css={};

    this.oggetti = {};

    this.init=function(c) {

        var css=c;
        this.css=css;
        var tag=this.tag;
        var oggetti=this.oggetti;
        var selected=this.selected;

        $('#imageSelect_'+tag+' option').each(function(){
            var img = $(this).attr("data-img");
            var text = $(this).html();
            var value = $(this).val();
            var item = '<li><img style="';
                for (var x in css.optionImg) {
                    item+=x+':'+css.optionImg[x];
                }
            item+='" src="'+ img +'" alt="" value="'+value+'"/>';
            item+='<span style="';
                for (var x in css.optionTxt) {
                    item+=x+':'+css.optionTxt[x];
                }
            item+='" >'+ text +'</span></li>';
           
            oggetti[value]=item;
            
            if ($(this).attr('selected')) {
                $('#imageSelect_'+tag+'_btn').html(item);
                $('#imageSelect_'+tag+'_btn').data('val',value);
            }

            var style="";
            for (var x in css.buttonList) {
                style+=x+':'+css.buttonList[x];
            }

            $('#imageSelect_'+tag+'_a').append('<button style="'+style+'" onclick="window._imageSelect_'+tag+'.selectItem(\''+value+'\');">'+item+'</button>');
        });
    }

    this.returnValue=function() {
        return $('#imageSelect_'+tag+'_btn').data('val');
    }

    this.toggleList=function() {
        $('div[id^="imageSelect_ct"]').css('z-index','49');
        $('#imageSelect_ct_'+tag).css('z-index','50');
        $('#imageSelect_'+tag+'_b').toggle();
    }

    this.selectItem=function(id) {
        $('#imageSelect_'+tag+'_btn').html(this.oggetti[id]);
        $('#imageSelect_'+tag+'_btn').data('val',id);
        this.toggleList();
    }

}


