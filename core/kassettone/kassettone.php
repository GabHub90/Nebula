<?php
class kassettone {

    protected $config=array(
        "tag"=>"",
        "default"=>"",
        "cssButton"=>array(),
        "cssLista"=>array()
    );

    protected $elem=array();

    function __construct($tag) {

        $this->config['tag']=$tag;
        
    }

    function loadCss($tipo,$arr) {
        if (array_key_exists("css".$tipo,$this->config)) {
            $this->config['css'.$tipo]=$arr;
        }
    }

    function addElem($txt,$val) {

        $this->elem[]=array(
            "testo"=>$txt,
            "valore"=>$val
        );
    }

    function drawJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/kassettone/kassettone.js" ></script>';    
    }

    function drawButton($txt,$mark) {

        $this->config['default']=$txt;

        echo '<div id="kassettone_button_'.$this->config['tag'].'" style="position:relative;cursor:pointer;border:1px solid black;border-radius:5px;overflow:visible;padding:1px;text-align:center;font-weight:bold;';
            foreach ($this->config['cssButton'] as $k=>$v) {
                echo $k.':'.$v.';';
            }
        echo '" onclick="window._kassettone_'.$this->config['tag'].'.switch(\''.$mark.'\');" >';

            echo '<div id="kassettone_button_image_'.$mark.'" style="position:relative;width:100%;height:100%;background-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/kassettone/img/kasbak.png);background-size:100% 100%;background-repeat:no-repeat;border-radius:5px;background-position-x: 50%;" >';

                echo $this->config['default'];
            
            echo '</div>';

        echo '</div>';
    }

    function drawLista() {

        echo '<div id="kassettone_lista_'.$this->config['tag'].'" style="position:relative;display:none;';
            foreach ($this->config['cssLista'] as $k=>$v) {
                echo $k.':'.$v.';';
            }
        echo '">'; 

            foreach ($this->elem as $k=>$e) {

                echo '<div style="cursor:pointer;border:1px solid black;margin-top:2px;margin-bottom:2px;width:95%;" onclick="window._kassettone_'.$this->config['tag'].'.select(\''.$e['valore'].'\');" >';
                    echo $e['testo'];
                echo '</div>';

            }

            echo '<script type="text/javascript">';
                echo 'window._kassettone_'.$this->config['tag'].'=new kassettone(\''.$this->config['tag'].'\');'; 
            echo '</script>';
        
        echo '</div>';
    }

}
?>