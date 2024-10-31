<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.3','RC');

        $this->common['expo']=array(
            "sto_officina"=>""
        );
        
        $this->common['conv']=array(
            "sto_officina"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "sto_tt"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>"sto_modello"),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "sto_modello"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"sto_marca","anxor"=>"sto_tt"),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "sto_marca"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"sto_modello","anxor"=>"sto_tt"),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "sto_ambito"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "sto_tt"=>"none",
            "sto_modello"=>"none",
            "sto_marca"=>"none",
            "sto_ambito"=>"none"
        );

        $this->uncommon['expo']=array(
            "sto_tt"=>"",
            "sto_modello"=>"",
            "sto_marca"=>"",
            "sto_ambito"=>""
        );

        $this->uncommon['conv']=array(
            "sto_tt"=>"sto_tt",
            "sto_modello"=>"sto_modello",
            "sto_marca"=>"sto_marca",
            "sto_ambito"=>"sto_ambito"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "sto_tt"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "sto_modello"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "sto_marca"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(
                        "A"=>"Audi",
                        "C"=>"Skoda",
                        "N"=>"Vic",
                        "P"=>"Porsche",
                        "S"=>"Seat",
                        "V"=>"Volkswagen"
                    ),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "sto_ambito"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"standard",
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );
        
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>