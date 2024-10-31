<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,$this->id->getAppVersion(),'INF');

        $this->common['expo']=array(
            "comest_commessa"=>"",
            "comest_tt"=>""
        );
        
        $this->common['conv']=array(
            "comest_commessa"=>"comest_commessa",
            "comest_tt"=>"comest_tt"
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "comest_fornitore"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "comest_da"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "comest_a"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "comest_fornitore"=>"none",
            "comest_da"=>"none",
            "comest_a"=>"none"
        );

        $this->uncommon['expo']=array(
            "comest_fornitore"=>"",
            "comest_da"=>"",
            "comest_a"=>""
        );

        $this->uncommon['conv']=array(
            "comest_fornitore"=>"comest_fornitore",
            "comest_da"=>"comest_da",
            "comest_a"=>"comest_a"
        );

        $this->uncommon['mappa']=array(
            "comest_fornitore"=>array(
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
            ),
            "comest_da"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"date",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "comest_a"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"date",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );

        $this->galileo->executeSelect('comest','COMEST_fornitori','','');
        if ( $result=$this->galileo->getResult() ) {
            $fetID=$this->galileo->preFetch('comest');
            while ($row=$this->galileo->getFetch('comest',$fetID)) {
                $this->uncommon['mappa']['comest_fornitore']['prop']['options'][$row['ID']]=$row;
            }
        }

    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }

?>