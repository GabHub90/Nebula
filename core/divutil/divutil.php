<?php

class nebulaUtilityDiv {

    protected $tag="";
    protected $link="";

    function __construct($tag,$link) {

        $this->tag=$tag;
        $this->link=$link;
        
    }

    function draw() {

        echo '<div id="'.$this->tag.'_utilityDiv" style="position:relative;width:100%;height:100%;display:none;" >';

            echo '<div style="position:relative;height:5%;text-align:left;">';
                echo '<div id="'.$this->tag.'_utilityDiv_head" style="position:relative;display:inline-block;vertical-align:top;font-weight:bold;width:85%;" ></div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:right;">';
                    echo '<img style="height:90%;position:relative;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/divutil/img/chiudi.png" onclick="'.$this->link.';" />';
                echo '</div>';
            echo '</div>';

            echo '<div id="'.$this->tag.'_utilityDivBody" style="position:relative;height:94%;"></div>';

        echo '</div>';

    }

}

?>