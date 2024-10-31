<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.3','Officina');

        $this->common['expo']=array(
            "gdm_tt"=>"",
            "gdm_dmstt"=>"",
            "gdm_telaio"=>"",
            "gdm_dms"=>"",
            "gdm_pratica"=>""
        );
        
        $this->common['conv']=array(
            "gdm_tt"=>"gdm_tt",
            "gdm_dmstt"=>"gdm_dmstt",
            "gdm_telaio"=>"gdm_telaio",
            "gdm_dms"=>"gdm_dms",
            "gdm_pratica"=>"gdm_pratica"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "gdm_ambito"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
           "gdm_ambito"=>"none"
        );

        $this->uncommon['expo']=array(
            "gdm_ambito"=>""
        );

        $this->uncommon['conv']=array(
           "gdm_ambito"=>"gdm_ambito"
        );

        $this->uncommon['mappa']=array(
            "gdm_ambito"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"gdmr",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
        );

    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>