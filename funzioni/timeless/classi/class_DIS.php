<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'DIS');

        $this->common['expo']=array(
            "officina"=>""
        );
        
        $this->common['conv']=array(
            "officina"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "time_today"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "time_today"=>"none"
        );

        $this->uncommon['expo']=array(
            "time_today"=>""
        );

        $this->uncommon['conv']=array(
            "time_today"=>"time_today"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "time_today"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
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