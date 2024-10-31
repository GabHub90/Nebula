<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.8','TDD');

        $this->common['expo']=array(
            "bgc_macroreparto"=>"",
            "bgc_today"=>"",
            "bgc_reparto"=>""
        );
        
        $this->common['conv']=array(
            "bgc_macroreparto"=>"macroreparto",
            "bgc_today"=>"today",
            "bgc_reparto"=>"reparto"

        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "bgc_tutti"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "bgc_tutti"=>"none"
        );

        $this->uncommon['expo']=array(
            "bgc_tutti"=>""
        );

        $this->uncommon['conv']=array(
            "bgc_tutti"=>"bgc_tutti"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "bgc_tutti"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"0",
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