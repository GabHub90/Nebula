<?php
//fornisce un sistema per caricare fotografie, taggarle e visionarle

include('fotone_cat.php');
//include('fotone_visual.php');
include('fotone_image.php');

abstract class Fotone {

    protected $indice="";

    protected $categorie=array();

    //protected $viewer;

    /*servono nel caso si debba accedere ad un server FTP per le foto
    protected $conn;
    protected $credenziali;
    */

    //è il riferimento alle funzioni che vengono implementate dall'applicazione che embedda FOTONE
    //la proprietà viene valorizzata dalla classe CHILD
    protected $path_func;

    function __construct($indice,$categorie) {

        /*
        $categorie=array(
            "esterni"=>array(
                "tag"=>'esterni',
                "testo"=>"Foto Esterno",
                "server"=>"\\10.55.99.89\USATO\GVA",
                "cartella"=>"".$this->id_pratica,
                "anteprime_per_riga"=>8,
                "righe"=>2
            ),
            "interni"=>array(
                "tag"=>'interni',
                "testo"=>"Foto Interno",
                "server"=>"\\10.55.99.89\USATO\GVA",
                "cartella"=>"".$this->id_pratica,
                "anteprime_per_riga"=>8,
                "righe"=>2
            ),
            "altro"=>array(
                "tag"=>'altro',
                "testo"=>"Altro",
                "server"=>"\\10.55.99.89\USATO\GVA",
                "cartella"=>"".$this->id_pratica,
                "anteprime_per_riga"=>8,
                "righe"=>2
            )
        );
        */

        $this->indice=$indice;

        //$lista=array();

        //$categorie (tag,testo)
        foreach ($categorie as $tag=>$c) {
            $this->categorie[$tag]=new FotoneCat($indice,$c);

            $this->categorie[$tag]->load_foto($this->list($tag));

            //$lista[$tag]=$this->categorie[$tag]->get_foto();
        }

        //$this->viewer=new FotoneVisual($this->indice,$lista);
    }

    function draw(){

        ob_start();
        
            echo '<div id="fotone_box_'.$this->indice.'" style="position:relative;width:100%;height:100%;overflow:scroll;" >';
                echo $this->draw_box();
            echo '</div>';

            echo '<div id="fotone_viewer_'.$this->indice.'" style="position:relative;width:100%;height:100%;display:none;" >';
                echo $this->draw_viewer();
            echo '</div>';

        return ob_get_clean();
    }

    function draw_box() {

        $txt="";

        $txt.='<div id="fotone_box_'.$this->indice.'_cats" style="position:relative;display:block;width:97%;height:99%;">';
        $txt.='</div>';

        $txt.='<script type="text/javascript">';
            $txt.='window._fotone_'.$this->indice.'_obj=new Fotone(\''.$this->indice.'\');';
            $txt.='window._fotone_'.$this->indice.'_obj.set_contesto("path","'.$this->path_func.'");';
            //$txt.='window._fotone_'.$this->indice.'_obj.prova();';
            foreach ($this->categorie as $c) {
                $txt.=$c->draw_js();
            }
            //funziona solo nel caso di caricamento di pagina e non di richiamo da ajax
            $txt.= '$( window ).on( "load", function() {';
                $txt.='window._fotone_'.$this->indice.'_obj.formatta()';
            $txt.= '});';
        $txt.='</script>';

        return $txt;
        
    }

    function draw_viewer() {

        echo '<div>';

            echo '<div style="position:relative;display:inline-block;width:10%;height:100%;text-align:center;vertical-align:top;">';
                //echo '<button style="margin-top:30px;" onclick="window._fotone_'.$this->indice.'_obj.load_viewer(-1,false);" ><--</button>';
                echo '<img style="margin-top:30px;" style="width:20px;height:40px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/fotone/img/bf_left.png" onclick="window._fotone_'.$this->indice.'_obj.load_viewer(-1,false);" />';

                echo '<div style="margin-top:60px;" >';
                    echo '<button style="font-size:1em;background-color:orange;" onclick="window._fotone_'.$this->indice.'_obj.close_viewer();">chiudi</button>';
                echo '</div>';
                
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:80%;height100%;">';

                echo '<div id="fotone_viewer_'.$this->indice.'_nav" style="position:relative;width:100%;height:5%;">';

                    echo '<div style="margin-top:20px;">';

                        echo '<span>Categoria:</span>';

                        echo '<select id="fotone_viewer_'.$this->indice.'_select" style="margin-left:10px;font-size:1.5em;" onchange="window._fotone_'.$this->indice.'_obj.viewer_selcat(this.value);" >';
                        echo '</select>';

                        echo '<span id="fotone_viewer_'.$this->indice.'_span" style="font-size:1em;margin-left:15px;" ></span>';

                    echo '</div>';

                echo '</div>';

                echo '<div id="fotone_viewer_'.$this->indice.'_view" style="position:relative;width:100%;height:95%;text-align:center;">';
                    echo '<img id="fotone_viewer_'.$this->indice.'_img" style="max-width:100%;" src="" />';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:10%;height:100%;text-align:center;vertical-align:top;">';
                //echo '<button style="margin-top:30px;" onclick="window._fotone_'.$this->indice.'_obj.load_viewer(1,false);" >--></button>';
                echo '<img style="margin-top:30px;" style="width:20px;height:40px;" src="http://'.$_SERVER['HTTP_HOST'].'/apps/gals/fotone/img/bf_right.png" onclick="window._fotone_'.$this->indice.'_obj.load_viewer(1,false);" />';
            echo '</div>';

        echo '</div>';
    }

    //definisce il metodo per recuperare la lista di foto per ogni categoria dal repository
    abstract function list($categoria);

    //definisce il caricamento delle foto nel repository
    abstract function upload($server,$upload_dir,$files);

    //definisce il metodo per il caricamento effettivo di una lista di foto
    abstract function download($server,$cartella,$tipo,$categoria,$arr_nomi);

    //definisce il metodo per aggiornare le categorie quando si carica o cancella una foto
    abstract function refresh($categorie);

    //definisce l'eliminazione delle foto dal repository
    abstract function delete();

}
?>