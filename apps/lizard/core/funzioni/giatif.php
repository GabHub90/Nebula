<?php

$this->closure['setStruttura']=function() {

    $a=array(
        "liz_exec"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_mag"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_cod"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_da"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_a"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        )
    );

    $this->form->add_fields($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"none",
        "liz_mag"=>"none",
        "liz_cod"=>"none",
        "liz_da"=>"none",
        "liz_a"=>"none"
    );

    $this->form->load_tipi($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"",
        "liz_mag"=>"",
        "liz_cod"=>"",
        "liz_da"=>"",
        "liz_a"=>""
    );

    $this->form->load_expo($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"liz_exec",
        "liz_mag"=>"liz_mag",
        "liz_cod"=>"liz_cod",
        "liz_da"=>"liz_da",
        "liz_a"=>"liz_a"
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
                "default"=>"giatif",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        ),
        "liz_mag"=>array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(
                    "01"=>array("testo"=>"VGI Pesaro","disabled"=>false),
                    "G1"=>array("testo"=>"GAR Pesaro","disabled"=>false)
                ),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array(
                "font-size"=>"1.2em;"
            )
        ),
        "liz_cod"=>array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(
                    "27101981"=>array("testo"=>"Olio motore","disabled"=>false),
                    "27101999"=>array("testo"=>"Olio cambio","disabled"=>false)
                ),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array(
                "font-size"=>"1.2em;"
            )
        ),
        "liz_da"=>array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"date",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>date('Y-m-d'),
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array(
                "font-size"=>"1.2em;"
            )
        ),
        "liz_a"=>array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"date",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>date('Y-m-d'),
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
                                "label"=>"Magazzino:",
                                "sub"=>"",
                                "map"=>"liz_mag",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"20%;",
                                    "padding"=>"3px;",
                                    "box-sizing"=>"border-box;"
                                )
                            ),
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"Codice:",
                                "sub"=>"",
                                "map"=>"liz_cod",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"20%;",
                                    "padding"=>"3px;",
                                    "box-sizing"=>"border-box;"
                                )
                            ),
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"Da:",
                                "sub"=>"",
                                "map"=>"liz_da",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"20%;",
                                    "padding"=>"3px;",
                                    "box-sizing"=>"border-box;"
                                )
                            ),
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"A:",
                                "sub"=>"",
                                "map"=>"liz_a",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"20%;",
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