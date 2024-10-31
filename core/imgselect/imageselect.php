<?php
class imageSelect {
    //genera una select con immagini e testo

    protected $tag;
    protected $map=array();

    protected $css=array(
        "select"=>array(
            "width"=>"100%;",
            "height"=>"100%;",
            "margin-top"=>"0px;"
        ),
        "button"=>array(
            "width"=>"100%;",
            "height"=>"100%;",
            "margin-top"=>"0px;"
        ),
        "buttonList"=>array(
            "width"=>"100%;",
            "height"=>"40px;",
            "margin-top"=>"0px;",
            "margin-bottom"=>"10px;",
        ),
        "list"=>array(
            "width"=>"100%;",
            "margin-top"=>"10px;",
            "z-index"=>"50;"
        ),
        "option"=>array(
            "width"=>"100%;",
            "height"=>"24px;",
            "margin-top"=>"0px;"
        ),
        "optionImg"=>array(
            "width"=>"20px;",
            "height"=>"20px;",
            "margin-top"=>"0px;"
        ),
        "optionTxt"=>array(
            "font-size"=>"1em;",
            "font-weight"=>"normal;",
            "margin-left"=>"20px;"
        ),
    );

    function __construct($tag,$css,$map) {

        $this->tag=$tag;
        $this->map=$map;

        foreach ($this->css as $k=>$o) {
            if (array_key_exists($k,$css)) {
                foreach ($css[$k] as $ck=>$c) {
                    $this->css[$k][$ck]=$c;
                }
            }
        }

    }

    static function imageSelectInit() {

        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/imgselect/style.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/imgselect/code.js?v='.time().'" />';
    }

    function draw() {

        //SELECT
        echo '<select id="imageSelect_'.$this->tag.'" style="display:none;" >';

            foreach ($this->map as $k=>$m) {
                echo '<option value="'.$m['value'].'" data-img="'.$m['img'].'" ';
                    if ($m['selected']) echo 'selected';
                echo ' >'.$m['txt'].'</option>';
            }

        echo '</select>';

        echo '<div id="imageSelect_ct_'.$this->tag.'" style="position:relative;height:100%;">';

            echo '<div style="';
                foreach ($this->css['select'] as $k=>$v) {
                    echo $k.':'.$v;
                }
            echo '">';

                echo '<button id="imageSelect_'.$this->tag.'_btn" class="imsbtn-select" style="';
                    foreach ($this->css['button'] as $k=>$v) {
                        echo $k.':'.$v;
                    }
                echo '"  data-val="" onclick="window._imageSelect_'.$this->tag.'.toggleList();"></button>';
            
                echo '<div id="imageSelect_'.$this->tag.'_b" class="imsbtn_b" style="display:none;';
                    foreach ($this->css['list'] as $k=>$v) {
                        echo $k.':'.$v;
                    }
                echo '">';

                    echo '<ul id="imageSelect_'.$this->tag.'_a" class="imsbtn_a" ></ul>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript">';
            echo 'window._imageSelect_'.$this->tag.'=new imageSelect(\''.$this->tag.'\');';
            echo 'var temp='.json_encode($this->css).';';
            echo 'window._imageSelect_'.$this->tag.'.init(temp);';
        echo '</script>';


    }

}
?>