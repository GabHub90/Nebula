function nebulaOdlBody(param) {

    this.param=param;

    this.main={
        "dms":"",
        "rifOdl":"",
        "actualQuick":"",
        "overall":{},
        "wormhole":"",
        "dates":{}
    }

    this.setMain=function(arr) {

        for (var x in this.main) {
            if (arr[x]) {
                this.main[x]=arr[x];
            }
        }
    }

    //###########################################
    //INIT
    this.setMain(this.param);
    //###########################################

    this.drawBody=function(id) {

        var divID=id;
        
        //$('#'+divID).html('ODL BODY');
    }

    this.setDates=function() {

        var main=this.main;

        $("input[id^='odielleMainDate']").each(function(){

            var suff=$(this).data('suff');
            var d=$(this).val();

            var dd=new Date(parseInt(d.substr(0,4)),parseInt(d.substr(5,2))-1,parseInt(d.substr(8,2)));

            //alert (d+' '+dd.getDay());

            var wd=dd.getDay();
            var txt="";
            var rif='xx:xx';
            var tt="";
            var sel=false;

            if (main.dates['d_'+suff]) var rif=main.dates['d_'+suff].substr(9,5);

            if (main.overall[wd]) {

                for (var x in main.overall[wd]) {

                    if (main.overall[wd][x].substr(0,1)=='b') {
                        txt+='<option value="">'+main.overall[wd][x]+'</option>';
                        continue;
                    }

                    if (tt!="") {
                        if (main.overall[wd][x]>rif && !sel) {
                            txt+='<option value="'+tt+'" selected >'+tt+'</option>';
                            tt=main.overall[wd][x];
                            sel=true;
                        }
                        else {
                            txt+='<option value="'+tt+'">'+tt+'</option>';
                            tt=main.overall[wd][x];
                        }
                    }
                    else {
                        tt=main.overall[wd][x];
                    }
                }

                $('#odielleMainDateSelect_'+suff).html(txt);
            }

        });

    }

}