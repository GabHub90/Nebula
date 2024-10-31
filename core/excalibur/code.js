function nebulaExcalibur(tag,selFlag) {

    this.tag=tag;

    this.head=true;
    this.highlight=true;
    this.select=selFlag;

    this.export=false;

    this.execute=function(str) {

        this['extra_'+str]();
    }

    this.download=function(result) {

        var a = document.createElement('a');
        if (window.URL && window.Blob && ('download' in a) && window.atob) {
            // Do it the HTML5 compliant way
            var blob = window._nebulaMain.base64ToBlob(result.download.data, result.download.mimetype);
            var url = window.URL.createObjectURL(blob);
            a.href = url;
            a.download = result.download.filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    }

    this.getLines=function(tipo) {

        //alert('Ti piacerebbe!!!');

        if (tipo=='txt') var res="";
        else if (tipo=='array') var res=[];
        else return;

        var tag=this.tag;
        var select=this.select;

        if (this.head) {

            if (tipo=='txt') var tt="";
            else if (tipo=='array') var tt=[];

            $('#excalibur_'+tag+'_tabHead th').each(function(){
                
                var temp=''+$(this).data('val');

                if (temp!='excaliburSelect') {

                    temp=window._nebulaMain.concatDivToText(temp);

                    if (tipo=='txt') {
                        if (!temp || temp=="") tt+=';';
                        else {
                            //console.log(temp);
                            temp.replace(';','');
                            tt+=temp+';';
                        }
                    }
                    else if (tipo=='array') {

                        if (!temp || temp=="") tt.push('');
                        else {
                            tt.push(temp);
                        }
                    }
                }
            });

            if (tipo=='txt') {
                res+=tt.slice(0, -1);
                res+='\r\n';
            }
            else if (tipo=='array') {
                res.push(tt);
            }
        }

        $('#excalibur_'+tag+'_tabBody tr').each(function() {

            var row=$(this).data('row');

            if (tipo=='txt') var tt="";
            else if (tipo=='array') var tt={};

            var exec=true;

            //alert(this.select);

            if (select) {
                var temp=$('#excalibur_select_'+tag+'_tabRow_'+row);
                if (temp) {
                    if (!temp.prop('checked')) exec=false;
                }
            }

            if (exec) {

                $('#excalibur_'+tag+'_tabRow_'+row+' td').each(function(){
                    
                    var temp=''+$(this).data('val');

                    if (temp!='excaliburSelect') {

                        temp=window._nebulaMain.concatDivToText(temp);

                        if (tipo=='txt') {
                            if (!temp || temp=="") tt+=';';
                            else {
                                //console.log(temp);
                                temp.replace(';','');
                                tt+=temp+';';
                            }
                        }
                        else if (tipo=='array') {

                            var key=$(this).data('key');

                            if (!temp || temp=="") tt[key]='';
                            else {
                                tt[key]=temp;
                            }
                        }
                    }
                });

                if (tipo=='txt') {
                    res+=tt.slice(0, -1);
                    res+='\r\n';
                }
                else if (tipo=='array') {
                    res.push(tt);
                }
            }

        });

        this.export=res;

    }

    this.toggle=function() {

        var t=$('#excalibur_toggle').val();

        if (!t) return;

        if (t==1) {
            $("input[id^='excalibur_select_"+this.tag+"_tabRow_']").prop('checked',false);
            $('#excalibur_toggle').val('0');
        }

        else if (t==0) {
            $("input[id^='excalibur_select_"+this.tag+"_tabRow_']").prop('checked',true);
            $('#excalibur_toggle').val('1');
        }
    }

    this.highlightRow=function(row) {

        if (!this.highlight) return;

        $('#excalibur_'+tag+'_tabBody td').css('border-color','#888888');

        $('#excalibur_'+tag+'_tabRow_'+row+' td').css('border-top-color','red');
        $('#excalibur_'+tag+'_tabRow_'+row+' td').css('border-bottom-color','red');
    }

    this.exportCsv=function() {
        
        this.export=false;
        this.head=true;
        this.getLines('txt');

        if (!this.export || this.export=='') return;

        //console.log(txt);
        var result={
            download: {
                mimetype: 'text/csv',
                filename: this.tag+'_export.csv',
                data: btoa(this.export)
            }
        }

        this.download(result);
        
    }

}