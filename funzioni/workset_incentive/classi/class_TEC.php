<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'TEC');

        $this->common['expo']=array(
            "macroreparto"=>""
        );
        
        $this->common['conv']=array(
            "macroreparto"=>"macroreparto"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "reparto"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "ctv_openType"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "ctv_panorama"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
             "ctv_logged"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "reparto"=>"none",
            "ctv_openType"=>"none",
            "ctv_panorama"=>"none",
            "ctv_logged"=>"none"
        );

        $this->uncommon['expo']=array(
            "ctv_reparto"=>"",
            "ctv_openType"=>"",
            "ctv_panorama"=>"",
            "ctv_logged"=>""
        );

        $this->uncommon['conv']=array(
            "ctv_reparto"=>"reparto",
            "ctv_openType"=>"ctv_openType",
            "ctv_panorama"=>"ctv_panorama",
            "ctv_logged"=>"ctv_logged"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "reparto"=>array(
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
            ),
            "ctv_openType"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"analisi",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "ctv_panorama"=>array(
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
            ),
            "ctv_logged"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->id->getCollID(),
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );

        $temprep=$this->id->getReparto('S');

        $this->galileo->getReparti("","");
        if ( $result=$this->galileo->getResult() ) {
            
            $fetID=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                if (array_key_exists($row['reparto'],$temprep)) {
                    $this->uncommon['mappa']['reparto']['prop']['options'][$row['reparto']]=$row;
                }
            }
        }
        
    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>