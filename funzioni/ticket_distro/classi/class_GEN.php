<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'GEN');

        $this->common['expo']=array(
            "officina"=>""
        );
        
        $this->common['conv']=array(
            "officina"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "categoria"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "categoria"=>"none"
        );

        $this->uncommon['expo']=array(
            "categoria"=>""
        );

        $this->uncommon['conv']=array(
            "categoria"=>"categoria"
        );

        $this->uncommon['mappa']=array(
            "categoria"=>array(
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

        $tk=new ermesTicket($this->galileo);

        if ($temp=$tk->getCategoria('VGM','DIS')) {
            $this->uncommon['mappa']['categoria']['prop']['options']['DIS']=array(
                "disabled"=>false,
                "reparto"=>"VGM",
                "titolo"=>$temp['titolo'],
                "tipo"=>"creazione"
            );
        }

    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>