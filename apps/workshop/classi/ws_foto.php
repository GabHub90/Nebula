<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fotone/fotone.php');

class workshopFoto extends Fotone {

    protected $IDlam="";
    protected $cartellaFoto="";

    protected $credenziali=array('utente'=>'usato','pw'=>'usato1');
    protected $serverFoto="";
    protected $conn;

    function __construct($indice,$IDlam,$host) {

        $this->path_func='/nebula/apps/workwshop/core/';

        $this->IDlam=$IDlam;
        $this->serverFoto=$host;

        $this->conn=ftp_connect($this->serverFoto);

        $this->cartellaFoto.="/".$this->IDlam;

        $categorie=array(
            "foto"=>array(
                "tag"=>'foto',
                "testo"=>"Foto",
                "server"=>$this->serverFoto,
                "cartella"=>$this->cartellaFoto,
                "anteprime_per_riga"=>8,
                "righe"=>1,
                "posizione"=>1,
                "stato"=>1
            )
        );


        /////////////////////////////////////////
        //definizione delle categorie in base alle cartelle già salvate

        $temp_cat=array();

        if ($this->conn) {

            if (ftp_login($this->conn,$this->credenziali["utente"],$this->credenziali["pw"]) ) {

                if (ftp_chdir( $this->conn,$this->cartellaFoto ) ) {
                    $temp_dir=ftp_mlsd($this->conn,'.');
                    foreach ($temp_dir as $t) {
                        if ($t['type']!='dir') continue;
                        $temp_cat[$t['name']]=$t['name'];
                    } 
                }
                else {
                    ftp_mkdir( $this->conn,$this->cartellaFoto );
                    foreach ($categorie as $tag=>$c) {
                        if ($c['stato']==0) continue;
                        ftp_mkdir ($this->conn,$this->cartellaFoto.'/'.$tag);
                        ftp_mkdir ($this->conn,$this->cartellaFoto.'/'.$tag.'/foto');
                        ftp_mkdir ($this->conn,$this->cartellaFoto.'/'.$tag.'/anteprime');
                        $temp_cat[$tag]=$tag;
                    }
                }
            }
        }

        ///////////////////////////////////
        //ACTUAL CATEGORIES - TEST

        $actual_cat=array();

        foreach ($categorie as $tag=>$c) {
            //if (array_key_exists($c['tag'],$temp_cat)) {
                $actualCat[$tag]=$c;
            //}
        }
        ///////////////////////////////// END TEST

        parent::__construct($indice,$actualCat);

        if ($this->conn) {
            ftp_close($this->conn);
        }
    }

    ////////////////////////////////////////////////////////
    //METODO SOVRASCRITTO - L'INTERA CLASSE fotone È DA RIVEDERE
    ////////////////////////////////////////////////////////
    function draw() {

        echo '<div id="fotone_box_'.$this->indice.'_cats" style="position:relative;display:block;width:98%;margin-top:5px;">';

            foreach ($this->categorie as $tag=>$o) {

                $c=$o->getInfo();

                $dim=round(100/$c['anteprime_per_riga'],2);

                $elem_riga=ceil((count($c['foto'])+1)/$c['righe']);
    
                echo '<div style="width:100%;margin-top:5px;">';
    
                    echo '<div style="font-weight:bold;">'.$c['testo'].'</div>';
    
                    if ($elem_riga>$c['anteprime_per_riga']) {
                        echo '<div style="position:absolute;right:15px;top:5px;font-size:smaller;font-weight:bold;">scorri --></div>';
                    }
    
                    echo '<div style="position:relative;width:100%;overflow:scroll;overflow-y:hidden;margin-top:10px;">';
                        echo $this->griglia($c,$dim,$elem_riga);
                    echo '</div>';
    
                echo '</div>';
            
            }

        echo '</div>';
    }

    function griglia($c,$dim,$elem_riga) {

        if ($elem_riga<$c['anteprime_per_riga']) $elem_riga=$c['anteprime_per_riga'];

        $row=1;
        $col=1;
        $pos=0;

        while ($row<=$c['righe']) {

            $col=1;

            echo '<div style="margin-bottom:20px;height:'.$dim.'%;">';

                //se è la prima foto della prima riga
                if ($col==1 && $row==1) {

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;text-align:center;margin-left:1%;width:'.$dim.'%;">';   
                        echo '<img style="position:relative;width:96%;height:96%;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/fotone/img/extra.png" onclick="window._fotone_'.$this->indice.'_obj.add_foto(\''.$c['tag'].'\')" />';
                        echo '<input type="file" id="fotone_upload_photo_'.$this->indice.'" name="files[]" multiple style="opacity:0;" onchange="window._fotone_'.$this->indice.'_obj.salva_foto();" accept="image/jpg,image/jpeg,image/png"/>';
                    echo '</div>';

                    $col++;
                }

                while ( $col<=$elem_riga && isset($c['foto'][$pos]) ) {

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;text-align:center;margin-left:1%;width:'.$dim.'%;">';
                        echo '<img id="fotone_'.$this->indice.'_'.$c['tag'].'_'.$pos.'" style="position:relative;width:96%;height:96%;cursor:pointer;margin-top:2%;" src="" onclick="window._fotone_'.$this->indice.'_obj.open_viewer(\''.$c['tag'].'\',\''.$pos.'\')" />';
                    echo '</div>';

                    $pos++;

                    if(!isset($c['foto'][$pos])) {
                        $col=$elem_riga+1;
                    }
                    else $col++;
                }

            echo '</div>';

            $row++;
        }

    }













