<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'3.0.9','TDD');

        $this->common['expo']=array(
            "tpo_macroreparto"=>"",
            "tpo_today"=>"",
            "tpo_reparto"=>"",
            "tpo_coll"=>"",
        );
        
        $this->common['conv']=array(
            "tpo_macroreparto"=>"macroreparto",
            "tpo_today"=>"today",
            "tpo_reparto"=>"reparto",
            "tpo_coll"=>"coll"

        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "divo"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "divo"=>"none"
        );

        $this->uncommon['expo']=array(
            "tpo_divo"=>""
        );

        $this->uncommon['conv']=array(
            "tpo_divo"=>"divo"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "divo"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"presenza",
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