<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.4','RT');

        $this->common['expo']=array(
            "wsp_officina"=>""
        );
        
        $this->common['conv']=array(
            "wsp_officina"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "visuale"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "wsp_tecnico"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "wsp_timb"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );
        
        $this->uncommon['tipi']=array(
            "visuale"=>"none",
            "wsp_tecnico"=>"none",
            "wsp_timb"=>"none"
        );

        $this->uncommon['expo']=array(
            "visuale"=>"",
            "wsp_tecnico"=>"",
            "wsp_timb"=>""
        );
        
        $this->uncommon['conv']=array(
            "visuale"=>"visuale",
            "wsp_tecnico"=>"wsp_tecnico",
            "wsp_timb"=>"wsp_timb"
        );

        $this->uncommon['mappa']=array(
            "visuale"=>array(
                "prop"=>array(
                    "input"=>"radio",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "wsp_tecnico"=>array(
                "prop"=>array(
                    "input"=>"hidden",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "wsp_timb"=>array(
                "prop"=>array(
                    "input"=>"hidden",
                    "tipo"=>"",
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

        //26.02.2021 non ci sono condizioni (UNDER CONSTRUCTION)
        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>