    function list($categoria) {

        $ret=array();

        //questa funzione viene chiamata in fase di costruzione da FOTONE
        //quindi $this->conn è instanziato

        if ($this->conn) {

            if (ftp_login($this->conn,$this->credenziali['utente'],$this->credenziali['pw']) ) {

                $cartella=$this->actualCat[$categoria]['cartella'].'/'.$categoria;

                //$base='ftp://'.$this->credenziali['utente'].':'.$this->credenziali['pw'].'@'.$this->server_foto.'/'.$cartella;

                ftp_chdir($this->conn,'/'.$cartella.'/anteprime');
                $temp_dir=ftp_mlsd($this->conn,'.');

                //$ret['anteprime'][]=$temp_dir;

                foreach($temp_dir as $t) {
                    if ($t['type']!='file') continue;
                    //$ret['anteprime'][]=$base.'/'.'anteprime'.'/'.$t['name'];
                    //$f=file_get_contents($base.'/'.'anteprime'.'/'.$t['name']);
                    //$ret['anteprime'][]='data:image/png;base64,'.base64_encode($f);
                    //$ret['anteprime'][]=$cartella.'/'.'anteprime'.'/'.$t['name'];
                    $ret[]=$t['name'];
                }

                /*ftp_chdir($this->conn,'/'.$cartella.'/foto');
                $temp_dir=ftp_mlsd($this->conn,'.');
                foreach($temp_dir as $t) {
                    if ($t['type']!='file') continue;
                    $ret['foto'][]=$cartella.'/'.'foto'.'/'.$t['name'];
                }*/
            }
        }

        return $ret;

    }

    function upload($server,$upload_dir,$files) {

        //se $files è vuoto non proseguire
        if ( !array_key_exists('error',$files['files']) ) return;

        $conn=ftp_connect($server);
        ftp_login($conn,$this->credenziali["utente"],$this->credenziali["pw"]);

        //$files = rearrange($_FILES);
        $allowed = ["jpg", "jpeg", "png"];

        $ts=mktime();
        //$res=json_encode($files);

        foreach ($files["files"]["error"] as $key => $error) {
            if ($error == 0) {
                $tmp_name = $files["files"]["tmp_name"][$key];
                // basename() may prevent filesystem traversal attacks;
                // further validation/sanitation of the filename may be appropriate
                //$name = basename($_FILES["files"]["name"][$key]);

                $temp_ext=explode('/',$files["files"]["type"][$key]);

                $ext = strtolower($temp_ext[1]);

                if ( !in_array($ext,$allowed) ) continue;

                $im=new fotoneImage($tmp_name,$ext,$_SERVER['DOCUMENT_ROOT'].$this->path_func.'/fotone_temp');

                $name=$ts.'_'.$this->id_pratica.'_'.$key.'.'.$ext;

                //ftp_chdir($conn,'/');

        //FOTOGRAFIA
                $im->resize(800,600);

                $res=$im->savepng($name);

                //SPOSTA LA FOTO NEL SERVER FTP
                if (ftp_chdir($conn,'/'.$upload_dir.'/foto') ) {
                    ftp_put($conn,$name,$res);
                }
                
                //CANCELLA LA FOTO DALLA CARTELLA TEMP
                unlink($res);

        //ANTEPRIMA

                $im->resize(120,90);

                $res=$im->savepng($name);

                //SPOSTA LA FOTO NEL SERVER FTP
                if (ftp_chdir($conn,'/'.$upload_dir.'/anteprime') ) {
                    ftp_put($conn,$name,$res);
                }
                
                //CANCELLA LA FOTO DALLA CARTELLA TEMP
                unlink($res);

            }
        }

        ftp_close($conn);
        return $upload_dir;
    }

    function download($server,$cartella,$tipo,$categoria,$arr) {

        //$conn=ftp_connect($server);
        //ftp_login($conn,$this->credenziali["utente"],$this->credenziali["pw"]);

        $base='ftp://'.$this->credenziali['utente'].':'.$this->credenziali['pw'].'@'.$server;

        $ret=array();

        foreach ($arr as $a) {

            $f=file_get_contents($base.'/'.$cartella.'/'.$categoria.'/'.$tipo.'/'.$a);
            $ret[]='data:image/png;base64,'.base64_encode($f);
        }

        return $ret;
    }

    function refresh($categorie) {

        foreach ($categorie as $key=>$c) {

            $categorie[$key]['foto']=array();

            $this->conn=ftp_connect($c['server']);

            ftp_login($this->conn,$this->credenziali['utente'],$this->credenziali['pw']);

            $cartella=$c['cartella'].'/'.$key;

            ftp_chdir($this->conn,'/'.$cartella.'/anteprime');
            $temp_dir=ftp_mlsd($this->conn,'.');

            $f=array();

            foreach($temp_dir as $t) {
                if ($t['type']!='file') continue;
            
                $categorie[$key]['foto'][]=$t['name'];
            }
        }

        return $categorie;
    }

    function delete() {

    }


}
    

?>