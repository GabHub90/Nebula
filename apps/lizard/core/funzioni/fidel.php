<?php

$this->closure['setStruttura']=function() {

    $a=array(
        "liz_exec"=>array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        ),
        "liz_stato"=>array(
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
        "liz_stato"=>"none",
        "liz_da"=>"none",
        "liz_a"=>"none"
    );

    $this->form->load_tipi($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"",
        "liz_stato"=>"",
        "liz_da"=>"",
        "liz_a"=>""
    );

    $this->form->load_expo($a);

    ///////////////////////////////////////////////

    $a=array(
        "liz_exec"=>"liz_exec",
        "liz_stato"=>"liz_stato",
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
                "default"=>"fidel",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        ),
        "liz_stato"=>array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(
                    "tutti"=>array(
                        "testo"=>"Tutti",
                        "disabled"=>false
                    ),
                    "aperto"=>array(
                        "testo"=>"Aperti",
                        "disabled"=>false
                    ),
                    "chiuso"=>array(
                        "testo"=>"Chiusi",
                        "disabled"=>false
                    ),
                    "scaduto"=>array(
                        "testo"=>"Scaduti",
                        "disabled"=>false
                    ),
                    "annullato"=>array(
                        "testo"=>"Annullati",
                        "disabled"=>false
                    )
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
                                "label"=>"Stato:",
                                "sub"=>"",
                                "map"=>"liz_stato",
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
                                "label"=>"Scadenza Da:",
                                "sub"=>"",
                                "map"=>"liz_da",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"27%;",
                                    "padding"=>"3px;",
                                    "box-sizing"=>"border-box;"
                                )
                            ),
                            array(
                                "extra"=>"",
                                "prefix"=>"",
                                "label"=>"Scadenza A:",
                                "sub"=>"",
                                "map"=>"liz_a",
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
                            ),
                            array(
                                "ID"=>"liz_scrivi_app",
                                "extra"=>"app",
                                "prefix"=>"",
                                "label"=>"",
                                "testo"=>"",
                                "disabled"=>false,
                                "sub"=>"",
                                "map"=>"",
                                "css"=>array(
                                    "position"=>"relative;",
                                    "display"=>"inline-block;",
                                    "width"=>"10%;",
                                    "text-align"=>"center;",
                                    "height"=>"53%;"
                                ),
                                "extraCSS"=>array(
                                    "width"=>"45px;",
                                    "position"=>"relative;",
                                    "top"=>"30%;",
                                    "cursor"=>"pointer;"
                                ),
                                "extraSRC"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/apps/lizard/img/voucher.png",
                                "extraCLICK"=>"Voucher",
                                "extraPARAM"=>base64_encode(json_encode(array("width"=>1150,"height"=>450))),
                                "extraURL"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/core/fidel/fidel_interface.php"
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