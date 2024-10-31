function Fotone(indice) {

    this.indice=indice;

    this.categorie={};

    this.viewer_cat=[];
    this.viewer_nav={ "cat":0 , "pos":0 };

    //path al PHP dove ci sono le fuzioni da chiamare (UPLOAD e DELETE)
    this.contesto={
        "path":""
    };

    /*$array(
        "tag"=>'esterni',
        "testo"=>"Foto Esterno",
        "server"=>"10.55.99.89",
        "cartella"=>"USATO/GVA/BX725MF_0"
        "anteprime_per_riga"=>8,
        "righe"=>2,
        "foto"=>array()
        ),
    );*/
    
    //la categoria in cui si è premutoil tasto +
    this.categoria_actual;

    this.add_categoria=function(arr) {
        this.categorie[arr.tag]=arr;
    }

    this.set_contesto=function(key,val) {
        this.contesto[key]=val;
    }

    //il metodo FORMATTA funziona SOLO per gli oggetti renderizzati di cui è possibile leggere le dimensioni
    this.formatta=function() {

        var txt='';
        var vwtxt='';
        var view_count=0;
        this.viewer_cat=[];

        for (var x in this.categorie) {
      
            //aggiorna l'array VIEWER_CAT
            var w={"tag":this.categorie[x].tag , "len":this.categorie[x].foto.length};
            this.viewer_cat.push(w);
            vwtxt+='<option value="'+view_count+'" >'+x+'</option>';
            view_count++;
            //////////////////////////////////

            var dim=this.get_dim(this.categorie[x].anteprime_per_riga,this.categorie[x].foto.length,this.categorie[x].righe);

            //alert(JSON.stringify(dim));

            txt+='<div style="width:100%;border-bottom:2px solid;">';

                txt+='<div>'+this.categorie[x].testo+'</div>';

                if (dim.elem_riga>this.categorie[x].anteprime_per_riga) {
                    txt+='<div style="position:absolute;left:'+(dim.dim-70)+'px;top:5px;font-size:smaller;font-weight:bold;">scorri --></div>';
                }

                txt+='<div style="position:relative;width:100%;overflow:scroll;">';
                    txt+=this.griglia(this.categorie[x],dim,x);
                txt+='</div>';

            txt+='</div>';

            //txt+='<div>'+JSON.stringify(this.categorie[x])+'</div>';

        }

        $('#fotone_box_'+this.indice+'_cats').html(txt);

        $('#fotone_viewer_'+this.indice+'_select').html(vwtxt);

        this.post_formatta();
    }

    //metodo da sovrascrivere pr far fre qualche cosa dopo la formattazione
    this.post_formatta=function() {

    }

    this.fotopercat=function() {

        var res={};

        for (var x in this.categorie) {
            res[x]=this.categorie[x].foto.length;
        }

        return res;
    }

    this.get_dim=function(apr,apl,ari) {

        var dim=document.getElementById('fotone_box_'+this.indice+'_cats').clientWidth-(3*apr);
        var elem_riga=Math.ceil((apl+1)/ari);

        var a={"dim":dim,"elem_riga":elem_riga};

        return a;
    }

    this.griglia=function(a,d,x) {

        //x=categoria

        /*var dim=document.getElementById('fotone_box_'+this.indice+'_cats').clientWidth-(3*a.anteprime_per_riga);

        //aggiungioamo una foto perchè la prima è il tasto più per caricarne di nuove
        //var righe=Math.ceil((a.photos.length+1)/a.anteprime_per_riga);
        var elem_riga=Math.ceil((a.photos.length+1)/a.righe);*/

        var dim=d.dim;
        var elem_riga=d.elem_riga;

        if (elem_riga<a.anteprime_per_riga) elem_riga=a.anteprime_per_riga;

        //fissiamo 5px di distanza tra le anteprime e tra le righe
        var space=5;

        var avail=dim-((parseInt(a.anteprime_per_riga)+2)*space);

        var w=Math.floor(avail/a.anteprime_per_riga);

        var row=1;
        var col=1;
        var pos=0;

        var txt='';

    /////////////////
        txt+='<div style="margin-top:'+space+'px;margin-bottom:'+(6*space)+'px;width: max-content;">';

            while (row<=a.righe) {

                col=1;

                txt+='<div style="margin-bottom:'+space+'px;">';

                    //se è la prima foto della prima riga
                    if (col==1 && row==1) {

                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;text-align:center;margin-left:'+space+'px;width:'+w+'px;height:'+w+'px;border:1px solid white;">';   
                            txt+='<img style="position:relative;width:96%;height:96%;cursor:pointer;margin-top:2%;" src="http://'+window.location.host+'/apps/gals/fotone/img/extra.png" onclick="window._fotone_'+this.indice+'_obj.add_foto(\''+a.tag+'\')" />';
                            //txt+='<img for="fotone_upload_photo" style="position:relative;width:96%;height:96%;cursor:pointer;margin-top:2%;" src="http://'+window.location.hostname+'/apps/gals/fotone/img/extra.png" />';
                            txt+='<input type="file" id="fotone_upload_photo_'+this.index+'" name="files[]" multiple style="opacity:0;" onchange="window._fotone_'+this.indice+'_obj.salva_foto();" accept="image/jpg,image/jpeg,image/png"/>';
                        txt+='</div>';

                        col++;
                    }

                    while (col<=elem_riga && typeof a.foto[pos] !== 'undefined') {

                        txt+='<div style="position:relative;display:inline-block;vertical-align:top;text-align:center;margin-left:'+space+'px;width:'+w+'px;height:'+w+'px;border:1px solid white;">';
                            //txt+='<img style="position:relative;width:96%;height:96%;cursor:pointer;margin-top:2%;" src="'+a.anteprime[pos]+'" onclick="window._fotone_'+this.indice+'_obj.open_viewer(\''+x+'\',\''+pos+'\')" />';
                            txt+='<img id="fotone_'+this.indice+'_'+x+'_'+pos+'" style="position:relative;width:96%;height:96%;cursor:pointer;margin-top:2%;" src="" onclick="window._fotone_'+this.indice+'_obj.open_viewer(\''+x+'\',\''+pos+'\')" />';
                            //txt+=''+pos+' righe:'+righe+' col:'+col+' row:'+row+' '+a.photos.length;
                        txt+='</div>';

                        pos++;

                        if(typeof a.foto[pos] === 'undefined') {
                            col=elem_riga+1;
                        }
                        else col++;
                    }

                txt+='</div>';

                row++;
            }

            txt+='<script type="text/javascript">';
                txt+="window['_fotone_"+indice+"_obj'].load_foto('anteprime','"+x+"');";
            txt+='</script>';

        txt+='</div>';
        
        return txt;
    }

    this.add_foto=function(categoria) {
        //alert('aggiungi foto');
        //attiva INPUT type="FILE"
        this.categoria_actual=categoria;
        $('#fotone_upload_photo_'+this.index).trigger('click');
    }

    this.salva_foto=function() {
        //legge le foto caricate in #fotone_upload_photo_'+this.index e le scrive nel SERVER
        //var files = document.getElementById('fotone_upload_photo_'+this.index).files;
        /*for (var x in files) {
            alert(files[x].name);
        }*/
        var fd=new FormData();
        fd.append('server',this.categorie[this.categoria_actual].server);
        fd.append('cartella',this.categorie[this.categoria_actual].cartella);
        fd.append('categoria',this.categoria_actual);

        for (var x in document.getElementById('fotone_upload_photo_'+this.index).files) {
            fd.append('files[]',document.getElementById('fotone_upload_photo_'+this.index).files[x]);
        }
    
        var indice=this.indice;

        $.ajax({
            "url":"http://"+location.host+this.contesto.path+"/fotone_upload.php",
            "async":true,
            "cache":false,
            "data":fd,
            "type":"POST",
            "processData":false,
            "contentType":false,
            "success":function(ret) {
                //alert ('_js_fotone_'+indice+'_obj');
                window['_fotone_'+indice+'_obj'].refresh();
                //var ht=window['_fotone_'+indice+'_obj'].formatta();
                //$('#fotone_box_'+indice).html(ht);
        }});

        $('#fotone_box_'+indice+'_cats').html('');
    }

    this.refresh=function() {

        var param={"categorie":this.categorie};

        var indice=this.indice;

        $.ajax({
            "url":"http://"+location.host+this.contesto.path+"/fotone_refresh.php",
            "async":true,
            "cache":false,
            "data":{"param":param},
            "type":"POST",
            "success":function(ret) {
                //alert (ret);
                eval('window._fotone_'+indice+'_obj.categorie='+ret+';');
                setTimeout(function() {window['_fotone_'+indice+'_obj'].refresh_post(indice);},200);
        }});
    }

    this.refresh_post=function(indice) {
        var ht=window['_fotone_'+indice+'_obj'].formatta();
    }

    this.load_foto=function(tipo,categoria) {

        var arr=this.categorie[categoria]['foto'];

        var indice=this.indice;

        var param={"server":this.categorie[categoria].server,"cartella":this.categorie[categoria].cartella,"tipo":tipo,"categoria":categoria,"foto":arr};

        //if  (param.foto.length==0) param.foto={};

        //alert(JSON.stringify(param));

        $.ajax({
            "url":"http://"+location.host+this.contesto.path+"/fotone_download.php",
            "async":true,
            "cache":false,
            "data":{"param":param},
            "type":"POST",
            "success":function(ret) {
                //alert ('_js_fotone_'+indice+'_obj');
                eval('var arr='+ret);

                for (var x in arr) {
                    $('#fotone_'+indice+'_'+categoria+'_'+x).prop('src',arr[x]);
                }
        }});
    }

    this.load_viewer=function(step,sel) {

        //SEL indica true/false se la richiesta proviene dalla select delle categorie

        var indice=this.indice;

        //calcola posizione
        //this.viewer_cat=[ {"tag":arr.tag , "len":arr.foto.length} ]
        //this.viewer_nav={ "cat":0 , "pos":0 };

        this.viewer_nav.pos=parseInt(this.viewer_nav.pos)+parseInt(step);

        var error=false;

        //alert(this.viewer_nav.pos+' '+this.viewer_nav.cat);

        //se la posizione è minore di zero torna indietro di una categoria
        if (this.viewer_nav.pos<0) {
            this.viewer_nav.cat=parseInt(this.viewer_nav.cat)-1;

            //se la categoria è la prima (cat<0) allora salta all'ultima
            if (this.viewer_nav.cat<0) {
                for (var x in this.viewer_cat) {
                    this.viewer_nav.cat=x;
                }
            }

            //posizionati sull'ultima foto (se esiste)
            if (this.viewer_cat[this.viewer_nav.cat].len==0) {
                this.viewer_nav.pos=0;
                error=true;
            }
            else this.viewer_nav.pos=this.viewer_cat[this.viewer_nav.cat].len-1;
        }

        //se la posizione è maggiore dell'ultima aumenta di una categoria
        else if (this.viewer_nav.pos>(this.viewer_cat[this.viewer_nav.cat].len-1) ) {

            //se la richiesta non proviene dalla selezione della categoria
            if (!sel) this.viewer_nav.cat=parseInt(this.viewer_nav.cat)+1;

             //se la categoria è l'ultima allora salta alla prima
             if(typeof this.viewer_cat[this.viewer_nav.cat] === 'undefined') {
                this.viewer_nav.cat=0;
             }

            //posizionati sulla prima foto (se esiste)
            if (this.viewer_cat[this.viewer_nav.cat].len==0) {
                error=true;
            }

            this.viewer_nav.pos=0;
        }

        //##################################

        $('#fotone_viewer_'+indice+'_select').val(this.viewer_nav.cat);

        //valorizzazione dello SPAN in testata
        var temp_txt='';

        if (!error) {
            temp_txt=''+(this.viewer_nav.pos+1)+'/'+this.viewer_cat[this.viewer_nav.cat].len;
        }

        $('#fotone_viewer_'+indice+'_span').html(temp_txt);

        $('#fotone_viewer_'+indice+'_img').prop('src','');

        //se il posizionamento aveva dato errore non proseguire
        if (error) return;

        var param={
            "server":this.categorie[this.viewer_cat[this.viewer_nav.cat].tag].server,
            "cartella":this.categorie[this.viewer_cat[this.viewer_nav.cat].tag].cartella,
            "tipo":"foto",
            "categoria":this.viewer_cat[this.viewer_nav.cat].tag,
            "foto":[ this.categorie[this.viewer_cat[this.viewer_nav.cat].tag].foto[this.viewer_nav.pos] ]
        };

        //alert(JSON.stringify(param));

        $.ajax({
            "url":"http://"+location.host+this.contesto.path+"/fotone_download.php",
            "async":true,
            "cache":false,
            "data":{"param":param},
            "type":"POST",
            "success":function(ret) {
                //alert ('_js_fotone_'+indice+'_obj');
                eval('var arr='+ret);

                $('#fotone_viewer_'+indice+'_img').prop('src',arr[0]);
        }});

    }

    this.open_viewer=function(cat,pos) {
        //alert('open viewer');
        //window['_js_fotone_visual_'+this.indice+'_obj'].init(cat,pos);

        for (var x in this.viewer_cat) {
            if (this.viewer_cat[x].tag==cat) {
                this.viewer_nav.cat=x;
                break;
            }
        }

        this.viewer_nav.pos=pos;

        this.load_viewer(0,false);

        $('#fotone_box_'+this.indice).hide();
        $('#fotone_viewer_'+this.indice).show();
    
    }

    this.close_viewer=function() {
        $('#fotone_viewer_'+this.indice).hide();
        $('#fotone_box_'+this.indice).show();
    }

    this.viewer_selcat=function(cat) {

        this.viewer_nav.cat=cat;
        this.viewer_nav.pos=0;

        this.load_viewer(0,true);
    }

}