<?php
class FotoneCat {

    protected $indice="";
    protected $info=array();

    function __construct($indice,$info) {

        $this->indice=$indice;
        $this->info=$info;

        //caricare i link delle fotografie in $this->info['photos'];

        ///////////////////////////////////
        //TEST
        /*$this->info['photos']=array(
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a",
            $info['server']."\\".$info['cartella']."\\a"
        );*/
        ///////////////////////////////////
    }

    //carica sia la lista delle ANTEPRIME che la lista delle FOTO
    function load_foto($arr) {
        //$this->info['anteprime']=$arr['anteprime'];
        $this->info['foto']=$arr;
    }

    function getInfo() {
        return $this->info;
    }

    //ritorna la lista delle SOLE fotografie per il visualizzatore
    function get_foto() {
        return $this->info['foto'];
    }

    function draw_js() {

        $txt="";
        $txt.='var temp='.json_encode($this->info).';';
        $txt.='window._fotone_'.$this->indice.'_obj.add_categoria(temp);';

        return $txt;
    }
}
?>