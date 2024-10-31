<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.3','RC');

        $this->common['expo']=array(
            "officina"=>""
        );
        
        $this->common['conv']=array(
            "officina"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "cir_tt"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"cir_ragsoc","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cir_ragsoc"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"cir_tt","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )   
        );

        $this->uncommon['tipi']=array(
            "cir_tt"=>"none",
            "cir_ragsoc"=>"none"
        );

        $this->uncommon['expo']=array(
            "cir_tt"=>"",
            "cir_ragsoc"=>""
        );

        $this->uncommon['conv']=array(
            "cir_tt"=>"cir_tt",
            "cir_ragsoc"=>"cir_ragsoc"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "cir_tt"=>array(
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
            "cir_ragsoc"=>array(
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
            )
        );
        
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>