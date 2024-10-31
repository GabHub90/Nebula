<?php

    $this->closure['initApp']=function() {

        include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/lizard/core/vendor_ribbon.php');

        //echo json_encode($lizardRibbon);

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.5','RV');

        $this->common['expo']=array(
           
        );
        
        $this->common['conv']=array(
           
        );

        ///////////////////////////////////////
        //SET UNCOMMON
        $this->uncommon['fields']=array(
            "liz_function"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->uncommon['tipi']=array(
            "liz_function"=>"none"
           
        );

        $this->uncommon['expo']=array(
           "liz_function"=>""
        );

        $this->uncommon['conv']=array(
           "liz_function"=>"liz_function"
        );

        $this->uncommon['mappa']=array(
            "liz_function"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$lizardRibbon,
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            )
        );

        //echo json_encode( $this->uncommon['mappa']['liz_function']);

    };

    $this->closure['postApp']=function() {

        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    };


?>