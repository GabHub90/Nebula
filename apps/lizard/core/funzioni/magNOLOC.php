<?php

$this->closure['setStruttura']=function() {

    $a=array(
        "liz_exec"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_reparto"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        )
    );

    $this->form->add_fields($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"none",
        "liz_reparto"=>"none"
    );

    $this->form->load_tipi($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"",
        "liz_reparto"=>""
    );

    $this->form->load_expo($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"liz_exec",
        "liz_reparto"=>"liz_reparto"
    );

    $this->form->load_conv($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"magNOLOC",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        ),
        "liz_reparto"=>array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array(
                "font-size"=>"1.2em;"
            )
        )

    );

    //lettura magazzini da GALILEO
    $this->galileo->getMagazzini();
    if ( $result=$this->galileo->getResult() ) {
        $fetID=$this->galileo->preFetchBase('reparti');
        while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $a['liz_reparto']['prop']['options'][$row['reparto']]=array("testo"=>$row['reparto'].' - '.$row['descrizione'],"disabled"=>false);
        }
    }

    $this->form->load_mappa($a);

    ///////////////////////////////////////////////

   
    $a=array(

        "head"=>array(
            "flag"=>false,
            "height"=>"6",
            "css"=>array(
                "text-align"=>"right;",
                "color"=>"black;",
                "padding-right"=>"10px;",
                "font-weight"=>"bold;",
                "font-size"=>"larger;"
            )
        ),

        "body"=>array(
            
            array(
                "class"=>"",
                "css"=>array(),
                "blocchi"=>array(
                    array(
                        "class"=>"",
                        "css"=>array(
                            "height"=>"0px;"
                        ),
                        "elementi"=>array(
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"",
                                "sub"=>"",
                                "map"=>"liz_exec",
                                "css"=>array()
                            )
                        )
                    ),
                    array(
                        "class"=>"",
                        "css"=>array(),
                        "elementi"=>array(
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"Reparto:",
                                "sub"=>"",
                                "map"=>"liz_reparto",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"40%;",
                                    "padding"=>"3px;",
                                    "box-sizing"=>"border-box;"
                                )
                            ),
                            array(
                                "ID"=>"liz_scrivi_button",
                                "extra"=>"button",
                                "prefix"=>"",
                                "label"=>"",
                                "testo"=>"query",
                                "disabled"=>false,
                                "sub"=>"",
                                "map"=>"",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"15%;",
                                    "text-align"=>"center;",
                                    "height"=>"53%;"
                                ),
                                "extraCSS"=>array(
                                    "width"=>"120px;",
                                    "position"=>"relative;",
                                    "top"=>"50%;"
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    $this->form->load_struttura($a);

}

?>