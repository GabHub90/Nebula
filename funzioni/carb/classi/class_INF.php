<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'INF');

        $this->common['expo']=array(
        );
        
        $this->common['conv']=array(
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "carb_reparto"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "carb_reparto"=>"none"
        );

        $this->uncommon['expo']=array(
           "carb_reparto"=>""
        );

        $this->uncommon['conv']=array(
           "carb_reparto"=>"carb_reparto"
        );

        $this->uncommon['mappa']=array(
            "carb_reparto"=>array(
                "prop"=>array(
                    "input"=>"select",
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

        //lettura TUTTI i reparti da GALILEO
        $this->galileo->getReparti("","");
        if ( $result=$this->galileo->getResult() ) {
            
            $fetID=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $this->uncommon['mappa']['carb_reparto']['prop']['options'][$row['reparto']]=$row;
            }
        }

    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>