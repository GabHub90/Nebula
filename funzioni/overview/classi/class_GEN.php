<?php
    $this->closure['initApp']=function() {

        $this->ribbon=new nebulaRibbon($this->ribbonID,100,12);
        $this->ribbon->setTitle($this->appTag,'0.2','');

        $this->ribbon->setClass('nebulaNoBorderRibbon');

        //////////////////////////////////////
        //completamento COMMON
        $this->common['expo']=array(
            "ov_reparto"=>""
        );
        
        $this->common['conv']=array(
            "ov_reparto"=>"reparto"
        );    

    };

    $this->closure['postApp']=function() {

        //26.02.2021 non ci sono condizioni (UNDER CONSTRUCTION)
        ob_start();
            include ('classi/class_'.$this->classe.'.js');
        $this->addJS( ob_get_clean() );

    }
?>