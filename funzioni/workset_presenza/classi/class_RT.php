<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.4','RT');

        $this->common['expo']=array(
            "tpo_reparto"=>""
        );
        
        $this->common['conv']=array(
            "tpo_reparto"=>"officina"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "tpo_macroreparto"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tpo_today"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tpo_coll"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "tpo_macroreparto"=>"none",
            "tpo_today"=>"none",
            "tpo_coll"=>"none"
        );

        $this->uncommon['expo']=array(
            "tpo_macroreparto"=>"",
            "tpo_today"=>"",
            "tpo_coll"=>""
        );

        $this->uncommon['conv']=array(
            "tpo_macroreparto"=>"tpo_macroreparto",
            "tpo_today"=>"tpo_today",
            "tpo_coll"=>"tpo_coll"
        );

        $this->uncommon['mappa']=array(
            "tpo_macroreparto"=>array(
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
            "tpo_today"=>array(
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
            "tpo_coll"=>array(
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

        //##########################################
        //valorizzare MACROREPARTO in base al parametro OFFICINA passato
        $this->galileo->getReparto($this->common['mappa']['officina']['prop']['default']);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $this->uncommon['mappa']['tpo_macroreparto']['prop']['default']=$row['macroreparto'];
            }
        }
        //##########################################
        
    };

    $this->closure['postApp']=function() {

        //26.02.2021 non ci sono condizioni (UNDER CONSTRUCTION)
        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>