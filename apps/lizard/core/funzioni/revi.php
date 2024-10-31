<?php

$this->closure['setStruttura']=function() {

    $a=array(
        "liz_exec"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_data"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        )
    );

    $this->form->add_fields($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"none",
        "liz_data"=>"none"
    );

    $this->form->load_tipi($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"",
        "liz_data"=>""
    );

    $this->form->load_expo($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"liz_exec",
        "liz_data"=>"liz_data"
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
                "default"=>"revi",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        ),
        "liz_data"=>array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"date",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>date('Y-m-t'),
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array(
                "font-size"=>"1.2em;"
            )
        )

    );

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
                                "label"=>"Scadenza:",
                                "sub"=>"",
                                "map"=>"liz_data",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"27%;",
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