<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,10);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'TEC');

        $this->common['expo']=array(
            "c2r_macroreparto"=>""
        );
        
        $this->common['conv']=array(
            "c2r_macroreparto"=>"macroreparto"
        );

        $this->common['mappa']=array(
            "macroreparto"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(
                        "S"=>"S - Service Officine"
                    ),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>true
                ),
                "css"=>array()
            )
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "c2rConfig"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );
        
        $this->uncommon['tipi']=array(
            "c2rConfig"=>"none"
        );

        $this->uncommon['expo']=array(
            "c2rConfig"=>""
        );
        
        $this->uncommon['conv']=array(
            "c2rConfig"=>"c2rConfig"
        );

        $this->uncommon['mappa']=array(
            "c2rConfig"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"personale",
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );
        
    };

    $this->closure['postApp']=function() {

        //26.02.2021 non ci sono condizioni (UNDER CONSTRUCTION)
        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>