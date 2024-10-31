<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,13);
        $this->ribbon->setTitle($this->appTag,'0.7','TDD');

        $this->common['expo']=array(
            "qt_macroreparto"=>"",
            "qt_today"=>"",
            "qt_reparto"=>""
        );
        
        $this->common['conv']=array(
            "qt_macroreparto"=>"macroreparto",
            "qt_today"=>"today",
            "qt_reparto"=>"reparto"
        );

        ///////////////////////////////////////
        //SET UNCOMMON

        $this->uncommon['fields']=array(
            "qt_openType"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "qt_date"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "qt_openType"=>"none",
            "qt_date"=>"none"
        );

        $this->uncommon['expo']=array(
            "qt_openType"=>"",
            "qt_date"=>""
        );

        $this->uncommon['conv']=array(
            "qt_openType"=>"qt_openType",
            "qt_date"=>"qt_date"
        );

        //###############################################################

        $this->uncommon['mappa']=array(
            "qt_openType"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"A",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "qt_date"=>array(
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