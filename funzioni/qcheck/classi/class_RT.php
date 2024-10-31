<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.9','RT');

        //////////////////////////////////////   
        //completamento COMMON
        $this->common['expo']=array(
            "qc_reparto"=>""
        );
        
        $this->common['conv']=array(
            "qc_reparto"=>"officina"
        );

        /////////////////////////////////////
        //definizione UNCOMMON

        $this->uncommon['fields']=array(
            "qc_openType"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "qc_check"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "qc_today"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "qc_openType"=>"none",
            "qc_check"=>"none",
            "qc_today"=>"none"
        );

        $this->uncommon['expo']=array(
            "qc_openType"=>"",
            "qc_check"=>"",
            "qc_today"=>""
        );

        $this->uncommon['conv']=array(
            "qc_openType"=>"qc_openType",
            "qc_check"=>"qc_check",
            "qc_today"=>"qc_today"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "qc_openType"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"inserimento",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "qc_check"=>array(
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
            "qc_today"=>array(
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

        //se è stata impostata un'officina di riferimento
        if ($this->common['mappa']['officina']['prop']['default']!="") {

            //carica il JS che esegue il ribbon
            ob_start();
                include ('classi/class_'.$this->classe.'.js');
            $this->addJS( ob_get_clean() );

        };

    }
?>